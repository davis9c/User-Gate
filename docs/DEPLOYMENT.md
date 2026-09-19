# Deployment Produksi

Panduan environment dan privilege database berada di [Docker README](../../Docker/README.md)
dan [Database README](../../Database/README.md). Dokumen ini hanya mendefinisikan
urutan verifikasi produksi.

## Sebelum deploy

1. Deploy source tanpa `.env` dan siapkan database, `app.baseURL`, environment
	production, HTTPS, serta folder cache/log yang writable.
2. Pastikan user database memiliki privilege migration yang dibutuhkan.
3. Siapkan backup database dan prosedur rollback.

## Deploy

1. Build dan jalankan aplikasi sesuai [Docker README](../../Docker/README.md),
	atau arahkan web server langsung ke `App/public`.
2. Jalankan `php spark migrate --all` satu kali pada environment target jika
	Setup belum pernah dijalankan.
3. Buka `/setup` untuk instalasi baru. Setup akan menjalankan migration dan
	seeding secara otomatis.
4. Aktifkan HTTPS dan pastikan HTTP diarahkan ke HTTPS.

## Verifikasi

- Login, `/auth/me`, refresh, logout, dan penolakan token yang sudah dicabut.
- Penolakan API key invalid/nonaktif dan rate limit login.
- Grant/deny permission RBAC dan permission API key.
- Event `LOGIN_SUCCESS`, `LOGIN_FAILED`, `TOKEN_ISSUED`, `TOKEN_REVOKED`,
  `LOGOUT`, dan `PERMISSION_DENIED` tersedia di `audit_logs`.
- Log tidak mengandung password, token, API key, atau credential.

Gunakan [Release Checklist](RELEASE_CHECKLIST.md) sebagai checklist final.
