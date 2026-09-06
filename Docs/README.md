# Dokumentasi UserGate

Halaman ini adalah indeks dokumentasi. Informasi operasional dipisahkan dari
kontrak API dan rancangan arsitektur agar setiap topik memiliki satu sumber utama.

## Menjalankan aplikasi

- [Docker Compose](../Docker/README.md): prasyarat, environment, build, log, dan troubleshooting.
- [Database](../Database/README.md): provisioning MySQL/MariaDB eksternal dan privilege.
- [Instalasi dari source](#instalasi-dari-source): Composer dan server lokal.

## Kontrak runtime

- [REST API](API.md): format response, permission API, dan endpoint user.
- [Authentication](../App/docs/AUTHENTICATION.md): login, token, logout, dan RBAC.

## Operasional rilis

- [Deployment produksi](../App/docs/DEPLOYMENT.md)
- [Release checklist](../App/docs/RELEASE_CHECKLIST.md)
- [Testing](../App/tests/README.md)

## Desain dan roadmap

- [Rancangan sistem](RancanganSistem.MD): batas tanggung jawab dan target arsitektur.
- [Development phases](DevelompmentPhase.MD): urutan pembangunan fitur.

## Instalasi dari source

Prasyarat: PHP 8.1+, Composer, MySQL 8+ atau MariaDB setara, serta ekstensi
PHP `intl`, `mbstring`, dan `mysqli`.

```bash
cd App
composer install --no-dev --optimize-autoloader
cp .env.example .env
php spark serve
```

Buka `http://localhost:8080/setup`. Setup akan menjalankan migration dan seeding
secara otomatis, lalu membuat akun `SUPER_ADMIN` pertama.

Untuk deployment database dan privilege, gunakan [Database README](../Database/README.md).
