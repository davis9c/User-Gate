<script>
    /**
     * Logika pengalih tema (terang / gelap / ikuti sistem).
     *
     * Mengpasang atribut `data-bs-theme` pada <html> — Bootstrap 5.3
     * memakai atribut itu sebagai color mode, jadi seluruh komponen
     * (tombol, form, tabel, modal) mengikuti tanpa CSS tambahan.
     */
    (function () {
        var STORAGE_KEY = 'ug-theme';

        // Urutan tombol ditekan: Ikuti Sistem -> Terang -> Gelap -> kembali.
        var MODES = ['auto', 'light', 'dark'];

        var LABELS = {
            auto: 'Ikuti Sistem',
            light: 'Terang',
            dark: 'Gelap'
        };

        var media = window.matchMedia('(prefers-color-scheme: dark)');

        /** Pilihan manual yang tersimpan, atau null saat mode Auto. */
        function readStored() {
            try {
                var value = window.localStorage.getItem(STORAGE_KEY);

                return value === 'light' || value === 'dark' ? value : null;
            } catch (error) {
                return null;
            }
        }

        /** Mode Auto ditulis sebagai "tidak ada entri", bukan string 'auto'. */
        function writeStored(mode) {
            try {
                if (mode === 'auto') {
                    window.localStorage.removeItem(STORAGE_KEY);

                    return;
                }

                window.localStorage.setItem(STORAGE_KEY, mode);
            } catch (error) {
                // Diabaikan: tema tetap berlaku di halaman ini, hanya tidak
                // terbawa ke halaman berikutnya.
            }
        }

        function systemMode() {
            return media.matches ? 'dark' : 'light';
        }

        /** Pasang atribut tema sesuai pilihan user. */
        function apply(mode) {
            document.documentElement.setAttribute(
                'data-bs-theme',
                mode === 'auto' ? systemMode() : mode
            );
        }

        /** Samakan ikon + label tombol dengan mode yang sedang aktif. */
        function render(mode) {
            var buttons = document.querySelectorAll('[data-theme-toggle]');

            for (var i = 0; i < buttons.length; i++) {
                var icons = buttons[i].querySelectorAll('[data-theme-icon]');

                for (var j = 0; j < icons.length; j++) {
                    icons[j].classList.toggle(
                        'theme-icon--active',
                        icons[j].getAttribute('data-theme-icon') === mode
                    );
                }

                var next = MODES[(MODES.indexOf(mode) + 1) % MODES.length];

                buttons[i].setAttribute(
                    'title',
                    'Tema: ' + LABELS[mode] + ' — klik untuk ' + LABELS[next]
                );

                buttons[i].setAttribute(
                    'aria-label',
                    'Ganti tema. Saat ini ' + LABELS[mode]
                        + '. Klik untuk ' + LABELS[next] + '.'
                );
            }
        }

        var current = readStored() || 'auto';

        apply(current);
        render(current);

        // Delegasi di level dokumen, bukan listener per tombol, supaya
        // tombol yang baru disisipkan lewat DOMParser (loadModalForm /
        // replaceModalForm) tetap tertangani dan tidak ada listener
        // yang menumpuk setiap kali form di-render ulang.
        document.addEventListener('click', function (event) {
            var button = event.target.closest('[data-theme-toggle]');

            if (!button) {
                return;
            }

            event.preventDefault();

            current = MODES[(MODES.indexOf(current) + 1) % MODES.length];

            writeStored(current);
            apply(current);
            render(current);
        });

        // Mode Auto: kalau user mengganti tema di level OS, halaman yang
        // sedang terbuka ikut berubah tanpa perlu reload. Pilihan manual
        // tidak boleh ditimpa di sini.
        function onSystemChange() {
            if (current === 'auto') {
                apply(current);
            }
        }

        if (typeof media.addEventListener === 'function') {
            media.addEventListener('change', onSystemChange);
        } else if (typeof media.addListener === 'function') {
            media.addListener(onSystemChange);
        }

        // Sinkron antar tab: event ini hanya chegada di tab LAIN, jadi tab
        // yang performing klik tidak akan terpanggil dua kali.
        window.addEventListener('storage', function (event) {
            if (event.key !== null && event.key !== STORAGE_KEY) {
                return;
            }

            current = readStored() || 'auto';

            apply(current);
            render(current);
        });
    })();
</script>