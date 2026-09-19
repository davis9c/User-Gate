# SDK Patterns — Pola Integrasi

> **Cara Pakai Dokumen Ini:**
> Upload file ini ke AI lalu minta:
> "Buatkan saya [pattern name] untuk integrasi UserGate dengan [bahasa/framework]"
> AI akan memberikan implementasi sesuai pola yang Anda pilih.

---

## Pola 1: Token Manager

Centralisasi semua operasi token dalam satu class/service.

### PHP

```php
<?php

class UserGateTokenManager
{
    private string $baseUrl;
    private string $apiKey;
    private ?string $accessToken = null;
    private ?string $refreshToken = null;

    public function __construct(string $baseUrl, string $apiKey)
    {
        $this->baseUrl = $baseUrl;
        $this->apiKey  = $apiKey;
    }

    public function login(string $username, string $password): array
    {
        $response = $this->request('POST', '/auth/login', [
            'username' => $username,
            'password' => $password,
        ]);

        if ($response['status']) {
            $this->accessToken  = $response['data']['access_token'];
            $this->refreshToken = $response['data']['refresh_token'];
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

    public function refresh(): bool
    {
        if (!$this->refreshToken) {
            return false;
        }

        $response = $this->request('POST', '/auth/refresh', [
            'refresh_token' => $this->refreshToken,
        ]);

        if ($response['status']) {
            $this->accessToken  = $response['data']['access_token'];
            $this->refreshToken = $response['data']['refresh_token'];
            return true;
        }

        return false;
    }

    public function logout(): bool
    {
        $response = $this->request('POST', '/auth/logout', [], true);

        if ($response['status']) {
            $this->accessToken  = null;
            $this->refreshToken = null;
            return true;
        }

        return false;
    }

    public function getCurrentUser(): ?array
    {
        $response = $this->request('GET', '/auth/me', [], true);

        return $response['status'] ? $response['data'] : null;
    }

    private function request(string $method, string $endpoint, array $data = [], bool $withAuth = false): array
    {
        $headers = [
            "X-API-Key: {$this->apiKey}",
            "Content-Type: application/json",
            "Accept: application/json",
        ];

        if ($withAuth && $this->accessToken) {
            $headers[] = "Authorization: Bearer {$this->accessToken}";
        }

        $ch = curl_init("{$this->baseUrl}{$endpoint}");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_POSTFIELDS     => !empty($data) ? json_encode($data) : null,
        ]);

        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);

        return $response;
    }
}

// Penggunaan
$ug = new UserGateTokenManager(
    'https://usergate.sandalgurun.web.id/api/v1',
    'ug_xxxxxxxxxxxxxxxx'
);

$result = $ug->login('user_app', 'password_user');
if ($result['status']) {
    echo "Login berhasil: " . $result['data']['user']['full_name'];

    // Ambil data user
    $user = $ug->getCurrentUser();
    print_r($user);

    // Refresh token jika diperlukan
    $ug->refresh();

    // Logout
    $ug->logout();
}
```

### JavaScript

