<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserCredential;
use App\Exceptions\UserException; 

class UserService
{
    protected User $userModel;
    protected UserCredential $credentialModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->credentialModel = new UserCredential();
    }

    public function getList(
        string $search = '',
        int $page = 1,
        int $perPage = 20
    ): array {
        $page = max(1, $page);
        $perPage = min(max($perPage, 1), 100);

        if ($search !== '') {
            $this->userModel
                ->groupStart()
                ->like('username', $search)
                ->orLike('email', $search)
                ->orLike('full_name', $search)
                ->groupEnd();
        }

        $users = $this->userModel
            ->select(
                'id, username, email, full_name, status, created_at, updated_at'
            )
            ->orderBy('created_at', 'DESC')
            ->paginate($perPage, 'default', $page);

        $pager = $this->userModel->pager;

        return [
            'data' => $users,
            'meta' => [
                'page'        => $page,
                'per_page'    => $perPage,
                'total'       => $pager->getTotal(),
                'total_pages' => $pager->getPageCount(),
            ],
        ];
    }

    public function findById(string $id): ?array
    {
        return $this->userModel
            ->select(
                'id, username, email, full_name, status, created_at, updated_at'
            )
            ->find($id);
    }

    public function create(array $data): array
    {
        if (
            $this->userModel
            ->where('username', $data['username'])
            ->first()
        ) {
            throw new UserException(
                'Username already exists.',
                409
            );
        }

        if (
            $this->userModel
            ->where('email', $data['email'])
            ->first()
        ) {
            throw new UserException(
                'Email already exists.',
                409
            );
        }

        $userId = $this->generateUuid();

        $db = db_connect();

        $db->transStart();

        $this->userModel->insert([
            'id'        => $userId,
            'username'  => $data['username'],
            'email'     => $data['email'],
            'full_name' => $data['full_name'],
            'status'    => 'ACTIVE',
        ]);

        $this->credentialModel->insert([
            'user_id'       => $userId,
            'password_hash' => password_hash(
                $data['password'],
                PASSWORD_DEFAULT
            ),
        ]);

        $db->transComplete();

        if (!$db->transStatus()) {
            throw new UserException(
                'Failed to create user.',
                500
            );
        }

        return [
            'id'        => $userId,
            'username'  => $data['username'],
            'email'     => $data['email'],
            'full_name' => $data['full_name'],
            'status'    => 'ACTIVE',
        ];
    }

    public function update(string $id, array $data): array
    {
        $user = $this->userModel->find($id);

        if (!$user) {
            throw new UserException(
                'User not found.',
                404
            );
        }

        $existingUsername = $this->userModel
            ->where('username', $data['username'])
            ->where('id !=', $id)
            ->first();

        if ($existingUsername) {
            throw new UserException(
                'Username already exists.',
                409
            );
        }

        $existingEmail = $this->userModel
            ->where('email', $data['email'])
            ->where('id !=', $id)
            ->first();

        if ($existingEmail) {
            throw new UserException(
                'Email already exists.',
                409
            );
        }

        $this->userModel->update($id, [
            'username'  => $data['username'],
            'email'     => $data['email'],
            'full_name' => $data['full_name'],
            'status'    => $data['status'],
        ]);

        return $this->findById($id);
    }

    public function delete(string $id): array
    {
        $user = $this->userModel->find($id);

        if (!$user) {
            throw new UserException(
                'User not found.',
                404
            );
        }

        $db = db_connect();

        $db->transStart();

        $this->credentialModel
            ->where('user_id', $id)
            ->delete();

        $this->userModel->delete($id);

        $db->transComplete();

        if (!$db->transStatus()) {
            throw new UserException(
                'Failed to delete user.',
                500
            );
        }

        return [
            'id'       => $id,
            'username' => $user['username'],
        ];
    }

    private function generateUuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0x0fff) | 0x4000,
            random_int(0, 0x3fff) | 0x8000,
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0xffff)
        );
    }
}
