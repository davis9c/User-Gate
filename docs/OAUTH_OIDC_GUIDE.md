# OAuth 2.0 & OpenID Connect Guide

> **Cara Pakai Dokumen Ini:**
> Upload file ini ke AI lalu minta:
> "Buatkan saya implementasi OAuth/OIDC dengan UserGate untuk [bahasa/framework]"
> atau "Bagaimana cara SSO dengan UserGate menggunakan Authorization Code + PKCE?"
> AI akan memberikan kode sesuai flow yang Anda pilih.

---

## Ringkasan

UserGate mendukung protokol standar untuk autentikasi dan otorisasi:

- **OAuth 2.0** — Framework otorisasi
- **OpenID Connect (OIDC)** — Layer identitas di atas OAuth 2.0
- **Authorization Code + PKCE** — Untuk aplikasi public & private
- **Client Credentials** — Untuk service-to-service

---

## Alur Authorization Code + PKCE

Flow ini cocok untuk **web apps, SPA, mobile apps**.

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│  Aplikasi   │     │  UserGate   │     │   Browser   │
│  (Client)   │     │  (Server)   │     │   (User)    │
└──────┬──────┘     └──────┬──────┘     └──────┬──────┘
       │                   │                   │
       │  1. Generate      │                   │
       │  code_verifier    │                   │
       │  & code_challenge │                   │
       │                   │                   │
       │  2. Redirect ke   │                   │
       │  /authorize?...   │──────────────────>│
       │                   │                   │
       │                   │  3. User login    │
       │                   │<──────────────────│
       │                   │  & consent        │
       │                   │                   │
       │  4. Redirect ke   │                   │
       │  redirect_uri     │<──────────────────│
       │  ?code=xxx        │                   │
       │                   │                   │
       │  5. Exchange      │                   │
       │  code +           │                   │
       │  code_verifier    │                   │
       │──────────────────>│                   │
       │                   │                   │
       │  6. Token         │                   │
       │  response         │                   │
       │<──────────────────│                   │
       │                   │                   │
```

### Step 1: Generate Code Verifier & Challenge

```javascript
// Untuk PKCE, generate code_verifier dan code_challenge

// Generate random string
const codeVerifier = generateRandomString(128);

// SHA256 hash, lalu base64url encode
const encoder = new TextEncoder();
const data = encoder.encode(codeVerifier);
const digest = await crypto.subtle.digest('SHA-256', data);
const codeChallenge = base64UrlEncode(digest);

function generateRandomString(length) {
  const charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-._~';
  let result = '';
  for (let i = 0; i < length; i++) {
    result += charset.charAt(Math.floor(Math.random() * charset.length));
  }
  return result;
}

function base64UrlEncode(buffer) {
  return btoa(String.fromCharCode(...new Uint8Array(buffer)))
    .replace(/\+/g, '-')
    .replace(/\//g, '_')
    .replace(/=+$/, '');
}
```

### Step 2: Redirect ke Authorization Endpoint

```text
GET https://usergate.sandalgurun.web.id/authorize?
  response_type=code&
  client_id=<client_id>&
  redirect_uri=<redirect_uri>&
  scope=openid profile email&
  state=<random_state>&
  code_challenge=<code_challenge>&
  code_challenge_method=S256
```

| Parameter | Keterangan |
|-----------|------------|
| `response_type` | `code` |
| `client_id` | Client ID dari dashboard UserGate |
| `redirect_uri` | URI redirect yang terdaftar |
| `scope` | `openid`, `profile`, `email` |
| `state` | Random string untuk CSRF protection |
| `code_challenge` | SHA256 hash dari code_verifier |
| `code_challenge_method` | `S256` |

### Step 3: Handle Callback

```javascript
// Setelah redirect, URL callback akan mengandung code dan state
const urlParams = new URLSearchParams(window.location.search);
const code = urlParams.get('code');
const state = urlParams.get('state');

// Verifikasi state untuk mencegah CSRF
if (state !== storedState) {
  throw new Error('Invalid state - possible CSRF attack');
}

// Simpan code_verifier untuk digunakan di step berikutnya
// (biasanya disimpan di session atau localStorage yang aman)
```

### Step 4: Exchange Code untuk Token

```javascript
const response = await fetch('https://usergate.sandalgurun.web.id/api/v1/oauth/token', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    grant_type: 'authorization_code',
    code: code,
    redirect_uri: '<redirect_uri>',
    client_id: '<client_id>',
    code_verifier: codeVerifier,  // code_verifier dari step 1
  }),
});

