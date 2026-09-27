<?php

namespace App\Libraries;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Model;

/**
 * Server-side processing untuk DataTables.
 *
 * DataTables mengirim parameter seperti:
 *   draw, start, length, search[value],
 *   order[i][column], order[i][dir],
 *   columns[i][data], columns[i][search][value]
 *
 * Nama kolom yang datang dari client TIDAK PERNAH dipakai langsung di SQL.
 * Yang dipakai hanya daftar putih (allowlist) yang Didaftarkan lewat indeks
 * kolom, jadi nama kolom karangan dari client diabaikan diam-diam.
 *
 * Contoh pemakaian:
 *
 *   $table = new DataTableServer($this->request, $this->response, $model, [
 *       'columns'   => ['full_name', 'username', 'email', 'status', 'created_at'],
 *       'needed'    => ['id'],
 *       'orderable' => [0, 1, 2, 3, 4],
 *       'defaultOrder' => [['column' => 'created_at', 'dir' => 'DESC']],
 *       'map'       => static fn (array $row): array => [
 *           'full_name'  => $row['full_name'],
 *           'username'   => $row['username'],
 *           'email'      => $row['email'],
 *           'status'     => $row['status'],
 *           'created_at' => $row['created_at'],
 *           'id'         => $row['id'],
 *       ],
 *   ]);
 *
 *   return $table->respond();
 */
class DataTableServer
{
    protected RequestInterface $request;

    protected ResponseInterface $response;

    protected Model $model;

    /**
     * Nama kolom DB yang boleh dipakai, urut sesuai indeks kolom tabel.
     *
     * @var list<string>
     */
    protected array $columns = [];

    /**
     * Kolom tambahan yang perlu di-select tapi tidak ditampilkan sebagai kolom
     * tabel (mis. `id` untuk tombol action).
     *
     * @var list<string>
     */
    protected array $needed = [];

    /**
     * Ekspresi SELECT tambahan (mis. subquery), ditulis sebagai string SQL
     * mentah. Berasal dari config server, BUKAN dari client — jadi tidak
     * membuka celah injeksi seperti allowlist `columns`.
     *
     * Dipakai untuk kolom hitung yang tidak ada sebagai kolom tabel.
     *
     * @var list<string>
     */
    protected array $extraSelect = [];

    /** @var list<int> indeks kolom yang boleh dicari */
    protected array $searchable = [];

    /** @var list<int> indeks kolom yang boleh diurutkan */
    protected array $orderable = [];

    /**
     * Urutan default kalau client tidak mengirim parameter order.
     *
     * @var list<array{column: string, dir: string}>
     */
    protected array $defaultOrder = [];

    /**
     * Memetakan satu baris DB menjadi objek data untuk DataTables.
     *
     * WAJIB mengembalikan array BERKUNCI (bukan list). Alasannya: DataTables
     * dengan server-side masuk looping tak terbatas kalau ada kolom
     * `data: null` sementara data-nya berbentuk array. Objek berkunci
     * menghindari itu dan jauh lebih enak dibaca.
     *
     * Nilai harus data polos (tanpa HTML) — rendering HTML dilakukan di
     * client supaya escaping-nya jelas dan tidak bisa bocor lewat JSON.
     *
     * @var callable(array): array<string, string|null>
     */
    protected $mapper;

    /**
     * Constrain tambahan, mis. ->where('application_id', $id).
     *
     * @var callable|null
     */
    protected $constraints;

    /** cache hasil columnMap() */
    protected ?array $columnMap = null;

