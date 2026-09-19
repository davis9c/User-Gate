# Python Integration Example — Lengkap

> **Cara Pakai Dokumen Ini:**
> Upload file ini ke AI lalu minta:
> "Buatkan saya aplikasi Python yang terintegrasi dengan UserGate"
> atau "Bagaimana cara integrasi UserGate dengan Django/Flask/FastAPI?"
> AI akan memberikan kode yang sudah sesuai.

---

## Setup Awal

### Environment Variables

```bash
# .env
USERGATE_BASE_URL=https://usergate.sandalgurun.web.id/api/v1
USERGATE_API_KEY=ug_xxxxxxxxxxxxxxxx
```

### Install Dependencies

```bash
pip install requests python-dotenv
```

### Struktur Folder

```
your-app/
├── .env
├── config/
│   └── usergate.py
├── src/
│   ├── __init__.py
│   ├── client.py
│   ├── auth.py
│   └── user.py
└── main.py
```

---

## Config

```python
# config/usergate.py
import os
from dotenv import load_dotenv

load_dotenv()

USERGATE_BASE_URL = os.getenv(
    'USERGATE_BASE_URL',
    'https://usergate.sandalgurun.web.id/api/v1'
)
USERGATE_API_KEY = os.getenv(
    'USERGATE_API_KEY',
    'ug_xxxxxxxxxxxxxxxx'
)
```

---

## UserGateClient — Base Client

```python
# src/client.py
import requests
from config.usergate import USERGATE_BASE_URL, USERGATE_API_KEY


class UserGateClient:
    def __init__(
        self,
        base_url: str = USERGATE_BASE_URL,
        api_key: str = USERGATE_API_KEY
    ):
        self.base_url = base_url.rstrip('/')
        self.api_key = api_key

    def request(
        self,
        method: str,
        endpoint: str,
        data: dict = None,
        token: str = None,
        params: dict = None
    ) -> dict:
        """Kirim request ke UserGate API"""
        url = f"{self.base_url}{endpoint}"

        headers = {
            'X-API-Key': self.api_key,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        }

        if token:
            headers['Authorization'] = f'Bearer {token}'

        response = requests.request(
            method,
            url,
            headers=headers,
            json=data,
            params=params,
            timeout=30
        )

        result = response.json()
        result['http_code'] = response.status_code

        return result

    def get(self, endpoint: str, token: str = None, params: dict = None) -> dict:
        return self.request('GET', endpoint, token=token, params=params)

    def post(self, endpoint: str, data: dict = None, token: str = None) -> dict:
        return self.request('POST', endpoint, data=data, token=token)

    def put(self, endpoint: str, data: dict = None, token: str = None) -> dict:
        return self.request('PUT', endpoint, data=data, token=token)

    def delete(self, endpoint: str, token: str = None) -> dict:
        return self.request('DELETE', endpoint, token=token)
```

---

## UserGateAuth — Authentication

```python
# src/auth.py
class UserGateAuth:
    def __init__(self, client):
        self.client = client
        self.access_token = None
        self.refresh_token = None

    def login(self, username: str, password: str) -> dict:
        """Login dan dapatkan token"""
        response = self.client.post('/auth/login', {
            'username': username,
            'password': password,
        })

        if response['status']:
            self.access_token = response['data']['access_token']
            self.refresh_token = response['data']['refresh_token']

        return response

    def refresh(self) -> dict:
        """Refresh token"""
        if not self.refresh_token:
            return {'status': False, 'message': 'No refresh token available'}

        response = self.client.post('/auth/refresh', {
            'refresh_token': self.refresh_token,
        })

        if response['status']:
            self.access_token = response['data']['access_token']
            self.refresh_token = response['data']['refresh_token']

        return response

    def me(self) -> dict:
        """Ambil data user yang sedang login"""
        return self.client.get('/auth/me', token=self.access_token)

    def logout(self) -> dict:
        """Logout"""
        response = self.client.post('/auth/logout', token=self.access_token)

        if response['status']:
            self.access_token = None
            self.refresh_token = None

        return response

    def is_logged_in(self) -> bool:
        return self.access_token is not None
```