const data = await response.json();

if (data.access_token) {
  // Simpan token
  const accessToken = data.access_token;
  const refreshToken = data.refresh_token;
  const expiresIn = data.expires_in;

  console.log('Token berhasil didapatkan');
}
```

### Response Token

```json
{
  "access_token": "eyJ...",
  "token_type": "Bearer",
  "expires_in": 900,
  "refresh_token": "rt_...",
  "id_token": "eyJ...",
  "scope": "openid profile email"
}
```

| Field | Keterangan |
|-------|------------|
| `access_token` | Token untuk akses API |
| `token_type` | Selalu `Bearer` |
| `expires_in` | Masa berlaku dalam detik |
| `refresh_token` | Token untuk memperbarui access token |
| `id_token` | JWT berisi informasi user (OIDC) |
| `scope` | Scope yang diberikan |

---

## Alur Client Credentials

Flow ini cocok untuk **service-to-service** (backend tanpa user login).

```
┌─────────────┐                    ┌─────────────┐
│  Service A  │                    │  UserGate   │
│  (Backend)  │                    │  (Server)   │
└──────┬──────┘                    └──────┬──────┘
       │                                  │
       │  1. POST /token                  │
       │  grant_type=client_credentials   │
       │  client_id=xxx                   │
       │  client_secret=xxx               │
       │  scope=api                       │
       │─────────────────────────────────>│
       │                                  │
       │  2. Token response              │
       │<─────────────────────────────────│
       │                                  │
       │  3. Use token for API calls      │
       │─────────────────────────────────>│
```

### Request Token

```bash
curl -X POST "https://usergate.sandalgurun.web.id/api/v1/oauth/token" \
  -H "Content-Type: application/json" \
  -d '{
    "grant_type": "client_credentials",
    "client_id": "<client_id>",
    "client_secret": "<client_secret>",
    "scope": "api"
  }'
```

### Response

```json
{
  "access_token": "eyJ...",
  "token_type": "Bearer",
  "expires_in": 3600,
  "scope": "api"
}
```

**Catatan:**

- Client credentials **tidak menghasilkan refresh token**.
- Token berlaku lebih lama (biasanya 1 jam).

---

## Refresh Token Flow

```javascript
async function refreshAccessToken(refreshToken, clientId) {
  const response = await fetch('https://usergate.sandalgurun.web.id/api/v1/oauth/token', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      grant_type: 'refresh_token',
      refresh_token: refreshToken,
      client_id: clientId,
    }),
  });

  const data = await response.json();

  if (data.access_token) {
    return {
      accessToken: data.access_token,
      refreshToken: data.refresh_token,  // New refresh token
      expiresIn: data.expires_in,
    };
  } else {
    throw new Error(data.error_description || 'Token refresh failed');
  }
}
```

---

## ID Token (OIDC)

ID Token adalah JWT yang berisi informasi user. Untuk membaca ID Token:

```javascript
// Decode JWT (untuk debugging — jangan verifikasi sendiri di production)
function decodeJWT(token) {
  const payload = token.split('.')[1];
  return JSON.parse(atob(payload));
}

const idToken = data.id_token;
const claims = decodeJWT(idToken);

// Contoh claims:
{
  "iss": "https://usergate.sandalgurun.web.id",
  "sub": "uuid-user",
  "aud": "<client_id>",
  "exp": 1695168000,
  "iat": 1695167100,
  "name": "Budi Santoso",
  "email": "budi@example.com",
  "email_verified": true,
  "preferred_username": "budi"
}
```

### Standard Claims

| Claim | Keterangan |
|-------|------------|
| `iss` | Issuer — URL UserGate |
| `sub` | Subject — User ID |
| `aud` | Audience — Client ID |
| `exp` | Expiration time |
| `iat` | Issued at |
| `name` | Nama lengkap |
| `email` | Email |
| `email_verified` | Apakah email terverifikasi |
| `preferred_username` | Username |

---

## JWKS (JSON Web Key Set)

UserGate menyediakan public keys untuk verifikasi JWT.

```http
GET /api/v1/.well-known/jwks.json
```

```bash
curl "https://usergate.sandalgurun.web.id/api/v1/.well-known/jwks.json"
```

### Response

```json
{
  "keys": [
    {
      "kty": "RSA",
      "kid": "key-id-1",
      "use": "sig",
      "alg": "RS256",
      "n": "...",
      "e": "AQAB"
    }
  ]
}
```

### Verifikasi JWT dengan JWKS

```python
# Python — menggunakan PyJWT
import jwt
import requests

