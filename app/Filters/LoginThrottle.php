<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class LoginThrottle implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $key = 'login_' . hash('sha256', $request->getIPAddress());
        if (!service('throttler')->check($key, 5, MINUTE)) {
            return service('response')->setStatusCode(429)->setHeader('Retry-After', '60')->setJSON([
                'status' => false,
                'message' => 'Too many login attempts. Please try again later.',
            ]);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
