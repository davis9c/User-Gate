<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Regression test untuk partial tema dan navbar admin.
 *
 * Yang diuji di sini adalah hal-hal yang mudah kembali ke kondisi lama
 * tanpa error yang terlihat: kelas utility yang memaksa warna terang,
 * atribut a11y pada menu aktif, dan partial tema yang harus ikut ter-render.
 *
 * @internal
 */
final class ThemeViewTest extends CIUnitTestCase
{
    private function renderAdmin(string $path = '/'): string
    {
        session()->set(['full_name' => 'Budi Santoso']);

        service('uri')->setPath($path);

        return (string) view('layouts/admin', ['title' => 'Test']);
    }

    public function testAdminLayoutRendersThemePartials(): void
    {
        $html = $this->renderAdmin();

        // Partial head: color-scheme + script anti-FOUC.
        $this->assertStringContainsString('name="color-scheme"', $html);
        $this->assertStringContainsString('prefers-color-scheme: dark', $html);

        // Partial script: logika pengalih tema.
        $this->assertStringContainsString('data-theme-toggle', $html);
        $this->assertStringContainsString('theme-toggle--sm', $html);
        $this->assertStringContainsString('ug-theme', $html);

        // Ikon ketiga mode harus ada di markup.
        foreach (['auto', 'light', 'dark'] as $mode) {
            $this->assertStringContainsString(
                'data-theme-icon="' . $mode . '"',
                $html
            );
        }
    }

    /**
     * bg-light adalah abu-abu terang literal yang tidak berubah di mode
     * gelap, jadi akan membocorkan background terang di halaman gelap.
     */
    public function testBodyUsesBgBodyNotBgLight(): void
    {
        $html = $this->renderAdmin();

        $this->assertStringContainsString('class="bg-body"', $html);
        $this->assertStringNotContainsString('class="bg-light"', $html);
    }

    public function testNavbarUsesBootstrap53ColorScheme(): void
    {
        $html = $this->renderAdmin();

        // Hanya tag pembuka <nav> yang diperiksa, bukan seluruh halaman:
        // file view memang menjelaskan .navbar-dark di komentar.
        preg_match('/<nav [^>]*>/', $html, $match);

        $this->assertNotEmpty($match, 'tag <nav> tidak ditemukan');

        // .navbar-dark deprecated di v5.3 -> data-bs-theme="dark".
        $this->assertStringNotContainsString('navbar-dark', $match[0]);
        $this->assertSame(
            '<nav class="navbar navbar-expand-lg bg-dark border-bottom" data-bs-theme="dark">',
            $match[0]
        );

        // Menu user memakai dropdown, bukan link + tombol terpisah.
        $this->assertStringContainsString('dropdown-menu dropdown-menu-end', $html);
        $this->assertStringContainsString('Budi Santoso', $html);
    }

    public function testOnlyTheMatchingNavLinkIsMarkedActive(): void
    {
        $html = $this->renderAdmin('dashboard/users');

        // Sub-path aktif. Dashboard tidak ikut aktif karena exact-match.
        $this->assertSame(1, substr_count($html, 'aria-current="page"'));
        $this->assertSame(1, substr_count($html, 'nav-link active'));
    }

    public function testNoNavLinkIsActiveOnUnrelatedPath(): void
    {
        $html = $this->renderAdmin('profile');

        $this->assertStringNotContainsString('aria-current="page"', $html);
        $this->assertStringNotContainsString('nav-link active', $html);
    }

    public function testLoginPageUsesBgBodyAndIncludesThemeParts(): void
    {
        $html = (string) view('login/index');

        $this->assertStringContainsString('class="bg-body"', $html);
        $this->assertStringNotContainsString('class="bg-light"', $html);
        $this->assertStringContainsString('data-theme-toggle', $html);
        $this->assertStringContainsString('prefers-color-scheme: dark', $html);
    }

    /**
     * Halaman yang memakai .table-light akan tetap terang di mode gelap.
     *
     * File-nya dibaca langsung, bukan di-render: ketiga halaman ini memanggil
     * DataTables dari section 'scripts' dan meninggalkan output buffer
     * terbuka, sehingga assertions atas hasil render tidak rapi.
     */
    public function testDataTableHeadersDoNotForceLightBackground(): void
    {
        $views = ['users/index', 'applications/index', 'api_keys/index'];

        foreach ($views as $view) {
            $source = file_get_contents(APPPATH . 'Views/' . $view . '.php');

            $this->assertStringNotContainsString(
                'table-light',
                $source,
                $view . ' masih memakai table-light'
            );
        }
    }

    /**
     * Landing page tidak memuat Bootstrap, jadi token warnanya harus
     *_sendiri lewat var() dan override [data-bs-theme="dark"].
     */
    public function testLandingPageColoursComeFromVariables(): void
    {
        $html = (string) view('home/index', ['title' => 'Home']);

        $this->assertStringContainsString(':root {', $html);
        $this->assertStringContainsString('[data-bs-theme="dark"] {', $html);

        // Aturan komponen tidak boleh lagi menulis hex langsung.
        $this->assertMatchesRegularExpression(
            '/\.hero \{[^}]*background: var\(--ug-surface\)/',
            $html
        );
        $this->assertMatchesRegularExpression(
            '/\.feature \{[^}]*border: 1px solid var\(--ug-border\)/',
            $html
        );
        $this->assertStringContainsString('data-theme-toggle', $html);
    }
}