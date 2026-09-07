# Database UserGate

Panduan ini digunakan jika server MySQL/MariaDB sudah tersedia. UserGate
menggunakan database eksternal dan tidak membuat container database sendiri.
Nilai di bawah adalah contoh; simpan credential sebenarnya hanya di environment.

## Konfigurasi default aplikasi

Nilai yang digunakan aplikasi:

```text
Database : <database-name>
User     : <database-user>
Password : <database-password>
Port     : 3306
Driver   : MySQLi
```

Host database diatur pada file environment aplikasi:

```ini
database.default.hostname = <database-host>
database.default.database = <database-name>
database.default.username = <database-user>
database.default.password = <database-password>
database.default.DBDriver = MySQLi
database.default.port = 3306
```

## 1. Masuk ke MySQL sebagai administrator

Jalankan perintah berikut dari server database atau mesin yang dapat mengaksesnya:

```bash
mysql -h <database-host> -u root -p
```

Ganti `root` dengan user administrator database Anda jika berbeda.

## 2. Membuat database

```sql
CREATE DATABASE IF NOT EXISTS `<database-name>`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
```

## 3. Membuat user aplikasi

Akun berikut dapat digunakan dari host mana pun yang diizinkan oleh firewall database:

```sql
CREATE USER IF NOT EXISTS '<database-user>'@'%' IDENTIFIED BY '<database-password>';
ALTER USER '<database-user>'@'%' IDENTIFIED BY '<database-password>';
```

Jika aplikasi dan database berada pada jaringan internal, lebih aman mengganti `%` dengan alamat IP server aplikasi:

```sql
CREATE USER IF NOT EXISTS '<database-user>'@'<application-host>' IDENTIFIED BY '<database-password>';
ALTER USER '<database-user>'@'<application-host>' IDENTIFIED BY '<database-password>';
```

## 4. Memberi privilege

Berikan privilege pada database UserGate:

```sql
GRANT SELECT, INSERT, UPDATE, DELETE,
      CREATE, ALTER, DROP, INDEX, REFERENCES,
      CREATE TEMPORARY TABLES, LOCK TABLES
ON `<database-name>`.* TO '<database-user>'@'%';

FLUSH PRIVILEGES;
```

Jika menggunakan user dengan host khusus, jalankan grant untuk host tersebut:

```sql
GRANT SELECT, INSERT, UPDATE, DELETE,
      CREATE, ALTER, DROP, INDEX, REFERENCES,
      CREATE TEMPORARY TABLES, LOCK TABLES
ON `<database-name>`.* TO '<database-user>'@'<application-host>';

FLUSH PRIVILEGES;
```

Privilege tersebut diperlukan untuk menjalankan migration dan menyimpan data aplikasi. User aplikasi tidak perlu diberi `GRANT OPTION` atau privilege global pada `*.*`.
UserGate tidak membuat database secara otomatis menggunakan user aplikasi. Database harus
sudah dibuat administrator sebelum halaman setup digunakan.

## 5. Memeriksa user dan privilege

```sql
SELECT User, Host
FROM mysql.user
WHERE User = '<database-user>';
```

```sql
SHOW GRANTS FOR '<database-user>'@'%';
```

Pastikan hasilnya mencakup akses ke:

```text
`<database-name>`.*
```

## 6. Menguji koneksi dari server aplikasi

Dari server yang menjalankan UserGate:

```bash
nc -vz <database-host> 3306
mysql -h <database-host> -u <database-user> -p <database-name>
```

Jangan menulis password pada command line di lingkungan produksi karena dapat terlihat pada history shell atau daftar proses.

## 7. Menyesuaikan environment aplikasi

Untuk Docker, edit:

```text
Docker/.env
```

Untuk menjalankan aplikasi secara manual atau testing, edit:

```text
App/.env
```

Kedua file harus menggunakan koneksi database yang sama jika menjalankan server database yang sama.

Jika aplikasi berjalan di Docker dan MySQL berjalan pada host Windows atau macOS, gunakan
`host.docker.internal` sebagai `database.default.hostname`, bukan `localhost`. Untuk database
di server lain, gunakan hostname atau alamat IP server tersebut.

## Catatan keamanan

- Gunakan password kuat dan simpan hanya di environment/secret manager.
- Jangan commit `Docker/.env` atau `App/.env` ke Git.
- Jangan menggunakan user `root` sebagai user aplikasi.
- Hindari grant `ON *.*` untuk user aplikasi.
- Gunakan host aplikasi spesifik daripada `%` jika aturan jaringan memungkinkan.
- Pastikan firewall database hanya membuka port `3306` untuk server aplikasi yang diperlukan.
