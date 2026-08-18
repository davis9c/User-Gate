<?php

namespace App\Controllers;

use App\Models\SystemSetting;
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

        try {
            $db = $this->bootstrapDatabase();
        } catch (\Throwable $exception) {
            log_message('error', '[Setup] Database bootstrap failed: ' . $exception->getMessage());

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Database belum siap. Periksa konfigurasi database dan privilege CREATE, ALTER, INDEX, INSERT, UPDATE, DELETE, SELECT, serta REFERENCES untuk user database.');
        }

        $userModel       = new User();
        $credentialModel = new UserCredential();
        $roleModel       = new UserRole();
        $settingModel    = new SystemSetting();

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
            'role'    => 'SUPER_ADMIN',
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
            // A fresh instance has no database or tables yet; Setup will create them on submit.
            return false;
        }
    }

    /**
     * Creates the configured MySQL database when needed, applies all migrations,
     * and seeds the baseline API permissions. No Spark command is required.
     */
    private function bootstrapDatabase()
    {
        $config = config('Database')->default;

        if (($config['DBDriver'] ?? '') !== 'MySQLi') {
            throw new \RuntimeException('Setup otomatis hanya mendukung MySQLi.');
        }

        $database = (string) ($config['database'] ?? '');
        if (!preg_match('/^[A-Za-z0-9_]+$/', $database)) {
            throw new \RuntimeException('Nama database harus menggunakan huruf, angka, atau underscore.');
        }

        $connection = new \mysqli(
            (string) ($config['hostname'] ?? 'localhost'),
            (string) ($config['username'] ?? ''),
            (string) ($config['password'] ?? ''),
            '',
            (int) ($config['port'] ?? 3306)
        );

        $connection->set_charset('utf8mb4');
        $existing = $connection->query(
            "SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = '" .
            $connection->real_escape_string($database) . "'"
        );

        if ($existing === false || $existing->num_rows === 0) {
            $connection->query(
                'CREATE DATABASE `' . $database . '` ' .
                'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
            );
        }
        if ($existing instanceof \mysqli_result) {
            $existing->free();
        }
        $connection->close();
        $connection->close();

        $db = db_connect();
        service('migrations')->latest();

        if ($db->table('api_permissions')->countAllResults() === 0) {
            \Config\Database::seeder()->call('ApiPermissionSeeder');
        }

        return $db;
    }
}
