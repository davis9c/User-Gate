<?php

/**
 * Form User untuk Create / Edit.
 *
 * Dipakai oleh:
 *   - modal inline di users/index.php (mode create)
 *   - modal yang di-fetch AJAX di users/index.php (mode edit)
 *   - halaman users/create.php dan users/edit.php (mode create|edit)
 *
 * Variabel:
 *   $mode   'create' | 'edit'
 *   $user   array data user, hanya untuk mode edit
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
    id="fUser<?= $isEdit ? 'Edit' : 'Create' ?>"
    method="post"
    action="<?= $isEdit
        ? site_url('dashboard/users/update/' . $user['id'])
        : site_url('dashboard/users/create') ?>">

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

            <label for="fUser_full_name" class="form-label">
                Nama Lengkap
            </label>

            <input
                type="text"
                class="form-control <?= $err('full_name') ? 'is-invalid' : '' ?>"
                id="fUser_full_name"
                name="full_name"
                value="<?= old('full_name', $isEdit ? $user['full_name'] : null) ?>"
                required>

            <?php if ($err('full_name')): ?>
                <div class="invalid-feedback"><?= esc($err('full_name')) ?></div>
            <?php endif; ?>

        </div>

        <div class="mb-3">

            <label for="fUser_username" class="form-label">
                Username
            </label>

            <input
                type="text"
                class="form-control <?= $err('username') ? 'is-invalid' : '' ?>"
                id="fUser_username"
                name="username"
                value="<?= old('username', $isEdit ? $user['username'] : null) ?>"
                required>

            <?php if ($err('username')): ?>
                <div class="invalid-feedback"><?= esc($err('username')) ?></div>
            <?php elseif (! $isEdit): ?>
                <div class="form-text">Hanya huruf dan angka.</div>
            <?php endif; ?>

        </div>

        <div class="mb-3">

            <label for="fUser_email" class="form-label">
                Email
            </label>

            <input
                type="email"
                class="form-control <?= $err('email') ? 'is-invalid' : '' ?>"
                id="fUser_email"
                name="email"
                value="<?= old('email', $isEdit ? $user['email'] : null) ?>"
                required>

            <?php if ($err('email')): ?>
                <div class="invalid-feedback"><?= esc($err('email')) ?></div>
            <?php endif; ?>

        </div>

        <?php if (! $isEdit): ?>

            <div class="mb-3">

                <label for="fUser_password" class="form-label">
                    Password
                </label>

                <input
                    type="password"
                    class="form-control <?= $err('password') ? 'is-invalid' : '' ?>"
                    id="fUser_password"
                    name="password"
                    required>

                <?php if ($err('password')): ?>
                    <div class="invalid-feedback"><?= esc($err('password')) ?></div>
                <?php else: ?>
                    <div class="form-text">Minimal 8 karakter.</div>
                <?php endif; ?>

            </div>

            <div class="mb-3">

                <label for="fUser_password_confirmation" class="form-label">
                    Konfirmasi Password
                </label>

                <input
                    type="password"
                    class="form-control <?= $err('password_confirmation') ? 'is-invalid' : '' ?>"
                    id="fUser_password_confirmation"
                    name="password_confirmation"
                    required>

                <?php if ($err('password_confirmation')): ?>
                    <div class="invalid-feedback"><?= esc($err('password_confirmation')) ?></div>
                <?php endif; ?>

            </div>

        <?php endif; ?>

        <div class="mb-1">

            <label for="fUser_status" class="form-label">
                Status
            </label>

            <select
                class="form-select <?= $err('status') ? 'is-invalid' : '' ?>"
                id="fUser_status"
                name="status"
                required>

                <option
                    value="ACTIVE"
                    <?= old('status', $isEdit ? $user['status'] : 'ACTIVE') === 'ACTIVE' ? 'selected' : '' ?>>
                    ACTIVE
                </option>

                <option
                    value="INACTIVE"
                    <?= old('status', $isEdit ? $user['status'] : 'ACTIVE') === 'INACTIVE' ? 'selected' : '' ?>>
                    INACTIVE
                </option>

            </select>

            <?php if ($err('status')): ?>
                <div class="invalid-feedback"><?= esc($err('status')) ?></div>
            <?php endif; ?>

        </div>

    </div>

</form>
