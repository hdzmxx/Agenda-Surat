<?php

/**
 * Ikon garis sederhana untuk tombol dan bilah alat. Pemakaian: helper('ui'); echo ui_icon('search');
 */
if (! function_exists('ui_icon')) {
    function ui_icon(string $nama): string
    {
        $jalur = [
            'search'   => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
            'plus'     => '<path d="M12 5v14M5 12h14"/>',
            'printer'  => '<path d="M7 8V3h10v5M7 17H5a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M7 14h10v7H7z"/>',
            'download' => '<path d="M12 4v11m0 0-4-4m4 4 4-4M5 20h14"/>',
            'upload'   => '<path d="M12 16V5m0 0-4 4m4-4 4 4M5 20h14"/>',
            'x'        => '<path d="M6 6l12 12M18 6 6 18"/>',
            'filter'   => '<path d="M4 6h16M7 12h10M10 18h4"/>',
        ];

        return '<svg class="ico" viewBox="0 0 24 24" aria-hidden="true">' . ($jalur[$nama] ?? '') . '</svg>';
    }
}

/**
 * Jumlah baris per halaman untuk daftar data. Pilihan pengguna (?per=) disimpan di sesi
 * sehingga berlaku untuk semua halaman daftar; tanpa pilihan dipakai nilai bawaan halaman.
 */
if (! function_exists('per_halaman')) {
    function per_halaman(int $bawaan = 15): int
    {
        $minta = service('request')->getGet('per');
        if (is_string($minta) && ctype_digit($minta) && (int) $minta > 0) {
            $n = min(500, (int) $minta);
            session()->set('per_halaman', $n);

            return $n;
        }
        $simpan = (int) session()->get('per_halaman');

        return $simpan > 0 ? $simpan : $bawaan;
    }
}
