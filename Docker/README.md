# Docker UserGate

Panduan menjalankan UserGate menggunakan Docker Compose. Provisioning database
dibahas terpisah di [Database README](../Database/README.md).

## 1. Prasyarat

Pastikan server menggunakan Linux dan memiliki akses internet untuk mengunduh image Docker.

### Ubuntu/Debian

```bash
sudo apt update
sudo apt install -y docker.io docker-compose-plugin
sudo systemctl enable --now docker
```

Periksa instalasi:

```bash
docker --version
docker compose version
```

Agar dapat menjalankan Docker tanpa `sudo`, tambahkan user ke group Docker:

```bash
sudo usermod -aG docker "$USER"
```

Logout dan login kembali setelah menjalankan perintah tersebut.

## 2. Struktur Docker

Perintah Docker dijalankan dari folder project utama, sedangkan konfigurasi Docker berada di folder ini:

```text
User-Gate/
├── App/
├── Docker/
│   ├── .env
│   ├── Dockerfile
│   ├── apache-vhost.conf
│   ├── docker-compose.yml
│   └── README.md
└── README.md
```

`Docker/.env` tidak boleh di-commit karena berisi kredensial database.

## 3. Konfigurasi environment

Masuk ke folder Docker:

```bash
cd /root/User-Gate/Docker
```

Buat file environment dari template jika belum ada:

```bash
cp ../App/.env.example .env
```

Jika template belum tersedia, buat `Docker/.env` dengan konfigurasi berikut lalu sesuaikan nilainya:

```ini
CI_ENVIRONMENT = development
APP_PORT = 8081
app.baseURL = 'http://localhost:8081/'
app.forceGlobalSecureRequests = false

database.default.hostname = <database-host>
database.default.database = <database-name>
database.default.username = <database-user>
database.default.password = <database-password>
database.default.DBDriver = MySQLi
database.default.port = 3306
```

Konfigurasi penting:

- `APP_PORT`: port host yang dipakai untuk membuka aplikasi.
- `app.baseURL`: URL publik aplikasi, wajib memakai trailing slash.
- `database.default.hostname`: alamat server MySQL eksternal.
- `database.default.port`: port MySQL, biasanya `3306`.
- `CI_ENVIRONMENT=development`: menampilkan detail error saat pengembangan.

Pastikan server Docker dapat terhubung ke database. Detail pembuatan user,
database, dan privilege ada di [Database README](../Database/README.md):

```bash
nc -vz <database-host> 3306
```

Jangan gunakan `localhost` jika database berjalan di luar container. Untuk MySQL
di host Windows atau macOS, gunakan `host.docker.internal`; untuk server lain,
gunakan hostname atau alamat IP server database. Database harus dibuat terlebih
dahulu oleh administrator; halaman `/setup` hanya menjalankan migration dan seeder.

## 4. Validasi konfigurasi

Sebelum build, validasi hasil konfigurasi Compose:

```bash
docker compose --env-file .env config
```

Perintah ini hanya membaca dan menampilkan konfigurasi. Tidak menjalankan container.

## 5. Build dan jalankan aplikasi

Dari folder `Docker/`:

```bash
docker compose --env-file .env build
docker compose --env-file .env up -d
```

Atau gunakan satu perintah:

```bash
docker compose --env-file .env up --build -d
```

Periksa status container:

```bash
docker compose --env-file .env ps
```

Buka aplikasi melalui URL yang sesuai dengan `app.baseURL`, atau langsung melalui port host:

```text
http://localhost:8081/
```

Halaman setup:

```text
http://localhost:8081/setup
```

## 6. Melihat log

Lihat log aplikasi:

```bash
docker compose --env-file .env logs -f app
```

Lihat log terakhir saja:

```bash
docker compose --env-file .env logs --tail=100 app
```

## 7. Rebuild aplikasi

Gunakan rebuild biasa setelah perubahan source atau konfigurasi Docker:

```bash
docker compose --env-file .env up --build -d
```

Gunakan `--no-cache` jika dependency atau layer Docker bermasalah:

```bash
docker compose --env-file .env build --no-cache
docker compose --env-file .env up -d
```

Rebuild penuh dengan menghentikan container terlebih dahulu:

```bash
docker compose --env-file .env down
docker compose --env-file .env build --no-cache
docker compose --env-file .env up -d
```

## 8. Menghentikan aplikasi

Menghentikan container:

```bash
docker compose --env-file .env down
```

Melihat container yang masih berjalan:

```bash
docker ps
```

## 9. Troubleshooting

### HTTP 500 secure cookie

Pastikan environment development digunakan saat mengakses melalui HTTP:

```ini
CI_ENVIRONMENT = development
app.forceGlobalSecureRequests = false
```

Konfigurasi cookie aplikasi juga harus mengizinkan HTTP pada environment development.

### Compose tidak membaca `.env`

Pastikan perintah dijalankan dari folder `Docker/` dan file berada di lokasi berikut:

```text
/root/User-Gate/Docker/.env
```

Gunakan opsi eksplisit:

```bash
docker compose --env-file /root/User-Gate/Docker/.env -f /root/User-Gate/Docker/docker-compose.yml config
```

### Database tidak dapat terhubung

Periksa hostname, port, database, user, dan password pada `Docker/.env`. Pastikan firewall database mengizinkan koneksi dari server Docker.

### Cloudflare Tunnel

Cloudflare Tunnel adalah service terpisah dari container aplikasi. Jangan menaruh perintah `docker run cloudflare/cloudflared ...` di dalam `Docker/.env`.

Perintah tunnel dijalankan di terminal atau dikelola melalui konfigurasi Compose terpisah. Token tunnel jangan ditulis di Git, README, atau file yang dibagikan.
