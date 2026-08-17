<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<div class="mb-4">

    <h1 class="h3 mb-1">Create API Key</h1>

    <p class="text-muted">
        <?= esc($application['name']) ?>
    </p>

</div>

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
            action="<?= site_url('dashboard/applications/' . $application['id'] . '/api-keys/create') ?>">

            <?= csrf_field() ?>

            <div class="mb-4">

                <label for="name" class="form-label">
                    API Key Name
                </label>

                <input
                    type="text"
                    class="form-control"
                    id="name"
                    name="name"
                    value="<?= old('name') ?>"
                    placeholder="Contoh: HR Production"
                    required>

            </div>

            <button
                type="submit"
                class="btn btn-primary">
                Generate API Key
            </button>

            <a
                href="<?= site_url('dashboard/applications/' . $application['id'] . '/api-keys') ?>"
                class="btn btn-outline-secondary">
                Cancel
            </a>

        </form>

    </div>

</div>

<?= $this->endSection() ?>