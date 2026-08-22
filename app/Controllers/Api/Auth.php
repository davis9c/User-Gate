<?php

namespace App\Controllers\Api;

use App\Models\User;
use App\Models\UserCredential;
use App\Models\UserRole;
use App\Services\AuditService;
use App\Services\AuthTokenService;

class Auth extends ApiController
{
    public function login()
    {
        $data = $this->request->getJSON(true) ?? [];
        if (!$this->validateData($data, ['username' => 'required|max_length[100]', 'password' => 'required|max_length[1024]'])) {
            return $this->invalidCredentials();
        }
        $user = (new User())->where('username', $data['username'])->first();
        $credential = $user ? (new UserCredential())->where('user_id', $user['id'])->first() : null;
        if (!$user || !$credential || $user['status'] !== 'ACTIVE' || !password_verify($data['password'], $credential['password_hash'])) {
            (new AuditService())->record('LOGIN_FAILED', null, $this->apiKey()['application_id'], ['username_present' => isset($data['username'])]);
            return $this->invalidCredentials();
        }
        if (password_needs_rehash($credential['password_hash'], PASSWORD_DEFAULT)) {
            (new UserCredential())->update($credential['id'], ['password_hash' => password_hash($data['password'], PASSWORD_DEFAULT)]);
        }
        $tokens = (new AuthTokenService())->issue($user, $this->apiKey(), $this->request->getIPAddress(), $this->request->getUserAgent()->getAgentString());
        $tokens['user'] = $this->userPayload($user);
        (new AuditService())->record('LOGIN_SUCCESS', $user['id'], $this->apiKey()['application_id']);
        (new AuditService())->record('TOKEN_ISSUED', $user['id'], $this->apiKey()['application_id']);
        return $this->apiResponse->success($this->response, $tokens, 'Authenticated successfully.');
    }

    public function refresh()
    {
        $data = $this->request->getJSON(true) ?? [];
        $result = isset($data['refresh_token']) && is_string($data['refresh_token'])
            ? (new AuthTokenService())->refresh($data['refresh_token'], $this->apiKey(), $this->request->getIPAddress(), $this->request->getUserAgent()->getAgentString()) : null;
        if (!$result) return $this->apiResponse->error($this->response, 'Invalid or expired refresh token.', 401);
        $result['tokens']['user'] = $this->userPayload($result['user']);
        (new AuditService())->record('TOKEN_ISSUED', null, $this->apiKey()['application_id'], ['grant' => 'refresh']);
        return $this->apiResponse->success($this->response, $result['tokens'], 'Token refreshed successfully.');
    }

    public function me()
    {
        $user = service('request')->authUser;
        return $this->apiResponse->success($this->response, [
            'id' => $user['id'], 'username' => $user['username'], 'email' => $user['email'],
            'full_name' => $user['full_name'], 'status' => $user['status'],
            'roles' => $this->userRoles($user['id']),
            'is_super_admin' => $this->isSuperAdmin($this->userRoles($user['id'])),
        ], 'Current user retrieved successfully.');
    }

    public function logout()
    {
        $token = service('request')->authToken;
        (new AuthTokenService())->revoke($token['id']);
        (new AuditService())->record('LOGOUT', $token['user_id'], $token['application_id']);
        (new AuditService())->record('TOKEN_REVOKED', $token['user_id'], $token['application_id']);
        return $this->apiResponse->success($this->response, null, 'Logged out successfully.');
    }

    private function apiKey(): array { return service('request')->apiKey; }
    private function invalidCredentials() { return $this->apiResponse->error($this->response, 'Invalid credentials.', 401); }

    private function userRoles(string $userId): array
    {
        return array_values(array_map(
            static fn (array $row): string => $row['role'],
            (new UserRole())->where('user_id', $userId)->findAll()
        ));
    }

    private function isSuperAdmin(array $roles): bool
    {
        return in_array('SUPER_ADMIN', $roles, true);
    }

    private function userPayload(array $user): array
    {
        $roles = $this->userRoles($user['id']);
        return [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'full_name' => $user['full_name'],
            'status' => $user['status'],
            'roles' => $roles,
            'is_super_admin' => $this->isSuperAdmin($roles),
        ];
    }
}
