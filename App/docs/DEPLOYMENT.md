# Production Deployment and Release Verification

1. Deploy application code without `.env`; configure database credentials, `app.baseURL`, production environment, HTTPS, and a writable cache/log directory on the server.
2. Run `php spark migrate --all` once per environment. This creates `auth_tokens` and `audit_logs` in addition to the existing schema.
3. Configure TLS at the web server and verify that HTTP redirects to HTTPS. Secure headers, cookies, CSRF (except the JSON API), and production error handling remain enabled by the application configuration.
4. Create an active application and API key, then verify login, `/me`, refresh, logout, revoked-token rejection, API-key rejection, rate limit, RBAC permission grant/denial, and API permission grant/denial.
5. Inspect `audit_logs` for `LOGIN_SUCCESS`, `LOGIN_FAILED`, `TOKEN_ISSUED`, `TOKEN_REVOKED`, `LOGOUT`, and `PERMISSION_DENIED`. Logs must contain neither passwords nor credentials.

Release checklist: migrations applied, HTTPS confirmed, secrets exist only in the environment, application/API key states checked, authentication tests green, and a rollback backup exists. No release command or Git push is performed by this change.
