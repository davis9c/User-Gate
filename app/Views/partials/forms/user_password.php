<?php

/**
 * Form Reset Password user.
 *
 * Dipakai oleh:
 *   - modal yang di-fetch AJAX di users/index.php
 *   - halaman users/reset_password.php
 *
 * Variabel:
 *   $user   array data user
 *   $errors array pesan validasi, null kalau tidak ada
 */

$errors = $errors ?? [];

$err = static function (string $key) use ($errors): ?string {
    return $errors[$key] ?? null;
};

?>
<form
    id="fUserPassword"
    method="post"
    action="<?= site_url('dashboard/users/reset-password/' . $user['id']) ?>">

    <?= csrf_field() ?>

    <div class="modal-body">

        <div class="alert alert-warning">
            Anda akan mengganti password user:
            <strong><?= esc($user['username']) ?></strong>
        </div>

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

            <label for="fUserPassword_password" class="form-label">
                Password Baru
            </label>

            <input
                type="password"
                class="form-control <?= $err('password') ? 'is-invalid' : '' ?>"
                id="fUserPassword_password"
                name="password"
                minlength="8"
                required>

            <?php if ($err('password')): ?>
                <div class="invalid-feedback"><?= esc($err('password')) ?></div>
            <?php else: ?>
                <div class="form-text">Minimal 8 karakter.</div>
            <?php endif; ?>

        </div>

        <div class="mb-1">

            <label for="fUserPassword_password_confirmation" class="form-label">
                Konfirmasi Password
            </label>

            <input
                type="password"
                class="form-control <?= $err('password_confirmation') ? 'is-invalid' : '' ?>"
                id="fUserPassword_password_confirmation"
                name="password_confirmation"
                minlength="8"
                required>

            <?php if ($err('password_confirmation')): ?>
                <div class="invalid-feedback"><?= esc($err('password_confirmation')) ?></div>
            <?php endif; ?>

        </div>

    </div>

</form>
