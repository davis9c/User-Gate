<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<div class="mb-4">

    <h1 class="h3 mb-1">Create User</h1>

    <p class="text-muted mb-0">
        Create a new UserGateway user.
    </p>

</div>

<div class="card shadow-sm">

    <div class="card-body p-0">

        <?= view('partials/forms/user', [
            'mode'   => 'create',
            'errors' => session()->getFlashdata('errors'),
        ]) ?>

        <div class="card-footer bg-transparent d-flex gap-2">
            <button type="submit" form="fUserCreate" class="btn btn-primary">
                Create User
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
