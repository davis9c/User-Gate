# Authentication dan RBAC

Kontrak endpoint authentication lengkap, termasuk contoh request/response,
tersedia di [REST API](../../Docs/API.md). Dokumen ini menjadi halaman orientasi
untuk aturan credential dan otorisasi.

## Credential

- `X-API-Key` mengidentifikasi application, bukan user.
- Login menggunakan API key, username, dan password.
- Request yang membutuhkan identitas user menambahkan `Authorization: Bearer <access_token>`.
- Access token dan refresh token bersifat opaque; server hanya menyimpan hash token.
- Refresh token terikat pada API key penerbit dan dirotasi saat digunakan.
- Token, password, dan API key tidak boleh ditulis ke log, URL, atau source control.

## Endpoint

| Endpoint | Credential | Tujuan |
| --- | --- | --- |
| `POST /api/v1/auth/login` | API key, username, password | Membuat sesi autentikasi |
| `POST /api/v1/auth/refresh` | API key, refresh token | Menerbitkan pasangan token baru |
| `GET /api/v1/auth/me` | API key, bearer token | Mengambil identity aktif |
| `POST /api/v1/auth/logout` | API key, bearer token | Mencabut pasangan token |

## Authorization

User permission menggunakan relasi `users` -> `user_roles` -> `roles` ->
`role_permissions` -> `api_permissions`. Permission API key tetap terpisah dan
digunakan untuk membatasi application client.

Role awal `SUPER_ADMIN` dibuat oleh Setup dan menerima permission dashboard.
Permission bisnis aplikasi tetap menjadi tanggung jawab aplikasi masing-masing.

## Error dan keamanan

Login gagal menggunakan response netral `401 Invalid credentials.` untuk user
yang tidak ditemukan, password salah, atau user inactive. Rate limit login
berlaku per IP. Status yang umum digunakan adalah `200`, `401`, `403`, `422`,
dan `429`.
