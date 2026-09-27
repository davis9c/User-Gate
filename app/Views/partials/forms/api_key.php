<?php

/**
 * Form Create API Key.
 *
 * Dipakai oleh:
 *   - modal inline di api_keys/index.php
 *   - halaman api_keys/create.php
 *
 * Variabel:
 *   $application array data application
 *   $errors     array pesan validasi, null kalau tidak ada
 */

$errors = $errors ?? [];

$err = static function (string $key) use ($errors): ?string {
    return $errors[$key] ?? null;
};

?>
<form
    id="fApiKeyCreate"
    method="post"
    action="<?= site_url('dashboard/applications/' . $application['id'] . '/api-keys/create') ?>">

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

        <div class="mb-1">

            <label for="fApiKeyCreate_name" class="form-label">
                API Key Name
            </label>

            <input
                type="text"
                class="form-control <?= $err('name') ? 'is-invalid' : '' ?>"
                id="fApiKeyCreate_name"
                name="name"
                value="<?= old('name') ?>"
                placeholder="Contoh: HR Production"
                required>

            <?php if ($err('name')): ?>
                <div class="invalid-feedback"><?= esc($err('name')) ?></div>
            <?php endif; ?>

        </div>

    </div>

</form>
