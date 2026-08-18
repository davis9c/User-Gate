# UserGate

UserGate adalah layanan pusat untuk mengelola identitas pengguna dan akses API. Aplikasi ini membantu tim menyediakan satu tempat untuk membuat user, mengelola status akun, membuat application/API key, dan mengamankan integrasi antar aplikasi.

## Yang dapat dilakukan

- Membuat dan mengelola user beserta password yang tersimpan sebagai hash.
- Mengelola application, API key, dan permission API per application.
- Login API dengan API key, access token, refresh token, endpoint current user, dan logout.
- Membatasi percobaan login, mencatat event audit, dan mencabut token saat logout.
- Menyediakan halaman dokumentasi API di `/api-documentation`, dengan contoh request/response dan tombol copy.

## Cara paling cepat: Docker

Prasyarat: Docker Engine dan Docker Compose.

```bash
git clone <repository-url> usergate
cd usergate
docker compose up --build -d
```

Buka [http://localhost:8080/setup](http://localhost:8080/setup), isi data Super Admin, lalu tekan **Install UserGateway**.

Pada langkah tersebut UserGate akan otomatis:

1. Memastikan database tersedia.
2. Menjalankan seluruh migration.
3. Menambahkan permission API dasar (`user.read`, `user.create`, `user.update`, dan `user.delete`).
4. Membuat akun Super Admin pertama.

Tidak perlu menjalankan `php spark migrate` atau seeder secara manual. Untuk menghentikan container:

```bash
docker compose down
```

Data MySQL tetap tersimpan di Docker volume `usergate_mysql`. Untuk menghapus data lokal sepenuhnya, jalankan `docker compose down -v`.

> Ganti password contoh di `docker-compose.yml` sebelum menjalankan aplikasi di lingkungan selain lokal.

## Instalasi tanpa Docker

Prasyarat: PHP 8.1+, Composer, MySQL 8+ (atau MariaDB setara), serta ekstensi PHP `intl`, `mbstring`, dan `mysqli`.

```bash
git clone <repository-url> usergate
cd usergate
composer install --no-dev --optimize-autoloader
cp .env.example .env
```

Ubah bagian database pada `.env` sesuai server Anda:

```ini
database.default.hostname = localhost
database.default.database = usergate
database.default.username = usergate
database.default.password = ganti_dengan_password_aman
database.default.DBDriver = MySQLi
database.default.port = 3306
```

Pastikan web server mengarah ke folder `public/`, lalu buka `https://domain-anda/setup`. Form Setup akan membuat database bila belum ada, menjalankan migration dan seeding, kemudian membuat Super Admin.

Untuk pengembangan lokal tanpa web server:

```bash
php spark serve
```

Lalu buka `http://localhost:8080/setup`.

## MySQL: user database dan privilege dasar

Jalankan sebagai MySQL administrator. Contoh ini memungkinkan UserGate membuat database otomatis apabila belum ada:

```sql
CREATE USER 'usergate'@'%' IDENTIFIED BY 'ganti_dengan_password_aman';
GRANT CREATE ON *.* TO 'usergate'@'%';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, DROP, INDEX, REFERENCES
ON usergate.* TO 'usergate'@'%';
FLUSH PRIVILEGES;
```

Jika database dibuat terlebih dahulu oleh administrator, gunakan privilege yang lebih terbatas berikut (tanpa global `CREATE`):

```sql
CREATE DATABASE usergate CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'usergate'@'%' IDENTIFIED BY 'ganti_dengan_password_aman';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, DROP, INDEX, REFERENCES
ON usergate.* TO 'usergate'@'%';
FLUSH PRIVILEGES;
```

Ganti host `%` dengan hostname atau IP aplikasi untuk produksi. Jangan memakai user `root` sebagai kredensial aplikasi.

## Setelah instalasi

1. Login ke dashboard dengan akun Super Admin yang dibuat pada Setup.
2. Buat **Application**.
3. Buat **API Key** untuk application tersebut dan atur permission-nya.
4. Gunakan halaman [API Documentation](/api-documentation) untuk contoh integrasi REST API.

Endpoint autentikasi memakai `X-API-Key`; endpoint `/auth/me` dan `/auth/logout` juga memerlukan `Authorization: Bearer <access_token>`.

## Konfigurasi produksi

- Set `CI_ENVIRONMENT = production`.
- Gunakan HTTPS dan set `app.forceGlobalSecureRequests = true`.
- Simpan `.env` hanya di server; file ini tidak boleh di-commit.
- Ganti seluruh password contoh dan batasi akses user MySQL sesuai host aplikasi.
- Buat backup database sebelum upgrade aplikasi.

Dokumentasi kontrak API dan checklist deployment lebih rinci tersedia di [docs/AUTHENTICATION.md](docs/AUTHENTICATION.md) dan [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md).
