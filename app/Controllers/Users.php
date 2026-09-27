<?php

namespace App\Controllers;

use App\Libraries\DataTableServer;
use App\Models\User;
use App\Models\UserCredential;
use App\Services\AuditService;
use App\Services\RoleService;

class Users extends BaseController
{
    /**
     * Satu-satunya role yang bisa diberikan lewat UI ini.
     */
    private const ROLE_SUPER_ADMIN = 'SUPER_ADMIN';

    public function index()
    {
        $permission = $this->requireRolePermission('user.read');

        if ($permission !== null) {
            return $permission;
        }

        // Baris tabel diambil lewat endpoint server-side (lihat data()).
        return view('users/index', ['title' => 'Users']);
    }

    /**
     * Endpoint server-side untuk tabel Users.
     */
    public function data()
    {
        $permission = $this->requireRolePermission('user.read');

        if ($permission !== null) {
            return $permission;
        }

        $table = new DataTableServer($this->request, $this->response, new User(), [
            'columns'      => ['full_name', 'username', 'email', 'status', 'created_at'],
            'needed'       => ['id'],
            'searchable'   => [0, 1, 2, 3, 4],
            'orderable'    => [0, 1, 2, 3, 4],
            'defaultOrder' => [['column' => 'created_at', 'dir' => 'DESC']],
            // Subquery terkorelasi, bukan JOIN: JOIN ke user_roles akan
            // menggandakan baris untuk user yang punya lebih dari satu role.
            // Ekspresi ini dari server, bukan dari client, jadi tidak masuk
            // jalur allowlist kolom.
            'extraSelect'  => [
                "(SELECT COUNT(*) FROM user_roles ur"
                . " JOIN roles r ON r.id = ur.role_id"
                . " WHERE ur.user_id = users.id"
                . "   AND r.code = 'SUPER_ADMIN') AS is_super_admin",
            ],
            'map'          => static fn (array $row): array => [
                'full_name'      => $row['full_name'],
                'username'       => $row['username'],
                'email'          => $row['email'],
                'status'         => $row['status'],
                'created_at'     => $row['created_at'],
                // Di luar allowlist `columns`, jadi tidak bisa dicari/diurutkan.
                'is_super_admin' => (int) $row['is_super_admin'] > 0 ? 1 : 0,
                'id'             => $row['id'],
            ],
        ]);

        return $table->respond();
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
            $errors = $this->validator->getErrors();

            if ($this->request->isAJAX()) {
                return $this->response
                    ->setStatusCode(422)
                    ->setBody(view('partials/forms/user', [
                        'mode'   => 'create',
                        'errors' => $errors,
                    ]));
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $errors);
        }

        $userModel       = new User();
        $credentialModel = new UserCredential();

        $username = trim($this->request->getPost('username'));
        $email    = trim($this->request->getPost('email'));

        if ($userModel->where('username', $username)->first()) {
            return $this->userCreateFailed('Username sudah digunakan.');
        }

