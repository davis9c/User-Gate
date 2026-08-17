<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>
        <h1 class="h3 mb-1">Edit User</h1>

        <p class="text-muted mb-0">
            Update UserGateway user information.
        </p>
    </div>

    <a
        href="<?= site_url('dashboard/users') ?>"
        class="btn btn-outline-secondary"
    >
        Back
    </a>

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
            action="<?= site_url('dashboard/users/update/' . $user['id']) ?>"
        >

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
                    required
                >

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
                    required
                >

            </div>

            <div class="mb-3">

                <label for="email" class="form-label">
                    Email
                </label>

                <input
                    type="email"
                    class="form-control"
                    id="email"
                    name="email"
                    value="<?= old('email', $user['email']) ?>"
                    required
                >

            </div>

            <div class="mb-4">

                <label for="status" class="form-label">
                    Status
                </label>

                <select
                    class="form-select"
                    id="status"
                    name="status"
                    required
                >

                    <option
                        value="ACTIVE"
                        <?= old('status', $user['status']) === 'ACTIVE' ? 'selected' : '' ?>
                    >
                        ACTIVE
                    </option>

                    <option
                        value="INACTIVE"
                        <?= old('status', $user['status']) === 'INACTIVE' ? 'selected' : '' ?>
                    >
                        INACTIVE
                    </option>

                </select>

            </div>

            <button
                type="submit"
                class="btn btn-primary"
            >
                Save Changes
            </button>

        </form>

    </div>

</div>

<?= $this->endSection() ?>
