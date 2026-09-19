# JavaScript Integration Example — Lengkap

> **Cara Pakai Dokumen Ini:**
> Upload file ini ke AI lalu minta:
> "Buatkan saya aplikasi JavaScript/Node.js yang terintegrasi dengan UserGate"
> atau "Bagaimana cara integrasi UserGate dengan React/Next.js/Express?"
> AI akan memberikan kode yang sudah sesuai.

---

## Setup Awal

### Environment Variables

```bash
# .env
USERGATE_BASE_URL=https://usergate.sandalgurun.web.id/api/v1
USERGATE_API_KEY=ug_xxxxxxxxxxxxxxxx
```

### Node.js Setup

```bash
mkdir usergate-integration
cd usergate-integration
npm init -y
npm install dotenv
```

### Browser Setup (Vanilla JS)

Tidak perlu dependency tambahan — gunakan `fetch` bawaan browser.

---

## Config

```javascript
// config/usergate.js
require('dotenv').config();

module.exports = {
  baseUrl: process.env.USERGATE_BASE_URL || 'https://usergate.sandalgurun.web.id/api/v1',
  apiKey: process.env.USERGATE_API_KEY || 'ug_xxxxxxxxxxxxxxxx',
};
```

---

## UserGateClient — Base Client

```javascript
// src/UserGateClient.js
const config = require('../config/usergate');

class UserGateClient {
  constructor(baseUrl = config.baseUrl, apiKey = config.apiKey) {
    this.baseUrl = baseUrl.replace(/\/$/, '');
    this.apiKey = apiKey;
  }

  /**
   * Kirim request ke UserGate API
   */
  async request(method, endpoint, data = {}, token = null, queryParams = {}) {
    let url = `${this.baseUrl}${endpoint}`;

    // Tambahkan query params jika ada
    const queryString = new URLSearchParams(queryParams).toString();
    if (queryString) {
      url += `?${queryString}`;
    }

    const headers = {
      'X-API-Key': this.apiKey,
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };

    if (token) {
      headers['Authorization'] = `Bearer ${token}`;
    }

    const options = {
      method,
      headers,
    };

    if (Object.keys(data).length > 0 && method !== 'GET') {
      options.body = JSON.stringify(data);
    }

    const response = await fetch(url, options);
    const responseData = await response.json();

    // Tambahkan HTTP code
    responseData.http_code = response.status;

    return responseData;
  }

  // Convenience methods
  async get(endpoint, token = null, queryParams = {}) {
    return this.request('GET', endpoint, {}, token, queryParams);
  }

  async post(endpoint, data = {}, token = null) {
    return this.request('POST', endpoint, data, token);
  }

  async put(endpoint, data = {}, token = null) {
    return this.request('PUT', endpoint, data, token);
  }

  async delete(endpoint, token = null) {
    return this.request('DELETE', endpoint, {}, token);
  }
}

module.exports = UserGateClient;
```

---

## UserGateAuth — Authentication

```javascript
// src/UserGateAuth.js
class UserGateAuth {
  constructor(client) {
    this.client = client;
    this.accessToken = null;
    this.refreshToken = null;
  }

  /**
   * Login dan dapatkan token
   */
  async login(username, password) {
    const response = await this.client.post('/auth/login', {
      username,
      password,
    });

    if (response.status) {
      this.accessToken = response.data.access_token;
      this.refreshToken = response.data.refresh_token;
    }

    return response;
  }

  /**
   * Refresh token
   */
  async refresh() {
    if (!this.refreshToken) {
      return { status: false, message: 'No refresh token available' };
    }

    const response = await this.client.post('/auth/refresh', {
      refresh_token: this.refreshToken,
    });

    if (response.status) {
      this.accessToken = response.data.access_token;
      this.refreshToken = response.data.refresh_token;
    }

    return response;
  }

  /**
   * Ambil data user yang sedang login
   */
  async me() {
    return this.client.get('/auth/me', this.accessToken);
  }

  /**
   * Logout
   */
  async logout() {
    const response = await this.client.post('/auth/logout', {}, this.accessToken);

    if (response.status) {
      this.accessToken = null;
      this.refreshToken = null;
    }

    return response;
  }

  isLoggedIn() {
    return this.accessToken !== null;
  }
}

module.exports = UserGateAuth;
```

---

## UserGateUser — CRUD User

```javascript
// src/UserGateUser.js
class UserGateUser {
  constructor(client, token) {
    this.client = client;
    this.token = token;
  }

  /**
   * Daftar user dengan pagination
   */
  async list(page = 1, perPage = 20, search = '') {
    const queryParams = { page, per_page: perPage };
    if (search) queryParams.search = search;

    return this.client.get('/users', this.token, queryParams);
  }

  /**
   * Detail user
   */
  async get(id) {
    return this.client.get(`/users/${id}`, this.token);
  }

  /**
   * Buat user baru
   */
  async create(userData) {
    // Validasi dasar
    const required = ['username', 'email', 'full_name', 'password'];
    for (const field of required) {
      if (!userData[field]) {
        return {
          status: false,
          message: `Field '${field}' is required`,
        };
      }
    }

    return this.client.post('/users', userData, this.token);
  }

  /**
   * Ubah user
   */
  async update(id, userData) {
    return this.client.put(`/users/${id}`, userData, this.token);
  }

  /**
   * Hapus user
   */
  async delete(id) {
    return this.client.delete(`/users/${id}`, this.token);
  }

  /**
   * Nonaktifkan user
   */
  async deactivate(id) {
    return this.update(id, { status: 'INACTIVE' });
  }

  /**
   * Aktifkan user
   */
  async activate(id) {
    return this.update(id, { status: 'ACTIVE' });
  }
}

module.exports = UserGateUser;
```

