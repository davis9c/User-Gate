<?php

namespace App\Controllers;

use App\Libraries\RolePermission;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    protected RolePermission $rolePermission;

    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger
    ) {
        parent::initController(
            $request,
            $response,
            $logger
        );

        $this->rolePermission = new RolePermission();
    }
    protected function requireRolePermission(string $permission): ?ResponseInterface
    {
        if ($this->rolePermission->has($permission)) {
            return null;
        }

        return $this->response
            ->setStatusCode(403)
            ->setJSON([
                'status'  => false,
                'message' => 'You do not have permission to perform this action.',
            ]);
    }
}
