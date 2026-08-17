<?php

namespace App\Services;

use App\Models\Role;
use App\Models\UserRole;
use App\Exceptions\UserException;

class RoleService
{
    protected Role $roleModel;
    protected UserRole $userRoleModel;

    public function __construct()
    {
        $this->roleModel = new Role();
        $this->userRoleModel = new UserRole();
    }

    public function getList(): array
    {
        return $this->roleModel
            ->where('status', 'ACTIVE')
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    public function findById(int $id): ?array
    {
        return $this->roleModel->find($id);
    }

    public function findByCode(string $code): ?array
    {
        return $this->roleModel
            ->where('code', $code)
            ->first();
    }

    public function assignToUser(
        string $userId,
        int $roleId
    ): bool {
        $role = $this->roleModel->find($roleId);

        if (!$role) {
            throw new UserException(
                'Role not found.',
                404
            );
        }

        $existing = $this->userRoleModel
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            $this->userRoleModel->update(
                $existing['id'],
                [
                    'role_id' => $roleId,
                ]
            );

            return true;
        }

        $this->userRoleModel->insert([
            'user_id' => $userId,
            'role_id' => $roleId,
        ]);

        return true;
    }

    public function getUserRole(string $userId): ?array
    {
        return $this->userRoleModel
            ->select(
                'user_roles.id,
                 user_roles.user_id,
                 user_roles.role_id,
                 roles.name,
                 roles.code'
            )
            ->join(
                'roles',
                'roles.id = user_roles.role_id'
            )
            ->where(
                'user_roles.user_id',
                $userId
            )
            ->first();
    }
}
