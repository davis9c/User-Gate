<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= esc($title ?? 'UserGateway') ?></title>

    <?= view('partials/theme/head') ?>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        href="https://cdn.jsdelivr.net/npm/datatables.net-bs5@3.1.2/css/dataTables.bootstrap5.min.css"
        rel="stylesheet">
</head>

<!-- bg-body, bukan bg-light: bg-light adalah abu-abu terang literal yang
     tidak berubah di mode gelap, jadi background halaman tetap terang. -->
<body class="bg-body">

<?php

/**
 * Menandai menu navbar yang sedang aktif.
 *
 * Fungsi ini mengembalikan string atribut siap pakai untuk <a>, sekaligus
 * aria-current="page" — atribut itu diminta Bootstrap 5.3 supaya item menu
 * yang sedang dibuka terbaca oleh screen reader.
 *
 * $exact = true  -> hanya cocok kalau path-nya sama persis (dipakai untuk
 *                   Dashboard, supaya tidak ikut aktif di /dashboard/users).
 * $exact = false -> cocok juga untuk sub-path, mis. /dashboard/applications/{id}
 */
$currentPath = trim(service('uri')->getPath(), '/');

$navAttrs = static function (string $path, bool $exact = false) use ($currentPath): string {
    $matched = $exact
        ? $currentPath === $path
        : ($currentPath === $path || str_starts_with($currentPath, $path . '/'));

    return $matched
        ? ' class="nav-link active" aria-current="page"'
        : ' class="nav-link"';
};

