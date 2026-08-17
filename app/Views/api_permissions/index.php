<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<div class="mb-4">

    <h1 class="h3 mb-1">
        API Permissions
    </h1>

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

    <div class="card-header">
        <strong>Allowed Permissions</strong>
    </div>

    <div class="card-body">

        <form
            method="post"
            action="<?= site_url('dashboard/api-keys/' . $apiKey['id'] . '/permissions') ?>">

            <?= csrf_field() ?>

            <?php if (empty($permissions)): ?>

                <div class="alert alert-warning">
                    Belum ada permission yang tersedia.
                </div>

            <?php else: ?>

                <?php foreach ($permissions as $permission): ?>

                    <div class="form-check mb-3">

                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="permissions[]"
                            value="<?= esc($permission['id']) ?>"
                            id="permission_<?= esc($permission['id']) ?>"
                            <?= in_array($permission['id'], $assignedIds) ? 'checked' : '' ?>>

                        <label
                            class="form-check-label"
                            for="permission_<?= esc($permission['id']) ?>">

                            <strong>
                                <?= esc($permission['code']) ?>
                            </strong>

                            <?php if (!empty($permission['description'])): ?>

                                <span class="text-muted">
                                    — <?= esc($permission['description']) ?>
                                </span>

                            <?php endif; ?>

                        </label>

                    </div>

                <?php endforeach; ?>

            <?php endif; ?>

            <button
                type="submit"
                class="btn btn-primary">
                Save Permissions
            </button>

        </form>

    </div>

</div>

<?= $this->endSection() ?>