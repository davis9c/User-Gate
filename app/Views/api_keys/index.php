<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>
        <h1 class="h3 mb-1">API Keys</h1>

        <p class="text-muted mb-0">
            <?= esc($application['name']) ?>
        </p>
    </div>

    <a
        href="<?= site_url('dashboard/applications/' . $application['id'] . '/api-keys/create') ?>"
        class="btn btn-primary">
        Create API Key
    </a>

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

        <div class="table-responsive">

            <table class="table table-hover mb-0 align-middle">

                <thead class="table-light">

                    <tr>
                        <th>Name</th>
                        <th>Key</th>
                        <th>Status</th>
                        <th>Last Used</th>
                        <th>Action</th>
                    </tr>

                </thead>

                <tbody>

                    <?php if (empty($apiKeys)): ?>

                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                Belum ada API Key.
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($apiKeys as $apiKey): ?>

                            <tr>

                                <td>
                                    <strong>
                                        <?= esc($apiKey['name']) ?>
                                    </strong>
                                </td>

                                <td>
                                    <code>
                                        <?= esc($apiKey['key_prefix']) ?>...
                                    </code>
                                </td>

                                <td>

                                    <?php if ($apiKey['status'] === 'ACTIVE'): ?>

                                        <span class="badge text-bg-success">
                                            ACTIVE
                                        </span>

                                    <?php else: ?>

                                        <span class="badge text-bg-secondary">
                                            INACTIVE
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td>
                                    <?= esc($apiKey['last_used_at'] ?? '-') ?>
                                </td>

                                <td>

                                    <form
                                        method="post"
                                        action="<?= site_url('dashboard/api-keys/toggle/' . $apiKey['id']) ?>"
                                        class="d-inline">

                                        <?= csrf_field() ?>

                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-outline-warning">
                                            Toggle Status
                                        </button>

                                    </form>
                                    <a
                                        href="<?= site_url('dashboard/api-keys/' . $apiKey['id'] . '/permissions') ?>"
                                        class="btn btn-sm btn-outline-primary">
                                        Permissions
                                    </a>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<?= $this->endSection() ?>