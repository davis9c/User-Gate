<meta name="color-scheme" content="light dark">

<style>
    /**
     * Tombol pengubah tema.
     *
     * Styling-nya sengaja mandiri dan tidak memakai kelas .btn Bootstrap,
     * supaya tampilannya persis sama di halaman yang memuat Bootstrap
     * (dashboard, login, setup) maupun di landing page '/' yang tidak
     * memuat CSS framework sama sekali. Mengganti .btn di sini berarti
     * harus menambah workaround di landing page.
     *
     * Warna diambil dari `currentColor`, jadi tombol otomatis mengikuti
     * mode warna tempat ia berada — termasuk di dalam navbar yang selalu
     * gelap — tanpa perlu kelas tambahan per lokasi.
     */
    .theme-toggle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.25rem;
        height: 2.25rem;
        padding: 0;
        color: inherit;
        background-color: transparent;
        border: 1px solid rgba(128, 128, 128, .45);
        border-radius: .375rem;
        cursor: pointer;
        transition: background-color .15s ease-in-out, border-color .15s ease-in-out;
    }

    .theme-toggle:hover {
        background-color: rgba(128, 128, 128, .18);
    }

    .theme-toggle:focus-visible {
        outline: 0;
        box-shadow: 0 0 0 .25rem rgba(13, 110, 253, .35);
    }

    .theme-toggle--sm {
        width: 2rem;
        height: 2rem;
    }

    .theme-toggle .theme-icon {
        width: 1.125rem;
        height: 1.125rem;
        /* Ditampilkan bergantian lewat .theme-icon--active. Sengaja pakai
           CSS, bukan atribut hidden: atribut hidden adalah atribut HTML
           dan tidak berlaku pada elemen di namespace SVG. */
        display: none;
        pointer-events: none;
    }

    .theme-toggle .theme-icon--active {
        display: inline-block;
    }

    @media (prefers-reduced-motion: reduce) {
        .theme-toggle {
            transition: none;
        }
    }
</style>

<script>
    /**
     * Terapkan tema sebelum browser mulai menggambar halaman.
     *
     * Script ini WAJIB berada di <head> dan berjalan sinkron. Kalau
     * atribut data-bs-theme baru dipasang setelah halaman dirender,
     * tema yang salah akan terlihat sesaat (flash of incorrect theme)
     * di perangkat yang sistemnya gelap.
     *
     * Urutan sumber tema:
     *   1. localStorage -> pilihan manual user ('light' / 'dark')
     *   2. sistem       -> prefers-color-scheme, dipakai bila (1) kosong
     *
     * Mode "Ikuti Sistem" TIDAK disimpan sebagai nilai tersendiri;
     * kondisi itu direpresentasikan dengan tidak adanya entri di
     * localStorage, supaya perubahan tema di level OS langsung terbaca
     * tanpa perlu reload.
     */
    (function () {
        var STORAGE_KEY = 'ug-theme';

        var stored = null;

        try {
            stored = window.localStorage.getItem(STORAGE_KEY);
        } catch (error) {
            // localStorage bisa ditolak (mode privat / kebijakan browser).
            // Tema tetap berjalan, hanya tidak bisa diingat.
            stored = null;
        }

        var manual = stored === 'light' || stored === 'dark' ? stored : null;

        var system = window.matchMedia('(prefers-color-scheme: dark)').matches
            ? 'dark'
            : 'light';

        document.documentElement.setAttribute('data-bs-theme', manual || system);
    })();
</script>