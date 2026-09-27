<?php

namespace App\Controllers;

use App\Models\ApiPermission;
use App\Models\ApiKey;
use App\Models\Application;

class ApiPermissions extends BaseController
{
    public function index($apiKeyId)
    {
        $permission = $this->requireRolePermission('api_permission.read');

        if ($permission !== null) {
            return $permission;
        }
        $apiKeyModel = new ApiKey();
        $permissionModel = new ApiPermission();

        $apiKey = $apiKeyModel->find($apiKeyId);

        if (!$apiKey) {
            return redirect()
                ->to('/dashboard/applications')
                ->with('error', 'API Key tidak ditemukan.');
        }

        $permissions = $permissionModel
            ->orderBy('code', 'ASC')
            ->findAll();

        $db = db_connect();

        $assigned = $db
            ->table('api_key_permissions')
            ->where('api_key_id', $apiKeyId)
            ->get()
            ->getResultArray();

        $assignedIds = array_column($assigned, 'permission_id');

        // Dipakai untuk link "Cancel" kembali ke daftar API Keys.
        $application = (new Application())->find($apiKey['application_id']);

        // Diminta dari modal: kirim form-nya saja, tanpa chrome halaman.
        if ($this->request->isAJAX()) {
            return $this->response->setBody(
                view('partials/forms/api_permissions', [
                    'apiKey'      => $apiKey,
                    'permissions' => $permissions,
                    'assignedIds' => $assignedIds,
                ])
            );
        }

        return view('api_permissions/index', [
            'title'       => 'API Permissions',
            'apiKey'      => $apiKey,
            'application' => $application,
            'permissions' => $permissions,
            'assignedIds' => $assignedIds,
        ]);
    }

    public function update($apiKeyId)
    {
        $permission = $this->requireRolePermission('api_permission.update');

        if ($permission !== null) {
            return $permission;
        }
        $apiKeyModel = new ApiKey();

        $apiKey = $apiKeyModel->find($apiKeyId);

        if (!$apiKey) {
            return redirect()
                ->to('/dashboard/applications')
                ->with('error', 'API Key tidak ditemukan.');
        }

        $permissionIds = $this->request->getPost('permissions') ?? [];

        $db = db_connect();

        $db->transStart();

        $db->table('api_key_permissions')
            ->where('api_key_id', $apiKeyId)
            ->delete();

        foreach ($permissionIds as $permissionId) {

            $db->table('api_key_permissions')->insert([
                'api_key_id'    => $apiKeyId,
                'permission_id' => (int) $permissionId,
            ]);
        }

        $db->transComplete();

        if (!$db->transStatus()) {
            return redirect()
                ->back()
                ->with('error', 'Permission gagal diperbarui.');
        }

        return redirect()
            ->back()
            ->with('success', 'Permission API Key berhasil diperbarui.');
    }
}