---

## Contoh Penggunaan — Node.js

```javascript
// index.js
require('dotenv').config();

const UserGateClient = require('./src/UserGateClient');
const UserGateAuth = require('./src/UserGateAuth');
const UserGateUser = require('./src/UserGateUser');

async function main() {
  // 1. Inisialisasi client
  const client = new UserGateClient();

  // 2. Login
  const auth = new UserGateAuth(client);
  const loginResult = await auth.login('admin', 'password_admin');

  if (!loginResult.status) {
    console.error('Login gagal:', loginResult.message);
    return;
  }

  console.log('Login berhasil!');
  console.log('User:', loginResult.data.user.full_name);
  console.log('Token berlaku:', loginResult.data.expires_in, 'detik\n');

  // 3. Ambil data user login
  const me = await auth.me();
  if (me.status) {
    console.log('Data user saat ini:');
    console.log('  Username:', me.data.username);
    console.log('  Email:', me.data.email);
    console.log('  Roles:', me.data.roles.join(', '));
    console.log();
  }

  // 4. CRUD User
  const userApi = new UserGateUser(client, auth.accessToken);

  // List users
  console.log('=== Daftar User ===');
  const users = await userApi.list(1, 10);
  if (users.status) {
    users.data.forEach(user => {
      console.log(`  - ${user.username} (${user.email}) [${user.status}]`);
    });
    console.log('  Total:', users.meta.total);
    console.log();
  }

  // Create user
  console.log('=== Buat User Baru ===');
  const newUser = await userApi.create({
    username: `demo_user_${Date.now()}`,
    email: `demo_${Date.now()}@example.com`,
    full_name: 'Demo User',
    password: 'password123',
  });

  if (newUser.status) {
    console.log('  Berhasil! ID:', newUser.data.id);
    const userId = newUser.data.id;

    // Update user
    console.log('\n=== Update User ===');
    const updated = await userApi.update(userId, {
      full_name: 'Demo User Updated',
    });

    if (updated.status) {
      console.log('  Nama baru:', updated.data.full_name);
    }

    // Get user detail
    console.log('\n=== Detail User ===');
    const detail = await userApi.get(userId);
    if (detail.status) {
      console.log('  Username:', detail.data.username);
      console.log('  Email:', detail.data.email);
      console.log('  Status:', detail.data.status);
    }

    // Delete user
    console.log('\n=== Hapus User ===');
    const deleted = await userApi.delete(userId);
    if (deleted.status) {
      console.log('  Berhasil dihapus');
    }
  }

  // 5. Refresh token
  console.log('\n=== Refresh Token ===');
  const refreshResult = await auth.refresh();
  if (refreshResult.status) {
    console.log('  Token berhasil di-refresh');
  }

  // 6. Logout
  console.log('\n=== Logout ===');
  const logoutResult = await auth.logout();
  if (logoutResult.status) {
    console.log('  Logout berhasil');
  }
}

main().catch(console.error);
```

---

## Contoh Penggunaan — Browser (Vanilla JS)

```html
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>UserGate Integration</title>
</head>
<body>
  <h1>UserGate Integration</h1>

  <div id="login-form">
    <h2>Login</h2>
    <input type="text" id="username" placeholder="Username" />
    <input type="password" id="password" placeholder="Password" />
    <button onclick="login()">Login</button>
  </div>

  <div id="user-info" style="display:none;">
    <h2>User Info</h2>
    <pre id="user-data"></pre>
    <button onclick="logout()">Logout</button>
  </div>

  <div id="error" style="color:red;"></div>

  <script>
    const BASE_URL = 'https://usergate.sandalgurun.web.id/api/v1';
    const API_KEY = 'ug_xxxxxxxxxxxxxxxx';

    let accessToken = null;
    let refreshToken = null;

    async function login() {
      const username = document.getElementById('username').value;
      const password = document.getElementById('password').value;

      try {
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
          accessToken = data.data.access_token;
          refreshToken = data.data.refresh_token;

          document.getElementById('login-form').style.display = 'none';
          document.getElementById('user-info').style.display = 'block';
          document.getElementById('user-data').textContent =
            JSON.stringify(data.data.user, null, 2);
        } else {
          showError(data.message);
        }
      } catch (error) {
        showError('Network error: ' + error.message);
      }
    }

    async function logout() {
      try {
        await fetch(`${BASE_URL}/auth/logout`, {
          method: 'POST',
          headers: {
            'X-API-Key': API_KEY,
            'Authorization': `Bearer ${accessToken}`,
          },
        });

        accessToken = null;
        refreshToken = null;

        document.getElementById('login-form').style.display = 'block';
        document.getElementById('user-info').style.display = 'none';
      } catch (error) {
        showError('Logout error: ' + error.message);
      }
    }

    function showError(message) {
      document.getElementById('error').textContent = message;
    }
  </script>
</body>
</html>
```

