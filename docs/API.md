# UserGate API

Dokumentasi penggunaan REST API UserGate.

## Base URL

Gunakan nilai `app.baseURL` dari file environment aplikasi. Contoh domain:

```text
https://usergate.sandalgurun.web.id
```

Endpoint API menggunakan prefix:

```text
https://usergate.sandalgurun.web.id/api/v1
```

Jika environment masih menggunakan HTTP, sesuaikan skema URL menjadi `http://`.

## Format response

Response sukses menggunakan format:

```json
{
  "status": true,
  "message": "Success",
  "data": {}
}
```

Response error menggunakan format:

```json
{
  "status": false,
  "message": "Error message"
}
```

Error validasi dapat menyertakan detail:

```json
{
  "status": false,
  "message": "Validation failed.",
  "errors": {
    "email": "The email field must contain a valid email address."
  }
}
```

## Header

Semua endpoint API membutuhkan API key:

```http
X-API-Key: <api_key>
Content-Type: application/json
Accept: application/json
```

Endpoint yang membutuhkan user login juga membutuhkan access token:

```http
Authorization: Bearer <access_token>
```

Jangan mengirim API key, access token, refresh token, atau password melalui URL maupun query string.

## Mendapatkan API key

API key dibuat dari dashboard UserGate:

1. Login sebagai Super Admin.
2. Buat sebuah Application.
3. Buat API key untuk Application tersebut.
4. Aktifkan permission API yang diperlukan.
5. Simpan API key saat dibuat karena nilainya digunakan pada header `X-API-Key`.

API key harus berasal dari Application yang aktif. API key dari Application nonaktif akan ditolak.

## Alur autentikasi

Alur umum penggunaan API:

1. Kirim username dan password ke endpoint login.
2. Simpan `access_token` dan `refresh_token` secara aman.
3. Kirim `access_token` sebagai Bearer token pada endpoint yang dilindungi.
4. Gunakan refresh token ketika access token kedaluwarsa.
5. Panggil logout untuk mencabut access token dan refresh token aktif.

## 1. Login

Membuat access token dan refresh token untuk user aktif.

```http
POST /api/v1/auth/login
X-API-Key: <api_key>
Content-Type: application/json
```

Request:

```json
{
  "username": "admin",
  "password": "password-user"
}
```

Contoh `curl`:

```bash
curl -X POST "https://usergate.sandalgurun.web.id/api/v1/auth/login" \
  -H "X-API-Key: <api_key>" \
  -H "Content-Type: application/json" \
  -d '{
    "username": "admin",
    "password": "password-user"
  }'
```

Response `200`:

```json
{
  "status": true,
  "message": "Authenticated successfully.",
  "data": {
    "access_token": "<access_token>",
    "token_type": "Bearer",
    "expires_in": 900,
    "refresh_token": "<refresh_token>",
    "refresh_expires_in": 2592000,
    "user": {
      "id": "uuid-user",
      "username": "admin",
      "email": "admin@example.com",
      "full_name": "Administrator",
      "status": "ACTIVE",
      "roles": ["SUPER_ADMIN"],
      "is_super_admin": true
    }
  }
}
```

Catatan:

- `access_token` berlaku selama 900 detik.
- `refresh_token` berlaku selama 30 hari.
- Login dibatasi oleh rate limit berdasarkan alamat IP.
- Username atau password salah menghasilkan response yang sama untuk mencegah kebocoran informasi akun.

Response umum:

- `401 Invalid credentials.`: kredensial salah, user tidak aktif, atau user tidak ditemukan.
- `401 API Key is required.`: header API key tidak dikirim.
- `401 Invalid or inactive API Key.`: API key salah atau tidak aktif.
- `429`: terlalu banyak percobaan login.

## 2. Refresh token

Mengganti refresh token dengan pasangan access token dan refresh token baru.

```http
POST /api/v1/auth/refresh
X-API-Key: <api_key>
Content-Type: application/json
```

Request:

```json
{
  "refresh_token": "<refresh_token>"
}
```

Contoh:

```bash
curl -X POST "https://usergate.sandalgurun.web.id/api/v1/auth/refresh" \
  -H "X-API-Key: <api_key>" \
  -H "Content-Type: application/json" \
  -d '{
    "refresh_token": "<refresh_token>"
  }'
```

