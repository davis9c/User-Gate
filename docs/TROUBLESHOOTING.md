# Troubleshooting — Debug & Error Handling

> **Cara Pakai Dokumen Ini:**
> Upload file ini ke AI lalu minta:
> "Saya dapat error [nama error] saat integrasi dengan UserGate, bagaimana cara mengatasinya?"
> AI akan memberikan solusi berdasarkan error yang Anda alami.

---

## Error Code Reference

### 401 — Unauthorized

| Error Message | Penyebab | Solusi |
|--------------|----------|--------|
| `API Key is required.` | Header `X-API-Key` tidak dikirim | Tambahkan header `X-API-Key` di semua request |
| `Invalid or inactive API Key.` | API key salah atau Application nonaktif | Cek API key di dashboard, pastikan Application aktif |
| `Invalid credentials.` | Username/password salah, user tidak aktif, atau user tidak ditemukan | Cek username dan password, pastikan user status `ACTIVE` |
| `Invalid or expired access token.` | Access token sudah kedaluwarsa atau dicabut | Refresh token atau login ulang |
| `Invalid or expired refresh token.` | Refresh token sudah digunakan atau expired | Login ulang untuk dapat refresh token baru |

### 403 — Forbidden

| Error Message | Penyebab | Solusi |
|--------------|----------|--------|
| `API Key does not have user.read permission.` | API key tidak punya izin membaca user | Aktifkan permission `user.read` di dashboard |
| `API Key does not have user.create permission.` | API key tidak punya izin membuat user | Aktifkan permission `user.create` di dashboard |
| `API Key does not have user.update permission.` | API key tidak punya izin mengubah user | Aktifkan permission `user.update` di dashboard |
| `API Key does not have user.delete permission.` | API key tidak punya izin menghapus user | Aktifkan permission `user.delete` di dashboard |

### 404 — Not Found

| Error Message | Penyebab | Solusi |
|--------------|----------|--------|
| `User not found.` | User dengan ID yang diberikan tidak ada | Cek ID user, gunakan endpoint `GET /users` untuk mencari |

### 409 — Conflict

| Error Message | Penyebab | Solusi |
|--------------|----------|--------|
| `Username already exists.` | Username sudah digunakan user lain | Gunakan username lain |
| `Email already exists.` | Email sudah digunakan user lain | Gunakan email lain |

### 422 — Validation Failed

| Error Message | Penyebab | Solusi |
|--------------|----------|--------|
| `Validation failed.` | Field tidak sesuai validasi | Cek field `errors` dalam response untuk detail |

**Validasi Fields:**

| Field | Aturan |
|-------|--------|
| `username` | Wajib, 3-100 karakter, alfanumerik saja |
| `email` | Wajib, format email valid |
| `full_name` | Wajib, 3-150 karakter |
| `password` | Wajib, minimal 8 karakter |

### 429 — Rate Limited

| Error Message | Penyebab | Solusi |
|--------------|----------|--------|
| Terlalu banyak percobaan login | Rate limit login terlampaui | Tunggu beberapa menit, gunakan IP berbeda |
| Too many requests | Rate limit API terlampaui | Implementasi retry dengan exponential backoff |

### 500 — Server Error

| Error Message | Penyebab | Solusi |
|--------------|----------|--------|
| `Internal server error.` | Kesalahan internal server | Coba lagi, hubungi admin jika berlanjut |

---

## Common Mistakes

### 1. Lupa Header `X-API-Key`

**Symptom:**

```json
{
  "status": false,
  "message": "API Key is required."
}
```

**Solution:**

```http
X-API-Key: ug_xxxxxxxxxxxxxxxx
Content-Type: application/json
Accept: application/json
```

### 2. Lupa Header `Authorization`

**Symptom:**

```json
{
  "status": false,
  "message": "Invalid or expired access token."
}
```

**Solution:**

```http
Authorization: Bearer <access_token>
```

### 3. Kirim Credential di URL

**Symptom:** Password atau token muncul di log server.

**Solution:**

```bash
# SALAH — jangan lakukan ini
curl "https://usergate.sandalgurun.web.id/api/v1/auth/login?username=admin&password=secret"

# BENAR — gunakan POST body
curl -X POST "https://usergate.sandalgurun.web.id/api/v1/auth/login" \
  -H "X-API-Key: ug_xxxx" \
  -H "Content-Type: application/json" \
  -d '{"username": "admin", "password": "secret"}'
```

### 4. Tidak Handle Token Expired

**Symptom:** Request gagal tiba-tiba setelah 15 menit.

**Solution:**

```javascript
// Implementasi auto-refresh
const response = await requestWithAuth(url);

if (response.status === false && response.message.includes('expired')) {
  const refreshed = await refreshToken();
  if (refreshed) {
    return await requestWithAuth(url); // Retry
  } else {
    // Redirect ke login
  }
}
```

### 5. Simpan Token di localStorage (Insecure)

**Symptom:** Token rentan terhadap XSS attacks.

**Solution:**

- **Web apps:** Gunakan httpOnly cookies.
- **Mobile apps:** Gunakan secure storage (Keychain/Keystore).
- **Backend:** Simpan di environment variables atau secret manager.

