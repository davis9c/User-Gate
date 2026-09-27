<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<div class="mb-4">

    <h1 class="h3 mb-1">API Permissions</h1>

    <p class="text-muted mb-0">
        API Key: <code><?= esc($apiKey['key_prefix']) ?>...</code>
    </p>

</div>

<?php if (session()->getFlashdata('success')): ?>

    <div class="alert alert-success">
        <?= esc(session()->getFlashdata('success')) ?>
    </div>

<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>

    <div class="alert alert-danger">
        <?= esc(session()->getFlashdata('error')) ?>
    </div>

<?php endif; ?>

<div class="card shadow-sm">

    <div class="card-body p-0">

        <?= view('partials/forms/api_permissions', [
            'apiKey'      => $apiKey,
            'permissions' => $permissions,
            'assignedIds' => $assignedIds,
        ]) ?>

        <div class="card-footer bg-transparent d-flex gap-2">
            <button type="submit" form="fApiPermissions" class="btn btn-primary">
                Save Permissions
            </button>

            <a
                href="<?= site_url('dashboard/applications/' . $application['id'] . '/api-keys') ?>"
                class="btn btn-outline-secondary">
                Cancel
            </a>
        </div>

    </div>

</div>

<?= $this->endSection() ?>
