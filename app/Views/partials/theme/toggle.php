<?php

/**
 * Tombol pengubah tema: siklus Ikuti Sistem -> Terang -> Gelap.
 *
 * Dipakai sebagai satu tombol, bukan dropdown, karena landing page '/'
 * tidak memuat Bootstrap JS sama sekali sehingga data-bs-toggle="dropdown"
 * tidak akan berfungsi di sana. Dengan satu tombol, implementasi yang
 * sama bisa dipakai di keempat kelompok halaman tanpa cabang kondisi.
 *
 * Hanya ikon mode yang sedang aktif yang ditampilkan; pergantian ikon
 * ditangani partials/theme/script.php lewat kelas .theme-icon--active.
 *
 * $themeSize = 'sm' untuk navbar yang lebih padat.
 */
$themeSize = $themeSize ?? '';

?>
<button
    type="button"
    class="theme-toggle<?= $themeSize === 'sm' ? ' theme-toggle--sm' : '' ?>"
    data-theme-toggle
    title="Tema"
    aria-label="Ganti tema">

    <svg
        class="theme-icon"
        data-theme-icon="auto"
        viewBox="0 0 16 16"
        fill="currentColor"
        aria-hidden="true">
        <path
            d="M8 15A7 7 0 1 0 8 1v14zm0 1A8 8 0 1 1 8 0a8 8 0 0 1 0 16z" />
    </svg>

    <svg
        class="theme-icon"
        data-theme-icon="light"
        viewBox="0 0 16 16"
        fill="currentColor"
        aria-hidden="true">
        <path
            d="M8 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6zm0 1a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0zm-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707.708zm9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707zM4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z" />
    </svg>

    <svg
        class="theme-icon"
        data-theme-icon="dark"
        viewBox="0 0 16 16"
        fill="currentColor"
        aria-hidden="true">
        <path
            d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 0 1 .81.316.733.733 0 0 1-.031.893A8.349 8.349 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 0 1 6 .278z" />
    </svg>

</button>