### 6. Tidak Verifikasi State Parameter (OAuth)

**Symptom:** Rentan CSRF attacks.

**Solution:**

```javascript
// Generate state
const state = generateRandomString(32);
sessionStorage.setItem('oauth_state', state);

// Redirect ke authorize dengan state
window.location = `https://usergate.../authorize?...&state=${state}`;

// Di callback, verifikasi state
const callbackState = urlParams.get('state');
const savedState = sessionStorage.getItem('oauth_state');

if (callbackState !== savedState) {
  throw new Error('CSRF detected');
}
```

---

## Debug Checklist

Ketika integrasi tidak berfungsi, cek satu per satu:

### Auth & Token

- [ ] API key sudah benar dan aktif di dashboard
- [ ] Header `X-API-Key` dikirim di semua request
- [ ] Header `Content-Type: application/json` dikirim
- [ ] Header `Accept: application/json` dikirim
- [ ] Access token belum expired (berlaku 15 menit)
- [ ] Header `Authorization: Bearer <token>` dikirim untuk endpoint yang butuh login
- [ ] Refresh token belum expired (berlaku 30 hari)
- [ ] Refresh token belum digunakan (one-time use)

### Permission

- [ ] API key memiliki permission yang dibutuhkan
- [ ] User memiliki role yang sesuai

### Data

- [ ] Username dan password benar
- [ ] User status `ACTIVE`
- [ ] Field sesuai validasi (panjang, format, dll)
- [ ] Email belum digunakan user lain
- [ ] Username belum digunakan user lain

### Network

- [ ] URL endpoint benar (cek base URL)
- [ ] HTTPS digunakan di production
- [ ] Tidak ada firewall yang memblokir request
- [ ] Tidak ada proxy yang memodifikasi headers

### Code

- [ ] Request body dalam format JSON
- [ ] Tidak ada typografi di field name
- [ ] Response di-parse dengan benar
- [ ] Error handling sudah diimplementasi
- [ ] Auto-refresh token sudah diimplementasi

---

## FAQ

### Q: Berapa lama access token berlaku?

**A:** 900 detik (15 menit). Gunakan refresh token untuk memperbarui.

### Q: Berapa lama refresh token berlaku?

**A:** 30 hari. Refresh token bersifat one-time use — dirotasi setiap kali digunakan.

### Q: Bagaimana cara cek apakah token masih valid?

**A:** Panggil endpoint `GET /auth/me`. Jika response `401`, token sudah tidak valid.

### Q: Apakah saya perlu membuat user di UserGate sebelum login?

**A:** Ya. User harus dibuat terlebih dahulu (melalui dashboard atau API `POST /users`).

### Q: Bagaimana cara mengatasi rate limit?

**A:** Tunggu beberapa menit, atau implementasi retry dengan exponential backoff.

### Q: Apakah password dikembalikan dalam response?

**A:** Tidak. Password tidak pernah dikembalikan dalam response API.

### Q: Bagaimana cara menonaktifkan user?

**A:** Gunakan `PUT /users/{id}` dengan field `"status": "INACTIVE"`.

### Q: Apakah saya bisa menggunakan satu API key untuk banyak aplikasi?

**A:** Tidak. Satu API key terikat pada satu Application. Buat API key terpisah untuk setiap aplikasi.

### Q: Apakah role/permission bisnis disimpan di UserGate?

**A:** Tidak. UserGate hanya mengelola identity dan authentication. Role/permission bisnis dikelola oleh aplikasi masing-masing.

---

## Log & Monitoring

### Audit Log

UserGate mencatat event berikut di `audit_logs`:

| Event | Keterangan |
|-------|------------|
| `LOGIN_SUCCESS` | Login berhasil |
| `LOGIN_FAILED` | Login gagal |
| `TOKEN_ISSUED` | Token baru diterbitkan |
| `TOKEN_REVOKED` | Token dicabut |
| `LOGOUT` | User logout |
| `PERMISSION_DENIED` | Akses ditolak |

### Cek Log

```bash
# Jika menggunakan Docker
docker logs usergate-app

# Jika manual
tail -f /path/to/usergate/logs/error.log
```

---

## Error Handling Template

Gunakan template ini di aplikasi Anda:

```php
<?php

function usergateApiCall(string $method, string $endpoint, array $data = [], ?string $token = null): array
{
    $headers = [
        'X-API-Key: ' . getenv('USERGATE_API_KEY'),
        'Content-Type: application/json',
        'Accept: application/json',
    ];

    if ($token) {
        $headers[] = "Authorization: Bearer $token";
    }

    $ch = curl_init(getenv('USERGATE_BASE_URL') . $endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_POSTFIELDS     => !empty($data) ? json_encode($data) : null,
        CURLOPT_TIMEOUT        => 30,
    ]);

    $response = json_decode(curl_exec($ch), true);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Tambahkan HTTP code ke response
    $response['code'] = $httpCode;

    // Handle error
    if (!$response['status']) {
        error_log("[UserGate] {$response['message']} (HTTP $httpCode)");
    }

    return $response;
}
```
