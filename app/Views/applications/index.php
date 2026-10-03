<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>
        <h1 class="h3 mb-1">Applications</h1>
        <p class="text-muted mb-0">
            Manage applications connected to UserGateway.
        </p>
    </div>

    <button
        type="button"
        class="btn btn-primary"
        data-bs-toggle="modal"
        data-bs-target="#modalApplicationCreate">
        Create Application
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

        <!-- Baris tabel diisi lewat server-side (Applications::data). -->
        <table id="tblApplications" class="table table-hover align-middle w-100">

            <thead>
                <tr>
                    <th>Application</th>
                    <th>Code</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody></tbody>

        </table>

    </div>

</div>

<?= $this->endSection() ?>

<?= $this->section('modals') ?>
<div class="modal fade" id="modalApplicationCreate" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Create Application</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div data-form-slot>
                <?= view('partials/forms/application', ['mode' => 'create', 'errors' => null]) ?>
            </div>

            <div class="modal-footer">
                <button type="submit" form="fApplicationCreate" class="btn btn-primary" data-modal-submit>
                    Create Application
                </button>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Batal
                </button>
            </div>

        </div>
    </div>

</div>

<div class="modal fade" id="modalApplicationEdit" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Edit Application</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div data-form-slot></div>

            <div class="modal-footer">
                <button type="submit" form="fApplicationEdit" class="btn btn-primary" data-modal-submit>
                    Save Changes
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
    var appEditBase = '<?= site_url('dashboard/applications/edit') ?>';
    var appKeysBase = '<?= site_url('dashboard/applications') ?>';

    initServerSideTable('#tblApplications', {
        ajax: '<?= site_url('dashboard/applications/data') ?>',
        columns: [
            {
                data: 'name',
                render: function (value) {
                    return '<strong>' + dtEscape(value) + '</strong>';
                }
            },
            {
                data: 'code',
                render: function (value) {
                    return '<code>' + dtEscape(value) + '</code>';
                }
            },
            {
                data: 'description',
                render: function (value) {
                    return dtEscape(value === '' ? '-' : value);
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
                data: 'id',
                orderable: false,
                searchable: false,
                exportable: false,
                render: function (value, type, row) {
                    return '<div class="d-flex gap-1">' +
                        '<a href="' + appKeysBase + '/' + encodeURIComponent(value) + '/api-keys"' +
                        ' class="btn btn-sm btn-outline-secondary">API Keys</a>' +
                        '<button type="button" class="btn btn-sm btn-outline-primary js-application-edit"' +
                        ' data-bs-toggle="modal" data-bs-target="#modalApplicationEdit"' +
                        ' data-id="' + dtEscape(value) + '"' +
                        ' data-name="' + dtEscape(row.name) + '">Edit</button>' +
                        '</div>';
                }
            }
        ]
    });

    initModalForm('#modalApplicationCreate');
    initModalForm('#modalApplicationEdit');

    // Delegasi: tombol Edit dirender ulang setiap draw.
    document.addEventListener('click', function (event) {
        var button = event.target.closest('.js-application-edit');

        if (!button) {
            return;
        }

        var title = document.querySelector('#modalApplicationEdit .modal-title');

        if (title) {
            title.textContent = 'Edit Application - ' + button.dataset.name;
        }

        loadModalForm(appEditBase + '/' + encodeURIComponent(button.dataset.id), '#modalApplicationEdit');
    });
</script>
<?= $this->endSection() ?>
