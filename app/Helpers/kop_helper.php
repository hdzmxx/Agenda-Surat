<?php

if (! function_exists('kop_settings')) {
    function kop_settings(): array
    {
        $settings = [
            'pemerintah' => (string) env('KOP_PEMERINTAH', ''),
            'dinas'      => (string) env('KOP_DINAS', ''),
            'instansi'   => (string) env('INSTANSI', 'SEKOLAH'),
            'alamat'     => array_values(array_filter(array_map('trim', explode('|', (string) env('ALAMAT', ''))))),
            'logo'       => (string) env('LOGO', 'logo.png'),
            // Penandatangan surat: bawaan dari .env, dapat diubah di Pengaturan kop surat.
            'ttd_jabatan' => (string) env('TTD_JABATAN', 'Kepala Sekolah'),
            'ttd_nama'    => (string) env('TTD_NAMA', ''),
            'ttd_nip'     => (string) env('TTD_NIP', ''),
            'ttd_pangkat' => (string) env('TTD_PANGKAT', ''),
        ];
        $legacyLogo = WRITEPATH . 'kop-logo';
        if (is_file($legacyLogo)) {
            $name = trim((string) file_get_contents($legacyLogo));
            if (in_array($name, ['kop-logo.png', 'kop-logo.jpg'], true) && is_file(FCPATH . $name)) {
                $settings['logo'] = $name;
            }
        }
        $path = WRITEPATH . 'kop-settings.json';

        if (! is_file($path)) {
            return $settings;
        }

        $saved = json_decode((string) file_get_contents($path), true);
        if (! is_array($saved)) {
            throw new RuntimeException('Pengaturan kop surat tidak dapat dibaca: format file tidak valid.');
        }

        foreach (['pemerintah', 'dinas', 'instansi', 'ttd_jabatan', 'ttd_nama', 'ttd_nip', 'ttd_pangkat'] as $key) {
            if (isset($saved[$key]) && is_string($saved[$key])) {
                $settings[$key] = $saved[$key];
            }
        }
        if (isset($saved['alamat']) && is_array($saved['alamat'])) {
            $settings['alamat'] = array_values(array_filter($saved['alamat'], 'is_string'));
        }
        if (isset($saved['logo']) && in_array($saved['logo'], ['kop-logo.png', 'kop-logo.jpg'], true)
            && is_file(FCPATH . $saved['logo'])) {
            $settings['logo'] = $saved['logo'];
        }

        return $settings;
    }
}

if (! function_exists('ttd_settings')) {
    /** Penandatangan surat (kepala sekolah) dari Pengaturan kop surat. */
    function ttd_settings(): array
    {
        $s = kop_settings();

        return [
            'jabatan' => $s['ttd_jabatan'],
            'nama'    => $s['ttd_nama'],
            'nip'     => $s['ttd_nip'],
            'pangkat' => $s['ttd_pangkat'],
        ];
    }
}

if (! function_exists('kop_logo_path')) {
    function kop_logo_path(): string
    {
        return kop_settings()['logo'];
    }
}
