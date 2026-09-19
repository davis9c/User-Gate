<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Login - UserGateway</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">

<div class="container min-vh-100 d-flex align-items-center justify-content-center">

    <div class="card shadow-sm" style="max-width: 420px; width: 100%;">

        <div class="card-body p-4 p-md-5">

            <div class="text-center mb-4">
                <h2 class="fw-bold">UserGateway</h2>
                <p class="text-muted mb-0">
                    Sign in to your account
                </p>
            </div>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger">
                    <?= esc(session()->getFlashdata('error')) ?>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success">
                    <?= esc(session()->getFlashdata('success')) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= site_url('login/authenticate') ?>">

                <?= csrf_field() ?>

                <div class="mb-3">
                    <label for="identity" class="form-label">
                        Username atau Email
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        id="identity"
                        name="identity"
                        value="<?= old('identity') ?>"
                        autocomplete="username"
                        required
                    >
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">
                        Password
                    </label>

                    <input
                        type="password"
                        class="form-control"
                        id="password"
                        name="password"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        Login
                    </button>
                </div>

            </form>

        </div>

    </div>

</div>

</body>
</html>
