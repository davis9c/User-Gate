<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\UserCredential;

class Users extends BaseController
{
    public function index()
    {
        $permission = $this->requireRolePermission('user.read');

        if ($permission !== null) {
            return $permission;
        }

        $userModel = new User();

        $data = [
            'title' => 'Users',
            'users' => $userModel
                ->orderBy('created_at', 'DESC')
                ->findAll(),
        ];

        return view('users/index', $data);
    }

    public function new()
    {
        $permission = $this->requireRolePermission('user.create');

        if ($permission !== null) {
            return $permission;
        }

        return view('users/create', [
            'title' => 'Create User',
        ]);
    }

    public function create()
    {
        $permission = $this->requireRolePermission('user.create');

        if ($permission !== null) {
            return $permission;
        }
        $rules = [
            'full_name' => 'required|min_length[3]|max_length[150]',
            'username'  => 'required|min_length[3]|max_length[100]|alpha_numeric',
            'email'     => 'required|valid_email|max_length[255]',
            'password'  => 'required|min_length[8]',
            'password_confirmation' => 'required|matches[password]',
            'status'    => 'required|in_list[ACTIVE,INACTIVE]',
        ];

        if (!$this->validate($rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $userModel       = new User();
        $credentialModel = new UserCredential();

        $username = trim($this->request->getPost('username'));
        $email    = trim($this->request->getPost('email'));

        if ($userModel->where('username', $username)->first()) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Username sudah digunakan.');
        }

        if ($userModel->where('email', $email)->first()) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Email sudah digunakan.');
        }

        $userId = $this->generateUuid();

        $db = db_connect();

        $db->transStart();

        $userModel->insert([
            'id'        => $userId,
            'username'  => $username,
            'email'     => $email,
            'full_name' => trim($this->request->getPost('full_name')),
            'status'    => $this->request->getPost('status'),
        ]);

        $credentialModel->insert([
            'user_id'       => $userId,
            'password_hash' => password_hash(
                $this->request->getPost('password'),
                PASSWORD_DEFAULT
            ),
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal membuat user.');
        }

        return redirect()
            ->to('/dashboard/users')
            ->with('success', 'User berhasil dibuat.');
    }

    public function edit($id)
    {
        $permission = $this->requireRolePermission('user.update');

        if ($permission !== null) {
            return $permission;
        }

        $userModel = new User();

        $user = $userModel->find($id);

        if (!$user) {
            return redirect()
                ->to('/dashboard/users')
                ->with('error', 'User tidak ditemukan.');
        }

        return view('users/edit', [
            'title' => 'Edit User',
            'user'  => $user,
        ]);
    }

    public function update($id)
    {
        $permission = $this->requireRolePermission('user.update');

        if ($permission !== null) {
            return $permission;
        }
        $userModel = new User();

        $user = $userModel->find($id);

        if (!$user) {
            return redirect()
                ->to('/dashboard/users')
                ->with('error', 'User tidak ditemukan.');
        }

        $rules = [
            'full_name' => 'required|min_length[3]|max_length[150]',
            'username'  => 'required|min_length[3]|max_length[100]|alpha_numeric',
            'email'     => 'required|valid_email|max_length[255]',
            'status'    => 'required|in_list[ACTIVE,INACTIVE]',
        ];

        if (!$this->validate($rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $username = trim($this->request->getPost('username'));
        $email    = trim($this->request->getPost('email'));

        $existingUsername = $userModel
            ->where('username', $username)
            ->where('id !=', $id)
            ->first();

        if ($existingUsername) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Username sudah digunakan.');
        }

        $existingEmail = $userModel
            ->where('email', $email)
            ->where('id !=', $id)
            ->first();

        if ($existingEmail) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Email sudah digunakan.');
        }

        $userModel->update($id, [
            'username'  => $username,
            'email'     => $email,
            'full_name' => trim($this->request->getPost('full_name')),
            'status'    => $this->request->getPost('status'),
        ]);

        return redirect()
            ->to('/dashboard/users')
            ->with('success', 'User berhasil diperbarui.');
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

    public function resetPassword($id)
    {
        $permission = $this->requireRolePermission('user.update');

        if ($permission !== null) {
            return $permission;
        }

        $userModel = new User();

        $user = $userModel->find($id);

        if (!$user) {
            return redirect()
                ->to('/dashboard/users')
                ->with('error', 'User tidak ditemukan.');
        }

        return view('users/reset_password', [
            'title' => 'Reset Password',
            'user'  => $user,
        ]);
    }

    public function updatePassword($id)
    {
        $permission = $this->requireRolePermission('user.update');

        if ($permission !== null) {
            return $permission;
        }

        $userModel = new User();

        $user = $userModel->find($id);

        if (!$user) {
            return redirect()
                ->to('/dashboard/users')
                ->with('error', 'User tidak ditemukan.');
        }

        // logic yang sudah ada...
    }
}
