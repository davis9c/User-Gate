# Contoh Implementasi

> **Cara Pakai Dokumen Ini:**
> Upload file ini ke AI lalu minta:
> "Buatkan saya integrasi UserGate dengan [bahasa/framework]"
> Pilih contoh yang sesuai dengan bahasa/framework yang Anda gunakan.

---

## Daftar Contoh

| Bahasa/Framework | File | Deskripsi |
|-----------------|------|-----------|
| **PHP** | [php-integration.md](php-integration.md) | Contoh lengkap PHP dengan cURL |
| **JavaScript** | [javascript-integration.md](javascript-integration.md) | Node.js, React, Vanilla JS |
| **Python** | [python-integration.md](python-integration.md) | Python, FastAPI, Flask |

---

## Pilih Contoh Berdasarkan Kebutuhan

### Backend Web App

| Bahasa | Framework | File |
|--------|-----------|------|
| PHP | CodeIgniter | php-integration.md |
| PHP | Laravel | php-integration.md |
| JavaScript | Express.js | javascript-integration.md |
| JavaScript | NestJS | javascript-integration.md |
| Python | Django | python-integration.md |
| Python | Flask | python-integration.md |
| Python | FastAPI | python-integration.md |

### Frontend Web App

| Framework | File |
|-----------|------|
| React | javascript-integration.md |
| Next.js | javascript-integration.md |
| Vue.js | javascript-integration.md |
| Vanilla JS | javascript-integration.md |

### Mobile App

| Platform | Bahasa | File |
|----------|--------|------|
| Android | Kotlin | API_QUICK_START.md |
| iOS | Swift | API_QUICK_START.md |
| Cross-platform | Flutter/Dart | API_QUICK_START.md |

---

## Struktur Code di Setiap Contoh

Setiap file contoh menyediakan:

1. **Config** — Konfigurasi environment variables
2. **Client** — Base client untuk API calls
3. **Auth** — Authentication (login, refresh, logout)
4. **User** — CRUD operations
5. **Error Handling** — Centralized error handling
6. **Usage Examples** — Contoh penggunaan lengkap

---

## Quick Start

Pilih bahasa Anda dan ikuti langkah-langkahnya:

### PHP

```bash
# 1. Buat project
mkdir my-app && cd my-app

# 2. Buat file config
cat > config/usergate.php << 'EOF'
<?php
return [
    'base_url' => 'https://usergate.sandalgurun.web.id/api/v1',
    'api_key'  => 'ug_xxxxxxxxxxxxxxxx',
];
EOF

# 3. Copy source code dari php-integration.md

# 4. Jalankan
php public/index.php
```

### JavaScript (Node.js)

```bash
# 1. Buat project
mkdir my-app && cd my-app
npm init -y
npm install dotenv

# 2. Buat .env
echo "USERGATE_BASE_URL=https://usergate.sandalgurun.web.id/api/v1" > .env
echo "USERGATE_API_KEY=ug_xxxxxxxxxxxxxxxx" >> .env

# 3. Copy source code dari javascript-integration.md

# 4. Jalankan
node index.js
```

### Python

```bash
# 1. Buat project
mkdir my-app && cd my-app
python -m venv venv
source venv/bin/activate
pip install requests python-dotenv

# 2. Buat .env
echo "USERGATE_BASE_URL=https://usergate.sandalgurun.web.id/api/v1" > .env
echo "USERGATE_API_KEY=ug_xxxxxxxxxxxxxxxx" >> .env

# 3. Copy source code dari python-integration.md

# 4. Jalankan
python main.py
```

---

## Referensi Lainnya

| Dokumentasi | Fungsi |
|-------------|--------|
| [Integration Guide](../INTEGRATION_GUIDE.md) | Panduan integrasi utama |
| [API Quick Start](../API_QUICK_START.md) | Contoh kode cepat |
| [API Reference](../API_REFERENCE_COMPLETE.md) | API reference lengkap |
| [OAuth/OIDC Guide](../OAUTH_OIDC_GUIDE.md) | Panduan OAuth & SSO |
| [SDK Patterns](../SDK_PATTERNS.md) | Pola integrasi umum |
| [Troubleshooting](../TROUBLESHOOTING.md) | Debug & error handling |
