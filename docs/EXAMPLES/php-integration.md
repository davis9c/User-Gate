# PHP Integration Example — Lengkap

> **Cara Pakai Dokumen Ini:**
> Upload file ini ke AI lalu minta:
> "Buatkan saya aplikasi PHP yang terintegrasi dengan UserGate"
> atau "Bagaimana cara integrasi UserGate dengan CodeIgniter/Laravel?"
> AI akan memberikan kode PHP yang sudah sesuai.

---

## Setup Awal

### Environment Variables

```ini
# .env
USERGATE_BASE_URL=https://usergate.sandalgurun.web.id/api/v1
USERGATE_API_KEY=ug_xxxxxxxxxxxxxxxx
```

### Struktur Folder

```
your-app/
├── .env
├── config/
│   └── usergate.php
├── src/
│   ├── UserGateClient.php
│   ├── UserGateAuth.php
│   └── UserGateUser.php
└── public/
    └── index.php
```

---

## Config

```php
<?php
// config/usergate.php

return [
    'base_url' => getenv('USERGATE_BASE_URL') ?: 'https://usergate.sandalgurun.web.id/api/v1',
    'api_key'  => getenv('USERGATE_API_KEY')  ?: 'ug_xxxxxxxxxxxxxxxx',
];
```

---

## UserGateClient — Base Client

```php
<?php
// src/UserGateClient.php

class UserGateClient
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct(string $baseUrl, string $apiKey)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey  = $apiKey;
    }

    public function fromConfig(): self
    {
        $config = require __DIR__ . '/../config/usergate.php';
        return new self($config['base_url'], $config['api_key']);
    }

    /**
     * Kirim request ke UserGate API
     */
    public function request(
        string $method,
        string $endpoint,
        array $data = [],
        ?string $token = null,
        array $queryParams = []
    ): array {
        $url = $this->baseUrl . $endpoint;

        // Tambahkan query params jika ada
        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }

        $headers = [
            "X-API-Key: {$this->apiKey}",
            "Content-Type: application/json",
            "Accept: application/json",
        ];

        if ($token) {
            $headers[] = "Authorization: Bearer {$token}";
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_POSTFIELDS     => !empty($data) ? json_encode($data) : null,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = json_decode(curl_exec($ch), true);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new RuntimeException("cURL Error: {$error}");
        }

        $response['http_code'] = $httpCode;

        return $response;
    }

    // Convenience methods
    public function get(string $endpoint, ?string $token = null, array $query = []): array
    {
        return $this->request('GET', $endpoint, [], $token, $query);
    }

    public function post(string $endpoint, array $data = [], ?string $token = null): array
    {
        return $this->request('POST', $endpoint, $data, $token);
    }

    public function put(string $endpoint, array $data = [], ?string $token = null): array
    {
        return $this->request('PUT', $endpoint, $data, $token);
    }

    public function delete(string $endpoint, ?string $token = null): array
    {
        return $this->request('DELETE', $endpoint, [], $token);
    }
}
```

---

## UserGateAuth — Authentication

```php
<?php
// src/UserGateAuth.php

class UserGateAuth
{
    private UserGateClient $client;
    private ?string $accessToken  = null;
    private ?string $refreshToken = null;

    public function __construct(UserGateClient $client)
    {
        $this->client = $client;
    }

    /**
     * Login dan dapatkan token
     */
    public function login(string $username, string $password): array
    {
        $response = $this->client->post('/auth/login', [
            'username' => $username,
            'password' => $password,
        ]);

        if ($response['status']) {
            $this->accessToken  = $response['data']['access_token'];
            $this->refreshToken = $response['data']['refresh_token'];
        }

        return $response;
    }

    /**
     * Refresh token
     */
    public function refresh(): array
    {
        if (!$this->refreshToken) {
            return ['status' => false, 'message' => 'No refresh token available'];
        }

        $response = $this->client->post('/auth/refresh', [
            'refresh_token' => $this->refreshToken,
        ]);

        if ($response['status']) {
            $this->accessToken  = $response['data']['access_token'];
            $this->refreshToken = $response['data']['refresh_token'];
        }

        return $response;
    }

    /**
     * Ambil data user yang sedang login
     */
    public function me(): array
    {
        return $this->client->get('/auth/me', $this->accessToken);
    }

    /**
     * Logout
     */
    public function logout(): array
    {
        $response = $this->client->post('/auth/logout', [], $this->accessToken);

        if ($response['status']) {
            $this->accessToken  = null;
            $this->refreshToken = null;
        }

        return $response;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function isLoggedIn(): bool
    {
        return $this->accessToken !== null;
    }
}
```

---

## UserGateUser — CRUD User

