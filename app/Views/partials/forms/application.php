<?php

/**
 * Form Application untuk Create / Edit.
 *
 * Dipakai oleh:
 *   - modal inline di applications/index.php (mode create)
 *   - modal yang di-fetch AJAX di applications/index.php (mode edit)
 *   - halaman applications/create.php dan applications/edit.php
 *
 * Variabel:
 *   $mode   'create' | 'edit'
 *   $application array data application, hanya untuk mode edit
 *   $errors array pesan validasi, null kalau tidak ada
 */

$mode   = $mode ?? 'create';
$isEdit = $mode === 'edit';
$errors = $errors ?? [];

$err = static function (string $key) use ($errors): ?string {
    return $errors[$key] ?? null;
};

?>
<form
    id="fApplication<?= $isEdit ? 'Edit' : 'Create' ?>"
    method="post"
    action="<?= $isEdit
        ? site_url('dashboard/applications/update/' . $application['id'])
        : site_url('dashboard/applications/create') ?>">

    <?= csrf_field() ?>

    <div class="modal-body">

        <?php if ($errors): ?>
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                    <?php foreach ($errors as $message): ?>
                        <li><?= esc($message) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="mb-3">

            <label for="fApplication_name" class="form-label">
                Application Name
            </label>

            <input
                type="text"
                class="form-control <?= $err('name') ? 'is-invalid' : '' ?>"
                id="fApplication_name"
                name="name"
                value="<?= old('name', $isEdit ? $application['name'] : null) ?>"
                required>

            <?php if ($err('name')): ?>
                <div class="invalid-feedback"><?= esc($err('name')) ?></div>
            <?php endif; ?>

        </div>

        <div class="mb-3">

            <label for="fApplication_code" class="form-label">
                Application Code
            </label>

            <input
                type="text"
                class="form-control <?= $err('code') ? 'is-invalid' : '' ?>"
                id="fApplication_code"
                name="code"
                value="<?= old('code', $isEdit ? $application['code'] : null) ?>"
                required>

            <?php if ($err('code')): ?>
                <div class="invalid-feedback"><?= esc($err('code')) ?></div>
            <?php elseif (! $isEdit): ?>
                <div class="form-text">Contoh: HR, FINANCE, INVENTORY.</div>
            <?php endif; ?>

        </div>

        <div class="mb-3">

            <label for="fApplication_description" class="form-label">
                Description
            </label>

            <textarea
                class="form-control <?= $err('description') ? 'is-invalid' : '' ?>"
                id="fApplication_description"
                name="description"
                rows="4"><?= old('description', $isEdit ? ($application['description'] ?? '') : '') ?></textarea>

            <?php if ($err('description')): ?>
                <div class="invalid-feedback"><?= esc($err('description')) ?></div>
            <?php endif; ?>

        </div>

        <div class="mb-1">

            <label for="fApplication_status" class="form-label">
                Status
            </label>

            <select
                class="form-select <?= $err('status') ? 'is-invalid' : '' ?>"
                id="fApplication_status"
                name="status">

                <option
                    value="ACTIVE"
                    <?= old('status', $isEdit ? $application['status'] : 'ACTIVE') === 'ACTIVE' ? 'selected' : '' ?>>
                    ACTIVE
                </option>

                <option
                    value="INACTIVE"
                    <?= old('status', $isEdit ? $application['status'] : 'ACTIVE') === 'INACTIVE' ? 'selected' : '' ?>>
                    INACTIVE
                </option>

            </select>

            <?php if ($err('status')): ?>
                <div class="invalid-feedback"><?= esc($err('status')) ?></div>
            <?php endif; ?>

        </div>

    </div>

</form>
