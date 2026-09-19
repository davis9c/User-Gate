<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>
        <h1 class="h3 mb-1">Reset Password</h1>

        <p class="text-muted mb-0">
            Reset password user.
        </p>
    </div>

    <a
        href="<?= site_url('dashboard/users') ?>"
        class="btn btn-outline-secondary"
    >
        Back
    </a>

</div>

<div class="alert alert-warning">
    Anda akan mengganti password user:
    <strong><?= esc($user['username']) ?></strong>
</div>

<?php if (session()->getFlashdata('error')): ?>

    <div class="alert alert-danger">
        <?= esc(session()->getFlashdata('error')) ?>
    </div>

<?php endif; ?>

<?php if (session()->getFlashdata('errors')): ?>

    <div class="alert alert-danger">

        <ul class="mb-0">

            <?php foreach (session()->getFlashdata('errors') as $error): ?>

                <li><?= esc($error) ?></li>

            <?php endforeach; ?>

        </ul>

    </div>

<?php endif; ?>

<div class="card shadow-sm">

    <div class="card-body">

        <form
            method="post"
            action="<?= site_url('dashboard/users/reset-password/' . $user['id']) ?>"
        >

            <?= csrf_field() ?>

            <div class="mb-3">

                <label for="password" class="form-label">
                    Password Baru
                </label>

                <input
                    type="password"
                    class="form-control"
                    id="password"
                    name="password"
                    minlength="8"
                    required
                >

                <div class="form-text">
                    Minimal 8 karakter.
                </div>

            </div>

            <div class="mb-4">

                <label for="password_confirmation" class="form-label">
                    Konfirmasi Password
                </label>

                <input
                    type="password"
                    class="form-control"
                    id="password_confirmation"
                    name="password_confirmation"
                    minlength="8"
                    required
                >

            </div>

            <button
                type="submit"
                class="btn btn-warning"
            >
                Reset Password
            </button>

        </form>

    </div>

</div>

<?= $this->endSection() ?>
