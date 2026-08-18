# User-Gate Authentication API

## Contract

An API key identifies the client application; it is not a user credential. Send it on every authentication request in `X-API-Key`. A successful password login returns opaque, cryptographically random bearer and refresh tokens. The server stores only SHA-256 hashes of those values.

| Endpoint | Credential | Purpose |
| --- | --- | --- |
| `POST /api/v1/auth/login` | API key, username, password | Creates a user authentication state |
| `POST /api/v1/auth/refresh` | API key, refresh token | Rotates refresh token and issues a new pair |
| `GET /api/v1/auth/me` | API key, bearer token | Returns the current safe identity |
| `POST /api/v1/auth/logout` | API key, bearer token | Revokes the current token pair |

## Login

```http
POST /api/v1/auth/login
X-API-Key: ug_live_…
Content-Type: application/json

{"username":"budi","password":"correct horse battery staple"}
```

```json
{
  "status": true,
  "message": "Authenticated successfully.",
  "data": {
    "access_token": "…",
    "token_type": "Bearer",
    "expires_in": 900,
    "refresh_token": "…",
    "refresh_expires_in": 2592000
  }
}
```

All failed login attempts, including unknown users and inactive accounts, return `401` with `Invalid credentials.`. Login is limited to five requests per IP per minute. Do not log, place in URLs, or store tokens/passwords in source control.

## Protected request, current identity, logout

```http
GET /api/v1/auth/me
X-API-Key: ug_live_…
Authorization: Bearer <access_token>
```

`/me` returns only `id`, `username`, `email`, `full_name`, and `status`; password fields are never returned. Use the same headers with `POST /api/v1/auth/logout`. A revoked, invalid, expired, or inactive-user token returns `401` and cannot be reused.

## Refresh

```http
POST /api/v1/auth/refresh
X-API-Key: ug_live_…
Content-Type: application/json

{"refresh_token":"…"}
```

Refresh tokens are valid for 30 days, bound to the issuing API key, and rotated on use. They must only be sent to this endpoint.

## RBAC and errors

Use the `accessToken` followed by `userPermission:<permission-code>` route filters for user-authorized resources. `RolePermission` resolves the authenticated user through `user_roles`, `roles`, `role_permissions`, and `api_permissions`. Existing `apiPermission` remains application-level authorization.

Responses have `status`, `message`, and (on success) `data`. Expected statuses: `200`, `401`, `403`, `422`, and `429`.
