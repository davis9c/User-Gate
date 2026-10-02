<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<div class="mb-4">

    <h1 class="h3">
        API Key Created
    </h1>

</div>

<div class="alert alert-warning">

    <strong>Important!</strong>

    API Key ini hanya ditampilkan sekarang.

    Simpan dengan aman. Setelah meninggalkan halaman ini,
    API Key asli tidak dapat ditampilkan kembali.

</div>

<div class="card shadow-sm">

    <div class="card-body">

        <label class="form-label">
            API Key
        </label>

        <div class="input-group mb-4">

            <input
                type="text"
                class="form-control"
                id="apiKey"
                value="<?= esc($apiKey) ?>"
                readonly>

            <button
                type="button"
                class="btn btn-outline-secondary"
                id="copyApiKey">
                Copy to Clipboard
            </button>

        </div>

        <div
            id="copyMessage"
            class="text-success small mb-3"
            hidden>
            API Key berhasil disalin.
        </div>

        <a
            href="<?= site_url('dashboard/applications/' . $application['id'] . '/api-keys') ?>"
            class="btn btn-primary">
            Done
        </a>

    </div>

</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Logika copy-nya dipakai bersama dengan modal hasil create, lihat
    // copyToClipboard() di layout admin.
    document.getElementById('copyApiKey').addEventListener('click', function () {
        copyToClipboard(
            document.getElementById('apiKey').value,
            document.getElementById('copyMessage'),
            this
        );
    });
</script>
<?= $this->endSection() ?>