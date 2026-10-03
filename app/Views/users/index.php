<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php if (session()->getFlashdata('success')): ?>

    <div class="alert alert-success">
        <?= esc(session()->getFlashdata('success')) ?>
    </div>

<?php endif; ?>
<div class="d-flex justify-content-between align-items-center mb-4">

    <div>
        <h1 class="h3 mb-1">Users</h1>

        <p class="text-muted mb-0">
            Manage UserGateway users.
        </p>
    </div>

    <button
        type="button"
        class="btn btn-primary"
        data-bs-toggle="modal"
        data-bs-target="#modalUserCreate">
        Create User
    </button>

</div>

<div class="card shadow-sm">

    <div class="card-body">

        <!-- Baris tabel diisi lewat server-side (Users::data), bukan dirender
             di sini, supaya tabel tetap ringan untuk data yang banyak. -->
        <table id="tblUsers" class="table table-hover align-middle w-100">

            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Dibuat</th>
                    <th>Role</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody></tbody>

        </table>

    </div>

</div>

<?= $this->endSection() ?>

<?= $this->section('modals') ?>
<div class="modal fade" id="modalUserCreate" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Create User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div data-form-slot>
                <?= view('partials/forms/user', ['mode' => 'create', 'errors' => null]) ?>
            </div>

            <div class="modal-footer">
                <button type="submit" form="fUserCreate" class="btn btn-primary" data-modal-submit>
                    Create User
                </button>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Batal
                </button>
            </div>

        </div>
    </div>

</div>

<div class="modal fade" id="modalUserEdit" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div data-form-slot></div>

            <div class="modal-footer">
                <button type="submit" form="fUserEdit" class="btn btn-primary" data-modal-submit>
                    Save Changes
                </button>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Batal
                </button>
            </div>

        </div>
    </div>

</div>

<div class="modal fade" id="modalUserPassword" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Reset Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div data-form-slot></div>

            <div class="modal-footer">
                <button type="submit" form="fUserPassword" class="btn btn-warning" data-modal-submit>
                    Reset Password
                </button>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Batal
                </button>
            </div>

        </div>
    </div>

</div>
<div class="modal fade" id="modalUserRole" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div data-form-slot></div>

            <div class="modal-footer">
                <button type="submit" form="fUserRole" class="btn btn-primary" data-modal-submit>
                    Simpan
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
    var editBase = '<?= site_url('dashboard/users/edit') ?>';
    var passBase = '<?= site_url('dashboard/users/reset-password') ?>';
    var roleBase = '<?= site_url('dashboard/users/role') ?>';

    initServerSideTable('#tblUsers', {
        ajax: '<?= site_url('dashboard/users/data') ?>',
        // Samakan dengan urutan default di controller.
        order: [[4, 'desc']],
        columns: [
            { data: 'full_name' },
            { data: 'username' },
            { data: 'email' },
            {
                data: 'status',
                render: function (value) {
                    return value === 'ACTIVE'
                        ? '<span class="badge text-bg-success">ACTIVE</span>'
                        : '<span class="badge text-bg-secondary">' + dtEscape(value) + '</span>';
                }
            },
            { data: 'created_at' },
            {
                // Di luar allowlist kolom di Users::data, jadi tidak bisa
                // dicari/diurutkan. Badge-nya tetap ikut diekspor.
                data: 'is_super_admin',
                orderable: false,
                searchable: false,
                render: function (value) {
                    return value === 1
                        ? '<span class="badge text-bg-dark">SUPER_ADMIN</span>'
                        : '<span class="text-muted">-</span>';
                }
            },
            {
                // Kolom action: bukan kolom data, jadi tidak dikirim ke server
                // dan tidak ikut diekspor.
                data: 'id',
                orderable: false,
                searchable: false,
                exportable: false,
                render: function (value, type, row) {
                    var isSuperAdmin = row.is_super_admin === 1;

                    return '<div class="d-flex gap-1">' +
                        '<button type="button" class="btn btn-sm btn-outline-primary js-user-edit"' +
                        ' data-bs-toggle="modal" data-bs-target="#modalUserEdit"' +
                        ' data-id="' + dtEscape(value) + '"' +
                        ' data-name="' + dtEscape(row.full_name) + '">Edit</button>' +
                        '<button type="button" class="btn btn-sm btn-outline-warning js-user-password"' +
                        ' data-bs-toggle="modal" data-bs-target="#modalUserPassword"' +
                        ' data-id="' + dtEscape(value) + '"' +
                        ' data-username="' + dtEscape(row.username) + '">Reset Password</button>' +
                        '<button type="button" class="btn btn-sm ' +
                        (isSuperAdmin ? 'btn-outline-danger' : 'btn-outline-dark') + ' js-user-role"' +
                        ' data-bs-toggle="modal" data-bs-target="#modalUserRole"' +
                        ' data-id="' + dtEscape(value) + '"' +
                        ' data-username="' + dtEscape(row.username) + '">' +
                        (isSuperAdmin ? 'Cabut Super Admin' : 'Jadikan Super Admin') +
                        '</button>' +
                        '</div>';
                }
            }
        ]
    });

    initModalForm('#modalUserCreate');
    initModalForm('#modalUserEdit');
    initModalForm('#modalUserPassword');
    initModalForm('#modalUserRole');

    // Label tombol "Jadikan Super Admin" / "Cabut Super Admin" ikut
    // berubah mengikuti state form, termasuk setelah 422.
    syncModalSubmit('#modalUserRole');

    // Delegasi: tombol Edit / Reset dirender ulang setiap draw, jadi
    // listener dipasang sekali di document, bukan per elemen.
    document.addEventListener('click', function (event) {
        var editBtn = event.target.closest('.js-user-edit');
        var passBtn = event.target.closest('.js-user-password');
        var roleBtn = event.target.closest('.js-user-role');

        if (editBtn) {
            var title = document.querySelector('#modalUserEdit .modal-title');

            if (title) {
                title.textContent = 'Edit User - ' + editBtn.dataset.name;
            }

            loadModalForm(editBase + '/' + encodeURIComponent(editBtn.dataset.id), '#modalUserEdit');
            return;
        }

        if (passBtn) {
            var passTitle = document.querySelector('#modalUserPassword .modal-title');

            if (passTitle) {
                passTitle.textContent = 'Reset Password - ' + passBtn.dataset.username;
            }

            loadModalForm(passBase + '/' + encodeURIComponent(passBtn.dataset.id), '#modalUserPassword');
            return;
        }

        if (roleBtn) {
            var roleTitle = document.querySelector('#modalUserRole .modal-title');

            if (roleTitle) {
                roleTitle.textContent = 'Role - ' + roleBtn.dataset.username;
            }

            loadModalForm(roleBase + '/' + encodeURIComponent(roleBtn.dataset.id), '#modalUserRole');
        }
    });
</script>
<?= $this->endSection() ?>
