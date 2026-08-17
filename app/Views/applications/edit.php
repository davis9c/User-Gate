<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<div class="mb-4">
    <h1 class="h3">Edit Application</h1>
    <p class="text-muted">
        Update application information.
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

<div class="card shadow-sm">

    <div class="card-body">

        <form
            method="post"
            action="<?= site_url('dashboard/applications/update/' . $application['id']) ?>">

            <?= csrf_field() ?>

            <div class="mb-3">

                <label for="name" class="form-label">
                    Application Name
                </label>

                <input
                    type="text"
                    class="form-control"
                    id="name"
                    name="name"
                    value="<?= old('name', $application['name']) ?>"
                    required>

            </div>

            <div class="mb-3">

                <label for="code" class="form-label">
                    Application Code
                </label>

                <input
                    type="text"
                    class="form-control"
                    id="code"
                    name="code"
                    value="<?= old('code', $application['code']) ?>"
                    required>

            </div>

            <div class="mb-3">

                <label for="description" class="form-label">
                    Description
                </label>

                <textarea
                    class="form-control"
                    id="description"
                    name="description"
                    rows="4"><?= old('description', $application['description']) ?></textarea>

            </div>

            <div class="mb-4">

                <label for="status" class="form-label">
                    Status
                </label>

                <select
                    class="form-select"
                    id="status"
                    name="status">

                    <option
                        value="ACTIVE"
                        <?= old('status', $application['status']) === 'ACTIVE' ? 'selected' : '' ?>>
                        ACTIVE
                    </option>

                    <option
                        value="INACTIVE"
                        <?= old('status', $application['status']) === 'INACTIVE' ? 'selected' : '' ?>>
                        INACTIVE
                    </option>

                </select>

            </div>

            <button type="submit" class="btn btn-primary">
                Save Changes
            </button>

            <a
                href="<?= site_url('dashboard/applications') ?>"
                class="btn btn-outline-secondary">
                Cancel
            </a>

        </form>

    </div>

</div>

<?= $this->endSection() ?>