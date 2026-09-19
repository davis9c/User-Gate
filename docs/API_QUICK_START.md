# API Quick Start — Code Examples

> **Cara Pakai Dokumen Ini:**
> Upload file ini ke AI lalu minta:
> "Buatkan saya kode integrasi UserGate dengan [bahasa/framework] saya"
> AI akan menghasilkan kode yang sudah sesuai dengan kontrak API UserGate.

---

## Konfigurasi Dasar

```text
Base URL    : https://usergate.sandalgurun.web.id
API Endpoint: https://usergate.sandalgurun.web.id/api/v1
API Key     : ug_xxxxxxxxxxxxxxxx
```

Semua request membutuhkan header:
```http
X-API-Key: ug_xxxxxxxxxxxxxxxx
Content-Type: application/json
Accept: application/json
```

---

## PHP (cURL)

### Login

```php
<?php

$base_url = 'https://usergate.sandalgurun.web.id/api/v1';
$api_key  = 'ug_xxxxxxxxxxxxxxxx';

// Login
$ch = curl_init("$base_url/auth/login");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => [
        "X-API-Key: $api_key",
        "Content-Type: application/json",
        "Accept: application/json",
    ],
    CURLOPT_POSTFIELDS => json_encode([
        'username' => 'user_app',
        'password' => 'password_user',
    ]),
]);

$response = json_decode(curl_exec($ch), true);
curl_close($ch);

if ($response['status']) {
    $access_token  = $response['data']['access_token'];
    $refresh_token = $response['data']['refresh_token'];
    $user          = $response['data']['user'];

    echo "Login berhasil: {$user['full_name']}\n";
    echo "Token berlaku: {$response['data']['expires_in']} detik\n";
} else {
    echo "Login gagal: {$response['message']}\n";
}
```

### Ambil Data User Login

```php
<?php

$ch = curl_init("$base_url/auth/me");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        "X-API-Key: $api_key",
        "Authorization: Bearer $access_token",
        "Accept: application/json",
    ],
]);

$response = json_decode(curl_exec($ch), true);
curl_close($ch);

if ($response['status']) {
    $user = $response['data'];
    echo "User: {$user['username']} ({$user['email']})\n";
    echo "Status: {$user['status']}\n";
    echo "Roles: " . implode(', ', $user['roles']) . "\n";
}
```

### Refresh Token

```php
<?php

$ch = curl_init("$base_url/auth/refresh");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => [
        "X-API-Key: $api_key",
        "Content-Type: application/json",
    ],
    CURLOPT_POSTFIELDS => json_encode([
        'refresh_token' => $refresh_token,
    ]),
]);

$response = json_decode(curl_exec($ch), true);
curl_close($ch);

if ($response['status']) {
    // Simpan token baru
    $access_token  = $response['data']['access_token'];
    $refresh_token = $response['data']['refresh_token'];
    echo "Token berhasil di-refresh\n";
}
```

### Logout

```php
<?php

$ch = curl_init("$base_url/auth/logout");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => [
        "X-API-Key: $api_key",
        "Authorization: Bearer $access_token",
    ],
]);

$response = json_decode(curl_exec($ch), true);
curl_close($ch);

if ($response['status']) {
    echo "Logout berhasil\n";
    $access_token  = null;
    $refresh_token = null;
}
```

### CRUD User

```php
<?php

// GET users (daftar)
$ch = curl_init("$base_url/users?page=1&per_page=20&search=budi");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        "X-API-Key: $api_key",
        "Authorization: Bearer $access_token",
        "Accept: application/json",
    ],
]);
$users = json_decode(curl_exec($ch), true);
curl_close($ch);

// POST user (buat baru)
$ch = curl_init("$base_url/users");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => [
        "X-API-Key: $api_key",
        "Authorization: Bearer $access_token",
        "Content-Type: application/json",
    ],
    CURLOPT_POSTFIELDS => json_encode([
        'username'  => 'newuser',
        'email'     => 'newuser@example.com',
        'full_name' => 'New User',
        'password'  => 'password-minimal-8',
    ]),
]);
$new_user = json_decode(curl_exec($ch), true);
curl_close($ch);

// PUT user (ubah)
$user_id = $new_user['data']['id'];
$ch = curl_init("$base_url/users/$user_id");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST  => 'PUT',
    CURLOPT_HTTPHEADER     => [
        "X-API-Key: $api_key",
        "Authorization: Bearer $access_token",
        "Content-Type: application/json",
    ],
    CURLOPT_POSTFIELDS => json_encode([
        'full_name' => 'New User Updated',
    ]),
]);
$updated = json_decode(curl_exec($ch), true);
curl_close($ch);

// DELETE user (hapus)
$ch = curl_init("$base_url/users/$user_id");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST  => 'DELETE',
    CURLOPT_HTTPHEADER     => [
        "X-API-Key: $api_key",
        "Authorization: Bearer $access_token",
    ],
]);
$deleted = json_decode(curl_exec($ch), true);
curl_close($ch);
```