        if ($userModel->where('email', $email)->first()) {
            return $this->userCreateFailed('Email sudah digunakan.');
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

    /**
     * Kegagalan saat create user: balas 422 + form untuk AJAX,
     * atau redirect seperti sebelumnya untuk request biasa.
     */
    private function userCreateFailed(string $message)
    {
        if ($this->request->isAJAX()) {
            return $this->response
                ->setStatusCode(422)
                ->setBody(view('partials/forms/user', [
                    'mode'   => 'create',
                    'errors' => [$message],
                ]));
        }

        return redirect()
            ->back()
            ->withInput()
            ->with('error', $message);
    }

    /**
     * Kegagalan saat update user.
     */
    private function userUpdateFailed($user, string $message)
    {
        if ($this->request->isAJAX()) {
            return $this->response
                ->setStatusCode(422)
                ->setBody(view('partials/forms/user', [
                    'mode'   => 'edit',
                    'user'   => $user,
                    'errors' => [$message],
                ]));
        }

        return redirect()
            ->back()
            ->withInput()
            ->with('error', $message);
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

        $data = [
            'title' => 'Edit User',
            'user'  => $user,
        ];

        // Diminta dari modal: kirim form-nya saja, tanpa chrome halaman.
        if ($this->request->isAJAX()) {
            return $this->response->setBody(
                view('partials/forms/user', [
                    'mode'   => 'edit',
                    'user'   => $user,
                    'errors' => session()->getFlashdata('errors'),
                ])
            );
        }

        return view('users/edit', $data);
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
            $errors = $this->validator->getErrors();

            if ($this->request->isAJAX()) {
                return $this->response
                    ->setStatusCode(422)
                    ->setBody(view('partials/forms/user', [
                        'mode'   => 'edit',
                        'user'   => $user,
                        'errors' => $errors,
                    ]));
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $errors);
        }

        $username = trim($this->request->getPost('username'));
        $email    = trim($this->request->getPost('email'));

        $existingUsername = $userModel
            ->where('username', $username)
            ->where('id !=', $id)
            ->first();

        if ($existingUsername) {
            return $this->userUpdateFailed($user, 'Username sudah digunakan.');
        }

        $existingEmail = $userModel
            ->where('email', $email)
            ->where('id !=', $id)
            ->first();

        if ($existingEmail) {
            return $this->userUpdateFailed($user, 'Email sudah digunakan.');
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

    /**
     * Form grant/revoke role, di-fetch AJAX ke dalam modal.
     *
     * Tanpa JavaScript tidak ada halaman tersendiri untuk form ini, jadi
     * request biasa diarahkan balik ke daftar user. POST-nya sendiri tetap
     * bekerja tanpa JS (redirect + flashdata).
     */
    public function role($id)
    {
        $permission = $this->requireRolePermission('user.assign_role');

        if ($permission !== null) {
            return $permission;
        }

        $user = (new User())->find($id);

        if (!$user) {
            return redirect()
                ->to('/dashboard/users')
                ->with('error', 'User tidak ditemukan.');
        }

        if ($this->request->isAJAX()) {
            return $this->response->setBody(
                view('partials/forms/user_role', $this->roleFormData($user))
            );
        }

        return redirect()->to('/dashboard/users');
    }

    /**
     * Berikan atau cabut role SUPER_ADMIN.
     */
    public function assignRole($id)
    {
        $permission = $this->requireRolePermission('user.assign_role');

        if ($permission !== null) {
            return $permission;
        }

        $user = (new User())->find($id);

        if (!$user) {
            return $this->roleFailed(null, 'User tidak ditemukan.');
        }

        $roleService = new RoleService();
        $role        = $roleService->findByCode(self::ROLE_SUPER_ADMIN);

        if (! $role) {
            return $this->roleFailed($user, 'Role SUPER_ADMIN belum tersedia di database.');
        }

        $action = (string) $this->request->getPost('role_action');

        if (! in_array($action, ['grant', 'revoke'], true)) {
            return $this->roleFailed($user, 'Aksi tidak dikenal.');
        }

        $hasRole = $roleService->hasRole($user['id'], self::ROLE_SUPER_ADMIN);

        if ($action === 'grant') {
            return $this->grantRole($user, $role, $roleService, $hasRole);
        }

        return $this->revokeRole($user, $role, $roleService, $hasRole);
    }

    private function grantRole(array $user, array $role, RoleService $roleService, bool $hasRole)
    {
        // Akun nonaktif tidak boleh memegang role yang hidup: kalau diaktifkan
        // lagi, ia langsung punya akses penuh tanpa ada yang pernah menyetujui.
        if ($user['status'] !== 'ACTIVE') {
            return $this->roleFailed(
                $user,
                'User berstatus INACTIVE tidak dapat diberi role. Aktifkan user dulu.'
            );
        }

        if ($hasRole) {
            return redirect()
                ->to('/dashboard/users')
                ->with('success', $user['username'] . ' sudah menjadi Super Admin.');
        }

        $roleService->assignToUser($user['id'], (int) $role['id']);

        $this->auditRole('ROLE_GRANTED', $user);

        return redirect()
            ->to('/dashboard/users')
            ->with('success', $user['username'] . ' sekarang menjadi Super Admin.');
    }

    private function revokeRole(array $user, array $role, RoleService $roleService, bool $hasRole)
    {
        $actorId = session()->get('user_id');

        // Mencabut role sendiri berarti mencabut hak akses dari orang yang
        // sedang menjalankan aksi ini.
        if ((string) $user['id'] === (string) $actorId) {
            return $this->roleFailed(
                $user,
                'Anda tidak dapat mencabut role Super Admin milik sendiri.'
            );
        }

        if (! $hasRole) {
            return redirect()
                ->to('/dashboard/users')
                ->with('success', $user['username'] . ' bukan Super Admin.');
        }

        // Pengaman terakhir: jangan sampai tidak ada Super Admin sama sekali.
        if ($roleService->countUsersWithRole(self::ROLE_SUPER_ADMIN) <= 1) {
            return $this->roleFailed(
                $user,
                'Tidak dapat dicabut: ini Super Admin terakhir yang tersisa.'
            );
        }

        $roleService->revokeFromUser($user['id'], (int) $role['id']);

        $this->auditRole('ROLE_REVOKED', $user);

        return redirect()
            ->to('/dashboard/users')
            ->with('success', 'Role Super Admin dari ' . $user['username'] . ' dicabut.');
    }

    /**
     * Catat perubahan role ke audit log. user_id diisi dengan pelaku aksi,
     * target-nya disimpan di metadata.
     */
    private function auditRole(string $event, array $user): void
    {
        (new AuditService())->record(
            $event,
            session()->get('user_id'),
            null,
            [
                'role'         => self::ROLE_SUPER_ADMIN,
                'target_user'  => $user['id'],
                'target_name'  => $user['username'],
            ]
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function roleFormData(array $user): array
    {
        return [
            'user'         => $user,
            'isSuperAdmin' => (new RoleService())->hasRole($user['id'], self::ROLE_SUPER_ADMIN),
            'error'        => null,
        ];
    }

    /**
     * Kegagalan saat mengubah role: balas 422 + form supaya front-end
     * menukar isi modal tanpa reload halaman.
     *
     * $user dipakai ulang kalau target tidak ada, supaya form tetap bisa
     * ditampilkan (dengan penanda bahwa datanya hilang).
     */
    private function roleFailed(?array $user, string $message)
    {
        if ($this->request->isAJAX()) {
            // Target hilang: form tetap dikirim supaya tombol submit tidak
            // menggantung, tapi tanpa action yang berarti.
            $data = $user !== null
                ? $this->roleFormData($user)
                : [
                    'user'         => [
                        'id'        => '',
                        'username'  => '(user tidak ditemukan)',
                        'full_name' => '',
                        'status'    => 'INACTIVE',
                    ],
                    'isSuperAdmin' => false,
                    'error'        => null,
                ];

            $data['error'] = $message;

            return $this->response
                ->setStatusCode(422)
                ->setBody(view('partials/forms/user_role', $data));
        }

        return redirect()
            ->back()
            ->withInput()
            ->with('error', $message);
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

        // Diminta dari modal: kirim form-nya saja, tanpa chrome halaman.
        if ($this->request->isAJAX()) {
            return $this->response->setBody(
                view('partials/forms/user_password', [
                    'user'   => $user,
                    'errors' => session()->getFlashdata('errors'),
                ])
            );
        }

        return view('users/reset_password', [
            'title' => 'Reset Password',
            'user'  => $user,
        ]);
    }

    /**
     * Kirim ulang form reset password dengan status 422 supaya front-end
     * menukar isi modal tanpa reload halaman.
     */
    private function resetPasswordForm($user, array $errors = [], ?string $error = null)
    {
        if ($errors === [] && $error !== null) {
            $errors = [$error];
        }

        return $this->response
            ->setStatusCode(422)
            ->setBody(view('partials/forms/user_password', [
                'user'   => $user,
                'errors' => $errors,
            ]));
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

        $rules = [
            'password'              => 'required|min_length[8]',
            'password_confirmation' => 'required|matches[password]',
        ];

        if (! $this->validate($rules)) {
            $errors = $this->validator->getErrors();

            if ($this->request->isAJAX()) {
                return $this->resetPasswordForm($user, $errors);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $errors);
        }

        $credentialModel = new UserCredential();

        $credential = $credentialModel
            ->where('user_id', $id)
            ->first();

        if (! $credential) {
            if ($this->request->isAJAX()) {
                return $this->resetPasswordForm($user, [], 'Credential user tidak ditemukan.');
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Credential user tidak ditemukan.');
        }

        $credentialModel->update($credential['id'], [
            'password_hash' => password_hash(
                $this->request->getPost('password'),
                PASSWORD_DEFAULT
            ),
        ]);

        return redirect()
            ->to('/dashboard/users')
            ->with('success', 'Password user berhasil diperbarui.');
    }
}
