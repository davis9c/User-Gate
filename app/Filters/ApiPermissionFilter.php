<?php

namespace App\Filters;

use App\Libraries\ApiPermission;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class ApiPermissionFilter implements FilterInterface
{
    public function before(
        RequestInterface $request,
        $arguments = null
    ) {
        if (empty($arguments)) {
            return service('response')
                ->setStatusCode(500)
                ->setJSON([
                    'status'  => false,
                    'message' => 'API permission is not configured.',
                ]);
        }

        $permission = $arguments[0];

        $apiPermission = new ApiPermission();

        if (!$apiPermission->has($permission)) {
            return service('response')
                ->setStatusCode(403)
                ->setJSON([
                    'status'  => false,
                    'message' => 'API Key does not have ' . $permission . ' permission.',
                ]);
        }
    }

    public function after(
        RequestInterface $request,
        ResponseInterface $response,
        $arguments = null
    ) {}
}
