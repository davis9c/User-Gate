# UserGate

UserGate adalah layanan pusat untuk identity, authentication, application registry,
API key, dan audit keamanan.

## Mulai cepat

- [Instalasi dan overview](Docs/README.md)
- [Menjalankan Docker](Docker/README.md)
- [Menyiapkan MySQL eksternal](Database/README.md)
- [Instalasi aplikasi dari source](Docs/README.md#instalasi-dari-source)

Setup web otomatis menjalankan migration dan seeding, kemudian membuat akun
`SUPER_ADMIN` pertama. Jangan commit `Docker/.env` atau `App/.env`.

## Dokumentasi

- [REST API](Docs/API.md)
- [Authentication, token, dan RBAC](App/docs/AUTHENTICATION.md)
- [Deployment produksi](App/docs/DEPLOYMENT.md)
- [Release checklist](App/docs/RELEASE_CHECKLIST.md)
- [Arsitektur sistem](Docs/RancanganSistem.MD)
- [Development phases](Docs/DevelompmentPhase.MD)
- [Testing](App/tests/README.md)