```javascript
class UserGateTokenManager {
  constructor(baseUrl, apiKey) {
    this.baseUrl = baseUrl;
    this.apiKey = apiKey;
    this.accessToken = null;
    this.refreshToken = null;
  }

  async login(username, password) {
    const response = await this.request('POST', '/auth/login', {
      username,
      password,
    });

    if (response.status) {
      this.accessToken = response.data.access_token;
      this.refreshToken = response.data.refresh_token;
    }

    return response;
  }

  async refresh() {
    if (!this.refreshToken) return false;

    const response = await this.request('POST', '/auth/refresh', {
      refresh_token: this.refreshToken,
    });

    if (response.status) {
      this.accessToken = response.data.access_token;
      this.refreshToken = response.data.refresh_token;
      return true;
    }

    return false;
  }

  async logout() {
    const response = await this.request('POST', '/auth/logout', {}, true);

    if (response.status) {
      this.accessToken = null;
      this.refreshToken = null;
      return true;
    }

    return false;
  }

  async getCurrentUser() {
    const response = await this.request('GET', '/auth/me', {}, true);
    return response.status ? response.data : null;
  }

  async request(method, endpoint, data = {}, withAuth = false) {
    const headers = {
      'X-API-Key': this.apiKey,
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };

    if (withAuth && this.accessToken) {
      headers['Authorization'] = `Bearer ${this.accessToken}`;
    }

    const options = {
      method,
      headers,
    };

    if (Object.keys(data).length > 0) {
      options.body = JSON.stringify(data);
    }

    const response = await fetch(`${this.baseUrl}${endpoint}`, options);
    return await response.json();
  }
}

// Penggunaan
const ug = new UserGateTokenManager(
  'https://usergate.sandalgurun.web.id/api/v1',
  'ug_xxxxxxxxxxxxxxxx'
);

const result = await ug.login('user_app', 'password_user');
if (result.status) {
  console.log(`Login berhasil: ${result.data.user.full_name}`);

  const user = await ug.getCurrentUser();
  console.log(user);

  await ug.refresh();
  await ug.logout();
}
```

### Python

```python
import requests

class UserGateTokenManager:
    def __init__(self, base_url, api_key):
        self.base_url = base_url
        self.api_key = api_key
        self.access_token = None
        self.refresh_token = None

    def login(self, username, password):
        response = self._request('POST', '/auth/login', {
            'username': username,
            'password': password,
        })

        if response['status']:
            self.access_token = response['data']['access_token']
            self.refresh_token = response['data']['refresh_token']

        return response

    def refresh(self):
        if not self.refresh_token:
            return False

        response = self._request('POST', '/auth/refresh', {
            'refresh_token': self.refresh_token,
        })

        if response['status']:
            self.access_token = response['data']['access_token']
            self.refresh_token = response['data']['refresh_token']
            return True

        return False

    def logout(self):
        response = self._request('POST', '/auth/logout', with_auth=True)

        if response['status']:
            self.access_token = None
            self.refresh_token = None
            return True

        return False

    def get_current_user(self):
        response = self._request('GET', '/auth/me', with_auth=True)
        return response['data'] if response['status'] else None

    def _request(self, method, endpoint, data=None, with_auth=False):
        headers = {
            'X-API-Key': self.api_key,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        }

        if with_auth and self.access_token:
            headers['Authorization'] = f'Bearer {self.access_token}'

        response = requests.request(
            method,
            f"{self.base_url}{endpoint}",
            headers=headers,
            json=data,
        )

        return response.json()


# Penggunaan
ug = UserGateTokenManager(
    'https://usergate.sandalgurun.web.id/api/v1',
    'ug_xxxxxxxxxxxxxxxx'
)

result = ug.login('user_app', 'password_user')
if result['status']:
    print(f"Login berhasil: {result['data']['user']['full_name']}")

    user = ug.get_current_user()
    print(user)

    ug.refresh()
    ug.logout()
```

---

## Pola 2: Auto-Refresh Middleware

Token di-refresh otomatis sebelum kedaluwarsa.

### PHP

```php
<?php

class UserGateClient
{
    private UserGateTokenManager $tokenManager;
    private int $refreshBuffer = 60; // refresh 60 detik sebelum expired

    public function __construct(UserGateTokenManager $tokenManager)
    {
        $this->tokenManager = $tokenManager;
    }

    public function request(string $method, string $endpoint, array $data = []): array
    {
        // Auto-refresh jika token hampir expired
        $this->ensureValidToken();

        $headers = [
            "X-API-Key: {$this->tokenManager->getApiKey()}",
            "Authorization: Bearer {$this->tokenManager->getAccessToken()}",
            "Content-Type: application/json",
            "Accept: application/json",
        ];

        $ch = curl_init("https://usergate.sandalgurun.web.id/api/v1{$endpoint}");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_POSTFIELDS     => !empty($data) ? json_encode($data) : null,
        ]);

        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);

        // Jika 401 (token expired), coba refresh dan retry
        if (isset($response['status']) && !$response['status'] && 
            strpos($response['message'], 'expired') !== false) {
            if ($this->tokenManager->refresh()) {
                return $this->request($method, $endpoint, $data); // Retry
            }
        }

        return $response;
    }

    private function ensureValidToken(): void
    {
        // Cek jika token akan expired dalam `refreshBuffer` detik
        // Implementasi: decode JWT exp claim atau track expiry time
    }
}

// Penggunaan
$ug = new UserGateClient($tokenManager);
$users = $ug->request('GET', '/users');
```

