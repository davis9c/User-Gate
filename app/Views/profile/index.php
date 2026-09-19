<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<div class="mb-4">

    <h1 class="h3 mb-1">My Profile</h1>

    <p class="text-muted mb-0">
        Kelola informasi identitas Anda.
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
                <strong>Profile Information</strong>
            </div>

            <div class="card-body">

                <form
                    method="post"
                    action="<?= site_url('profile/update') ?>">

                    <?= csrf_field() ?>

                    <div class="mb-3">

                        <label for="full_name" class="form-label">
                            Nama Lengkap
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="full_name"
                            name="full_name"
                            value="<?= old('full_name', $user['full_name']) ?>"
                            required>

                    </div>

                    <div class="mb-3">

                        <label for="username" class="form-label">
                            Username
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="username"
                            name="username"
                            value="<?= old('username', $user['username']) ?>"
                            required>

                    </div>

                    <div class="mb-4">

                        <label for="email" class="form-label">
                            Email
                        </label>

                        <input
                            type="email"
                            class="form-control"
                            id="email"
                            name="email"
                            value="<?= old('email', $user['email']) ?>"
                            required>

                    </div>

                    <button
                        type="submit"
                        class="btn btn-primary">
                        Save Changes
                    </button>

                </form>

            </div>

        </div>
        <div class="row mt-4">

            <div class="col-lg-8">

                <div class="card shadow-sm">

                    <div class="card-body">

                        <h5 class="card-title">
                            Security
                        </h5>

                        <p class="text-muted">
                            Ubah password akun Anda.
                        </p>

                        <a
                            href="<?= site_url('profile/password') ?>"
                            class="btn btn-outline-warning">
                            Change Password
                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<?= $this->endSection() ?>