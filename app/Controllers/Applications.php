<?php

namespace App\Controllers;

use App\Models\Application;

class Applications extends BaseController
{
    public function index()
    {
        $permission = $this->requireRolePermission('application.read');

        if ($permission !== null) {
            return $permission;
        }
        $model = new Application();

        return view('applications/index', [
            'title' => 'Applications',
            'applications' => $model
                ->orderBy('created_at', 'DESC')
                ->findAll(),
        ]);
    }

    public function new()
    {
        $permission = $this->requireRolePermission('application.create');

        if ($permission !== null) {
            return $permission;
        }
        return view('applications/create', [
            'title' => 'Create Application',
        ]);
    }

    public function create()
    {
        $permission = $this->requireRolePermission('application.create');

        if ($permission !== null) {
            return $permission;
        }
        $rules = [
            'name' => 'required|min_length[3]|max_length[150]',
            'code' => 'required|min_length[2]|max_length[100]|alpha_numeric_punct',
            'description' => 'permit_empty|max_length[1000]',
            'status' => 'required|in_list[ACTIVE,INACTIVE]',
        ];

        if (!$this->validate($rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $model = new Application();

        $code = trim($this->request->getPost('code'));

        if ($model->where('code', $code)->first()) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Application code sudah digunakan.');
        }

        $model->insert([
            'id' => $this->generateUuid(),
            'name' => trim($this->request->getPost('name')),
            'code' => $code,
            'description' => trim($this->request->getPost('description')),
            'status' => $this->request->getPost('status'),
        ]);

        return redirect()
            ->to('/dashboard/applications')
            ->with('success', 'Application berhasil dibuat.');
    }

    public function edit($id)
    {
        $permission = $this->requireRolePermission('application.update');

        if ($permission !== null) {
            return $permission;
        }
        $model = new Application();

        $application = $model->find($id);

        if (!$application) {
            return redirect()
                ->to('/dashboard/applications')
                ->with('error', 'Application tidak ditemukan.');
        }

        return view('applications/edit', [
            'title' => 'Edit Application',
            'application' => $application,
        ]);
    }

    public function update($id)
    {
        $permission = $this->requireRolePermission('application.update');

        if ($permission !== null) {
            return $permission;
        }
        $model = new Application();

        $application = $model->find($id);

        if (!$application) {
            return redirect()
                ->to('/dashboard/applications')
                ->with('error', 'Application tidak ditemukan.');
        }

        $rules = [
            'name' => 'required|min_length[3]|max_length[150]',
            'code' => 'required|min_length[2]|max_length[100]|alpha_numeric_punct',
            'description' => 'permit_empty|max_length[1000]',
            'status' => 'required|in_list[ACTIVE,INACTIVE]',
        ];

        if (!$this->validate($rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $code = trim($this->request->getPost('code'));

        $existing = $model
            ->where('code', $code)
            ->where('id !=', $id)
            ->first();

        if ($existing) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Application code sudah digunakan.');
        }

        $model->update($id, [
            'name' => trim($this->request->getPost('name')),
            'code' => $code,
            'description' => trim($this->request->getPost('description')),
            'status' => $this->request->getPost('status'),
        ]);

        return redirect()
            ->to('/dashboard/applications')
            ->with('success', 'Application berhasil diperbarui.');
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