Response `200` memiliki struktur token yang sama seperti login.

Refresh token bersifat one-time use dan dirotasi setiap kali digunakan. Refresh token harus digunakan dengan API key yang menerbitkannya.

Response error:

- `401 Invalid or expired refresh token.`
- `401 API Key is required.`
- `401 Invalid or inactive API Key.`

## 3. Current user

Mengambil informasi user dari access token aktif.

```http
GET /api/v1/auth/me
X-API-Key: <api_key>
Authorization: Bearer <access_token>
Accept: application/json
```

Contoh:

```bash
curl "https://usergate.sandalgurun.web.id/api/v1/auth/me" \
  -H "X-API-Key: <api_key>" \
  -H "Authorization: Bearer <access_token>" \
  -H "Accept: application/json"
```

Response `200`:

```json
{
  "status": true,
  "message": "Current user retrieved successfully.",
  "data": {
    "id": "uuid-user",
    "username": "admin",
    "email": "admin@example.com",
    "full_name": "Administrator",
    "status": "ACTIVE",
    "roles": ["SUPER_ADMIN"],
    "is_super_admin": true
  }
}
```

Password dan password hash tidak pernah dikembalikan.

## 4. Logout

Mencabut access token aktif beserta pasangan tokennya.

```http
POST /api/v1/auth/logout
X-API-Key: <api_key>
Authorization: Bearer <access_token>
```

Contoh:

```bash
curl -X POST "https://usergate.sandalgurun.web.id/api/v1/auth/logout" \
  -H "X-API-Key: <api_key>" \
  -H "Authorization: Bearer <access_token>"
```

Response `200`:

```json
{
  "status": true,
  "message": "Logged out successfully.",
  "data": null
}
```

Token yang sudah dicabut tidak dapat digunakan kembali.

## Permission API

Endpoint user membutuhkan permission berikut:

| Permission | Endpoint | Operasi |
| --- | --- | --- |
| `user.read` | `GET /users`, `GET /users/{id}` | Membaca user |
| `user.create` | `POST /users` | Membuat user |
| `user.update` | `PUT /users/{id}` | Mengubah user |
| `user.delete` | `DELETE /users/{id}` | Menghapus user |

API key harus memiliki permission yang sesuai. Jika tidak, API mengembalikan `403`.

## 5. Daftar user

Mengambil daftar user dengan pagination dan pencarian opsional.

```http
GET /api/v1/users
X-API-Key: <api_key>
Authorization: Bearer <access_token>
Accept: application/json
```

Query parameter:

| Parameter | Tipe | Default | Keterangan |
| --- | --- | --- | --- |
| `page` | integer | `1` | Nomor halaman |
| `per_page` | integer | `20` | Jumlah data per halaman, maksimum `100` |
| `search` | string | kosong | Mencari berdasarkan username, email, atau nama lengkap |

Contoh:

```bash
curl "https://usergate.sandalgurun.web.id/api/v1/users?page=1&per_page=20&search=budi" \
  -H "X-API-Key: <api_key>" \
  -H "Authorization: Bearer <access_token>"
```

Response `200`:

```json
{
  "status": true,
  "message": "Users retrieved successfully.",
  "data": [
    {
      "id": "uuid-user",
      "username": "budi",
      "email": "budi@example.com",
      "full_name": "Budi Santoso",
      "status": "ACTIVE",
      "created_at": "2026-09-05 10:00:00",
      "updated_at": "2026-09-05 10:00:00"
    }
  ],
  "meta": {
    "page": 1,
    "per_page": 20,
    "total": 1,
    "total_pages": 1
  }
}
```

## 6. Detail user

```http
GET /api/v1/users/{id}
X-API-Key: <api_key>
Authorization: Bearer <access_token>
Accept: application/json
```

Contoh:

```bash
curl "https://usergate.sandalgurun.web.id/api/v1/users/{id}" \
  -H "X-API-Key: <api_key>" \
  -H "Authorization: Bearer <access_token>"
```

Response `200` mengembalikan satu object user tanpa password.

Response error:

- `404 User not found.`
- `403 API Key does not have user.read permission.`
- `401 Invalid or expired access token.`

## 7. Membuat user

Membuat user baru. Password disimpan sebagai hash oleh server.

```http
POST /api/v1/users
X-API-Key: <api_key>
Authorization: Bearer <access_token>
Content-Type: application/json
```

Request:

```json
{
  "username": "budi",
  "email": "budi@example.com",
  "full_name": "Budi Santoso",
  "password": "password-minimal-8"
}
```

Contoh:

```bash
curl -X POST "https://usergate.sandalgurun.web.id/api/v1/users" \
  -H "X-API-Key: <api_key>" \
  -H "Authorization: Bearer <access_token>" \
  -H "Content-Type: application/json" \
  -d '{
    "username": "budi",
    "email": "budi@example.com",
    "full_name": "Budi Santoso",
    "password": "password-minimal-8"
  }'
```

Response `201`:

```json
{
  "status": true,
  "message": "User created successfully.",
  "data": {
    "id": "uuid-user",
    "username": "budi",
    "email": "budi@example.com",
    "full_name": "Budi Santoso",
    "status": "ACTIVE"
  }
}
```

Validasi:

- `username`: wajib, 3-100 karakter, hanya alfanumerik.
- `email`: wajib dan harus valid.
- `full_name`: wajib, 3-150 karakter.
- `password`: wajib, minimal 8 karakter.

Response error:

- `409 Username already exists.`
- `409 Email already exists.`
- `422 Validation failed.`
- `403 API Key does not have user.create permission.`

## 8. Mengubah user

```http
PUT /api/v1/users/{id}
X-API-Key: <api_key>
Authorization: Bearer <access_token>
Content-Type: application/json
```

Request:

```json
{
  "username": "budi-update",
  "email": "budi-update@example.com",
  "full_name": "Budi Santoso Update",
  "status": "ACTIVE"
}
```

Contoh:

```bash
curl -X PUT "https://usergate.sandalgurun.web.id/api/v1/users/{id}" \
  -H "X-API-Key: <api_key>" \
  -H "Authorization: Bearer <access_token>" \
  -H "Content-Type: application/json" \
  -d '{
    "username": "budi-update",
    "email": "budi-update@example.com",
    "full_name": "Budi Santoso Update",
    "status": "ACTIVE"
  }'
```

Field `status` hanya menerima:

- `ACTIVE`
- `INACTIVE`

Response `200` mengembalikan data user yang sudah diperbarui.

Response error:

- `404 User not found.`
- `409 Username already exists.`
- `409 Email already exists.`
- `422 Validation failed.`
- `403 API Key does not have user.update permission.`

## 9. Menghapus user

```http
DELETE /api/v1/users/{id}
X-API-Key: <api_key>
Authorization: Bearer <access_token>
```

Contoh:

```bash
curl -X DELETE "https://usergate.sandalgurun.web.id/api/v1/users/{id}" \
  -H "X-API-Key: <api_key>" \
  -H "Authorization: Bearer <access_token>"
```

Response `200`:

```json
{
  "status": true,
  "message": "User deleted successfully.",
  "data": {
    "id": "uuid-user",
    "username": "budi"
  }
}
```

Penghapusan juga menghapus credential user. Operasi ini tidak dapat dibatalkan melalui API.

## Status HTTP

| Status | Arti |
| --- | --- |
| `200` | Request berhasil |
| `201` | Resource berhasil dibuat |
| `401` | API key atau token tidak valid |
| `403` | Tidak memiliki permission |
| `404` | Resource tidak ditemukan |
| `409` | Data bentrok, misalnya username atau email sudah digunakan |
| `422` | Payload tidak lolos validasi |
| `429` | Rate limit terlampaui |
| `500` | Kesalahan internal server |

## Keamanan penggunaan

- Selalu gunakan HTTPS pada production.
- Simpan API key dan token di secret manager atau environment variable.
- Jangan commit credential, API key, password, atau token ke repository.
- Jangan menaruh credential di URL.
- Batasi permission API sesuai kebutuhan Application.
- Gunakan refresh token hanya pada endpoint `/auth/refresh`.
- Logout ketika sesi tidak lagi digunakan.