jwks_url = "https://usergate.sandalgurun.web.id/api/v1/.well-known/jwks.json"
jwks = requests.get(jwks_url).json()

# Dapatkan public key dari JWKS
key = jwt.algorithms.RSAAlgorithm.from_jwk(jwks["keys"][0])

# Verifikasi token
payload = jwt.decode(
    id_token,
    key,
    algorithms=["RS256"],
    audience="<client_id>",
    issuer="https://usergate.sandalgurun.web.id"
)

print(payload)
```

---

## Discovery Endpoint

```http
GET /api/v1/.well-known/openid-configuration
```

```bash
curl "https://usergate.sandalgurun.web.id/api/v1/.well-known/openid-configuration"
```

### Response

```json
{
  "issuer": "https://usergate.sandalgurun.web.id",
  "authorization_endpoint": "https://usergate.sandalgurun.web.id/authorize",
  "token_endpoint": "https://usergate.sandalgurun.web.id/api/v1/oauth/token",
  "userinfo_endpoint": "https://usergate.sandalgurun.web.id/api/v1/oauth/userinfo",
  "jwks_uri": "https://usergate.sandalgurun.web.id/api/v1/.well-known/jwks.json",
  "scopes_supported": ["openid", "profile", "email"],
  "response_types_supported": ["code"],
  "grant_types_supported": ["authorization_code", "refresh_token", "client_credentials"],
  "token_endpoint_auth_methods_supported": ["client_secret_basic", "client_secret_post"]
}
```

---

## SSO (Single Sign-On)

UserGate mendukung SSO — user yang sudah login di satu aplikasi tidak perlu login ulang di aplikasi lain.

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│  App A      │     │  UserGate   │     │  App B      │
│  (HRIS)     │     │  (SSO)      │     │  (Finance)  │
└──────┬──────┘     └──────┬──────┘     └──────┬──────┘
       │                   │                   │
       │  1. User login    │                   │
       │──────────────────>│                   │
       │                   │                   │
       │  2. Session SSO   │                   │
       │  created          │                   │
       │<──────────────────│                   │
       │                   │                   │
       │  3. User akses    │                   │
       │  App B            │                   │
       │                   │                   │
       │                   │  4. Check SSO     │
       │                   │  session          │
       │                   │<──────────────────│
       │                   │                   │
       │                   │  5. SSO valid     │
       │                   │  → auto login     │
       │                   │──────────────────>│
       │                   │                   │
       │                   │                   │  6. User
       │                   │                   │  authenticated
       │                   │                   │  tanpa login
```

**Cara kerja:**

1. User login ke App A → session SSO dibuat di UserGate.
2. User membuka App B → redirect ke UserGate.
3. UserGate mendeteksi session SSO masih aktif.
4. UserGate menerbitkan authorization code untuk App B.
5. App B exchange code untuk token → user terotentikasi tanpa login ulang.

---

## Error Response

```json
{
  "error": "invalid_grant",
  "error_description": "The authorization code has expired."
}
```

| Error Code | Keterangan |
|-----------|------------|
| `invalid_request` | Parameter yang dibutuhkan tidak ada |
| `invalid_client` | Client ID atau secret salah |
| `invalid_grant` | Authorization code atau refresh token tidak valid/expired |
| `unauthorized_client` | Client tidak diizinkan menggunakan grant type ini |
| `unsupported_grant_type` | Grant type tidak didukung |
| `invalid_scope` | Scope tidak valid |

---

## Keamanan

- **Selalu gunakan PKCE** untuk Authorization Code flow.
- **Simpan code_verifier** di tempat aman (session, bukan localStorage jika memungkinkan).
- **Verifikasi state** untuk mencegah CSRF.
- **Jangan simpan token** di localStorage di production — gunakan httpOnly cookies.
- **Rotate refresh token** setiap kali digunakan.
- **Batasi scope** sesuai kebutuhan aplikasi.
