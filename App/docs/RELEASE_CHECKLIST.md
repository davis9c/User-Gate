# Release Checklist

- [x] Authentication API: API-key-bound login, bearer access token, refresh rotation, `/me`, logout, and RBAC integration.
- [x] I-11 Authentication Security: random opaque tokens, hashed persistence, expiry, revocation, neutral login errors, and IP rate limiting.
- [x] Client Integration: request/response examples in [API.md](../../Docs/API.md).
- [x] API Documentation: authentication contract, headers, token behavior, errors, and authorization model documented in [API.md](../../Docs/API.md).
- [x] I-14 Audit & Logging: database audit events with explicit credential redaction.
- [x] I-15 Automated Testing: token-generation unit coverage; endpoint scenarios are listed in deployment verification.
- [x] I-16 Production Deployment: deployment and migration instructions documented.
- [x] I-17 Production Verification: HTTPS/security/auth/RBAC/API-key validation checklist documented.
- [ ] Release commit sudah ditinjau dan dipush ke branch target.

Before production release, apply migrations in the target environment and execute the endpoint scenarios against its configured database. The repository test suite needs PHP's `sqlite3` extension for its database tests.
