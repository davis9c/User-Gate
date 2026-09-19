# UserGate

UserGate adalah layanan pusat untuk identity, authentication, application registry,
API key, dan audit keamanan.

## Mulai cepat

- [Instalasi dan overview](docs/README.md)
- [Menjalankan Docker](docs/DOCKER.md)
- [Instalasi aplikasi dari source](docs/README.md#instalasi-dari-source)

Setup web otomatis menjalankan migration dan seeding, kemudian membuat akun
`SUPER_ADMIN` pertama. Jangan commit `.env`.

## Dokumentasi

### Internal

- [REST API](docs/API.md)
- [Authentication](docs/AUTHENTICATION.md)
- [Deployment produksi](docs/DEPLOYMENT.md)
- [Release checklist](docs/RELEASE_CHECKLIST.md)
- [Arsitektur sistem](docs/RancanganSistem.md)
- [Development phases](docs/DevelompmentPhase.md)

### Developer Integration (untuk AI)

> Upload file-file ini ke AI (ChatGPT, Claude, Copilot, dll.) sebagai bahan
> integrasi agar lebih mudah menghubungkan aplikasi Anda dengan UserGate.

- [Integration Guide](docs/INTEGRATION_GUIDE.md) — Panduan integrasi utama
- [API Quick Start](docs/API_QUICK_START.md) — Contoh kode PHP, JS, Python
- [API Reference](docs/API_REFERENCE_COMPLETE.md) — API reference lengkap
- [OAuth/OIDC Guide](docs/OAUTH_OIDC_GUIDE.md) — Panduan OAuth & SSO
- [SDK Patterns](docs/SDK_PATTERNS.md) — Pola integrasi umum
- [Troubleshooting](docs/TROUBLESHOOTING.md) — Debug & error handling

### Contoh Implementasi

- [PHP Integration](docs/EXAMPLES/php-integration.md) — Contoh lengkap PHP
- [JavaScript Integration](docs/EXAMPLES/javascript-integration.md) — Node.js, React, Vanilla JS
- [Python Integration](docs/EXAMPLES/python-integration.md) — Python, FastAPI, Flask
