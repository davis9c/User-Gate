<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <style>
        :root { --ink:#172033; --muted:#64748b; --line:#e2e8f0; --brand:#4f46e5; --soft:#eef2ff; --code:#0f172a; }
        * { box-sizing:border-box; } body { margin:0; color:var(--ink); background:#f8fafc; font:15px/1.6 Inter,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif; }
        a { color:inherit; text-decoration:none; } .container { width:min(1160px,calc(100% - 40px)); margin:auto; }
        header, footer { background:#fff; border-color:var(--line); border-style:solid; } header { border-width:0 0 1px; } footer { border-width:1px 0 0; padding:26px 0; color:var(--muted); font-size:13px; }
        .nav { height:72px; display:flex; align-items:center; justify-content:space-between; } .brand { font-size:20px; font-weight:800; } .brand b { display:inline-grid; place-items:center; width:34px; height:34px; margin-right:8px; border-radius:9px; background:#111827; color:#fff; }
        .button { padding:9px 14px; border-radius:8px; background:#111827; color:#fff; font-weight:700; }
        .hero { padding:62px 0 40px; background:#fff; } .eyebrow { color:var(--brand); font-size:13px; font-weight:800; letter-spacing:.08em; text-transform:uppercase; } h1 { margin:8px 0 12px; font-size:clamp(34px,5vw,52px); letter-spacing:-.04em; line-height:1.1; } .hero p { max-width:720px; color:var(--muted); font-size:17px; }
        .layout { display:grid; grid-template-columns:225px minmax(0,1fr); gap:36px; padding:38px 0 64px; } .toc { position:sticky; top:20px; align-self:start; padding:18px; background:#fff; border:1px solid var(--line); border-radius:12px; } .toc strong { display:block; margin-bottom:8px; } .toc a { display:block; padding:6px 0; color:var(--muted); font-size:14px; } .toc a:hover { color:var(--brand); }
        section { margin-bottom:46px; scroll-margin-top:20px; } h2 { margin:0 0 10px; font-size:27px; letter-spacing:-.02em; } p, li { color:#475569; } .notice { padding:15px 18px; border-left:4px solid var(--brand); border-radius:0 8px 8px 0; background:var(--soft); color:#3730a3; }
        .endpoint { overflow:hidden; margin-top:18px; border:1px solid var(--line); border-radius:12px; background:#fff; } .endpoint-head { display:flex; gap:10px; align-items:center; padding:14px 18px; border-bottom:1px solid var(--line); font-weight:750; } .endpoint-body { padding:18px; } .endpoint-body p { margin:0 0 10px; }
        .method { min-width:51px; padding:3px 7px; border-radius:5px; color:#fff; font-size:12px; text-align:center; } .get { background:#059669; } .post { background:#2563eb; } .put { background:#d97706; } .delete { background:#dc2626; }
        .code-wrap { position:relative; margin:14px 0; } .code-label { display:block; margin-bottom:5px; color:#64748b; font-size:12px; font-weight:800; text-transform:uppercase; } pre { margin:0; padding:18px; overflow:auto; border-radius:9px; background:var(--code); color:#dbeafe; font:13px/1.55 "SFMono-Regular",Consolas,monospace; white-space:pre; } .copy { position:absolute; top:29px; right:9px; padding:6px 10px; border:1px solid #475569; border-radius:6px; background:#1e293b; color:#e2e8f0; cursor:pointer; font:600 12px inherit; } .copy:hover { background:#334155; }
        table { width:100%; border-collapse:collapse; overflow:hidden; border:1px solid var(--line); border-radius:10px; background:#fff; } th,td { padding:12px 14px; border-bottom:1px solid var(--line); text-align:left; } th { background:#f8fafc; font-size:13px; } td { color:#475569; } code { padding:2px 5px; border-radius:4px; background:#e2e8f0; color:#334155; }
        @media(max-width:760px) { .layout { grid-template-columns:1fr; gap:20px; } .toc { position:static; } h1 { font-size:36px; } }
    </style>
</head>
<body>
<header><div class="container nav"><a class="brand" href="/"><b>U</b>UserGate</a><a href="/login" class="button">Dashboard</a></div></header>
<main>
    <div class="hero"><div class="container"><div class="eyebrow">Developer documentation · v1</div><h1>REST API UserGate</h1><p>Gunakan API key untuk mengidentifikasi aplikasi, lalu bearer token untuk request yang mewakili user yang sudah login.</p></div></div>
    <div class="container layout">
        <aside class="toc"><strong>Daftar isi</strong><a href="#start">Memulai</a><a href="#auth">Authentication</a><a href="#me">Current user</a><a href="#users">Users API</a><a href="#errors">Error response</a></aside>
        <article>
            <section id="start">
                <h2>Memulai</h2>
                <p>Base URL: <code><?= esc(rtrim(site_url(), '/')) ?>/api/v1</code>. Semua request API membutuhkan <code>X-API-Key</code>. Jangan mengirimkan API key atau token melalui URL.</p>
                <div class="notice">Simpan password, API key, access token, dan refresh token secara aman. Nilai ini tidak boleh disimpan dalam source control atau log aplikasi.</div>
                <div class="code-wrap"><span class="code-label">Header dasar</span><pre>Content-Type: application/json
X-API-Key: ugk_live_your_api_key</pre><button class="copy" type="button">Copy</button></div>
            </section>
            <section id="auth">
                <h2>Authentication</h2>
                <p>Access token berlaku 15 menit. Refresh token berlaku 30 hari, terikat pada API key penerbitnya, dan dirotasi setiap berhasil digunakan.</p>
                <div class="endpoint"><div class="endpoint-head"><span class="method post">POST</span> /auth/login</div><div class="endpoint-body">
                    <p>Login dengan username dan password. Pesan kegagalan dibuat konsisten untuk mencegah user enumeration.</p>
                    <div class="code-wrap"><span class="code-label">Request</span><pre>POST /api/v1/auth/login
X-API-Key: ugk_live_your_api_key
Content-Type: application/json

{
  "username": "budi",
  "password": "correct horse battery staple"
}</pre><button class="copy" type="button">Copy</button></div>
                    <div class="code-wrap"><span class="code-label">Response · 200 OK</span><pre>{
  "status": true,
  "message": "Authenticated successfully.",
  "data": {
    "access_token": "ACCESS_TOKEN",
    "token_type": "Bearer",
    "expires_in": 900,
    "refresh_token": "REFRESH_TOKEN",
    "refresh_expires_in": 2592000,
    "user": {
      "id": "uuid",
      "username": "budi",
      "email": "budi@example.com",
      "full_name": "Budi Santoso",
      "status": "ACTIVE",
      "roles": ["SUPER_ADMIN"],
      "is_super_admin": true
    }
  }
}</pre><button class="copy" type="button">Copy</button></div>
                    <p>Objek <code>user</code> berisi daftar role dan penanda <code>is_super_admin</code> (true bila user memegang role <code>SUPER_ADMIN</code>).</p>
                </div></div>
                <div class="endpoint"><div class="endpoint-head"><span class="method post">POST</span> /auth/refresh</div><div class="endpoint-body">
                    <p>Gunakan hanya untuk memperbarui access token. Refresh token lama tidak dapat dipakai lagi setelah request sukses.</p>
                    <div class="code-wrap"><span class="code-label">Request</span><pre>POST /api/v1/auth/refresh
X-API-Key: ugk_live_your_api_key
Content-Type: application/json

{ "refresh_token": "REFRESH_TOKEN" }</pre><button class="copy" type="button">Copy</button></div>
                    <div class="code-wrap"><span class="code-label">Response · 200 OK</span><pre>{
  "status": true,
  "message": "Token refreshed successfully.",
  "data": {
    "access_token": "NEW_ACCESS_TOKEN",
    "token_type": "Bearer",
    "expires_in": 900,
    "refresh_token": "NEW_REFRESH_TOKEN",
    "refresh_expires_in": 2592000,
    "user": {
      "id": "uuid",
      "username": "budi",
      "email": "budi@example.com",
      "full_name": "Budi Santoso",
      "status": "ACTIVE",
      "roles": ["SUPER_ADMIN"],
      "is_super_admin": true
    }
  }
}</pre><button class="copy" type="button">Copy</button></div>
                </div></div>
                <div class="endpoint"><div class="endpoint-head"><span class="method post">POST</span> /auth/logout</div><div class="endpoint-body">
                    <p>Menarik kembali token access dan refresh untuk sesi saat ini.</p>
                    <div class="code-wrap"><span class="code-label">Request</span><pre>POST /api/v1/auth/logout
X-API-Key: ugk_live_your_api_key
Authorization: Bearer ACCESS_TOKEN</pre><button class="copy" type="button">Copy</button></div>
                    <div class="code-wrap"><span class="code-label">Response · 200 OK</span><pre>{ "status": true, "message": "Logged out successfully.", "data": null }</pre><button class="copy" type="button">Copy</button></div>
                </div></div>
            </section>
            <section id="me"><h2>Current user</h2><div class="endpoint"><div class="endpoint-head"><span class="method get">GET</span> /auth/me</div><div class="endpoint-body">
                <p>Mengembalikan identity user dari access token. Password tidak pernah dikembalikan.</p>
                <div class="code-wrap"><span class="code-label">Request</span><pre>GET /api/v1/auth/me
X-API-Key: ugk_live_your_api_key
Authorization: Bearer ACCESS_TOKEN</pre><button class="copy" type="button">Copy</button></div>
                <div class="code-wrap"><span class="code-label">Response · 200 OK</span><pre>{
  "status": true,
  "message": "Current user retrieved successfully.",
  "data": {
    "id": "uuid",
    "username": "budi",
    "email": "budi@example.com",
    "full_name": "Budi Santoso",
    "status": "ACTIVE",
    "roles": ["SUPER_ADMIN"],
    "is_super_admin": true
  }
}</pre><button class="copy" type="button">Copy</button></div>
            </div></div></section>
            <section id="users"><h2>Users API</h2><p>Endpoint ini membutuhkan API key aktif dengan permission sesuai.</p>
                <table><thead><tr><th>Method</th><th>Endpoint</th><th>Permission</th></tr></thead><tbody><tr><td>GET</td><td><code>/users</code>, <code>/users/{id}</code></td><td>user.read</td></tr><tr><td>POST</td><td><code>/users</code></td><td>user.create</td></tr><tr><td>PUT</td><td><code>/users/{id}</code></td><td>user.update</td></tr><tr><td>DELETE</td><td><code>/users/{id}</code></td><td>user.delete</td></tr></tbody></table>
                <div class="endpoint"><div class="endpoint-head"><span class="method post">POST</span> /users</div><div class="endpoint-body">
                    <div class="code-wrap"><span class="code-label">Request</span><pre>POST /api/v1/users
X-API-Key: ugk_live_your_api_key
Content-Type: application/json

{
  "username": "budi",
  "email": "budi@example.com",
  "full_name": "Budi Santoso",
  "password": "a-strong-password"
}</pre><button class="copy" type="button">Copy</button></div>
                    <div class="code-wrap"><span class="code-label">Response · 201 Created</span><pre>{
  "status": true,
  "message": "User created successfully.",
  "data": { "id": "uuid", "username": "budi", "email": "budi@example.com", "full_name": "Budi Santoso", "status": "ACTIVE" }
}</pre><button class="copy" type="button">Copy</button></div>
                </div></div>
                <div class="endpoint"><div class="endpoint-head"><span class="method get">GET</span> /users</div><div class="endpoint-body">
                    <p>Daftar user mendukung query opsional <code>search</code>, <code>page</code>, dan <code>per_page</code>.</p>
                    <div class="code-wrap"><span class="code-label">Request</span><pre>GET /api/v1/users?search=budi&page=1&per_page=20
X-API-Key: ugk_live_your_api_key</pre><button class="copy" type="button">Copy</button></div>
                    <div class="code-wrap"><span class="code-label">Response · 200 OK</span><pre>{
  "status": true,
  "message": "Users retrieved successfully.",
  "data": [{ "id": "uuid", "username": "budi", "email": "budi@example.com", "full_name": "Budi Santoso", "status": "ACTIVE" }],
  "meta": { "page": 1, "per_page": 20, "total": 1, "total_pages": 1 }
}</pre><button class="copy" type="button">Copy</button></div>
                </div></div>
                <div class="endpoint"><div class="endpoint-head"><span class="method put">PUT</span> /users/{id}</div><div class="endpoint-body">
                    <div class="code-wrap"><span class="code-label">Request</span><pre>PUT /api/v1/users/USER_UUID
X-API-Key: ugk_live_your_api_key
Content-Type: application/json

{
  "username": "budi",
  "email": "budi@example.com",
  "full_name": "Budi Santoso",
  "status": "ACTIVE"
}</pre><button class="copy" type="button">Copy</button></div>
                    <div class="code-wrap"><span class="code-label">Response · 200 OK</span><pre>{
  "status": true,
  "message": "User updated successfully.",
  "data": { "id": "uuid", "username": "budi", "email": "budi@example.com", "full_name": "Budi Santoso", "status": "ACTIVE" }
}</pre><button class="copy" type="button">Copy</button></div>
                </div></div>
                <div class="endpoint"><div class="endpoint-head"><span class="method delete">DELETE</span> /users/{id}</div><div class="endpoint-body">
                    <div class="code-wrap"><span class="code-label">Request</span><pre>DELETE /api/v1/users/USER_UUID
X-API-Key: ugk_live_your_api_key</pre><button class="copy" type="button">Copy</button></div>
                    <div class="code-wrap"><span class="code-label">Response · 200 OK</span><pre>{
  "status": true,
  "message": "User deleted successfully.",
  "data": { "id": "uuid", "username": "budi" }
}</pre><button class="copy" type="button">Copy</button></div>
                </div></div>
            </section>
            <section id="errors"><h2>Error response</h2>
                <table><thead><tr><th>Status</th><th>Makna</th></tr></thead><tbody><tr><td>401</td><td>API key, credentials, atau token tidak valid/expired</td></tr><tr><td>403</td><td>API key tidak memiliki permission</td></tr><tr><td>422</td><td>Data request tidak valid</td></tr><tr><td>429</td><td>Terlalu banyak percobaan login</td></tr></tbody></table>
                <div class="code-wrap"><span class="code-label">Contoh response · 401</span><pre>{ "status": false, "message": "Invalid credentials." }</pre><button class="copy" type="button">Copy</button></div>
            </section>
        </article>
    </div>
</main>
<footer><div class="container">&copy; <?= date('Y') ?> UserGate · REST API Documentation</div></footer>
<script>
document.querySelectorAll('.copy').forEach((button) => {
    button.addEventListener('click', async () => {
        const text = button.previousElementSibling.textContent;
        try {
            await navigator.clipboard.writeText(text);
        } catch (error) {
            const area = document.createElement('textarea');
            area.value = text; document.body.appendChild(area); area.select();
            document.execCommand('copy'); area.remove();
        }
        button.textContent = 'Copied!';
        setTimeout(() => { button.textContent = 'Copy'; }, 1800);
    });
});
</script>
</body>
</html>
