<?php

if (! function_exists('website_settings')) {
    function website_settings(): array
    {
        $themes = website_theme_options();
        $settings = [
            'mode' => 'light',
            'theme' => 'custom',
            'primary_color' => '#198754',
            'accent_color' => '#C66A24',
            'accent_soft' => '#FFF4E5',
            'accent_border' => '#F1DDC6',
            'accent_dark' => '#F2B36F',
            'accent_soft_dark' => '#3A3026',
            'accent_border_dark' => '#594531',
            'app_name' => 'Agenda Surat',
            'browser_title' => 'Agenda Surat',
            'logo' => null,
            'favicon' => null,
        ];
        $path = WRITEPATH . 'website-settings.json';

        if (! is_file($path)) {
            return $settings;
        }

        $saved = json_decode((string) file_get_contents($path), true);
        if (! is_array($saved)) {
            throw new RuntimeException('Pengaturan website tidak dapat dibaca: format file tidak valid.');
        }

        if (isset($saved['mode']) && in_array($saved['mode'], ['light', 'dark'], true)) {
            $settings['mode'] = $saved['mode'];
        }
        if (isset($saved['theme']) && is_string($saved['theme']) && isset($themes[$saved['theme']])) {
            $settings['theme'] = $saved['theme'];
            foreach (['accent_color', 'accent_soft', 'accent_border', 'accent_dark', 'accent_soft_dark', 'accent_border_dark'] as $color) {
                // Tema kustom memakai warna yang disimpan pengguna; tema preset memakai warna bawaannya.
                $punyaKustom = $saved['theme'] === 'custom'
                    && isset($saved[$color]) && is_string($saved[$color])
                    && preg_match('/^#[0-9a-fA-F]{6}$/', $saved[$color]);
                $settings[$color] = $punyaKustom ? strtoupper($saved[$color]) : $themes[$saved['theme']][$color];
            }
        }
        if (isset($saved['primary_color']) && is_string($saved['primary_color'])
            && preg_match('/^#[0-9a-fA-F]{6}$/', $saved['primary_color'])) {
            $settings['primary_color'] = strtoupper($saved['primary_color']);
        }
        if (isset($saved['theme']) && is_string($saved['theme']) && isset($themes[$saved['theme']])) {
            $settings['primary_color'] = $saved['theme'] === 'custom'
                ? $settings['primary_color']
                : $themes[$saved['theme']]['primary_color'];
        }
        if (isset($saved['app_name']) && is_string($saved['app_name']) && trim($saved['app_name']) !== '') {
            $settings['app_name'] = trim($saved['app_name']);
        }
        if (isset($saved['browser_title']) && is_string($saved['browser_title']) && trim($saved['browser_title']) !== '') {
            $settings['browser_title'] = trim($saved['browser_title']);
        } else {
            $settings['browser_title'] = $settings['app_name'];
        }
        if (isset($saved['logo']) && is_string($saved['logo'])
            && preg_match('/^website-logo-[a-zA-Z0-9]+\.(?:png|jpe?g)$/', $saved['logo'])
            && is_file(WRITEPATH . 'uploads/website-logo/' . $saved['logo'])) {
            $settings['logo'] = $saved['logo'];
        }
        if (isset($saved['favicon']) && is_string($saved['favicon'])
            && preg_match('/^website-favicon-[a-zA-Z0-9]+\.png$/', $saved['favicon'])
            && is_file(WRITEPATH . 'uploads/website-logo/' . $saved['favicon'])) {
            $settings['favicon'] = $saved['favicon'];
        }

        return $settings;
    }
}

if (! function_exists('website_theme_options')) {
    function website_theme_options(): array
    {
        return [
            'teal-orange' => [
                'name' => 'Teal & Oranye',
                'primary_color' => '#0F766E',
                'accent_color' => '#C66A24',
                'accent_soft' => '#FFF4E5',
                'accent_border' => '#F1DDC6',
                'accent_dark' => '#F2B36F',
                'accent_soft_dark' => '#3A3026',
                'accent_border_dark' => '#594531',
            ],
            'blue-gold' => [
                'name' => 'Biru & Emas',
                'primary_color' => '#3157A4',
                'accent_color' => '#A66C19',
                'accent_soft' => '#FFF6E8',
                'accent_border' => '#F1E1C5',
                'accent_dark' => '#F1C77F',
                'accent_soft_dark' => '#3B3225',
                'accent_border_dark' => '#55452D',
            ],
            'forest-coral' => [
                'name' => 'Hijau Hutan & Coral',
                'primary_color' => '#2F6B4F',
                'accent_color' => '#B94F43',
                'accent_soft' => '#FFF0ED',
                'accent_border' => '#F0D8D3',
                'accent_dark' => '#F09B8F',
                'accent_soft_dark' => '#3B2928',
                'accent_border_dark' => '#59403D',
            ],
            'plum-amber' => [
                'name' => 'Plum & Amber',
                'primary_color' => '#6B4E8A',
                'accent_color' => '#A66C19',
                'accent_soft' => '#FFF6E8',
                'accent_border' => '#F1E1C5',
                'accent_dark' => '#F1C77F',
                'accent_soft_dark' => '#3B3225',
                'accent_border_dark' => '#55452D',
            ],
            'slate-cyan' => [
                'name' => 'Slate & Cyan',
                'primary_color' => '#475569',
                'accent_color' => '#0E7490',
                'accent_soft' => '#E9F8FB',
                'accent_border' => '#CBEAF0',
                'accent_dark' => '#70D2E5',
                'accent_soft_dark' => '#20383D',
                'accent_border_dark' => '#34545B',
            ],
            'custom' => [
                'name' => 'Warna kustom',
                'primary_color' => null,
                'accent_color' => '#C66A24',
                'accent_soft' => '#FFF4E5',
                'accent_border' => '#F1DDC6',
                'accent_dark' => '#F2B36F',
                'accent_soft_dark' => '#3A3026',
                'accent_border_dark' => '#594531',
            ],
        ];
    }
}

if (! function_exists('website_color_rgb')) {
    function website_color_rgb(string $hex): string
    {
        $hex = ltrim($hex, '#');

        return hexdec(substr($hex, 0, 2)) . ', ' . hexdec(substr($hex, 2, 2)) . ', ' . hexdec(substr($hex, 4, 2));
    }
}

if (! function_exists('website_contrast_color')) {
    function website_contrast_color(string $hex): string
    {
        $hex = ltrim($hex, '#');
        $channels = [0, 2, 4];
        $luminance = 0;
        foreach ($channels as $index => $offset) {
            $value = hexdec(substr($hex, $offset, 2)) / 255;
            $value = $value <= 0.04045 ? $value / 12.92 : (($value + 0.055) / 1.055) ** 2.4;
            $luminance += $value * [0.2126, 0.7152, 0.0722][$index];
        }

        return $luminance > 0.179 ? '#000000' : '#FFFFFF';
    }
}
