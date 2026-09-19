<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= esc($title ?? 'UserGateway') ?></title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">
</head>

<body class="bg-light">

    <nav class="navbar navbar-dark bg-dark">
        <div class="container">

            <a href="<?= site_url('dashboard') ?>" class="navbar-brand">
                UserGateway
            </a>

            <div class="d-flex align-items-center gap-3">

                <a
                    href="<?= site_url('profile') ?>"
                    class="text-white text-decoration-none">
                    <?= esc(session()->get('full_name')) ?>
                </a>

                <a
                    href="<?= site_url('logout') ?>"
                    class="btn btn-outline-light btn-sm">
                    Logout
                </a>

            </div>

        </div>
    </nav>

    <main class="container py-4">

        <?= $this->renderSection('content') ?>

    </main>

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
    </script>

</body>

</html>