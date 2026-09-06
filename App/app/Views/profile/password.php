<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<div class="mb-4">

    <h1 class="h3 mb-1">Change Password</h1>

    <p class="text-muted mb-0">
        Ubah password akun Anda.
    </p>

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

<div class="row">

    <div class="col-lg-8">

        <div class="card shadow-sm">

            <div class="card-header">
                <strong>Change Password</strong>
            </div>

            <div class="card-body">

                <form
                    method="post"
                    action="<?= site_url('profile/password') ?>"
                >

                    <?= csrf_field() ?>

                    <div class="mb-3">

                        <label
                            for="current_password"
                            class="form-label"
                        >
                            Password Lama
                        </label>

                        <input
                            type="password"
                            class="form-control"
                            id="current_password"
                            name="current_password"
                            required
                        >

                    </div>

                    <div class="mb-3">

                        <label
                            for="password"
                            class="form-label"
                        >
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

                        <label
                            for="password_confirmation"
                            class="form-label"
                        >
                            Konfirmasi Password Baru
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

                    <div class="d-flex gap-2">

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Change Password
                        </button>

                        <a
                            href="<?= site_url('profile') ?>"
                            class="btn btn-outline-secondary"
                        >
                            Cancel
                        </a>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

<?= $this->endSection() ?>
