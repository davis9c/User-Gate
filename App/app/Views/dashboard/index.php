<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<div class="mb-4">
    <h1 class="h3">Dashboard</h1>

    <p class="text-muted">
        Selamat datang, <?= esc(session()->get('full_name')) ?>.
    </p>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <h5 class="card-title">UserGateway</h5>

        <p class="card-text">
            Central Identity & Authentication Platform.
        </p>

        <a
            href="<?= site_url('dashboard/users') ?>"
            class="btn btn-primary">
            Manage Users
        </a>
        <a
            href="<?= site_url('dashboard/applications') ?>"
            class="btn btn-primary">
            Manage Applications
        </a>
        <a
            href="<?= site_url('/api-documentation') ?>"
            class="btn btn-primary">
            API Documentation
        </a>
    </div>
</div>

<?= $this->endSection() ?>