<?php

namespace App\Controllers;

use App\Models\ApiKey;
use App\Models\Application;

class ApiKeys extends BaseController
{
    public function index($applicationId)
    {
        $permission = $this->requireRolePermission('api_key.read');

        if ($permission !== null) {
            return $permission;
        }
        $applicationModel = new Application();
        $apiKeyModel      = new ApiKey();

        $application = $applicationModel->find($applicationId);

        if (!$application) {
            return redirect()
                ->to('/dashboard/applications')
                ->with('error', 'Application tidak ditemukan.');
        }

        return view('api_keys/index', [
            'title'       => 'API Keys',
            'application' => $application,
            'apiKeys'     => $apiKeyModel
                ->where('application_id', $applicationId)
                ->orderBy('created_at', 'DESC')
                ->findAll(),
        ]);
    }

    public function create($applicationId)
    {
        $permission = $this->requireRolePermission('api_key.create');

        if ($permission !== null) {
            return $permission;
        }
        $applicationModel = new Application();

        $application = $applicationModel->find($applicationId);

        if (!$application) {
            return redirect()
                ->to('/dashboard/applications')
                ->with('error', 'Application tidak ditemukan.');
        }

        return view('api_keys/create', [
            'title'       => 'Create API Key',
            'application' => $application,
        ]);
    }

    public function store($applicationId)
    {
        $permission = $this->requireRolePermission('api_key.create');

        if ($permission !== null) {
            return $permission;
        }
        $applicationModel = new Application();

        $application = $applicationModel->find($applicationId);

        if (!$application) {
            return redirect()
                ->to('/dashboard/applications')
                ->with('error', 'Application tidak ditemukan.');
        }

        $rules = [
            'name' => 'required|min_length[3]|max_length[150]',
        ];

        if (!$this->validate($rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        /*
         * API Key asli.
         *
         * Contoh:
         * ugk_live_xxxxxxxxxxxxxxxxxxxxxxxxx
         */
        $plainKey = 'ugk_live_' . bin2hex(random_bytes(32));

        $prefix = substr($plainKey, 0, 16);

        $model = new ApiKey();

        $model->insert([
            'id'             => $this->generateUuid(),
            'application_id' => $applicationId,
            'name'           => trim($this->request->getPost('name')),
            'key_hash'       => hash('sha256', $plainKey),
            'key_prefix'     => $prefix,
            'status'         => 'ACTIVE',
        ]);

        return view('api_keys/show_key', [
            'title'       => 'API Key Created',
            'application' => $application,
            'apiKey'      => $plainKey,
        ]);
    }

    public function toggle($id)
    {
        $permission = $this->requireRolePermission('api_key.update');

        if ($permission !== null) {
            return $permission;
        }
        $model = new ApiKey();

        $apiKey = $model->find($id);

        if (!$apiKey) {
            return redirect()
                ->back()
                ->with('error', 'API Key tidak ditemukan.');
        }

        $newStatus = $apiKey['status'] === 'ACTIVE'
            ? 'INACTIVE'
            : 'ACTIVE';

        $model->update($id, [
            'status' => $newStatus,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Status API Key berhasil diperbarui.');
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
