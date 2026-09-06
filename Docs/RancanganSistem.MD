# UserGateway — Rancangan Sistem

## 1. Konsep

**UserGateway** adalah pusat identitas dan autentikasi untuk seluruh aplikasi yang dikembangkan.

Prinsip utama:

> **UserGateway menentukan siapa user dan apakah user dapat login. Aplikasi menentukan apa yang boleh dilakukan user.**

User yang berstatus `ACTIVE` dapat login ke seluruh aplikasi yang terintegrasi dengan UserGateway.

---

## 2. Arsitektur

```text
                         UserGateway
                    ┌────────────────────┐
                    │                    │
                    │ User & Identity    │
                    │ Authentication     │
                    │ SSO                │
                    │ OAuth / OIDC       │
                    │ API Key            │
                    │ Application        │
                    │ Session / Token    │
                    │ Audit Log          │
                    │                    │
                    └─────────┬──────────┘
                              │
                       Identity / Token
                              │
             ┌────────────────┼────────────────┐
             ▼                ▼                ▼
           HRIS             Finance         Inventory
             │                │                │
         Role/Perm         Role/Perm         Role/Perm
         milik HRIS       milik Finance     milik Inventory
```

---

## 3. Tanggung Jawab UserGateway

UserGateway bertanggung jawab atas:

* Identity user
* Authentication
* SSO
* OAuth 2.0 / OpenID Connect
* Status user
* Password
* MFA
* Session
* Token
* API Key
* Application Registry
* Application Admin
* Audit keamanan

UserGateway **tidak bertanggung jawab** atas role dan permission bisnis aplikasi.

---

## 4. User Identity

Setiap user memiliki satu identity terpusat.

Contoh:

```text
User ID : usr_001
Name    : Budi Santoso
Email   : budi@example.com
Status  : ACTIVE
```

User dapat menggunakan identity yang sama untuk:

```text
HRIS
Finance
Inventory
CRM
Helpdesk
```

Aplikasi menggunakan `user_id` dari UserGateway sebagai identitas utama.

---

## 5. Status User

Status utama:

```text
ACTIVE
INACTIVE
```

### ACTIVE

User dapat:

* Login ke UserGateway
* Login/SSO ke aplikasi terintegrasi
* Mengakses profil sendiri

### INACTIVE

User tidak dapat:

* Login baru
* Mendapatkan session baru
* Mendapatkan token baru

Session/token yang sudah ada harus memiliki mekanisme expiration/revocation sehingga pencabutan akses dapat berlaku dengan cepat.

---

## 6. User Portal

User dapat login langsung ke UserGateway untuk mengelola identitasnya.

Fitur:

* Melihat profil
* Mengubah nama
* Mengubah email
* Mengubah nomor telepon
* Mengubah foto
* Mengubah password
* Mengatur MFA
* Melihat session/device
* Logout dari session
* Melihat aktivitas login

UserGateway menjadi **Single Source of Truth** untuk data identity.

---

## 7. Application

Setiap aplikasi baru harus didaftarkan di UserGateway.

Contoh:

```text
Application
├── HRIS
├── Finance
├── Inventory
├── CRM
└── Helpdesk
```

Informasi aplikasi dapat meliputi:

```text
application_id
name
description
status
client_id
client_secret
redirect_uri
created_at
updated_at
```

Aplikasi menggunakan UserGateway sebagai Identity Provider.

---

## 8. Application Admin

Application Admin adalah administrator teknis/koneksi untuk sebuah aplikasi di UserGateway.

Contoh:

```text
UserGateway
└── HRIS
    └── Application Admin
        └── Budi
```

Application Admin dapat:

* Mengelola konfigurasi aplikasi
* Mengelola OAuth/OIDC configuration
* Mengelola Redirect URI
* Mengelola API Key
* Melakukan rotation credential
* Melihat status integrasi
* Melihat audit aktivitas aplikasi
* Mengelola konfigurasi teknis aplikasi

Application Admin **tidak mengelola role/permission bisnis user**.

Contoh yang bukan tanggung jawab Application Admin:

```text
HR Manager
Finance Manager
Employee.Delete
Salary.View
Invoice.Approve
```

Semua itu dikelola oleh aplikasi masing-masing.

---

## 9. Role UserGateway

UserGateway cukup memiliki tiga role utama:

### Super Admin

Memiliki akses penuh terhadap UserGateway.

Dapat:

* Mengelola seluruh user
* Mengubah status user
* Mengelola aplikasi
* Menentukan Application Admin
* Mengelola konfigurasi sistem
* Melihat audit log
* Mengelola konfigurasi keamanan

### Application Admin

Memiliki akses administratif terhadap aplikasi yang ditugaskan.

Dapat:

* Mengelola konfigurasi aplikasi
* Mengelola credential aplikasi
* Mengelola API Key
* Mengelola integrasi SSO
* Melihat audit aplikasi

Tidak dapat mengubah role/permission bisnis aplikasi.

