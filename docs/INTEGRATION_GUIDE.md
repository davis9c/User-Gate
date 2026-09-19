# UserGate Integration Guide

> **Cara Pakai Dokumen Ini:**
> Upload file ini ke AI (ChatGPT, Claude, Copilot, dll.) lalu minta:
> "Bantu saya integrasikan aplikasi [nama aplikasi] dengan UserGate berdasarkan dokumentasi ini"
> AI akan memberikan panduan step-by-step sesuai kebutuhan Anda.

---

## Apa Itu UserGate?

UserGate adalah **pusat identitas dan autentikasi** untuk seluruh ekosistem aplikasi. UserGate menentukan **siapa user** dan **apakah user dapat login**. Setiap aplikasi yang terintegrasi tidak perlu membuat sistem autentikasi sendiri.

**Prinsip utama:**

> **UserGate = "Siapa Anda?"**
> **Aplikasi Anda = "Apa yang boleh Anda lakukan?"**

---

## Arsitektur

```
                         UserGate (Identity Provider)
                    ┌─────────────────────────────┐
                    │                             │
                    │  User Identity              │
                    │  Authentication (Login)     │
                    │  SSO (Single Sign-On)       │
                    │  Token Management           │
                    │  API Key Management         │
                    │  Application Registry       │
                    │  Audit Log                  │
                    │                             │
                    └──────────────┬──────────────┘
                                   │
                            Identity / Token
                                   │
                   ┌───────────────┼───────────────┐
                   ▼               ▼               ▼
                 HRIS           Finance         Inventory
                   │               │               │
               Role/Perm       Role/Perm       Role/Perm
               (milik app)    (milik app)     (milik app)
```

---

## Alur Integrasi Step-by-Step

### Step 1: Daftarkan Aplikasi Anda

1. Login ke dashboard UserGate sebagai **Super Admin**.
2. Buat **Application** baru.
3. Buat **API Key** untuk application tersebut.
4. Aktifkan permission API yang diperlukan.
5. Simpan API key — nilainya hanya ditampilkan sekali.

### Step 2: Dapatkan Kredensial

Anda akan mendapatkan:

| Keterangan | Nilai |
|-----------|-------|
| **Base URL** | `https://usergate.sandalgurun.web.id` |
| **API Endpoint** | `https://usergate.sandalgurun.web.id/api/v1` |
| **API Key** | `ug_xxxxxxxxxxxxxxxx` (dari dashboard) |

### Step 3: Implementasi Login

```bash
# Login menggunakan API key + username + password
curl -X POST "https://usergate.sandalgurun.web.id/api/v1/auth/login" \
  -H "X-API-Key: ug_xxxxxxxxxxxxxxxx" \
  -H "Content-Type: application/json" \
  -d '{
    "username": "user_app",
    "password": "password_user"
  }'
```

Response:
```json
{
  "status": true,
  "message": "Authenticated successfully.",
  "data": {
    "access_token": "eyJ...",
    "token_type": "Bearer",
    "expires_in": 900,
    "refresh_token": "rt_...",
    "refresh_expires_in": 2592000,
    "user": {
      "id": "uuid-user",
      "username": "user_app",
      "email": "user@example.com",
      "full_name": "User App",
      "status": "ACTIVE",
      "roles": ["USER"],
      "is_super_admin": false
    }
  }
}
```

### Step 4: Gunakan Token untuk Request Lain

```bash
# Ambil data user yang sedang login
curl "https://usergate.sandalgurun.web.id/api/v1/auth/me" \
  -H "X-API-Key: ug_xxxxxxxxxxxxxxxx" \
  -H "Authorization: Bearer eyJ..."
```

### Step 5: Refresh Token Saat Kedaluwarsa

```bash
# Token baru menggunakan refresh token
curl -X POST "https://usergate.sandalgurun.web.id/api/v1/auth/refresh" \
  -H "X-API-Key: ug_xxxxxxxxxxxxxxxx" \
  -H "Content-Type: application/json" \
  -d '{
    "refresh_token": "rt_..."
  }'
```

### Step 6: Logout

```bash
# Cabut sesi
curl -X POST "https://usergate.sandalgurun.web.id/api/v1/auth/logout" \
  -H "X-API-Key: ug_xxxxxxxxxxxxxxxx" \
  -H "Authorization: Bearer eyJ..."
```

---

## Endpoint Reference Cepat

| Endpoint | Method | Fungsi | Butuh Login |
|----------|--------|--------|-------------|
| `/api/v1/auth/login` | POST | Login | Tidak |
| `/api/v1/auth/refresh` | POST | Refresh token | Tidak |
| `/api/v1/auth/me` | GET | Ambil data user login | Ya |
| `/api/v1/auth/logout` | POST | Logout | Ya |
| `/api/v1/users` | GET | Daftar user | Ya |
| `/api/v1/users/{id}` | GET | Detail user | Ya |
| `/api/v1/users` | POST | Buat user | Ya |
| `/api/v1/users/{id}` | PUT | Ubah user | Ya |
| `/api/v1/users/{id}` | DELETE | Hapus user | Ya |

---

## Permission API

| Permission | Fungsi | Endpoint |
|-----------|--------|----------|
| `user.read` | Membaca data user | `GET /users` |
| `user.create` | Membuat user baru | `POST /users` |
| `user.update` | Mengubah data user | `PUT /users/{id}` |
| `user.delete` | Menghapus user | `DELETE /users/{id}` |

API key harus memiliki permission yang sesuai. Jika tidak → response `403`.

---

## Status HTTP

| Status | Arti |
|--------|------|
| `200` | Request berhasil |
| `201` | Resource berhasil dibuat |
| `401` | API key atau token tidak valid |
| `403` | Tidak memiliki permission |
| `404` | Resource tidak ditemukan |
| `409` | Data bentrok (username/email sudah ada) |
| `422` | Validasi gagal |
| `429` | Rate limit terlampaui |
| `500` | Kesalahan internal server |

---

## Keamanan

- **Selalu gunakan HTTPS** di production.
- **Jangan commit** API key, token, atau password ke repository.
- **Simpan credential** di environment variable atau secret manager.
- **Jangan kirim** credential melalui URL atau query string.
- **Logout** saat sesi tidak digunakan.

---

## Dokumentasi Lengkap

| Dokumentasi | Fungsi |
|-------------|--------|
| [API Reference](API_REFERENCE_COMPLETE.md) | Semua endpoint lengkap |
| [Quick Start Code](API_QUICK_START.md) | Contoh kode PHP, JS, Python |
| [OAuth/OIDC Guide](OAUTH_OIDC_GUIDE.md) | Panduan SSO & OAuth |
| [SDK Patterns](SDK_PATTERNS.md) | Pola integrasi umum |
| [Troubleshooting](TROUBLESHOOTING.md) | Debug & error handling |
| [PHP Example](EXAMPLES/php-integration.md) | Contoh lengkap PHP |
| [JavaScript Example](EXAMPLES/javascript-integration.md) | Contoh lengkap JS |
| [Python Example](EXAMPLES/python-integration.md) | Contoh lengkap Python |