### JavaScript

```javascript
class UserGateClient {
  constructor(tokenManager) {
    this.tokenManager = tokenManager;
    this.refreshBuffer = 60000; // 60 detik dalam ms
  }

  async request(method, endpoint, data = {}) {
    await this.ensureValidToken();

    const response = await this.tokenManager.request(method, endpoint, data, true);

    // Auto-retry jika token expired
    if (!response.status && response.message?.includes('expired')) {
      const refreshed = await this.tokenManager.refresh();
      if (refreshed) {
        return this.tokenManager.request(method, endpoint, data, true);
      }
    }

    return response;
  }

  async ensureValidToken() {
    // Implementasi: cek expiry dari JWT atau track expiry time
    // Jika hampir expired, refresh sebelum request
  }
}

// Penggunaan
const client = new UserGateClient(tokenManager);
const users = await client.request('GET', '/users');
```

### Python

```python
class UserGateClient:
    def __init__(self, token_manager):
        self.token_manager = token_manager
        self.refresh_buffer = 60  # detik

    def request(self, method, endpoint, data=None):
        self.ensure_valid_token()

        response = self.token_manager._request(
            method, endpoint, data, with_auth=True
        )

        # Auto-retry jika token expired
        if not response.get('status') and 'expired' in response.get('message', ''):
            if self.token_manager.refresh():
                response = self.token_manager._request(
                    method, endpoint, data, with_auth=True
                )

        return response

    def ensure_valid_token(self):
        # Implementasi: cek expiry dari JWT atau track expiry time
        pass


# Penggunaan
client = UserGateClient(token_manager)
users = client.request('GET', '/users')
```

---

## Pola 3: Error Handler Centralized

Centralisasi penanganan error API.

### PHP

```php
<?php

class UserGateErrorHandler
{
    public static function handle(array $response): void
    {
        if ($response['status']) {
            return; // Tidak ada error
        }

        $message = $response['message'] ?? 'Unknown error';
        $errors  = $response['errors'] ?? [];

        // Log untuk debugging
        error_log("[UserGate Error] $message");

        // Handle berdasarkan jenis error
        if (strpos($message, 'Invalid credentials') !== false) {
            throw new UserGateAuthException('Username atau password salah');
        }

        if (strpos($message, 'expired') !== false) {
            throw new UserGateTokenExpiredException('Token sudah kedaluwarsa, silakan refresh');
        }

        if (strpos($message, 'does not have') !== false) {
            throw new UserGatePermissionException('Tidak memiliki izin: ' . $message);
        }

        if (strpos($message, 'not found') !== false) {
            throw new UserGateNotFoundException('Resource tidak ditemukan');
        }

        if (strpos($message, 'already exists') !== false) {
            throw new UserGateConflictException('Data sudah ada: ' . $message);
        }

        if (!empty($errors)) {
            throw new UserGateValidationException('Validasi gagal', $errors);
        }

        throw new UserGateException($message);
    }
}

// Custom Exceptions
class UserGateException extends Exception {}
class UserGateAuthException extends UserGateException {}
class UserGateTokenExpiredException extends UserGateException {}
class UserGatePermissionException extends UserGateException {}
class UserGateNotFoundException extends UserGateException {}
class UserGateConflictException extends UserGateException {}
class UserGateValidationException extends UserGateException
{
    private array $errors;

    public function __construct(string $message, array $errors)
    {
        parent::__construct($message);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}

// Penggunaan
try {
    $result = $ug->login('user_app', 'wrong_password');
    UserGateErrorHandler::handle($result);
} catch (UserGateAuthException $e) {
    echo "Login gagal: " . $e->getMessage();
} catch (UserGateValidationException $e) {
    echo "Validasi gagal:\n";
    foreach ($e->getErrors() as $field => $error) {
        echo "  - $field: $error\n";
    }
}
```

