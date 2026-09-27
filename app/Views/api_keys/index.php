<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>
        <h1 class="h3 mb-1">API Keys</h1>

        <p class="text-muted mb-0">
            <?= esc($application['name']) ?>
        </p>
    </div>

    <button
        type="button"
        class="btn btn-primary"
        data-bs-toggle="modal"
        data-bs-target="#modalApiKeyCreate">
        Create API Key
    </button>

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

<div class="card shadow-sm">

    <div class="card-body">

        <!-- Baris tabel diisi lewat server-side (ApiKeys::data). -->
        <table id="tblApiKeys" class="table table-hover align-middle w-100">

            <thead class="table-light">

                <tr>
                    <th>Name</th>
                    <th>Key</th>
                    <th>Status</th>
                    <th>Last Used</th>
                    <th>Action</th>
                </tr>

            </thead>

            <tbody></tbody>

        </table>

    </div>

</div>

<?= $this->endSection() ?>

<?= $this->section('modals') ?>
<div class="modal fade" id="modalApiKeyCreate" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Create API Key</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div data-form-slot>
                <?= view('partials/forms/api_key', ['application' => $application, 'errors' => null]) ?>
            </div>

            <div class="modal-footer">
                <button type="submit" form="fApiKeyCreate" class="btn btn-primary" data-modal-submit>
                    Generate API Key
                </button>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Batal
                </button>
            </div>

        </div>
    </div>

</div>

<?php
/**
 * Satu modal konfirmasi untuk semua baris. Form-nya dirender sekali di
 * sini (termasuk csrf_field); JavaScript hanya mengisi action, nama key,
 * status sekarang, dan status tujuan.
 */
?>
<div class="modal fade" id="modalToggleKey" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">

            <form method="post" id="fToggleKey">

                <?= csrf_field() ?>

                <div class="modal-header">
                    <h5 class="modal-title">Ubah Status API Key</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <p class="mb-2">
                        Ubah status
                        <strong data-toggle-name></strong>
                        dari
                        <span class="badge text-bg-secondary" data-toggle-current></span>
                        menjadi
                        <span class="badge text-bg-success" data-toggle-next></span>?
                    </p>

                    <p class="text-muted small mb-0" data-toggle-warning hidden>
                        API Key ini akan langsung tidak bisa dipakai.
                    </p>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-warning">
                        Ya, Ubah Status
                    </button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Batal
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>

<?php

/**
 * Satu modal permission untuk semua baris. Form-nya (daftar checkbox)
 * di-fetch dari ApiPermissions::index lalu disuntik ke [data-form-slot].
 * Tombol Save memakai atribut form="fApiPermissions" supaya submit-nya
 * tetap milik form yang disuntik, bukan form statis.
 */
?>
<div class="modal fade" id="modalPermissions" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">API Permissions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div data-form-slot></div>

            <div class="modal-footer">
                <button type="submit" form="fApiPermissions" class="btn btn-primary" data-modal-submit>
                    Save Permissions
                </button>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Batal
                </button>
            </div>

        </div>
    </div>

</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    var keysBase = '<?= site_url('dashboard/applications') ?>';
    var toggleBase = '<?= site_url('dashboard/api-keys/toggle') ?>';
    var permsBase = '<?= site_url('dashboard/api-keys') ?>';

    initServerSideTable('#tblApiKeys', {
        ajax: '<?= site_url('dashboard/applications/' . $application['id'] . '/api-keys/data') ?>',
        columns: [
            {
                data: 'name',
                render: function (value) {
                    return '<strong>' + dtEscape(value) + '</strong>';
                }
            },
            {
                data: 'key_prefix',
                render: function (value) {
                    return '<code>' + dtEscape(value) + '...</code>';
                }
            },
            {
                data: 'status',
                render: function (value) {
                    return value === 'ACTIVE'
                        ? '<span class="badge text-bg-success">ACTIVE</span>'
                        : '<span class="badge text-bg-secondary">' + dtEscape(value) + '</span>';
                }
            },
            {
                data: 'last_used_at',
                render: function (value) {
                    return dtEscape(value === '' ? '-' : value);
                }
            },
            {
                data: 'id',
                orderable: false,
                searchable: false,
                exportable: false,
                render: function (value, type, row) {
                    return '<div class="d-flex gap-1">' +
                        '<button type="button" class="btn btn-sm btn-outline-warning js-key-toggle"' +
                        ' data-bs-toggle="modal" data-bs-target="#modalToggleKey"' +
                        ' data-id="' + dtEscape(value) + '"' +
                        ' data-name="' + dtEscape(row.name) + '"' +
                        ' data-status="' + dtEscape(row.status) + '"' +
                        ' data-action="' + toggleBase + '/' + encodeURIComponent(value) + '">Toggle Status</button>' +
                        '<button type="button" class="btn btn-sm btn-outline-primary js-key-permissions"' +
                        ' data-bs-toggle="modal" data-bs-target="#modalPermissions"' +
                        ' data-id="' + dtEscape(value) + '"' +
                        ' data-name="' + dtEscape(row.name) + '">Permissions</button>' +
                        '</div>';
                }
            }
        ]
    });

    initModalForm('#modalApiKeyCreate');
    initModalForm('#modalPermissions');

    var toggleModal = document.getElementById('modalToggleKey');
    var toggleForm = document.getElementById('fToggleKey');

    // Delegasi: tombol Toggle dirender ulang setiap draw.
    document.addEventListener('click', function (event) {
        var permsBtn = event.target.closest('.js-key-permissions');

        if (permsBtn) {
            var permsTitle = document.querySelector('#modalPermissions .modal-title');

            if (permsTitle) {
                permsTitle.textContent = 'API Permissions - ' + permsBtn.dataset.name;
            }

            loadModalForm(
                permsBase + '/' + encodeURIComponent(permsBtn.dataset.id) + '/permissions',
                '#modalPermissions'
            );
            return;
        }

        var button = event.target.closest('.js-key-toggle');

        if (!button || !toggleModal) {
            return;
        }

        var isActive = button.dataset.status === 'ACTIVE';
        var next = isActive ? 'INACTIVE' : 'ACTIVE';

        toggleForm.action = button.dataset.action;
        toggleForm.reset();

        toggleModal.querySelector('[data-toggle-name]').textContent = button.dataset.name;
        toggleModal.querySelector('[data-toggle-current]').textContent = button.dataset.status;
        toggleModal.querySelector('[data-toggle-next]').textContent = next;

        // Peringatan hanya relevan saat menonaktifkan key yang sedang aktif.
        var warning = toggleModal.querySelector('[data-toggle-warning]');
        warning.hidden = !isActive;
    });
</script>
<?= $this->endSection() ?>
