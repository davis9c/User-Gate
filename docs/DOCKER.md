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

Template `env` sudah memuat tiga preset konfigurasi. Pilih salah satu, lalu
samakan nilai `CI_ENVIRONMENT`, `app.baseURL`, `COOKIE_SECURE`, dan
`PROXY_IPS` pada `.env`:

| Preset | `CI_ENVIRONMENT` | `app.baseURL` | `COOKIE_SECURE` | `PROXY_IPS` |
|---|---|---|---|---|
| Development (lokal, HTTP) | `development` | `http://localhost:8081/` | `false` | kosong |
| Production Cloudflared (publik HTTPS, origin HTTP) | `production` | `https://usergate.sandalgurun.web.id/` | `true` | IP host cloudflared, mis. `10.10.10.15` |
| Production jaringan lokal tanpa Cloudflared (HTTP) | `production` | `http://<IP-SERVER>:8081/` | `false` | kosong |

Sisanya (`APP_PORT` dan bagian `DATABASE`) diisi sesuai server.

Konfigurasi penting:

- `APP_PORT`: port host yang dipakai untuk membuka aplikasi.
- `app.baseURL`: URL publik aplikasi, wajib memakai trailing slash.
- `COOKIE_SECURE`: `true` untuk production (termasuk Cloudflared) agar cookie
  sesi memakai atribut `Secure`. Gunakan `false` hanya bila aplikasi memang
  diakses langsung melalui HTTP (development atau jaringan lokal).
- `PROXY_IPS`: daftar alamat IP atau CIDR reverse proxy yang tepercaya,
  dipisahkan koma. Wajib diisi pada preset Cloudflared agar header
  `X-Forwarded-Proto: https` dipercaya dan cookie sesi `Secure` tidak ditolak
  (error 500 "Attempted to send a secure cookie over a non-secure connection").
  Isi dengan IP yang terlihat oleh container, misalnya IP host yang menjalankan
  `cloudflared`. Kosongkan untuk preset development dan jaringan lokal.
- `database.default.hostname`: alamat server MySQL eksternal.
- `database.default.port`: port MySQL, biasanya 3306.

Pastikan server Docker dapat terhubung ke database. Jangan gunakan localhost jika database berjalan di luar container. Untuk MySQL di host Windows atau macOS, gunakan host.docker.internal.

Jangan percaya header `X-Forwarded-For`/`X-Forwarded-Proto` dari semua IP.
Daftarkan hanya alamat proxy yang benar-benar tepercaya melalui `PROXY_IPS`.

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
