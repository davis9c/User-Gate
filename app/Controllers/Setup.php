<?php

namespace App\Controllers;

use App\Models\SystemSetting;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use App\Models\UserCredential;

class Setup extends BaseController
{
    public function index()
    {
        if ($this->isInstalled()) {
            return redirect()->to('/');
        }

        return view('setup/index');
    }

    public function install()
    {
        if ($this->isInstalled()) {
            return redirect()->to('/');
        }

        $rules = [
            'full_name' => 'required|min_length[3]|max_length[150]',
            'username'  => 'required|min_length[3]|max_length[100]|alpha_numeric',
            'email'     => 'required|valid_email|max_length[255]',
            'password'  => 'required|min_length[8]',
            'password_confirmation' => 'required|matches[password]',
        ];

        if (!$this->validate($rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $db = db_connect();

        $userModel       = new User();
        $credentialModel = new UserCredential();
        $roleModel       = new UserRole();
        $adminRole       = (new Role())->where('code', 'SUPER_ADMIN')->first();
        $settingModel    = new SystemSetting();

        if (!$adminRole) {
            return redirect()->back()->withInput()->with('error', 'Role SUPER_ADMIN belum tersedia. Jalankan php spark migrate dan php spark db:seed terlebih dahulu.');
        }

        $userId = sprintf(
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
        $db->transStart();

        $userModel->insert([
            'id'        => $userId,
            'username'  => $this->request->getPost('username'),
            'email'     => $this->request->getPost('email'),
            'full_name' => $this->request->getPost('full_name'),
            'status'    => 'ACTIVE',
        ]);

        $credentialModel->insert([
            'user_id'       => $userId,
            'password_hash' => password_hash(
                $this->request->getPost('password'),
                PASSWORD_DEFAULT
            ),
        ]);

        $roleModel->insert([
            'user_id' => $userId,
            'role_id' => $adminRole['id'],
        ]);

        $settingModel->insert([
            'key'   => 'installation_status',
            'value' => 'installed',
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Installation gagal. Silakan coba lagi.');
        }

        return redirect()
            ->to('/')
            ->with('success', 'UserGateway berhasil di-install.');
    }

    private function isInstalled(): bool
    {
        try {
            return (new SystemSetting())
                ->where('key', 'installation_status')
                ->where('value', 'installed')
                ->first() !== null;
        } catch (\Throwable $exception) {
            return false;
        }
    }
}
