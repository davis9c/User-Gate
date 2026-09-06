# I-18 Final Release Checklist

- [x] I-10 Authentication API: API-key-bound login, bearer access token, refresh rotation, `/me`, logout, and RBAC filter integration.
- [x] I-11 Authentication Security: random opaque tokens, hashed persistence, expiry, revocation, neutral login errors, and IP rate limiting.
- [x] I-12 Client Integration: request/response examples in `AUTHENTICATION.md`.
- [x] I-13 API Documentation: authentication contract, headers, token behavior, errors, and authorization model documented.
- [x] I-14 Audit & Logging: database audit events with explicit credential redaction.
- [x] I-15 Automated Testing: token-generation unit coverage; endpoint scenarios are listed in deployment verification.
- [x] I-16 Production Deployment: deployment and migration instructions documented.
- [x] I-17 Production Verification: HTTPS/security/auth/RBAC/API-key validation checklist documented.
- [x] I-18 Release: checklist complete; local branch only, with no commit or push.

Before production release, apply migrations in the target environment and execute the endpoint scenarios against its configured database. The repository test suite needs PHP's `sqlite3` extension for its database tests.
