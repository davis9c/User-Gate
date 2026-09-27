<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<div class="mb-4">

    <h1 class="h3 mb-1">Edit User</h1>

    <p class="text-muted mb-0">
        Update UserGateway user information.
    </p>

</div>

<?php if (session()->getFlashdata('error')): ?>

    <div class="alert alert-danger">
        <?= esc(session()->getFlashdata('error')) ?>
    </div>

<?php endif; ?>

<div class="card shadow-sm">

    <div class="card-body p-0">

        <?= view('partials/forms/user', [
            'mode'   => 'edit',
            'user'   => $user,
            'errors' => session()->getFlashdata('errors'),
        ]) ?>

        <div class="card-footer bg-transparent d-flex gap-2">
            <button type="submit" form="fUserEdit" class="btn btn-primary">
                Save Changes
            </button>

            <a
                href="<?= site_url('dashboard/users') ?>"
                class="btn btn-outline-secondary">
                Cancel
            </a>
        </div>

    </div>

</div>

<?= $this->endSection() ?>
