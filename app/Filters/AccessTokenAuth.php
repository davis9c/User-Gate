<?php

namespace App\Filters;

use App\Models\User;
use App\Services\AuthTokenService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AccessTokenAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $header = $request->getHeaderLine('Authorization');
        if (!preg_match('/^Bearer\\s+([A-Za-z0-9_-]{32,})$/', $header, $matches)) {
            return $this->unauthorized();
        }

        $token = (new AuthTokenService())->findAccessToken($matches[1]);
        $user = $token ? (new User())->find($token['user_id']) : null;
        if (!$user || $user['status'] !== 'ACTIVE') {
            return $this->unauthorized();
        }

        (new \App\Models\AuthToken())->update($token['id'], ['last_used_at' => date('Y-m-d H:i:s')]);
        service('request')->authToken = $token;
        service('request')->authUser = $user;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}

    private function unauthorized(): ResponseInterface
    {
        return service('response')->setStatusCode(401)->setJSON([
            'status' => false,
            'message' => 'Invalid or expired access token.',
        ]);
    }
}