### JavaScript

```javascript
class UserGateError extends Error {
  constructor(message, errors = {}) {
    super(message);
    this.name = 'UserGateError';
    this.errors = errors;
  }
}

class UserGateAuthError extends UserGateError {
  constructor(message = 'Authentication failed') {
    super(message);
    this.name = 'UserGateAuthError';
  }
}

class UserGateTokenExpiredError extends UserGateError {
  constructor(message = 'Token expired') {
    super(message);
    this.name = 'UserGateTokenExpiredError';
  }
}

class UserGatePermissionError extends UserGateError {
  constructor(message = 'Permission denied') {
    super(message);
    this.name = 'UserGatePermissionError';
  }
}

class UserGateValidationError extends UserGateError {
  constructor(message = 'Validation failed', errors = {}) {
    super(message);
    this.name = 'UserGateValidationError';
    this.errors = errors;
  }
}

function handleUserGateError(response) {
  if (response.status) return;

  const message = response.message || 'Unknown error';
  const errors = response.errors || {};

  console.error(`[UserGate Error] ${message}`);

  if (message.includes('Invalid credentials')) {
    throw new UserGateAuthError('Username atau password salah');
  }

  if (message.includes('expired')) {
    throw new UserGateTokenExpiredError('Token sudah kedaluwarsa');
  }

  if (message.includes('does not have')) {
    throw new UserGatePermissionError(`Tidak memiliki izin: ${message}`);
  }

  if (Object.keys(errors).length > 0) {
    throw new UserGateValidationError('Validasi gagal', errors);
  }

  throw new UserGateError(message);
}

// Penggunaan
try {
  const result = await ug.login('user_app', 'wrong_password');
  handleUserGateError(result);
} catch (error) {
  if (error instanceof UserGateAuthError) {
    console.error('Login gagal:', error.message);
  } else if (error instanceof UserGateValidationError) {
    console.error('Validasi gagal:', error.errors);
  }
}
```

### Python

```python
class UserGateError(Exception):
    def __init__(self, message, errors=None):
        super().__init__(message)
        self.errors = errors or {}


class UserGateAuthError(UserGateError):
    pass


class UserGateTokenExpiredError(UserGateError):
    pass


class UserGatePermissionError(UserGateError):
    pass


class UserGateValidationError(UserGateError):
    pass


def handle_user_gate_error(response):
    if response.get('status'):
        return

    message = response.get('message', 'Unknown error')
    errors = response.get('errors', {})

    print(f"[UserGate Error] {message}")

    if 'Invalid credentials' in message:
        raise UserGateAuthError('Username atau password salah')

    if 'expired' in message:
        raise UserGateTokenExpiredError('Token sudah kedaluwarsa')

    if 'does not have' in message:
        raise UserGatePermissionError(f'Tidak memiliki izin: {message}')

    if errors:
        raise UserGateValidationError('Validasi gagal', errors)

    raise UserGateError(message)


# Penggunaan
try:
    result = ug.login('user_app', 'wrong_password')
    handle_user_gate_error(result)
except UserGateAuthError as e:
    print(f"Login gagal: {e}")
except UserGateValidationError as e:
    print(f"Validasi gagal: {e.errors}")
```

---

## Pola 4: Retry with Exponential Backoff

Automatic retry untuk transient errors (rate limit, network).

### PHP

```php
<?php

function usergateRequestWithRetry(
    callable $requestFn,
    int $maxRetries = 3,
    int $baseDelay = 1000
): array {
    $attempt = 0;

    while ($attempt < $maxRetries) {
        $response = $requestFn();

        // Jika sukses atau error permanent, return langsung
        if ($response['status'] || !in_array($response['code'] ?? 0, [429, 500])) {
            return $response;
        }

        // Rate limit — tunggu lebih lama
        $delay = ($response['code'] ?? 0) === 429
            ? $baseDelay * pow(2, $attempt) * 2
            : $baseDelay * pow(2, $attempt);

        error_log("[UserGate Retry] Attempt {$attempt}, waiting {$delay}ms");
        usleep($delay * 1000);

        $attempt++;
    }

    return $response; // Return response terakhir meskipun gagal
}

// Penggunaan
$response = usergateRequestWithRetry(function () use ($ug) {
    return $ug->request('GET', '/users');
});
```

