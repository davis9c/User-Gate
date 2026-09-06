<?php

namespace App\Filters;

use App\Libraries\RolePermission;
use App\Services\AuditService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class UserPermissionFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $permission = $arguments[0] ?? null;
        if (!$permission || !(new RolePermission())->has($permission)) {
            $user = $request->authUser ?? null;
            if ($permission) (new AuditService())->record('PERMISSION_DENIED', $user['id'] ?? null, null, ['permission' => $permission]);
            return service('response')->setStatusCode(403)->setJSON([
                'status' => false, 'message' => 'Permission denied.',
            ]);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
