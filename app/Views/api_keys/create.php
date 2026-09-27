<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<div class="mb-4">
    <h1 class="h3 mb-1">Create API Key</h1>
    <p class="text-muted mb-0">
        <?= esc($application['name']) ?>
    </p>
</div>

<div class="card shadow-sm">

    <div class="card-body p-0">

        <?= view('partials/forms/api_key', [
            'application'=> $application,
            'errors'     => session()->getFlashdata('errors'),
        ]) ?>

        <div class="card-footer bg-transparent d-flex gap-2">
            <button type="submit" form="fApiKeyCreate" class="btn btn-primary">
                Generate API Key
            </button>

            <a
                href="<?= site_url('dashboard/applications/' . $application['id'] . '/api-keys') ?>"
                class="btn btn-outline-secondary">
                Cancel
            </a>
        </div>

    </div>

</div>

<?= $this->endSection() ?>
