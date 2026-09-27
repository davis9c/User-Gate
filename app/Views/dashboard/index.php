<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<?php

$cards = [
    [
        'label'  => 'Total Users',
        'value'  => $stats['users']['total'],
        'active' => $stats['users']['active'],
        'inactive' => $stats['users']['inactive'],
        'url'    => site_url('dashboard/users'),
        'label_url' => 'Manage Users',
    ],
    [
        'label'  => 'Total Applications',
        'value'  => $stats['applications']['total'],
        'active' => $stats['applications']['active'],
        'inactive' => $stats['applications']['inactive'],
        'url'    => site_url('dashboard/applications'),
        'label_url' => 'Manage Applications',
    ],
    [
        'label'  => 'Total API Keys',
        'value'  => $stats['apiKeys']['total'],
        'active' => $stats['apiKeys']['active'],
        'inactive' => $stats['apiKeys']['inactive'],
        'url'    => site_url('dashboard/applications'),
        'label_url' => 'Manage API Keys',
    ],
];

?>

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>
        <h1 class="h3 mb-1">Dashboard</h1>

        <p class="text-muted mb-0">
            Selamat datang, <?= esc(session()->get('full_name')) ?>.
        </p>
    </div>

    <a
        href="https://github.com/davis9c/User-Gate/tree/main/docs"
        target="_blank"
        class="btn btn-outline-secondary btn-sm">
        API Documentation
    </a>

</div>

<div class="row g-3 mb-4">

    <?php foreach ($cards as $card): ?>

        <div class="col-12 col-md-4">

            <div class="card shadow-sm h-100">

                <div class="card-body">

                    <p class="text-muted text-uppercase small mb-1">
                        <?= esc($card['label']) ?>
                    </p>

                    <p class="display-5 fw-semibold mb-2">
                        <?= number_format($card['value'], 0, ',', '.') ?>
                    </p>

                    <p class="small text-muted mb-3">
                        <span class="text-success fw-semibold">
                            <?= number_format($card['active'], 0, ',', '.') ?> active
                        </span>
                        ·
                        <span class="text-secondary">
                            <?= number_format($card['inactive'], 0, ',', '.') ?> inactive
                        </span>
                    </p>

                    <a
                        href="<?= esc($card['url']) ?>"
                        class="btn btn-sm btn-outline-primary">
                        <?= esc($card['label_url']) ?>
                    </a>

                </div>

            </div>

        </div>

    <?php endforeach; ?>

</div>

<?= $this->endSection() ?>