    /**
     * @param array{
     *     columns: list<string>,
     *     needed?: list<string>,
     *     extraSelect?: list<string>,
     *     searchable?: list<int>,
     *     orderable?: list<int>,
     *     defaultOrder?: list<array{column: string, dir: string}>,
     *     constraints?: callable|null,
     *     map: callable(array): array<string, mixed>
     * } $config
     */
    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        Model $model,
        array $config
    ) {
        $this->request  = $request;
        $this->response = $response;
        $this->model    = $model;

        $this->columns      = $config['columns'];
        $this->needed       = $config['needed'] ?? [];
        $this->extraSelect  = $config['extraSelect'] ?? [];
        $this->searchable   = $config['searchable'] ?? array_keys($this->columns);
        $this->orderable    = $config['orderable'] ?? array_keys($this->columns);
        $this->defaultOrder = $config['defaultOrder'] ?? [];
        $this->constraints  = $config['constraints'] ?? null;
        $this->mapper       = $config['map'];
    }

    /**
     * Balas JSON untuk DataTables.
     *
     * `length = -1` (atau "All") berarti tanpa pagination, jadi semua baris
     * yang cocok filter dikirim. Ini yang dipakai tombol export supaya file
     *-nya berisi seluruh hasil, bukan cuma satu halaman.
     */
    public function respond(): ResponseInterface
    {
        $recordsTotal = $this->baseQuery()->countAllResults();

        // Builder terpisah untuk menghitung, karena countAllResults() mereset
        // kondisi WHERE di builder itu. Kalau builder yang sama lalu dipakai
        // lagi untuk ambil baris, filternya ikut hilang — akibatnya
        // recordsFiltered benar tapi data yang dikirim tidak terfilter.
        $countBuilder = $this->baseQuery();
        $this->applySearch($countBuilder);
        $recordsFiltered = $countBuilder->countAllResults();

        $builder = $this->baseQuery();
        $this->applySearch($builder);
        $this->applyOrder($builder);

        $rows = $this->selectRows($builder, $this->length(), $this->start());

        return $this->response
            ->setJSON([
                'draw'            => $this->draw(),
                'recordsTotal'    => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data'            => $this->mapRows($rows),
            ])
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    /**
     * Query builder BARU dengan batasan dasar (belum search & order).
     *
     * Penting: setiap pemanggilan harus mengembalikan builder baru.
     * Model::builder() menyimpan instance-nya, jadi kalau dipakai dua kali
     * builder yang sama akan dipakai lagi — padahal countAllResults() sudah
     * mengubah state-nya (mis. orderBy jadi null), dan itu bisa membuat
     * query berikutnya error.
     */
    protected function baseQuery()
    {
        $builder = db_connect($this->dbGroup())->table($this->model->getTable());

        $this->applyConstraints($builder);

        return $builder;
    }

    /**
     * Grup koneksi yang dipakai model.
     */
    protected function dbGroup(): string
    {
        $group = $this->model->DBGroup ?? 'default';

        return $group === '' ? 'default' : $group;
    }

    /**
     * Titik ekstensi untuk constrain tambahan, atau diisi lewat config
     * 'constraints' (mis. filter application_id).
     */
    protected function applyConstraints($builder): void
    {
        if ($this->constraints !== null) {
            ($this->constraints)($builder);
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function selectRows($builder, ?int $limit, int $offset): array
    {
        $builder->select($this->selectColumns(), false);

        if ($limit !== null) {
            $builder->limit($limit, $offset ?? 0);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Daftar SELECT. Nama kolom sudah aman (allowlist server), sedangkan
     * extraSelect adalah ekspresi mentah dari config server.
     *
     * @return list<string>
     */
    protected function selectColumns(): array
    {
        $columns = $this->columns;

        foreach ($this->needed as $column) {
            if (! in_array($column, $columns, true)) {
                $columns[] = $column;
            }
        }

        foreach ($this->extraSelect as $expression) {
            $columns[] = $expression;
        }

        return $columns;
    }

    /**
     * @param  list<array<string, mixed>> $rows
     * @return list<array<string, mixed>>
     */
    protected function mapRows(array $rows): array
    {
        $mapper = $this->mapper;

        return array_map(
            static fn (array $row): array => $mapper($row),
            $rows
        );
    }

    /**
     * Searching global + per kolom, hanya untuk kolom yang ada di allowlist.
     */
    /**
     * Escape karakter wildcard LIKE.
     *
     * CodeIgniter 4.7 menulis `ESCAPE '!'` di SQL-nya, TAPI tidak meng-escape
     * nilai yang dikirim — sehingga pencarian `%` menjadi `LIKE '%%%'` yang
     * match semua baris. Escaping dilakukan manual di sini memakai karakter
     * escape yang sama dengan yang dipakai CI4, yaitu `!`.
     */
    protected function escapeLike(string $value): string
    {
        return str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $value);
    }

    protected function applySearch($builder): void
    {
        $global = $this->searchTerm();
        $map    = $this->columnMap();

        $perColumn = [];

        foreach ($map as $index => $column) {
            if (! in_array($index, $this->searchable, true)) {
                continue;
            }

            $value = trim((string) ($this->columnSearch($index) ?? ''));

            if ($value !== '') {
                $perColumn[$column][] = $value;
            }
        }

        if ($global === '' && $perColumn === []) {
            return;
        }

        $builder->groupStart();

        foreach ($map as $index => $column) {
            if (! in_array($index, $this->searchable, true)) {
                continue;
            }

            if ($global !== '') {
                $builder->orLike($column, $this->escapeLike($global), 'both', true, true);
            }

            foreach ($perColumn[$column] ?? [] as $value) {
                $builder->orLike($column, $this->escapeLike($value), 'both', true, true);
            }
        }

        $builder->groupEnd();
    }

    /**
     * Ordering dari client, dibatasi allowlist. Kalau client tidak mengirim
     * order, dipakai defaultOrder.
     */
    protected function applyOrder($builder): void
    {
        $map    = $this->columnMap();
        $order  = $this->request->getGet('order');
        $applied = false;

        if (is_array($order)) {
            foreach ($order as $item) {
                if (! is_array($item) || ! isset($item['column'], $item['dir'])) {
                    continue;
                }

                $index = (int) $item['column'];

                if (! in_array($index, $this->orderable, true) || ! isset($map[$index])) {
                    continue;
                }

                $dir = strtoupper((string) $item['dir']) === 'ASC' ? 'ASC' : 'DESC';

                $builder->orderBy($map[$index], $dir, false);
                $applied = true;
            }
        }

        if ($applied) {
            return;
        }

        foreach ($this->defaultOrder as $rule) {
            $column = $rule['column'];

            if (! in_array($column, $this->columns, true)) {
                continue;
            }

            $builder->orderBy(
                $column,
                strtoupper($rule['dir']) === 'ASC' ? 'ASC' : 'DESC',
                false
            );
        }
    }

    /**
     * Indeks kolom dari client => nama kolom DB, hanya yang ada di allowlist.
     * Indeks yang tidak dikenal dibuang.
     *
     * Kalau client tidak mengirim kolom sama sekali, allowlist dibalik
     * posisinya supaya endpoint tetap bisa dipakai untuk export.
     *
     * @return array<int, string>
     */
    protected function columnMap(): array
    {
        if ($this->columnMap !== null) {
            return $this->columnMap;
        }

        $map   = [];
        $index = 0;

        $columns = $this->request->getGet('columns');

        if (is_array($columns)) {
            while (isset($columns[$index])) {
                $data = is_array($columns[$index]) ? $columns[$index] : [];
                $name = (string) ($data['data'] ?? '');

                if ($name !== '' && in_array($name, $this->columns, true)) {
                    $map[$index] = $name;
                }

                $index++;
            }
        }

        return $this->columnMap = ($map === [] ? array_flip($this->columns) : $map);
    }

    protected function columnSearch(int $index): ?string
    {
        $columns = $this->request->getGet('columns');

        if (! is_array($columns) || ! isset($columns[$index]['search']['value'])) {
            return null;
        }

        return (string) $columns[$index]['search']['value'];
    }

    /**
     * Isi pencarian global dari `search[value]`.
     *
     * Penting: getGet('search') mengembalikan ARRAY (['value' => ..., 'regex' => ...]),
     * jadi tidak boleh langsung di-cast ke string — hasilnya jadi "Array".
     */
    protected function searchTerm(): string
    {
        $search = $this->request->getGet('search');

        if (is_array($search)) {
            return trim((string) ($search['value'] ?? ''));
        }

        if (is_string($search)) {
            return trim($search);
        }

        return '';
    }

    protected function draw(): int
    {
        return (int) ($this->request->getGet('draw') ?? 0);
    }

    protected function start(): int
    {
        return max(0, (int) ($this->request->getGet('start') ?? 0));
    }

    /** null berarti "tampilkan semua" (length = -1 / "All"). */
    protected function length(): ?int
    {
        $length = $this->request->getGet('length');

        if ($length === null || $length === '' || (int) $length === -1) {
            return null;
        }

        return max(1, (int) $length);
    }
}
