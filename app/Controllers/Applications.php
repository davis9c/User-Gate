<?php

namespace App\Controllers;

use App\Libraries\DataTableServer;
use App\Models\Application;

class Applications extends BaseController
{
    public function index()
    {
        $permission = $this->requireRolePermission('application.read');

        if ($permission !== null) {
            return $permission;
        }

        // Baris tabel diambil lewat endpoint server-side (lihat data()).
        return view('applications/index', ['title' => 'Applications']);
    }

    /**
     * Endpoint server-side untuk tabel Applications.
     */
    public function data()
    {
        $permission = $this->requireRolePermission('application.read');

        if ($permission !== null) {
            return $permission;
        }

        $table = new DataTableServer($this->request, $this->response, new Application(), [
            'columns'      => ['name', 'code', 'description', 'status'],
            'needed'       => ['id'],
            'searchable'   => [0, 1, 2, 3],
            'orderable'    => [0, 1, 2, 3],
            'defaultOrder' => [['column' => 'created_at', 'dir' => 'DESC']],
            'map'          => static fn (array $row): array => [
                'name'        => $row['name'],
                'code'        => $row['code'],
                'description' => $row['description'] ?? '',
                'status'      => $row['status'],
                'id'          => $row['id'],
            ],
        ]);

        return $table->respond();
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

    /**
     * Kegagalan create/update application: balas 422 + form untuk AJAX,
     * atau redirect seperti sebelumnya untuk request biasa.
     */
    private function failed(string $mode, $application, string $message, array $errors = [])
    {
        if ($this->request->isAJAX()) {
            return $this->response
                ->setStatusCode(422)
                ->setBody(view('partials/forms/application', [
                    'mode'        => $mode,
                    'application' => $application,
                    'errors'      => $errors === [] ? [$message] : $errors,
                ]));
        }

        $redirect = redirect()
            ->back()
            ->withInput();

        return $errors === []
            ? $redirect->with('error', $message)
            : $redirect->with('errors', $errors);
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
            return $this->failed(
                'create',
                null,
                '',
                $this->validator->getErrors()
            );
        }

        $model = new Application();

        $code = trim($this->request->getPost('code'));

        if ($model->where('code', $code)->first()) {
            return $this->failed('create', null, 'Application code sudah digunakan.');
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

        // Diminta dari modal: kirim form-nya saja, tanpa chrome halaman.
        if ($this->request->isAJAX()) {
            return $this->response->setBody(
                view('partials/forms/application', [
                    'mode'        => 'edit',
                    'application' => $application,
                    'errors'      => session()->getFlashdata('errors'),
                ])
            );
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
            return $this->failed(
                'edit',
                $application,
                '',
                $this->validator->getErrors()
            );
        }

        $code = trim($this->request->getPost('code'));

        $existing = $model
            ->where('code', $code)
            ->where('id !=', $id)
            ->first();

        if ($existing) {
            return $this->failed('edit', $application, 'Application code sudah digunakan.');
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
