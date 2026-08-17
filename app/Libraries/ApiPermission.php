<?php

namespace App\Libraries;

class ApiPermission
{
    protected $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    public function has(string $permission): bool
    {
        $apiKey = service('request')->apiKey;

        if (empty($apiKey['id'])) {
            return false;
        }

        return $this->db
            ->table('api_key_permissions akp')
            ->join(
                'api_permissions p',
                'p.id = akp.permission_id'
            )
            ->where('akp.api_key_id', $apiKey['id'])
            ->where('p.code', $permission)
            ->countAllResults() > 0;
    }

    public function hasRolePermission(
        string $userId,
        string $permission
    ): bool {
        $result = $this->db
            ->table('user_roles ur')
            ->select('api_permissions.code')
            ->join(
                'role_permissions rp',
                'rp.role_id = ur.role_id'
            )
            ->join(
                'api_permissions',
                'api_permissions.id = rp.permission_id'
            )
            ->where('ur.user_id', $userId)
            ->where('api_permissions.code', $permission)
            ->get()
            ->getRowArray();

        return !empty($result);
    }
}
