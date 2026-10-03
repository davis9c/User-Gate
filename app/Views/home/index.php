<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= esc($title) ?></title>

    <?= view('partials/theme/head') ?>

    <meta
        name="description"
        content="UserGate is a centralized User Management, RBAC, Application and API Key Management platform.">

    <style>
        /**
         * Token warna.
         *
         * Nilai warna tidak ditulis langsung di tiap aturan; semuanya
         * memakai var() supaya halaman ini bisa mengikuti mode gelap.
         * Nilai gelapnya diletakkan di [data-bs-theme="dark"] — atribut yang
         * sama dengan yang dipasang partials/theme/head.php.
         */
        :root {
            --ug-bg: #f8fafc;
            --ug-surface: #ffffff;
            --ug-surface-alt: #f9fafb;
            --ug-border: #e5e7eb;
            --ug-border-strong: #d1d5db;
            --ug-text: #111827;
            --ug-text-soft: #172033;
            --ug-muted: #6b7280;
            --ug-brand: #4f46e5;
            --ug-brand-soft: #eef2ff;
            --ug-inverse: #111827;
            --ug-inverse-hover: #1f2937;
            --ug-inverse-text: #ffffff;
            --ug-on-inverse: #d1d5db;
            --ug-section-dark: #111827;
            --ug-code-bg: #030712;
            --ug-code-border: #374151;
            --ug-code-key: #93c5fd;
            --ug-code-value: #86efac;
        }

        [data-bs-theme="dark"] {
            --ug-bg: #0b1120;
            --ug-surface: #111827;
            --ug-surface-alt: #161f31;
            --ug-border: #1f2937;
            --ug-border-strong: #374151;
            --ug-text: #f3f4f6;
            --ug-text-soft: #e5e7eb;
            --ug-muted: #9ca3af;
            --ug-brand: #818cf8;
            --ug-brand-soft: #1e1b4b;
            --ug-inverse: #f3f4f6;
            --ug-inverse-hover: #e5e7eb;
            --ug-inverse-text: #111827;
            --ug-on-inverse: #9ca3af;
            /* Section API tetap gelap, tapi sedikit lebih menyatu dengan
               latar halaman supaya tidak terlihat seperti blok asing. */
            --ug-section-dark: #0f172a;
            --ug-code-bg: #020617;
            --ug-code-border: #1f2937;
            --ug-code-key: #93c5fd;
            --ug-code-value: #86efac;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family:
                Inter,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;
            color: var(--ug-text-soft);
            background: var(--ug-bg);
            line-height: 1.6;
        }

        a {
            text-decoration: none;
        }

        .container {
            width: min(1120px, calc(100% - 40px));
            margin: 0 auto;
        }

        /* Navbar */

        .navbar {
            background: var(--ug-surface);
            border-bottom: 1px solid var(--ug-border);
        }

        .nav-inner {
            height: 72px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--ug-text);
            font-size: 21px;
            font-weight: 700;
        }

        .brand-icon {
            width: 36px;
            height: 36px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--ug-inverse);
            color: var(--ug-inverse-text);
            font-weight: 700;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .nav-links a {
            color: var(--ug-muted);
            font-size: 14px;
            font-weight: 500;
        }

        .nav-links a:hover {
            color: var(--ug-text);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 11px 18px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            transition: .2s ease;
        }

        .btn-primary {
            color: var(--ug-inverse-text) !important;
            background: var(--ug-inverse);
        }

        .btn-primary:hover {
            background: var(--ug-inverse-hover);
        }

        .btn-secondary {
            color: var(--ug-text);
            background: var(--ug-surface);
            border: 1px solid var(--ug-border-strong);
        }

        .btn-secondary:hover {
            background: var(--ug-surface-alt);
        }

        /* Hero */

        .hero {
            padding: 96px 0 88px;
            background: var(--ug-surface);
        }

        .hero-content {
            max-width: 820px;
            margin: 0 auto;
            text-align: center;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            margin-bottom: 22px;
            border: 1px solid var(--ug-border);
            border-radius: 999px;
            color: var(--ug-muted);
            background: var(--ug-surface-alt);
            font-size: 13px;
            font-weight: 600;
        }

        .hero h1 {
            font-size: clamp(42px, 6vw, 68px);
            line-height: 1.08;
            letter-spacing: -2px;
            color: var(--ug-text);
            margin-bottom: 24px;
        }

        .hero h1 span {
            color: var(--ug-brand);
        }

        .hero p {
            max-width: 680px;
            margin: 0 auto 32px;
            color: var(--ug-muted);
            font-size: 18px;
        }

        .hero-actions {
            display: flex;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        /* Features */

        .features {
            padding: 80px 0;
        }

        .section-heading {
            max-width: 650px;
            margin: 0 auto 42px;
            text-align: center;
        }

        .section-heading h2 {
            color: var(--ug-text);
            font-size: 34px;
            margin-bottom: 12px;
        }

        .section-heading p {
            color: var(--ug-muted);
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .feature {
            padding: 28px;
            background: var(--ug-surface);
            border: 1px solid var(--ug-border);
            border-radius: 14px;
        }

        .feature-icon {
            width: 44px;
            height: 44px;
            margin-bottom: 18px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--ug-brand-soft);
            color: var(--ug-brand);
            font-weight: 700;
        }

        .feature h3 {
            margin-bottom: 8px;
            color: var(--ug-text);
            font-size: 18px;
        }

        .feature p {
            color: var(--ug-muted);
            font-size: 14px;
        }

        /* API */

        .api-section {
            padding: 80px 0;
            background: var(--ug-section-dark);
            color: #ffffff;
        }

        .api-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            align-items: center;
        }

        .api-content h2 {
            font-size: 36px;
            line-height: 1.2;
            margin-bottom: 16px;
        }

        .api-content p {
            color: var(--ug-on-inverse);
            margin-bottom: 24px;
        }

        .code-block {
            padding: 24px;
            overflow-x: auto;
            border: 1px solid var(--ug-code-border);
            border-radius: 12px;
            background: var(--ug-code-bg);
            color: var(--ug-on-inverse);
            font-family: "SFMono-Regular", Consolas, monospace;
            font-size: 13px;
        }

        .code-key {
            color: #93c5fd;
        }

        .code-value {
            color: #86efac;
        }

        /* Footer */

        footer {
            padding: 28px 0;
            background: var(--ug-surface);
            border-top: 1px solid var(--ug-border);
        }

        .footer-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .footer-inner p {
            color: var(--ug-muted);
            font-size: 13px;
        }

        @media (max-width: 768px) {
            .nav-links a:not(.btn) {
                display: none;
            }

            .hero {
                padding: 70px 0;
            }

            .feature-grid {
                grid-template-columns: 1fr;
            }

            .api-content {
                grid-template-columns: 1fr;
            }

            .footer-inner {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>

<body>

    <nav class="navbar">
        <div class="container nav-inner">

            <a href="/" class="brand">
                <span class="brand-icon">U</span>
                UserGate
            </a>

            <div class="nav-links">
                <a href="#features">Features</a>
                <a href="#api">API</a>
                <a href="https://github.com/davis9c/User-Gate/tree/main/docs" target="_blank">Documentation</a>
                <?= view('partials/theme/toggle') ?>
                <a href="/login" class="btn btn-primary">Login</a>
            </div>

        </div>
    </nav>

    <main>

        <section class="hero">
            <div class="container">

                <div class="hero-content">

                    <div class="badge">
                        User Management & API Access Platform
                    </div>

                    <h1>
                        One place to manage
                        <span>users and API access.</span>
                    </h1>

                    <p>
                        UserGate provides centralized user management,
                        role-based access control, application management,
                        API keys and API permissions in one secure platform.
                    </p>

                    <div class="hero-actions">
                        <a href="/login" class="btn btn-primary">
                            Sign in to Dashboard
                        </a>

                        <a href="#features" class="btn btn-secondary">
                            Explore Features
                        </a>
                    </div>

                </div>

            </div>
        </section>

        <section class="features" id="features">
            <div class="container">

                <div class="section-heading">
                    <h2>Everything you need for access management</h2>

                    <p>
                        Keep application users, roles and API access
                        under centralized control.
                    </p>
                </div>

                <div class="feature-grid">

                    <div class="feature">
                        <div class="feature-icon">U</div>

                        <h3>User Management</h3>

                        <p>
                            Create and manage users, accounts, credentials
                            and account status from a centralized dashboard.
                        </p>
                    </div>

                    <div class="feature">
                        <div class="feature-icon">R</div>

                        <h3>RBAC</h3>

                        <p>
                            Control access using roles and permissions
                            so users only access the functionality they need.
                        </p>
                    </div>

                    <div class="feature">
                        <div class="feature-icon">K</div>

                        <h3>API Key Management</h3>

                        <p>
                            Generate, manage and revoke API keys while
                            keeping the actual credentials securely hashed.
                        </p>
                    </div>

                    <div class="feature">
                        <div class="feature-icon">A</div>

                        <h3>Application Management</h3>

                        <p>
                            Organize API access by application and manage
                            credentials independently for each application.
                        </p>
                    </div>

                    <div class="feature">
                        <div class="feature-icon">P</div>

                        <h3>API Permissions</h3>

                        <p>
                            Assign granular API permissions to individual
                            API keys for controlled service access.
                        </p>
                    </div>

                    <div class="feature">
                        <div class="feature-icon">S</div>

                        <h3>Security First</h3>

                        <p>
                            Designed with CSRF protection, secure cookies,
                            security headers and API authentication.
                        </p>
                    </div>

                </div>

            </div>
        </section>

        <section class="api-section" id="api">
            <div class="container">

                <div class="api-content">

                    <div>
                        <h2>
                            Simple API authentication.
                        </h2>

                        <p>
                            Applications can authenticate against UserGate
                            using an API key and consume authorized API
                            endpoints.
                        </p>

                        <a href="/login" class="btn btn-secondary">
                            Open Dashboard
                        </a>

                        <a href="https://github.com/davis9c/User-Gate/tree/main/docs" target="_blank" class="btn btn-secondary">
                            API Documentation
                        </a>
                    </div>

                    <div class="code-block">
                        <span class="code-key">GET</span>
                        /api/v1/users

                        <br><br>

                        X-API-Key:
                        <span class="code-value">
                            ugk_live_••••••••••••
                        </span>

                        <br><br>

                        <span class="code-key">200 OK</span>

                        <br>
                        {
                        <br>
                        &nbsp;&nbsp;"status": true,
                        <br>
                        &nbsp;&nbsp;"message":
                        <span class="code-value">
                            "Users retrieved successfully."
                        </span>
                        <br>
                        }
                    </div>

                </div>

            </div>
        </section>

    </main>

    <footer>
        <div class="container footer-inner">

            <p>
                &copy; <?= date('Y') ?> UserGate.
                User Management & API Access Platform.
            </p>

            <p>
                Secure access. Centralized management.
            </p>

        </div>
    </footer>

<?= view('partials/theme/script') ?>

</body>

</html>
