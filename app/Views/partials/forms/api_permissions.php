<?php

/**
 * Form permission untuk satu API Key.
 *
 * Dipakai oleh:
 *   - modal yang di-fetch AJAX di api_keys/index.php
 *   - halaman api_permissions/index.php
 *
 * Tombol submit tidak ada di dalam form ini;-nya di .modal-footer (modal)
 * atau card-footer (halaman) memakai atribut form="fApiPermissions".
 *
 * Variabel:
 *   $apiKey      array data API Key
 *   $permissions array seluruh permission yang tersedia
 *   $assignedIds array id permission yang sudah terpasang ke API Key ini
 */

$assignedInt = array_map('intval', $assignedIds ?? []);

?>
<form
    id="fApiPermissions"
    method="post"
    action="<?= site_url('dashboard/api-keys/' . $apiKey['id'] . '/permissions') ?>">

    <?= csrf_field() ?>

    <div class="modal-body">

        <p class="text-muted small mb-3">
            API Key:
            <code><?= esc($apiKey['key_prefix']) ?>...</code>
        </p>

        <?php if (empty($permissions)): ?>

            <div class="alert alert-warning mb-0">
                Belum ada permission yang tersedia.
            </div>

        <?php else: ?>

            <p class="small text-muted mb-2">
                Centang permission yang boleh dipakai oleh API Key ini.
            </p>

            <?php foreach ($permissions as $permission): ?>

                <?php $permissionId = (int) $permission['id']; ?>

                <div class="form-check">

                    <input
                        class="form-check-input"
                        type="checkbox"
                        name="permissions[]"
                        value="<?= esc($permissionId) ?>"
                        id="permission_<?= esc($permissionId) ?>"
                        <?= in_array($permissionId, $assignedInt, true) ? 'checked' : '' ?>>

                    <label
                        class="form-check-label"
                        for="permission_<?= esc($permissionId) ?>">

                        <code><?= esc($permission['code']) ?></code>

                        <?php if (! empty($permission['description'])): ?>

                            <span class="text-muted">
                                — <?= esc($permission['description']) ?>
                            </span>

                        <?php endif; ?>

                    </label>

                </div>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>

</form>
