<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>UserGateway Setup</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <style>
        .cmd-box {
            display: flex;
            align-items: center;
            background: #1e293b;
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 8px;
        }
        .cmd-box code {
            flex: 1;
            color: #e2e8f0;
            font-size: 13px;
            white-space: nowrap;
            overflow-x: auto;
        }
        .cmd-box .btn-copy {
            background: #334155;
            border: 1px solid #475569;
            color: #e2e8f0;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            white-space: nowrap;
        }
        .cmd-box .btn-copy:hover {
            background: #475569;
        }
    </style>
</head>

<body class="bg-light">

    <div class="container min-vh-100 d-flex align-items-center justify-content-center">

        <div class="card shadow-sm" style="max-width: 520px; width: 100%;">

            <div class="card-body p-4 p-md-5">

                <div class="text-center mb-4">
                    <h2 class="fw-bold">UserGateway</h2>
                    <p class="text-muted mb-0">
                        Initial Setup
                    </p>
                </div>

                <div class="alert alert-info">
                    <strong>Selamat datang.</strong>
                    Buat akun SUPER_ADMIN pertama. Pastikan migration dan seed sudah dijalankan terlebih dahulu:
                </div>

                <div class="cmd-box">
                    <code>php spark migrate</code>
                    <button class="btn-copy" onclick="copyCmd(this, 'php spark migrate')">Copy</button>
                </div>

                <div class="cmd-box">
                    <code>php spark db:seed ApiPermissionSeeder</code>
                    <button class="btn-copy" onclick="copyCmd(this, 'php spark db:seed ApiPermissionSeeder')">Copy</button>
                </div>

                <hr class="my-4">

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger">
                        <?= esc(session()->getFlashdata('error')) ?>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('errors')): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post" action="<?= site_url('setup/install') ?>">

                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="full_name" class="form-label">
                            Nama Lengkap
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="full_name"
                            name="full_name"
                            value="<?= old('full_name') ?>"
                            required>
                    </div>

                    <div class="mb-3">
                        <label for="username" class="form-label">
                            Username
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="username"
                            name="username"
                            value="<?= old('username') ?>"
                            required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">
                            Email
                        </label>

                        <input
                            type="email"
                            class="form-control"
                            id="email"
                            name="email"
                            value="<?= old('email') ?>"
                            required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">
                            Password
                        </label>

                        <input
                            type="password"
                            class="form-control"
                            id="password"
                            name="password"
                            required>

                        <div class="form-text">
                            Minimal 8 karakter.
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">
                            Konfirmasi Password
                        </label>

                        <input
                            type="password"
                            class="form-control"
                            id="password_confirmation"
                            name="password_confirmation"
                            required>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            Install UserGateway
                        </button>
                    </div>

                </form>

            </div>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function copyCmd(btn, text) {
        navigator.clipboard.writeText(text).then(function() {
            btn.textContent = 'Copied!';
            setTimeout(function() { btn.textContent = 'Copy'; }, 1500);
        });
    }
    </script>

</body>

</html>
