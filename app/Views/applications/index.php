<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>
        <h1 class="h3 mb-1">Applications</h1>
        <p class="text-muted mb-0">
            Manage applications connected to UserGateway.
        </p>
    </div>

    <a
        href="<?= site_url('dashboard/applications/create') ?>"
        class="btn btn-primary">
        Create Application
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
                        <th>Application</th>
                        <th>Code</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (empty($applications)): ?>

                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                Belum ada application.
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($applications as $application): ?>

                            <tr>

                                <td>
                                    <strong>
                                        <?= esc($application['name']) ?>
                                    </strong>
                                </td>

                                <td>
                                    <code>
                                        <?= esc($application['code']) ?>
                                    </code>
                                </td>

                                <td>
                                    <?= esc($application['description'] ?? '-') ?>
                                </td>

                                <td>

                                    <?php if ($application['status'] === 'ACTIVE'): ?>

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
                                    <a
                                        href="<?= site_url('dashboard/applications/' . $application['id'] . '/api-keys') ?>"
                                        class="btn btn-sm btn-outline-secondary">
                                        API Keys
                                    </a>
                                    <a
                                        href="<?= site_url('dashboard/applications/edit/' . $application['id']) ?>"
                                        class="btn btn-sm btn-outline-primary">
                                        Edit
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