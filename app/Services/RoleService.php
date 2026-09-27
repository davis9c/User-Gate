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
            ->where('role_id', $roleId)
            ->first();

        if ($existing) {
            return true;
        }

        $this->userRoleModel->insert([
            'user_id' => $userId,
            'role_id' => $roleId,
        ]);

        return true;
    }

    /**
     * Cabut role dari user. Aman dipanggil berulang (idempoten).
     */
    public function revokeFromUser(string $userId, int $roleId): bool
    {
        $this->userRoleModel
            ->where('user_id', $userId)
            ->where('role_id', $roleId)
            ->delete();

        return true;
    }

    /**
     * Apakah user punya role dengan kode tertentu?
     */
    public function hasRole(string $userId, string $roleCode): bool
    {
        return $this->userRoleModel
            ->select('user_roles.id')
            ->join('roles', 'roles.id = user_roles.role_id')
            ->where('user_roles.user_id', $userId)
            ->where('roles.code', $roleCode)
            ->first() !== null;
    }

    /**
     * Jumlah user yang masih memegang role dengan kode tertentu.
     * Dipakai untuk mencegah SUPER_ADMIN terakhir kehilangan akses.
     */
    public function countUsersWithRole(string $roleCode): int
    {
        return $this->userRoleModel
            ->select('user_roles.id')
            ->join('roles', 'roles.id = user_roles.role_id')
            ->where('roles.code', $roleCode)
            ->countAllResults();
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
