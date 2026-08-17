<?php

namespace App\Libraries;

class RolePermission
{
    protected $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    public function has(string $permission): bool
    {
        $userId = session()->get('user_id');

        if (empty($userId)) {
            return false;
        }

        return $this->db
            ->table('user_roles ur')
            ->join(
                'roles r',
                'r.id = ur.role_id'
            )
            ->join(
                'role_permissions rp',
                'rp.role_id = r.id'
            )
            ->join(
                'api_permissions p',
                'p.id = rp.permission_id'
            )
            ->where('ur.user_id', $userId)
            ->where('p.code', $permission)
            ->countAllResults() > 0;
    }
}