### JavaScript

```javascript
async function usergateRequestWithRetry(requestFn, maxRetries = 3, baseDelay = 1000) {
  let attempt = 0;

  while (attempt < maxRetries) {
    const response = await requestFn();

    // Jika sukses atau error permanent
    if (response.status || ![429, 500].includes(response.code)) {
      return response;
    }

    // Rate limit — tunggu lebih lama
    const delay = response.code === 429
      ? baseDelay * Math.pow(2, attempt) * 2
      : baseDelay * Math.pow(2, attempt);

    console.log(`[UserGate Retry] Attempt ${attempt}, waiting ${delay}ms`);
    await new Promise(resolve => setTimeout(resolve, delay));

    attempt++;
  }

  return response;
}

// Penggunaan
const response = await usergateRequestWithRetry(() =>
  client.request('GET', '/users')
);
```

### Python

```python
import time

def usergate_request_with_retry(request_fn, max_retries=3, base_delay=1.0):
    attempt = 0

    while attempt < max_retries:
        response = request_fn()

        # Jika sukses atau error permanent
        if response.get('status') or response.get('code') not in [429, 500]:
            return response

        # Rate limit — tunggu lebih lama
        delay = (base_delay * (2 ** attempt) * 2
                 if response.get('code') == 429
                 else base_delay * (2 ** attempt))

        print(f"[UserGate Retry] Attempt {attempt}, waiting {delay}s")
        time.sleep(delay)

        attempt += 1

    return response


# Penggunaan
response = usergate_request_with_retry(lambda: client.request('GET', '/users'))
```

---

## Pola 5: Session Storage

Menyimpan session di file/database untuk persistence.

### PHP

```php
<?php

class UserGateSession
{
    private string $sessionFile;

    public function __construct(string $sessionDir = '/tmp')
    {
        $this->sessionFile = "{$sessionDir}/usergate_session.json";
    }

    public function save(string $accessToken, string $refreshToken, array $user): void
    {
        $data = [
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'user'          => $user,
            'saved_at'      => time(),
        ];

        file_put_contents($this->sessionFile, json_encode($data));
    }

    public function load(): ?array
    {
        if (!file_exists($this->sessionFile)) {
            return null;
        }

        $data = json_decode(file_get_contents($this->sessionFile), true);

        // Session expired (30 hari)
        if (time() - $data['saved_at'] > 2592000) {
            $this->clear();
            return null;
        }

        return $data;
    }

    public function clear(): void
    {
        if (file_exists($this->sessionFile)) {
            unlink($this->sessionFile);
        }
    }

    public function isLoggedIn(): bool
    {
        $session = $this->load();
        return $session !== null && !empty($session['access_token']);
    }
}

// Penggunaan
$session = new UserGateSession();

if ($session->isLoggedIn()) {
    $data = $session->load();
    echo "Sudah login sebagai: {$data['user']['full_name']}";
} else {
    $result = $ug->login('user_app', 'password_user');
    if ($result['status']) {
        $session->save(
            $result['data']['access_token'],
            $result['data']['refresh_token'],
            $result['data']['user']
        );
    }
}
```

---

## Ringkasan Pola

| Pola | Fungsi | Gunakan Ketika |
|------|--------|----------------|
| **Token Manager** | Centralisasi operasi token | Selalu — ini dasar |
| **Auto-Refresh** | Refresh otomatis sebelum expired | Production apps |
| **Error Handler** | Centralisasi penanganan error | Selalu — untuk robustness |
| **Retry Backoff** | Auto retry transient errors | API calls yang kritis |
| **Session Storage** | Persistence session | Web apps, CLI tools |