---

## UserGateUser — CRUD User

```python
# src/user.py
class UserGateUser:
    def __init__(self, client, token: str):
        self.client = client
        self.token = token

    def list(self, page: int = 1, per_page: int = 20, search: str = '') -> dict:
        """Daftar user dengan pagination"""
        params = {'page': page, 'per_page': per_page}
        if search:
            params['search'] = search

        return self.client.get('/users', token=self.token, params=params)

    def get(self, user_id: str) -> dict:
        """Detail user"""
        return self.client.get(f'/users/{user_id}', token=self.token)

    def create(self, user_data: dict) -> dict:
        """Buat user baru"""
        required = ['username', 'email', 'full_name', 'password']
        for field in required:
            if not user_data.get(field):
                return {
                    'status': False,
                    'message': f"Field '{field}' is required"
                }

        return self.client.post('/users', data=user_data, token=self.token)

    def update(self, user_id: str, user_data: dict) -> dict:
        """Ubah user"""
        return self.client.put(f'/users/{user_id}', data=user_data, token=self.token)

    def delete(self, user_id: str) -> dict:
        """Hapus user"""
        return self.client.delete(f'/users/{user_id}', token=self.token)

    def deactivate(self, user_id: str) -> dict:
        """Nonaktifkan user"""
        return self.update(user_id, {'status': 'INACTIVE'})

    def activate(self, user_id: str) -> dict:
        """Aktifkan user"""
        return self.update(user_id, {'status': 'ACTIVE'})
```

---

## Contoh Penggunaan

```python
# main.py
from src.client import UserGateClient
from src.auth import UserGateAuth
from src.user import UserGateUser


def handle_error(response: dict) -> str:
    """Handle error dari UserGate API"""
    if response.get('status'):
        return ''

    message = response.get('message', 'Unknown error')

    if 'Invalid credentials' in message:
        return 'Username atau password salah.'

    if 'expired' in message:
        return 'Sesi sudah berakhir. Silakan login kembali.'

    if 'does not have' in message:
        return 'Anda tidak memiliki izin untuk melakukan operasi ini.'

    if 'already exists' in message:
        return 'Data sudah ada. Gunakan data lain.'

    if 'Validation failed' in message:
        errors = response.get('errors', {})
        details = ', '.join([f'{k}: {v}' for k, v in errors.items()])
        return f'Validasi gagal: {details}'

    return f'Error: {message}'


def main():
    # 1. Inisialisasi client
    client = UserGateClient()

    # 2. Login
    auth = UserGateAuth(client)
    login_result = auth.login('admin', 'password_admin')

    error = handle_error(login_result)
    if error:
        print(f'Login gagal: {error}')
        return

    print('Login berhasil!')
    print(f"User: {login_result['data']['user']['full_name']}")
    print(f"Token berlaku: {login_result['data']['expires_in']} detik\n")

    # 3. Ambil data user login
    me = auth.me()
    if me['status']:
        user_data = me['data']
        print('Data user saat ini:')
        print(f"  Username: {user_data['username']}")
        print(f"  Email: {user_data['email']}")
        print(f"  Roles: {', '.join(user_data['roles'])}\n")

    # 4. CRUD User
    user_api = UserGateUser(client, auth.access_token)

    # List users
    print('=== Daftar User ===')
    users = user_api.list(1, 10)
    if users['status']:
        for user in users['data']:
            print(f"  - {user['username']} ({user['email']}) [{user['status']}]")
        print(f"  Total: {users['meta']['total']}\n")

    # Create user
    print('=== Buat User Baru ===')
    import time
    new_user = user_api.create({
        'username': f'demo_user_{int(time.time())}',
        'email': f'demo_{int(time.time())}@example.com',
        'full_name': 'Demo User',
        'password': 'password123',
    })

    error = handle_error(new_user)
    if error:
        print(f'  Gagal: {error}')
    else:
        user_id = new_user['data']['id']
        print(f"  Berhasil! ID: {user_id}")

        # Update user
        print('\n=== Update User ===')
        updated = user_api.update(user_id, {
            'full_name': 'Demo User Updated',
        })

        if updated['status']:
            print(f"  Nama baru: {updated['data']['full_name']}")

        # Get user detail
        print('\n=== Detail User ===')
        detail = user_api.get(user_id)
        if detail['status']:
            print(f"  Username: {detail['data']['username']}")
            print(f"  Email: {detail['data']['email']}")
            print(f"  Status: {detail['data']['status']}")

        # Delete user
        print('\n=== Hapus User ===')
        deleted = user_api.delete(user_id)
        if deleted['status']:
            print('  Berhasil dihapus')

    # 5. Refresh token
    print('\n=== Refresh Token ===')
    refresh_result = auth.refresh()
    if refresh_result['status']:
        print('  Token berhasil di-refresh')

    # 6. Logout
    print('\n=== Logout ===')
    logout_result = auth.logout()
    if logout_result['status']:
        print('  Logout berhasil')


if __name__ == '__main__':
    main()
```