?>

    <!--
        Navbar tetap gelap di kedua mode warna (pola GitHub), jadi color
        mode-nya dikunci lewat data-bs-theme="dark" pada <nav> sendiri —
        .navbar-dark sudah deprecated di Bootstrap v5.3.

        Ini juga yang membuat dropdown user ikut gelap otomatis: .dropdown-menu
        mengambil warna dari --bs-body-bg / --bs-body-color, bukan dari
        --bs-navbar-*. Kelas .navbar-dark hanya menimpa variabel navbar,
        sehingga dropdown-nya akan tetap terang di mode gelap.
    -->
    <nav class="navbar navbar-expand-lg bg-dark border-bottom" data-bs-theme="dark">
        <div class="container">

            <a href="<?= site_url('dashboard') ?>" class="navbar-brand">
                UserGateway
            </a>

            <button
                class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarMain"
                aria-controls="navbarMain"
                aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarMain">

                <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                    <li class="nav-item">
                        <a<?= $navAttrs('dashboard', true) ?>
                            href="<?= site_url('dashboard') ?>">
                            Dashboard
                        </a>
                    </li>

                    <li class="nav-item">
                        <a<?= $navAttrs('dashboard/users') ?>
                            href="<?= site_url('dashboard/users') ?>">
                            Users
                        </a>
                    </li>

                    <li class="nav-item">
                        <a<?= $navAttrs('dashboard/applications') ?>
                            href="<?= site_url('dashboard/applications') ?>">
                            Applications
                        </a>
                    </li>

                </ul>

                <ul class="navbar-nav align-items-lg-center gap-lg-3">

                    <li class="nav-item">
                        <?= view('partials/theme/toggle', ['themeSize' => 'sm']) ?>
                    </li>

                    <li class="nav-item dropdown">
                        <a
                            class="nav-link dropdown-toggle"
                            href="#"
                            role="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <?= esc(session()->get('full_name')) ?>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a
                                    class="dropdown-item"
                                    href="<?= site_url('profile') ?>">
                                    My Profile
                                </a>
                            </li>

                            <li>
                                <a
                                    class="dropdown-item"
                                    href="<?= site_url('logout') ?>">
                                    Logout
                                </a>
                            </li>
                        </ul>
                    </li>

                </ul>

            </div>

        </div>
    </nav>

    <main class="container py-4">

        <?= $this->renderSection('content') ?>

    </main>

    <?= $this->renderSection('modals') ?>

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js">
    </script>

    <!-- Wajib: DataTables 3 core sudah bebas jQuery, TAPI extensions
         (buttons, colVis, html5) di 3.1.2 masih memakai jQuery. -->
    <script
        src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js">
    </script>

    <script
        src="https://cdn.jsdelivr.net/npm/datatables.net@3.1.2/js/dataTables.min.js">
    </script>

    <script
        src="https://cdn.jsdelivr.net/npm/datatables.net-bs5@3.1.2/js/dataTables.bootstrap5.min.js">
    </script>

    <!-- Catatan versi: paket "buttons" punya nomor versi sendiri dan TIDAK
         selalu sama dengan core. Core = 3.1.2, buttons = 4.1.2 (tag "latest"
         masing-masing). Kalau dicampur, ekstensi buttons melempar error
         "Cannot read properties of undefined (reading 'buttons')". -->
    <script
        src="https://cdn.jsdelivr.net/npm/datatables.net-buttons@4.1.2/js/dataTables.buttons.min.js">
    </script>

    <script
        src="https://cdn.jsdelivr.net/npm/datatables.net-buttons-bs5@4.1.2/js/buttons.bootstrap5.min.js">
    </script>

    <script
        src="https://cdn.jsdelivr.net/npm/datatables.net-buttons@4.1.2/js/buttons.colVis.min.js">
    </script>

    <script
        src="https://cdn.jsdelivr.net/npm/datatables.net-buttons@4.1.2/js/buttons.html5.min.js">
    </script>

    <script
        src="https://cdn.jsdelivr.net/npm/datatables.net-buttons@4.1.2/js/buttons.print.min.js">
    </script>

    <script
        src="https://cdn.jsdelivr.net/npm/jszip@3.10.1/dist/jszip.min.js">
    </script>

    <script>
        window.initDataTable = function (selector, options) {
            options = options || {};

            var defaults = {
                // Kolom Action selalu di kolom terakhir: jangan bisa diurutkan/dicari.
                columnDefs: [
                    { targets: -1, orderable: false, searchable: false }
                ],
                // Kolom Action juga tidak ikut di-export (isinya cuma tombol),
                // jadi dibatasi lewat exportOptions.columns per tombol export.
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: 'Excel',
                        titleAttr: 'Export Excel',
                        exportOptions: {
                            columns: function (idx, data, node) {
                                return idx < node.parentNode.cells.length - 1;
                            }
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        text: 'CSV',
                        titleAttr: 'Export CSV',
                        exportOptions: {
                            columns: function (idx, data, node) {
                                return idx < node.parentNode.cells.length - 1;
                            }
                        }
                    },
                    {
                        extend: 'print',
                        text: 'Print',
                        titleAttr: 'Cetak halaman',
                        exportOptions: {
                            columns: function (idx, data, node) {
                                return idx < node.parentNode.cells.length - 1;
                            }
                        }
                    },
                    {
                        extend: 'colvis',
                        text: 'Kolom',
                        titleAttr: 'Tampilkan/sembunyikan kolom'
                    }
                ],
                // Kosongkan agar urutan dari controller (created_at DESC) tetap utuh.
                order: [],
                paging: { buttons: 5 },
                layout: {
                    topStart: { pageLength: { menu: [10, 25, 50, 100] } },
                    topEnd: { search: { placeholder: 'Cari...' } },
                    top1Start: 'buttons',
                    bottomStart: 'info',
                    bottomEnd: 'paging'
                },
                language: {
                    emptyTable: 'Tidak ada data.',
                    info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                    infoEmpty: 'Tidak ada data untuk ditampilkan',
                    infoFiltered: '(disaring dari total _MAX_ data)',
                    loadingRecords: 'Memuat...',
                    processing: 'Memproses...',
                    zeroRecords: 'Tidak ada data yang cocok',
                    search: 'Cari:',
                    searchPlaceholder: 'Cari...',
                    lengthMenu: 'Tampilkan _MENU_ data per halaman',
                    paginate: {
                        first: 'Pertama',
                        last: 'Terakhir',
                        next: 'Berikutnya',
                        previous: 'Sebelumnya'
                    },
                    aria: {
                        sortAscending: ': urutkan naik',
                        sortDescending: ': urutkan turun'
                    }
                }
            };

            // Gabungkan jadi satu level supaya opsi per halaman bisa menimpa default.
            var merged = Object.assign({}, defaults, options);
            merged.layout = Object.assign({}, defaults.layout, options.layout || {});
            merged.language = Object.assign({}, defaults.language, options.language || {});

            return new DataTable(selector, merged);
        };

        /** Escape teks sebelum masuk ke innerHTML kolom. */
        window.dtEscape = function (value) {
            if (value === null || value === undefined) {
                return '';
            }

            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        };

        /**
         * Inisialisasi tabel dengan server-side processing.
         *
         * Bedanya dengan initDataTable():
         *   - data diambil dari endpoint `ajax`
         *   - tombol export mengunduh SEMUA baris yang cocok filter, bukan
         *     cuma baris yang sedang ada di DOM.
         */
        window.initServerSideTable = function (selector, options) {
            options = options || {};

            var ajaxUrl = options.ajax;

            // Kolom action tidak ikut diekspor (isinya cuma tombol), jadi
            // ambil semua kolom kecuali yang terakhir.
            var exportOptions = {
                columns: function (idx, data, node) {
                    return idx < node.parentNode.cells.length - 1;
                }
            };

            var serverOptions = Object.assign({}, options, {
                serverSide: true,
                deferRender: true,
                ajax: { url: ajaxUrl, type: 'GET' },
                buttons: [
                    { extend: 'excelHtml5', text: 'Excel', titleAttr: 'Export Excel', exportOptions: exportOptions },
                    { extend: 'csvHtml5', text: 'CSV', titleAttr: 'Export CSV', exportOptions: exportOptions },
                    { extend: 'print', text: 'Print', titleAttr: 'Cetak halaman', exportOptions: exportOptions },
                    { extend: 'colvis', text: 'Kolom', titleAttr: 'Tampilkan/sembunyikan kolom' }
                ]
            });

            var table = initDataTable(selector, serverOptions);

            makeExportDownloadEverything(table);

            return table;
        };

        /**
         * Dengan server-side, DataTables hanya mengekspor baris yang ada di
         * DOM — yaitu satu halaman saja. Supaya file berisi seluruh hasil
         * filter, page length diubah ke "All" dulu, lalu tombolnya di-klik
         * ulang setelah selesai memuat.
         *
         * Dipakai satu instance DataTables yang sama, bukan tabel kedua,
         * supaya tidak bentrok dengan internal DataTables.
         */
        function makeExportDownloadEverything(table) {
            var tableNode = table.table ? table.table().node() : null;

            if (!tableNode) {
                return;
            }

            // Tombol export berada di dalam .dt-container, sama dengan tabelnya.
            var container = tableNode.closest('.dt-container') || tableNode.parentNode;

            if (!container) {
                return;
            }

            var exporting = false;

            // Capture phase: listener ini harus jalan SEBELUM handler bawaan
            // tombol DataTables, makanya stopPropagation() di sini penting.
            container.addEventListener('click', function (event) {
                var button = event.target.closest('.buttons-excel, .buttons-csv, .buttons-print');

                if (!button || exporting) {
                    return;
                }

                // Sudah menampilkan semua baris -> biarkan apa adanya.
                if (table.page.len() === -1) {
                    return;
                }

                var previousLength = table.page.len();

                exporting = true;
                event.preventDefault();
                event.stopPropagation();

                table.one('draw', function () {
                    // Penting: page length dibiarkan -1 sampai AFTER export
                    // selesai, kalau tidak guard di atas akan menangkap lagi
                    // dan export-nya tidak pernah jalan.
                    exporting = false;
                    button.click();

                    window.setTimeout(function () {
                        if (table.page.len() === -1) {
                            table.page.len(previousLength).draw();
                        }
                    }, 400);
                });

                // draw() tanpa argumen supaya data benar-benar diambil ulang
                // dari server; draw(false) hanya redraw dari cache.
                table.page.len(-1).draw();
            }, true);
        }

        /**
         * Helper untuk form yang berada di dalam modal.
         *
         * Submit memakai fetch:
         *   - 422 + HTML form  -> isi modal diganti dengan form yang sudah
         *                         dirender ulang (nilai input ikut terisi)
         *   - sukses + options.onSuccess -> handler itu dipanggil dengan JSON
         *                         dari server, tanpa reload halaman
         *   - selain itu        -> reload halaman supaya tabel & flashdata success
         *                         ikut ter-update
         *
         * options.onSuccess dipakai untuk hasil yang hanya boleh dilihat satu
         * kali, misalnya API Key — kalau responsnya dibuang lalu halaman di
         * reload, nilainya hilang selamanya karena yang tersimpan di database
         * hanya hash-nya.
         *
         * redirect:'manual' dipakai supaya redirect dari controller tidak
         * diikuti fetch. Kalau diikuti, flashdata 'success' akan habis
         * dikonsumsi halaman yang di-fetch dan reload berikutnya tidak
         * menampilkan alert-nya.
         */
        window.initModalForm = function (modalSelector, options) {
            var modal = document.querySelector(modalSelector);

            if (!modal) {
                return;
            }

            var formWrap = modal.querySelector('[data-form-slot]');

            if (!formWrap) {
                return;
            }

            options = options || {};

            // Delegasi: bekerja untuk form yang sudah ada (create) maupun
            // form yang baru disisipkan lewat fetch (edit / reset).
            formWrap.addEventListener('submit', function (event) {
                var form = event.target.closest('form');

                if (!form) {
                    return;
                }

                event.preventDefault();
                submitModalForm(form, modal, options);
            });
        };

        window.submitModalForm = function (form, modal, options) {
            var submitBtn = modal.querySelector('[data-modal-submit]');

            options = options || {};

            if (submitBtn) {
                submitBtn.disabled = true;
            }

            // Hasil dipisah dari promise agar error saat merender form TIDAK
            // ikut masuk .catch() — kalau iya, bug UI diam-diam berubah jadi
            // submit native yang reload halaman.
            //
            // getAttribute, bukan form.action: <input name="action"> di
            // dalam form akan menyembunyikan properti form.action, sehingga
            // request dikirim ke "[object HTMLInputElement]".
            fetch(form.getAttribute('action'), {
                method: 'POST',
                body: new FormData(form),
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                redirect: 'manual',
            })
                .then(function (response) {
                    if (response.status === 422) {
                        return response.text().then(function (html) {
                            return { html: html };
                        });
                    }

                    if (options.onSuccess) {
                        // Penangan kedua pada .then() di bawah hanya untuk
                        // JSON rusak / server membalas non-JSON — tetap surut
                        // ke submit native supaya tidak menggantung.
                        return response.json().then(
                            function (data) {
                                return { success: data };
                            },
                            function () {
                                return { fallback: true };
                            }
                        );
                    }

                    return { reload: true };
                })
                .catch(function () {
                    // Hanya gagal jaringan / server tidak terjangkau.
                    return { fallback: true };
                })
                .then(function (result) {
                    if (result.reload) {
                        window.location.reload();
                        return;
                    }

                    if (result.fallback) {
                        // Dipanggil lewat prototype: field form bernama
                        // "submit" akan menyembunyikan form.submit, persis
                        // seperti field "action" menyembunyikan form.action.
                        window.HTMLFormElement.prototype.submit.call(form);
                        return;
                    }

                    // Halaman tidak di-reload pada jalur sukses, jadi tombol
                    // harus di-enable lagi supaya form bisa dikirim ulang.
                    if (submitBtn) {
                        submitBtn.disabled = false;
                    }

                    if (result.success) {
                        options.onSuccess(result.success, modal, form);
                        return;
                    }

                    replaceModalForm(modal, result.html);
                });
        };

        window.replaceModalForm = function (modal, html) {
            var parsed = new DOMParser().parseFromString(html, 'text/html');
            var form = parsed.querySelector('form');

            if (!form) {
                return;
            }

            var slot = modal.querySelector('[data-form-slot]');
            var inserted = document.importNode(form, true);

            slot.innerHTML = '';
            slot.appendChild(inserted);

            syncCsrfToken(inserted);

            var firstInvalid = slot.querySelector('.is-invalid');

            if (firstInvalid) {
                firstInvalid.focus();
            }
        };

        /**
         * Config Security.php memakai regenerate = true, jadi setiap POST
         * memutar token CSRF. Karena halaman tidak di-reload saat 422 maupun
         * saat sukses lewat options.onSuccess, form lain di halaman ini (mis.
         * modal konfirmasi toggle) akan memegang token lama dan submit
         * berikutnya ditolak. Samakan semua token dengan yang baru saja
         * datang dari server.
         *
         * 'csrf_test_name' mengikuti Security.php::$tokenName.
         */
        window.applyCsrfToken = function (value) {
            if (!value) {
                return;
            }

            document.querySelectorAll('input[name="csrf_test_name"]').forEach(function (input) {
                input.value = value;
            });
        };

        window.syncCsrfToken = function (sourceNode) {
            var source = sourceNode && sourceNode.querySelector
                ? sourceNode.querySelector('input[name="csrf_test_name"]')
                : null;

            if (!source) {
                return;
            }

            applyCsrfToken(source.value);
        };

        /**
         * Tombol submit di footer modal berada di luar <form>, jadi label dan
         * warnanya tidak ikut berubah kalau form-nya diganti — termasuk saat
         * form di-replace setelah 422.
         *
         * <form>.opt-inline lewat data-submit-label / -variant / -disabled,
         * disalin ke tombol di footer setiap kali isi slot berubah.
         */
        window.syncModalSubmit = function (modalSelector) {
            var modal = document.querySelector(modalSelector);

            if (!modal) {
                return;
            }

            var slot = modal.querySelector('[data-form-slot]');
            var button = modal.querySelector('[data-modal-submit]');

            if (!slot || !button) {
                return;
            }

            var apply = function () {
                var form = slot.querySelector('form');

                if (!form) {
                    return;
                }

                if (form.dataset.submitLabel) {
                    button.textContent = form.dataset.submitLabel;
                }

                button.className = 'btn btn-'
                    + (form.dataset.submitVariant || 'primary');

                button.disabled = form.dataset.submitDisabled === '1';
            };

            // Slot form di-innerHTML- ulang oleh loadModalForm() maupun
            // replaceModalForm(), jadi cukup amati perubahannya.
            new MutationObserver(apply).observe(slot, { childList: true });

            apply();
        };

        /**
         * Ambil isi form dari URL lalu sisipkan ke modal.
         * Dipakai untuk form Edit / Reset yang datanya per-baris.
         */
        window.loadModalForm = function (url, modalSelector) {
            var modal = document.querySelector(modalSelector);

            if (!modal) {
                return;
            }

            var slot = modal.querySelector('[data-form-slot]');

            slot.innerHTML = '<div class="modal-body text-center text-muted py-5">' +
                '<div class="spinner-border spinner-border-sm me-2" role="status"></div>Memuat...</div>';

            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status);
                    }
                    return response.text();
                })
                .then(function (html) {
                    var parsed = new DOMParser().parseFromString(html, 'text/html');
                    var form = parsed.querySelector('form');

                    if (!form) {
                        slot.innerHTML = '<div class="modal-body text-danger">Form tidak ditemukan.</div>';
                        return;
                    }

                    slot.innerHTML = '';
                    slot.appendChild(document.importNode(form, true));
                })
                .catch(function () {
                    slot.innerHTML = '<div class="modal-body text-danger">Gagal memuat form.</div>';
                });
        };

        /**
         * Salin teks ke clipboard lalu tampilkan feedback singkat.
         *
         * Clipboard API butuh konteks secure (https / localhost) dan izin
         * clipboard, jadi fallback-nya pakai elemen textarea sementara —
         *_execCommand_ deprecated tapi masih satu-satunya cara di http://.
         *
         * Dipakai oleh halaman show_key.php dan modal API Key hasil create.
         */
        window.copyToClipboard = function (text, feedbackEl, buttonEl) {
            var fallback = function () {
                var temp = document.createElement('textarea');

                temp.value = text;
                temp.setAttribute('readonly', '');
                temp.style.position = 'fixed';
                temp.style.opacity = '0';

                document.body.appendChild(temp);
                temp.select();

                var ok = false;

                try {
                    ok = document.execCommand('copy');
                } catch (error) {
                    ok = false;
                }

                document.body.removeChild(temp);

                return ok;
            };

            var onCopied = function () {
                if (buttonEl) {
                    // Label asli disimpan sekali, jadi klik kedua dalam 3
                    // detik tidak ikut menyimpan 'Tersalin!' sebagai asli.
                    if (! buttonEl.dataset.copyLabel) {
                        buttonEl.dataset.copyLabel = buttonEl.textContent;
                    }

                    buttonEl.textContent = 'Tersalin!';
                }

                if (feedbackEl) {
                    feedbackEl.hidden = false;
                }

                // Timer sebelumnya dibuang dulu — kalau tidak, timer lama
                // bisa menyembunyikan feedback dari klik yang lebih baru.
                window.clearTimeout(window.__copyFeedbackTimer);

                window.__copyFeedbackTimer = window.setTimeout(function () {
                    if (buttonEl && buttonEl.dataset.copyLabel) {
                        buttonEl.textContent = buttonEl.dataset.copyLabel;
                    }

                    if (feedbackEl) {
                        feedbackEl.hidden = true;
                    }
                }, 3000);
            };

            var onFailed = function () {
                window.alert('Gagal menyalin. Salin manual dari kolom di atas.');
            };

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(onCopied, function () {
                    if (fallback()) {
                        onCopied();
                        return;
                    }

                    onFailed();
                });

                return;
            }

            if (fallback()) {
                onCopied();
                return;
            }

            onFailed();
        };
    </script>

    <?= view('partials/theme/script') ?>

    <?= $this->renderSection('scripts') ?>

</body>

</html>