---

## JavaScript (fetch)

### Login

```javascript
const BASE_URL = 'https://usergate.sandalgurun.web.id/api/v1';
const API_KEY  = 'ug_xxxxxxxxxxxxxxxx';

async function login(username, password) {
  const response = await fetch(`${BASE_URL}/auth/login`, {
    method: 'POST',
    headers: {
      'X-API-Key': API_KEY,
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
    body: JSON.stringify({ username, password }),
  });

  const data = await response.json();

  if (data.status) {
    console.log(`Login berhasil: ${data.data.user.full_name}`);
    return {
      accessToken: data.data.access_token,
      refreshToken: data.data.refresh_token,
      user: data.data.user,
    };
  } else {
    throw new Error(data.message);
  }
}

// Gunakan
const session = await login('user_app', 'password_user');
```

### Request dengan Token

```javascript
async function getCurrentUser(accessToken) {
  const response = await fetch(`${BASE_URL}/auth/me`, {
    method: 'GET',
    headers: {
      'X-API-Key': API_KEY,
      'Authorization': `Bearer ${accessToken}`,
      'Accept': 'application/json',
    },
  });

  const data = await response.json();

  if (data.status) {
    return data.data;
  } else {
    throw new Error(data.message);
  }
}

const user = await getCurrentUser(session.accessToken);
console.log(user);
```

### Refresh Token

```javascript
async function refreshToken(refreshToken) {
  const response = await fetch(`${BASE_URL}/auth/refresh`, {
    method: 'POST',
    headers: {
      'X-API-Key': API_KEY,
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ refresh_token: refreshToken }),
  });

  const data = await response.json();

  if (data.status) {
    return {
      accessToken: data.data.access_token,
      refreshToken: data.data.refresh_token,
    };
  } else {
    throw new Error(data.message);
  }
}
```

### Logout

```javascript
async function logout(accessToken) {
  const response = await fetch(`${BASE_URL}/auth/logout`, {
    method: 'POST',
    headers: {
      'X-API-Key': API_KEY,
      'Authorization': `Bearer ${accessToken}`,
    },
  });

  const data = await response.json();
  return data.status;
}
```

### CRUD User

```javascript
// GET users
async function getUsers(accessToken, page = 1, search = '') {
  const params = new URLSearchParams({ page, per_page: 20 });
  if (search) params.append('search', search);

  const response = await fetch(`${BASE_URL}/users?${params}`, {
    headers: {
      'X-API-Key': API_KEY,
      'Authorization': `Bearer ${accessToken}`,
      'Accept': 'application/json',
    },
  });

  return await response.json();
}

// POST user
async function createUser(accessToken, userData) {
  const response = await fetch(`${BASE_URL}/users`, {
    method: 'POST',
    headers: {
      'X-API-Key': API_KEY,
      'Authorization': `Bearer ${accessToken}`,
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(userData),
  });

  return await response.json();
}

// PUT user
async function updateUser(accessToken, userId, userData) {
  const response = await fetch(`${BASE_URL}/users/${userId}`, {
    method: 'PUT',
    headers: {
      'X-API-Key': API_KEY,
      'Authorization': `Bearer ${accessToken}`,
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(userData),
  });

  return await response.json();
}

// DELETE user
async function deleteUser(accessToken, userId) {
  const response = await fetch(`${BASE_URL}/users/${userId}`, {
    method: 'DELETE',
    headers: {
      'X-API-Key': API_KEY,
      'Authorization': `Bearer ${accessToken}`,
    },
  });

  return await response.json();
}
```

---

## Python (requests)

### Login

```python
import requests

BASE_URL = "https://usergate.sandalgurun.web.id/api/v1"
API_KEY  = "ug_xxxxxxxxxxxxxxxx"

def login(username, password):
    response = requests.post(
        f"{BASE_URL}/auth/login",
        headers={
            "X-API-Key": API_KEY,
            "Content-Type": "application/json",
            "Accept": "application/json",
        },
        json={
            "username": username,
            "password": password,
        },
    )

    data = response.json()

    if data["status"]:
        print(f"Login berhasil: {data['data']['user']['full_name']}")
        return {
            "access_token": data["data"]["access_token"],
            "refresh_token": data["data"]["refresh_token"],
            "user": data["data"]["user"],
        }
    else:
        raise Exception(data["message"])


# Gunakan
session = login("user_app", "password_user")
```

### Request dengan Token