```php
<?php
// src/UserGateUser.php

class UserGateUser
{
    private UserGateClient $client;
    private string $token;

    public function __construct(UserGateClient $client, string $token)
    {
        $this->client = $client;
        $this->token  = $token;
    }

    /**
     * Daftar user dengan pagination
     */
    public function list(int $page = 1, int $perPage = 20, string $search = ''): array
    {
        $query = ['page' => $page, 'per_page' => $perPage];
        if ($search) {
            $query['search'] = $search;
        }

        return $this->client->get('/users', $this->token, $query);
    }

    /**
     * Detail user
     */
    public function get(string $id): array
    {
        return $this->client->get("/users/{$id}", $this->token);
    }

    /**
     * Buat user baru
     */
    public function create(array $data): array
    {
        // Validasi dasar
        $required = ['username', 'email', 'full_name', 'password'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return [
                    'status'  => false,
                    'message' => "Field '{$field}' is required",
                ];
            }
        }

        return $this->client->post('/users', $data, $this->token);
    }

    /**
     * Ubah user
     */
    public function update(string $id, array $data): array
    {
        return $this->client->put("/users/{$id}", $data, $this->token);
    }

    /**
     * Hapus user
     */
    public function delete(string $id): array
    {
        return $this->client->delete("/users/{$id}", $this->token);
    }

    /**
     * Nonaktifkan user
     */
    public function deactivate(string $id): array
    {
        return $this->update($id, ['status' => 'INACTIVE']);
    }

    /**
     * Aktifkan user
     */
    public function activate(string $id): array
    {
        return $this->update($id, ['status' => 'ACTIVE']);
    }
}
```

---

## Contoh Penggunaan Lengkap

```php
<?php
// public/index.php

require_once __DIR__ . '/../src/UserGateClient.php';
require_once __DIR__ . '/../src/UserGateAuth.php';
require_once __DIR__ . '/../src/UserGateUser.php';

// 1. Inisialisasi client
$client = (new UserGateClient(
    'https://usergate.sandalgurun.web.id/api/v1',
    'ug_xxxxxxxxxxxxxxxx'
));

// 2. Login
$auth = new UserGateAuth($client);
$result = $auth->login('admin', 'password_admin');

if (!$result['status']) {
    echo "Login gagal: " . $result['message'];
    exit;
}

echo "Login berhasil!\n";
echo "User: " . $result['data']['user']['full_name'] . "\n";
echo "Token berlaku: " . $result['data']['expires_in'] . " detik\n\n";

// 3. Ambil data user login
$me = $auth->me();
if ($me['status']) {
    echo "Data user saat ini:\n";
    echo "  Username: " . $me['data']['username'] . "\n";
    echo "  Email: " . $me['data']['email'] . "\n";
    echo "  Roles: " . implode(', ', $me['data']['roles']) . "\n\n";
}

// 4. CRUD User
$userApi = new UserGateUser($client, $auth->getAccessToken());

// List users
echo "=== Daftar User ===\n";
$users = $userApi->list(1, 10);
if ($users['status']) {
    foreach ($users['data'] as $user) {
        echo "  - {$user['username']} ({$user['email']}) [{$user['status']}]\n";
    }
    echo "  Total: {$users['meta']['total']}\n\n";
}

// Create user
echo "=== Buat User Baru ===\n";
$newUser = $userApi->create([
    'username'  => 'demo_user_' . time(),
    'email'     => 'demo_' . time() . '@example.com',
    'full_name' => 'Demo User',
    'password'  => 'password123',
]);

if ($newUser['status']) {
    echo "  Berhasil! ID: {$newUser['data']['id']}\n";
    $userId = $newUser['data']['id'];

    // Update user
    echo "\n=== Update User ===\n";
    $updated = $userApi->update($userId, [
        'full_name' => 'Demo User Updated',
    ]);

    if ($updated['status']) {
        echo "  Nama baru: {$updated['data']['full_name']}\n";
    }

    // Get user detail
    echo "\n=== Detail User ===\n";
    $detail = $userApi->get($userId);
    if ($detail['status']) {
        echo "  Username: {$detail['data']['username']}\n";
        echo "  Email: {$detail['data']['email']}\n";
        echo "  Status: {$detail['data']['status']}\n";
    }

    // Delete user
    echo "\n=== Hapus User ===\n";
    $deleted = $userApi->delete($userId);
    if ($deleted['status']) {
        echo "  Berhasil dihapus\n";
    }
}

// 5. Refresh token
echo "\n=== Refresh Token ===\n";
$refreshResult = $auth->refresh();
if ($refreshResult['status']) {
    echo "  Token berhasil di-refresh\n";
}

// 6. Logout
echo "\n=== Logout ===\n";
$logoutResult = $auth->logout();
if ($logoutResult['status']) {
    echo "  Logout berhasil\n";
}
```

---

## Error Handling

```php
<?php

function handleUserGateError(array $response): string
{
    if ($response['status']) {
        return '';
    }

    $message = $response['message'] ?? 'Unknown error';

    // Return user-friendly message
    if (strpos($message, 'Invalid credentials') !== false) {
        return 'Username atau password salah.';
    }

    if (strpos($message, 'expired') !== false) {
        return 'Sesi sudah berakhir. Silakan login kembali.';
    }

    if (strpos($message, 'does not have') !== false) {
        return 'Anda tidak memiliki izin untuk melakukan operasi ini.';
    }

    if (strpos($message, 'already exists') !== false) {
        return 'Data sudah ada. Gunakan data lain.';
    }

    if (strpos($message, 'Validation failed') !== false) {
        $errors = $response['errors'] ?? [];
        $details = [];
        foreach ($errors as $field => $error) {
            $details[] = "{$field}: {$error}";
        }
        return 'Validasi gagal: ' . implode(', ', $details);
    }

    return "Error: {$message}";
}

// Penggunaan
$result = $auth->login('admin', 'wrong_password');
$error = handleUserGateError($result);
if ($error) {
    echo $error;
}
```
