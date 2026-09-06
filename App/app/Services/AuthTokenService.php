<?php

namespace App\Services;

use App\Models\AuthToken;

class AuthTokenService
{
    private const ACCESS_TTL = 900;
    private const REFRESH_TTL = 2592000;

    public function issue(array $user, array $apiKey, string $ipAddress, string $userAgent): array
    {
        $accessToken = $this->randomToken();
        $refreshToken = $this->randomToken();
        $now = time();

        (new AuthToken())->insert([
            'id' => $this->uuid(),
            'user_id' => $user['id'],
            'application_id' => $apiKey['application_id'],
            'api_key_id' => $apiKey['id'],
            'access_token_hash' => hash('sha256', $accessToken),
            'refresh_token_hash' => hash('sha256', $refreshToken),
            'access_expires_at' => date('Y-m-d H:i:s', $now + self::ACCESS_TTL),
            'refresh_expires_at' => date('Y-m-d H:i:s', $now + self::REFRESH_TTL),
            'ip_address' => $ipAddress,
            'user_agent' => substr($userAgent, 0, 255),
        ]);

        return [
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
            'expires_in' => self::ACCESS_TTL,
            'refresh_token' => $refreshToken,
            'refresh_expires_in' => self::REFRESH_TTL,
        ];
    }

    public function findAccessToken(string $token): ?array
    {
        $record = (new AuthToken())->where('access_token_hash', hash('sha256', $token))->first();
        if (!$record || $record['revoked_at'] !== null || strtotime($record['access_expires_at']) <= time()) {
            return null;
        }
        return $record;
    }

    public function refresh(string $refreshToken, array $apiKey, string $ipAddress, string $userAgent): ?array
    {
        $model = new AuthToken();
        $record = $model->where('refresh_token_hash', hash('sha256', $refreshToken))->first();
        if (!$record || $record['revoked_at'] !== null || $record['api_key_id'] !== $apiKey['id'] || strtotime($record['refresh_expires_at']) <= time()) {
            return null;
        }
        $model->update($record['id'], ['revoked_at' => date('Y-m-d H:i:s')]);
        $user = (new \App\Models\User())->find($record['user_id']);
        if (!$user || $user['status'] !== 'ACTIVE') {
            return null;
        }
        return ['tokens' => $this->issue($user, $apiKey, $ipAddress, $userAgent), 'user' => $user];
    }

    public function revoke(string $id): void
    {
        (new AuthToken())->update($id, ['revoked_at' => date('Y-m-d H:i:s')]);
    }

    private function randomToken(): string { return rtrim(strtr(base64_encode(random_bytes(48)), '+/', '-_'), '='); }
    private function uuid(): string { $data = random_bytes(16); $data[6] = chr(ord($data[6]) & 0x0f | 0x40); $data[8] = chr(ord($data[8]) & 0x3f | 0x80); return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4)); }
}
