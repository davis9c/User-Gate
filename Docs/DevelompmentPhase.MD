# UserGateway — Development Phases

## Tujuan

UserGateway adalah pusat **identity, authentication, dan SSO** untuk seluruh aplikasi.

Prinsip utama:

> **UserGateway mengelola Identity. Setiap aplikasi mengelola Role dan Permission-nya sendiri.**

User `ACTIVE` dapat login ke seluruh aplikasi yang terintegrasi.  
User `INACTIVE` tidak dapat melakukan login.

---

## Development Rules

AI wajib:

- Mengikuti phase secara berurutan.
- Tidak melompati phase.
- Tidak mengubah business rule tanpa persetujuan.
- Tidak membuat Role/Permission aplikasi di UserGateway.
- Membuat test untuk fitur yang dibuat.
- Menjaga fitur yang sudah selesai.
- Jika requirement ambigu, tanyakan sebelum implementasi.

---

# Phase 1 — Architecture

Definisikan:

- System architecture
- Module
- Database design
- Authentication flow
- SSO flow
- Security architecture
- API structure

**Output:** architecture & database design.

---

# Phase 2 — Foundation

Bangun:

- Project structure
- Database
- Configuration
- Logging
- Error handling
- Migration
- Basic testing

**Output:** project dapat dijalankan dan terhubung ke database.

---

# Phase 3 — Identity & Authentication

Bangun:

- User
- User status
- Login
- Logout
- Password
- Password reset
- Session
- User profile

Status:

```text
ACTIVE
INACTIVE
```

**Output:** user dapat melakukan authentication dan mengelola identity sendiri.

---

# Phase 4 — Admin & Application

Bangun:

- Super Admin
- Application Admin
- Application Registry
- Application configuration
- API credential management
- Audit dasar

**Output:** aplikasi dapat didaftarkan dan dikelola di UserGateway.

---

# Phase 5 — OAuth / OpenID Connect

Bangun UserGateway sebagai **Identity Provider**.

Implementasikan:

- OAuth 2.0
- OpenID Connect
- Authorization Code + PKCE
- Token
- Refresh Token
- JWKS
- Discovery

**Output:** aplikasi dapat menggunakan UserGateway untuk authentication.

---

# Phase 6 — SSO

Implementasikan:

```text
Login Application A
        ↓
   UserGateway
        ↓
    SSO Session
        ↓
Login Application B
        ↓
Tidak perlu login ulang
```

**Output:** satu login dapat digunakan di seluruh aplikasi.

---

# Phase 7 — API Key & Security

Bangun:

- API Key
- Key rotation
- Key revoke
- Expiration
- MFA
- Rate limiting
- Token security
- Session security

**Output:** mekanisme security dan API authentication siap digunakan.

---

# Phase 8 — Testing & Documentation

Lakukan:

- Unit test
- Integration test
- Authentication test
- SSO test
- Security test
- API documentation

**Output:** UserGateway siap untuk deployment.

---

# Final Boundary

```text
UserGateway
├── Identity
├── Authentication
├── SSO
├── OAuth/OIDC
├── Application
├── API Key
├── Session
├── MFA
└── Audit

Application
├── Role
├── Permission
├── Business Authorization
└── Business Logic
```

> **UserGateway = Who are you?**  
> **Application = What can you do?**