# Docker UserGate

Panduan menjalankan UserGate menggunakan Docker Compose.

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

## 2. Struktur Project

```text
User-Gate/
├── app/                  <- CodeIgniter app (Controllers, Models, Views)
├── public/               <- Document root Apache
├── docs/                 <- Dokumentasi
├── env                   <- Template environment
├── Dockerfile
├── docker-compose.yml
├── docker-entrypoint.sh
├── apache-vhost.conf
└── README.md
```

## 3. Konfigurasi environment

Buat file `.env` dari template:

```bash
cp env .env
```

Edit `.env` sesuai kebutuhan:

```text
CI_ENVIRONMENT = production
APP_PORT = 8081
app.baseURL = http://localhost:8081/
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
- `database.default.port`: port MySQL, biasanya 3306.

Pastikan server Docker dapat terhubung ke database. Jangan gunakan localhost jika database berjalan di luar container. Untuk MySQL di host Windows atau macOS, gunakan host.docker.internal.

## 4. Build dan jalankan

Dari folder root project:

```bash
docker compose up --build -d
```

Periksa status container:

```bash
docker compose ps
```

Buka aplikasi: `http://localhost:8081/`

Halaman setup: `http://localhost:8081/setup`

## 5. Log

```bash
docker compose logs -f app
docker compose logs --tail=100 app
```
