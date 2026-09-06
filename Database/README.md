# Database UserGate

Panduan ini digunakan jika server MySQL/MariaDB sudah tersedia. UserGate menggunakan database eksternal dan tidak membuat container database sendiri.

## Konfigurasi default aplikasi

Nilai yang digunakan aplikasi:

```text
Database : db_am_usergate
User     : usg
Password : usg11
Port     : 3306
Driver   : MySQLi
```

Host database diatur pada file environment aplikasi:

```ini
database.default.hostname = 10.10.10.12
database.default.database = db_am_usergate
database.default.username = usg
database.default.password = usg11
database.default.DBDriver = MySQLi
database.default.port = 3306
```

## 1. Masuk ke MySQL sebagai administrator

Jalankan perintah berikut dari server database atau mesin yang dapat mengaksesnya:

```bash
mysql -h 10.10.10.12 -u root -p
```

Ganti `root` dengan user administrator database Anda jika berbeda.

## 2. Membuat database

```sql
CREATE DATABASE IF NOT EXISTS `db_am_usergate`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
```

## 3. Membuat user aplikasi

Akun berikut dapat digunakan dari host mana pun yang diizinkan oleh firewall database:

```sql
CREATE USER IF NOT EXISTS 'usg'@'%' IDENTIFIED BY 'usg11';
ALTER USER 'usg'@'%' IDENTIFIED BY 'usg11';
```

Jika aplikasi dan database berada pada jaringan internal, lebih aman mengganti `%` dengan alamat IP server aplikasi:

```sql
CREATE USER IF NOT EXISTS 'usg'@'10.10.10.15' IDENTIFIED BY 'usg11';
ALTER USER 'usg'@'10.10.10.15' IDENTIFIED BY 'usg11';
```

## 4. Memberi privilege

Berikan privilege pada database UserGate:

```sql
GRANT SELECT, INSERT, UPDATE, DELETE,
      CREATE, ALTER, DROP, INDEX, REFERENCES,
      CREATE TEMPORARY TABLES, LOCK TABLES
ON `db_am_usergate`.* TO 'usg'@'%';

FLUSH PRIVILEGES;
```

Jika menggunakan user dengan host khusus, jalankan grant untuk host tersebut:

```sql
GRANT SELECT, INSERT, UPDATE, DELETE,
      CREATE, ALTER, DROP, INDEX, REFERENCES,
      CREATE TEMPORARY TABLES, LOCK TABLES
ON `db_am_usergate`.* TO 'usg'@'10.10.10.15';

FLUSH PRIVILEGES;
```

Privilege tersebut diperlukan untuk menjalankan migration dan menyimpan data aplikasi. User aplikasi tidak perlu diberi `GRANT OPTION` atau privilege global pada `*.*`.

## 5. Memeriksa user dan privilege

```sql
SELECT User, Host
FROM mysql.user
WHERE User = 'usg';
```

```sql
SHOW GRANTS FOR 'usg'@'%';
```

Pastikan hasilnya mencakup akses ke:

```text
`db_am_usergate`.*
```

## 6. Menguji koneksi dari server aplikasi

Dari server yang menjalankan UserGate:

```bash
nc -vz 10.10.10.12 3306
mysql -h 10.10.10.12 -u usr -p db_am_usergate
```

Saat diminta password, masukkan:

```text
usr11
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

## Catatan keamanan

- Password `usg11` adalah nilai awal dan sebaiknya segera diganti.
- Jangan commit `Docker/.env` atau `App/.env` ke Git.
- Jangan menggunakan user `root` sebagai user aplikasi.
- Hindari grant `ON *.*` untuk user aplikasi.
- Gunakan host spesifik seperti `10.10.10.15` daripada `%` jika aturan jaringan memungkinkan.
- Pastikan firewall database hanya membuka port `3306` untuk server aplikasi yang diperlukan.