---

## Contoh dengan FastAPI

```python
# fastapi_example.py
from fastapi import FastAPI, HTTPException, Depends
from fastapi.security import HTTPBearer, HTTPAuthorizationCredentials
from pydantic import BaseModel
from src.client import UserGateClient
from src.auth import UserGateAuth

app = FastAPI(title="My App with UserGate")
security = HTTPBearer()

client = UserGateClient()
auth = UserGateAuth(client)


class LoginRequest(BaseModel):
    username: str
    password: str


class CreateUserRequest(BaseModel):
    username: str
    email: str
    full_name: str
    password: str


async def get_current_user(
    credentials: HTTPAuthorizationCredentials = Depends(security)
):
    """Ambil data user dari token"""
    token = credentials.credentials

    # Decode JWT atau validate token
    # Untuk contoh ini, kita gunakan /auth/me
    response = client.get('/auth/me', token=token)

    if not response.get('status'):
        raise HTTPException(status_code=401, detail="Invalid token")

    return response['data']


@app.post("/login")
async def login(request: LoginRequest):
    """Login ke UserGate"""
    result = auth.login(request.username, request.password)

    if not result['status']:
        raise HTTPException(status_code=401, detail=result['message'])

    return {
        "access_token": result['data']['access_token'],
        "token_type": "Bearer",
        "user": result['data']['user']
    }


@app.get("/me")
async def get_me(user=Depends(get_current_user)):
    """Ambil data user yang sedang login"""
    return user


@app.get("/users")
async def list_users(
    page: int = 1,
    per_page: int = 20,
    search: str = '',
    user=Depends(get_current_user)
):
    """Daftar user"""
    result = client.get(
        '/users',
        params={'page': page, 'per_page': per_page, 'search': search}
    )
    return result


@app.post("/users")
async def create_user(
    request: CreateUserRequest,
    user=Depends(get_current_user)
):
    """Buat user baru"""
    result = client.post('/users', data=request.dict())

    if not result['status']:
        raise HTTPException(status_code=400, detail=result['message'])

    return result['data']


@app.put("/users/{user_id}")
async def update_user(
    user_id: str,
    request: CreateUserRequest,
    user=Depends(get_current_user)
):
    """Ubah user"""
    result = client.put(f'/users/{user_id}', data=request.dict())

    if not result['status']:
        raise HTTPException(status_code=400, detail=result['message'])

    return result['data']


@app.delete("/users/{user_id}")
async def delete_user(
    user_id: str,
    user=Depends(get_current_user)
):
    """Hapus user"""
    result = client.delete(f'/users/{user_id}')

    if not result['status']:
        raise HTTPException(status_code=400, detail=result['message'])

    return result['data']
```

---

## Contoh dengan Flask

