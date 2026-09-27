<?php

/**
 * Form grant/revoke role SUPER_ADMIN.
 *
 * Di-fetch AJAX ke dalam modal oleh Users::role(), lalu di-submit ke
 * Users::assignRole(). Satu form yang sama melayani dua arah: nilai
 * `action` menentukan apakah role diberikan atau dicabut.
 *
 * Tombol submit ada di footer modal (di luar form ini), jadi label dan
 * warnanya dibawa lewat data-submit-*. Label itu ikut ter-update saat form
 * di-replace setelah 422 — lihat syncModalSubmit() di layout.
 *
 * Variabel:
 *   $user         array data user
 *   $isSuperAdmin bool apakah user sudah memegang role SUPER_ADMIN
 *   $error        string pesan penolakan, null kalau tidak ada
 */

$error = $error ?? null;

$action   = $isSuperAdmin ? 'revoke' : 'grant';
$hasId    = ($user['id'] ?? '') !== '';
$isActive = ($user['status'] ?? null) === 'ACTIVE';

?>
<form
    id="fUserRole"
    method="post"
    action="<?= site_url('dashboard/users/assign-role/' . rawurlencode((string) ($user['id'] ?? ''))) ?>"
    data-submit-label="<?= $action === 'grant' ? 'Jadikan Super Admin' : 'Cabut Super Admin' ?>"
    data-submit-variant="<?= $action === 'grant' ? 'primary' : 'danger' ?>"
    data-submit-disabled="<?= $hasId ? '0' : '1' ?>">

    <?= csrf_field() ?>

    <!-- Namanya "role_action", bukan "action". Field bernama "action"
         akan menyembunyikan properti form.action: named property anak
         mendahulukan properti native, sehingga form.action mengembalikan
         elemen input itu sendiri, bukan URL. -->
    <input
        type="hidden"
        name="role_action"
        value="<?= esc($action) ?>">

    <div class="modal-body">

        <?php if ($error !== null): ?>
            <div class="alert alert-danger" role="alert">
                <?= esc($error) ?>
            </div>
        <?php endif; ?>

        <dl class="row mb-3">

            <dt class="col-sm-4">User</dt>
            <dd class="col-sm-8">
                <strong><?= esc($user['username'] ?? '') ?></strong>
                <?php if (($user['full_name'] ?? '') !== ''): ?>
                    <span class="text-muted"><?= esc($user['full_name']) ?></span>
                <?php endif; ?>
            </dd>

            <dt class="col-sm-4">Status akun</dt>
            <dd class="col-sm-8">
                <?php if ($isActive): ?>
                    <span class="badge text-bg-success">ACTIVE</span>
                <?php else: ?>
                    <span class="badge text-bg-secondary">INACTIVE</span>
                <?php endif; ?>
            </dd>

            <dt class="col-sm-4">Role sekarang</dt>
            <dd class="col-sm-8">
                <?php if ($isSuperAdmin): ?>
                    <span class="badge text-bg-dark">SUPER_ADMIN</span>
                <?php else: ?>
                    <span class="text-muted">Tidak punya role</span>
                <?php endif; ?>
            </dd>

        </dl>

        <?php if ($action === 'grant'): ?>
            <div class="alert alert-warning mb-0">
                <?php if (! $isActive): ?>
                    User ini berstatus INACTIVE sehingga role tidak bisa diberikan.
                    Aktifkan user lebih dulu lewat tombol Edit.
                <?php elseif ($hasId): ?>
                    Setelah diberikan, user ini akan punya akses penuh ke semua
                    halaman dashboard, dan setiap user lain bisa dicabut role-nya
                    olehnya.
                <?php else: ?>
                    User tidak ditemukan.
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-danger mb-0">
                Role Super Admin akan dicabut dari
                <strong><?= esc($user['username'] ?? '') ?></strong>.
                User kehilangan akses ke seluruh halaman dashboard sampai role
                diberikan lagi.
            </div>
        <?php endif; ?>

    </div>

</form>
