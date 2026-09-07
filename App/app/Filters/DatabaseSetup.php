<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\IncomingRequest;

class DatabaseSetup implements FilterInterface
{
    private const REQUIRED_TABLES = [
        'migrations',
        'system_settings',
        'users',
        'user_credentials',
        'user_roles',
        'roles',
        'api_permissions',
        'role_permissions',
    ];

    public function before(RequestInterface $request, $arguments = null)
    {
        $path = trim($request->getUri()->getPath(), '/');

        if ($path === 'setup' || $path === 'setup/install') {
            return null;
        }

        try {
            $db = db_connect();

            foreach (self::REQUIRED_TABLES as $table) {
                if (! $db->tableExists($table)) {
                    return $this->setupResponse($request);
                }
            }
        } catch (\Throwable $exception) {
            log_message('error', '[DatabaseSetup] Database is not ready: ' . $exception->getMessage());

            return $this->setupResponse($request);
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}

    private function setupResponse(RequestInterface $request): ResponseInterface
    {
        if ($request instanceof IncomingRequest && $request->isAJAX()) {
            return service('response')
                ->setStatusCode(503)
                ->setJSON([
                    'status'  => false,
                    'message' => 'Database belum siap. Silakan buka /setup.',
                ]);
        }

        if (str_starts_with(trim($request->getUri()->getPath(), '/'), 'api/')) {
            return service('response')
                ->setStatusCode(503)
                ->setJSON([
                    'status'  => false,
                    'message' => 'Database belum siap.',
                ]);
        }

        return redirect()->to('/setup');
    }
}
