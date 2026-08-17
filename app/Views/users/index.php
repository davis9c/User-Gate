<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php if (session()->getFlashdata('success')): ?>

    <div class="alert alert-success">
        <?= esc(session()->getFlashdata('success')) ?>
    </div>

<?php endif; ?>
<div class="d-flex justify-content-between align-items-center mb-4">

    <div>
        <h1 class="h3 mb-1">Users</h1>

        <p class="text-muted mb-0">
            Manage UserGateway users.
        </p>
    </div>

    <a
        href="<?= site_url('dashboard/users/create') ?>"
        class="btn btn-primary">
        Create User
    </a>

</div>

<div class="card shadow-sm">

    <div class="card-body p-0">

        <div class="table-responsive">

            <table class="table table-hover mb-0 align-middle">

                <thead class="table-light">
                    <tr>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Dibuat</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (empty($users)): ?>

                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                Belum ada user.
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($users as $user): ?>

                            <tr>

                                <td>
                                    <?= esc($user['full_name']) ?>
                                </td>

                                <td>
                                    <?= esc($user['username']) ?>
                                </td>

                                <td>
                                    <?= esc($user['email']) ?>
                                </td>

                                <td>

                                    <?php if ($user['status'] === 'ACTIVE'): ?>

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
                                    <?= esc($user['created_at']) ?>
                                </td>

                                <td>
                                    <div class="d-flex gap-1">

                                        <a
                                            href="<?= site_url('dashboard/users/edit/' . $user['id']) ?>"
                                            class="btn btn-sm btn-outline-primary">
                                            Edit
                                        </a>

                                        <a
                                            href="<?= site_url('dashboard/users/reset-password/' . $user['id']) ?>"
                                            class="btn btn-sm btn-outline-warning">
                                            Reset Password
                                        </a>

                                    </div>
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