```python
def get_current_user(access_token):
    response = requests.get(
        f"{BASE_URL}/auth/me",
        headers={
            "X-API-Key": API_KEY,
            "Authorization": f"Bearer {access_token}",
            "Accept": "application/json",
        },
    )

    data = response.json()

    if data["status"]:
        return data["data"]
    else:
        raise Exception(data["message"])


user = get_current_user(session["access_token"])
print(user)
```

### Refresh Token

```python
def refresh_token(refresh_token):
    response = requests.post(
        f"{BASE_URL}/auth/refresh",
        headers={
            "X-API-Key": API_KEY,
            "Content-Type": "application/json",
        },
        json={
            "refresh_token": refresh_token,
        },
    )

    data = response.json()

    if data["status"]:
        return {
            "access_token": data["data"]["access_token"],
            "refresh_token": data["data"]["refresh_token"],
        }
    else:
        raise Exception(data["message"])
```

### Logout

```python
def logout(access_token):
    response = requests.post(
        f"{BASE_URL}/auth/logout",
        headers={
            "X-API-Key": API_KEY,
            "Authorization": f"Bearer {access_token}",
        },
    )

    data = response.json()
    return data["status"]
```

### CRUD User

```python
def get_users(access_token, page=1, search=""):
    params = {"page": page, "per_page": 20}
    if search:
        params["search"] = search

    response = requests.get(
        f"{BASE_URL}/users",
        headers={
            "X-API-Key": API_KEY,
            "Authorization": f"Bearer {access_token}",
            "Accept": "application/json",
        },
        params=params,
    )

    return response.json()


def create_user(access_token, user_data):
    response = requests.post(
        f"{BASE_URL}/users",
        headers={
            "X-API-Key": API_KEY,
            "Authorization": f"Bearer {access_token}",
            "Content-Type": "application/json",
        },
        json=user_data,
    )

    return response.json()


def update_user(access_token, user_id, user_data):
    response = requests.put(
        f"{BASE_URL}/users/{user_id}",
        headers={
            "X-API-Key": API_KEY,
            "Authorization": f"Bearer {access_token}",
            "Content-Type": "application/json",
        },
        json=user_data,
    )

    return response.json()


def delete_user(access_token, user_id):
    response = requests.delete(
        f"{BASE_URL}/users/{user_id}",
        headers={
            "X-API-Key": API_KEY,
            "Authorization": f"Bearer {access_token}",
        },
    )

    return response.json()
```

---

## cURL Lengkap

### Login

```bash
curl -X POST "https://usergate.sandalgurun.web.id/api/v1/auth/login" \
  -H "X-API-Key: ug_xxxxxxxxxxxxxxxx" \
  -H "Content-Type: application/json" \
  -d '{
    "username": "user_app",
    "password": "password_user"
  }'
```

### Current User

```bash
curl "https://usergate.sandalgurun.web.id/api/v1/auth/me" \
  -H "X-API-Key: ug_xxxxxxxxxxxxxxxx" \
  -H "Authorization: Bearer <access_token>"
```

### Refresh Token

```bash
curl -X POST "https://usergate.sandalgurun.web.id/api/v1/auth/refresh" \
  -H "X-API-Key: ug_xxxxxxxxxxxxxxxx" \
  -H "Content-Type: application/json" \
  -d '{
    "refresh_token": "<refresh_token>"
  }'
```

### Logout

```bash
curl -X POST "https://usergate.sandalgurun.web.id/api/v1/auth/logout" \
  -H "X-API-Key: ug_xxxxxxxxxxxxxxxx" \
  -H "Authorization: Bearer <access_token>"
```

### List Users

```bash
curl "https://usergate.sandalgurun.web.id/api/v1/users?page=1&per_page=20" \
  -H "X-API-Key: ug_xxxxxxxxxxxxxxxx" \
  -H "Authorization: Bearer <access_token>"
```

### Create User

```bash
curl -X POST "https://usergate.sandalgurun.web.id/api/v1/users" \
  -H "X-API-Key: ug_xxxxxxxxxxxxxxxx" \
  -H "Authorization: Bearer <access_token>" \
  -H "Content-Type: application/json" \
  -d '{
    "username": "newuser",
    "email": "newuser@example.com",
    "full_name": "New User",
    "password": "password-minimal-8"
  }'
```

### Update User

```bash
curl -X PUT "https://usergate.sandalgurun.web.id/api/v1/users/<user_id>" \
  -H "X-API-Key: ug_xxxxxxxxxxxxxxxx" \
  -H "Authorization: Bearer <access_token>" \
  -H "Content-Type: application/json" \
  -d '{
    "full_name": "Updated Name"
  }'
```

### Delete User

```bash
curl -X DELETE "https://usergate.sandalgurun.web.id/api/v1/users/<user_id>" \
  -H "X-API-Key: ug_xxxxxxxxxxxxxxxx" \
  -H "Authorization: Bearer <access_token>"
```