```python
# flask_example.py
from flask import Flask, request, jsonify
from functools import wraps
from src.client import UserGateClient
from src.auth import UserGateAuth

app = Flask(__name__)
client = UserGateClient()
auth = UserGateAuth(client)


def token_required(f):
    @wraps(f)
    def decorated(*args, **kwargs):
        token = request.headers.get('Authorization', '').replace('Bearer ', '')

        if not token:
            return jsonify({'error': 'Token missing'}), 401

        # Validate token
        result = client.get('/auth/me', token=token)

        if not result.get('status'):
            return jsonify({'error': 'Invalid token'}), 401

        request.current_user = result['data']
        return f(*args, **kwargs)

    return decorated


@app.route('/login', methods=['POST'])
def login():
    data = request.get_json()
    result = auth.login(data['username'], data['password'])

    if not result['status']:
        return jsonify({'error': result['message']}), 401

    return jsonify({
        'access_token': result['data']['access_token'],
        'user': result['data']['user']
    })


@app.route('/me')
@token_required
def me():
    return jsonify(request.current_user)


@app.route('/users')
@token_required
def list_users():
    page = request.args.get('page', 1, type=int)
    per_page = request.args.get('per_page', 20, type=int)
    search = request.args.get('search', '')

    result = client.get(
        '/users',
        params={'page': page, 'per_page': per_page, 'search': search}
    )

    return jsonify(result)


@app.route('/users', methods=['POST'])
@token_required
def create_user():
    data = request.get_json()
    result = client.post('/users', data=data)

    if not result['status']:
        return jsonify({'error': result['message']}), 400

    return jsonify(result['data']), 201


@app.route('/users/<user_id>', methods=['PUT'])
@token_required
def update_user(user_id):
    data = request.get_json()
    result = client.put(f'/users/{user_id}', data=data)

    if not result['status']:
        return jsonify({'error': result['message']}), 400

    return jsonify(result['data'])


@app.route('/users/<user_id>', methods=['DELETE'])
@token_required
def delete_user(user_id):
    result = client.delete(f'/users/{user_id}')

    if not result['status']:
        return jsonify({'error': result['message']}), 400

    return jsonify({'message': 'User deleted'})


if __name__ == '__main__':
    app.run(debug=True)
```

---

## Auto-Refresh Token

```python
# src/auto_refresh.py
import time


class AutoRefreshToken:
    def __init__(self, auth, refresh_buffer: int = 60):
        """
        auth: UserGateAuth instance
        refresh_buffer: detik sebelum token expired untuk refresh
        """
        self.auth = auth
        self.refresh_buffer = refresh_buffer
        self.last_refresh = time.time()
        self.token_lifetime = 900  # 15 menit dalam detik

    def ensure_valid_token(self) -> bool:
        """Pastikan token masih valid, refresh jika perlu"""
        time_since_refresh = time.time() - self.last_refresh

        if time_since_refresh > self.token_lifetime - self.refresh_buffer:
            result = self.auth.refresh()
            if result['status']:
                self.last_refresh = time.time()
                return True
            return False

        return True

    def request_with_refresh(self, client, method, endpoint, data=None, **kwargs):
        """Request dengan auto-refresh"""
        self.ensure_valid_token()

        result = client.request(
            method, endpoint, data, token=self.auth.access_token, **kwargs
        )

        # Auto-retry jika token expired
        if not result.get('status') and 'expired' in result.get('message', ''):
            refreshed = self.auth.refresh()
            if refreshed['status']:
                result = client.request(
                    method, endpoint, data, token=self.auth.access_token, **kwargs
                )

        return result
```

---

## Error Handling Template

```python
# src/error_handler.py
class UserGateError(Exception):
    def __init__(self, message: str, errors: dict = None):
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


def handle_error(response: dict):
    """Raise exception berdasarkan error response"""
    if response.get('status'):
        return

    message = response.get('message', 'Unknown error')
    errors = response.get('errors', {})

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
    result = auth.login('admin', 'wrong_password')
    handle_error(result)
except UserGateAuthError as e:
    print(f'Login gagal: {e}')
except UserGateValidationError as e:
    print(f'Validasi gagal: {e.errors}')
except UserGateError as e:
    print(f'Error: {e}')
```