### User

Dapat:

* Mengelola profil sendiri
* Mengubah password
* Mengelola MFA
* Mengelola session/device
* Melihat aktivitas login

---

## 10. Role & Permission Aplikasi

Role dan permission **tidak disimpan di UserGateway**.

Contoh HRIS:

```text
HRIS
├── HR Manager
├── HR Staff
└── Viewer
```

Finance:

```text
Finance
├── Finance Manager
├── Accountant
└── Viewer
```

Inventory:

```text
Inventory
├── Warehouse Manager
├── Warehouse Staff
└── Viewer
```

Setiap aplikasi memiliki database authorization sendiri.

---

## 11. Prinsip Authorization

Pembagian tanggung jawab:

```text
UserGateway
    │
    └── WHO ARE YOU?
         ├── Identity
         ├── Authentication
         └── ACTIVE / INACTIVE

Application
    │
    └── WHAT CAN YOU DO?
         ├── Role
         ├── Permission
         ├── Feature Access
         └── Business Authorization
```

Dengan demikian UserGateway tidak menjadi database pusat seluruh permission aplikasi.

---

## 12. Authentication & SSO

Integrasi aplikasi menggunakan:

* OAuth 2.0
* OpenID Connect
* Authorization Code + PKCE
* Client Credentials untuk service-to-service
* Refresh Token
* API Key untuk kebutuhan tertentu

Alur:

```text
User
  │
  ▼
HRIS
  │
  │ belum login
  ▼
UserGateway
  │
  │ Login
  ▼
UserGateway
  │
  │ Identity / Authorization Code
  ▼
HRIS
  │
  ▼
User authenticated
```

Jika user sudah login di UserGateway, aplikasi lain dapat menggunakan session SSO tersebut.

---

## 13. API Key

API Key digunakan terutama untuk kebutuhan integrasi API/service.

API Key memiliki lifecycle:

```text
Create
  ↓
Use
  ↓
Rotate
  ↓
Expire / Revoke
```

API Key sebaiknya memiliki:

```text
id
application_id
name
key_hash
prefix
created_at
expires_at
last_used_at
revoked_at
status
```

Secret hanya ditampilkan ketika pertama kali dibuat dan disimpan secara aman dalam bentuk hash/secret storage yang sesuai.

---

## 14. Database Core

Struktur awal UserGateway:

```text
users

applications

application_admins

sessions

oauth_clients
oauth_authorization_codes
oauth_access_tokens
oauth_refresh_tokens

api_keys

mfa_methods

password_resets
email_verifications

audit_logs
```

Tidak perlu:

```text
roles
permissions
user_roles
role_permissions
user_application_access
```

karena authorization bisnis berada di aplikasi masing-masing.

---

## 15. Prinsip Arsitektur

### Single Source of Truth

Identity user hanya berasal dari UserGateway.

### Separation of Responsibility

UserGateway:

> Identity & Authentication

Aplikasi:

> Authorization & Business Logic

### Least Privilege

Credential/API aplikasi hanya diberikan akses sesuai kebutuhan.

### Centralized Identity

Satu user dapat digunakan oleh seluruh aplikasi.

### Independent Authorization

Setiap aplikasi bebas mengembangkan role dan permission tanpa mengubah UserGateway.

---

## 16. Teknologi Awal

Untuk tahap awal disarankan:

```text
Architecture : Modular Monolith
Database     : PostgreSQL
Cache        : Redis
Authentication: OAuth 2.0 / OpenID Connect
API          : REST API
```

Tidak perlu langsung menggunakan microservices.

Modul backend dapat dipisahkan:

```text
UserGateway
├── Identity
├── Authentication
├── Authorization Platform
├── OAuth/OIDC
├── Application
├── API Key
├── Session
├── MFA
├── Audit
└── Notification
```

---

## 17. Prinsip Final

```text
                    USERGATEWAY
                         │
              ┌──────────┴──────────┐
              │                     │
          Identity              Application
              │                     │
       ACTIVE / INACTIVE       SSO / OAuth
              │                     │
              └──────────┬──────────┘
                         │
                  User Identity
                         │
          ┌──────────────┼──────────────┐
          ▼              ▼              ▼
        HRIS           Finance        Inventory
          │              │              │
       Role/Perm       Role/Perm      Role/Perm
          │              │              │
       HRIS owns      Finance owns   Inventory owns
```

**Kesimpulan:**

> **UserGateway adalah gerbang identitas seluruh ekosistem aplikasi.**
>
> User cukup memiliki satu akun dan satu identity.
>
> User `ACTIVE` dapat login ke seluruh aplikasi yang terintegrasi.
>
> User `INACTIVE` tidak dapat melakukan authentication.
>
> UserGateway mengelola identity, authentication, SSO, token, API key, dan aplikasi.
>
> Setiap aplikasi mengelola sendiri role, permission, dan business authorization-nya.
>
> **UserGateway tidak menyimpan role/permission dari aplikasi lain.**