---

## Contoh Penggunaan — React

```jsx
// hooks/useUserGate.js
import { useState, useCallback } from 'react';

const BASE_URL = process.env.REACT_APP_USERGATE_BASE_URL;
const API_KEY = process.env.REACT_APP_USERGATE_API_KEY;

export function useUserGate() {
  const [user, setUser] = useState(null);
  const [token, setToken] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const login = useCallback(async (username, password) => {
    setLoading(true);
    setError(null);

    try {
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
        setToken(data.data.access_token);
        setUser(data.data.user);
        return data.data;
      } else {
        setError(data.message);
        return null;
      }
    } catch (err) {
      setError(err.message);
      return null;
    } finally {
      setLoading(false);
    }
  }, []);

  const logout = useCallback(async () => {
    if (!token) return;

    try {
      await fetch(`${BASE_URL}/auth/logout`, {
        method: 'POST',
        headers: {
          'X-API-Key': API_KEY,
          'Authorization': `Bearer ${token}`,
        },
      });
    } finally {
      setToken(null);
      setUser(null);
    }
  }, [token]);

  const apiCall = useCallback(async (method, endpoint, data = {}) => {
    const headers = {
      'X-API-Key': API_KEY,
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };

    if (token) {
      headers['Authorization'] = `Bearer ${token}`;
    }

    const response = await fetch(`${BASE_URL}${endpoint}`, {
      method,
      headers,
      body: method !== 'GET' ? JSON.stringify(data) : undefined,
    });

    return await response.json();
  }, [token]);

  return { user, token, loading, error, login, logout, apiCall };
}
```

```jsx
// components/UserList.js
import React, { useState, useEffect } from 'react';
import { useUserGate } from '../hooks/useUserGate';

export function UserList() {
  const { user, token, apiCall } = useUserGate();
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (token) {
      fetchUsers();
    }
  }, [token]);

  async function fetchUsers() {
    setLoading(true);
    const response = await apiCall('GET', '/users?page=1&per_page=20');
    if (response.status) {
      setUsers(response.data);
    }
    setLoading(false);
  }

  if (loading) return <p>Loading...</p>;

  return (
    <div>
      <h2>Users</h2>
      <p>Logged in as: {user?.full_name}</p>
      <ul>
        {users.map(u => (
          <li key={u.id}>
            {u.username} - {u.email} [{u.status}]
          </li>
        ))}
      </ul>
    </div>
  );
}
```

---

## Error Handling

```javascript
// utils/errorHandler.js
function handleUserGateError(response) {
  if (response.status) return null;

  const message = response.message || 'Unknown error';

  if (message.includes('Invalid credentials')) {
    return 'Username atau password salah.';
  }

  if (message.includes('expired')) {
    return 'Sesi sudah berakhir. Silakan login kembali.';
  }

  if (message.includes('does not have')) {
    return 'Anda tidak memiliki izin untuk melakukan operasi ini.';
  }

  if (message.includes('already exists')) {
    return 'Data sudah ada. Gunakan data lain.';
  }

  if (message.includes('Validation failed')) {
    const errors = response.errors || {};
    const details = Object.entries(errors)
      .map(([field, error]) => `${field}: ${error}`)
      .join(', ');
    return `Validasi gagal: ${details}`;
  }

  return `Error: ${message}`;
}

module.exports = { handleUserGateError };
```

---

## Auto-Refresh Token

```javascript
// middleware/autoRefresh.js
class AutoRefreshMiddleware {
  constructor(auth, refreshBuffer = 60000) {
    this.auth = auth;
    this.refreshBuffer = refreshBuffer;
    this.lastRefresh = Date.now();
    this.tokenLifetime = 900000; // 15 menit dalam ms
  }

  async ensureValidToken() {
    const timeSinceRefresh = Date.now() - this.lastRefresh;

    if (timeSinceRefresh > this.tokenLifetime - this.refreshBuffer) {
      const result = await this.auth.refresh();
      if (result.status) {
        this.lastRefresh = Date.now();
        return true;
      }
      return false;
    }

    return true;
  }

  async requestWithRefresh(client, method, endpoint, data = {}) {
    await this.ensureValidToken();

    let response = await client.request(
      method, endpoint, data, this.auth.accessToken
    );

    // Auto-retry jika token expired
    if (!response.status && response.message?.includes('expired')) {
      const refreshed = await this.auth.refresh();
      if (refreshed) {
        response = await client.request(
          method, endpoint, data, this.auth.accessToken
        );
      }
    }

    return response;
  }
}

module.exports = AutoRefreshMiddleware;
```
