<?php

namespace App\Controllers;

use CodeIgniter\Files\File;

class WebsiteSettings extends BaseController
{
    private function adminOnly()
    {
        if (session('role') !== 'admin') {
            return redirect()->to(site_url('/'))->with('error', 'Hanya admin yang dapat mengubah pengaturan website.');
        }

        return null;
    }

    public function index()
    {
        if ($redirect = $this->adminOnly()) {
            return $redirect;
        }

        helper('website');

        return view('website/pengaturan', [
            'title' => 'Pengaturan website',
            'settings' => website_settings(),
            'themes' => website_theme_options(),
        ]);
    }

    public function logo()
    {
        helper('website');
        $name = website_settings()['logo'];
        if ($name === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $path = WRITEPATH . 'uploads/website-logo/' . $name;

        return $this->kirimGambar($path, $name);
    }

    public function favicon()
    {
        helper('website');
        $name = website_settings()['favicon'];
        if ($name === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $this->kirimGambar(WRITEPATH . 'uploads/website-logo/' . $name, $name);
    }

    private function kirimGambar(string $path, string $name)
    {
        if (! is_file($path)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $file = new File($path);

        return $this->response
            ->setHeader('Content-Type', $file->getMimeType())
            ->setHeader('Content-Disposition', 'inline; filename="' . addslashes($name) . '"')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setBody(file_get_contents($path));
    }

    public function simpan()
    {
        if ($redirect = $this->adminOnly()) {
            return $redirect;
        }

        // --- 1. Ambil semua input ---
        $mode = $this->request->getPost('mode');
        $theme = $this->request->getPost('theme');
        $appName = $this->request->getPost('app_name');
        $browserTitle = $this->request->getPost('browser_title');
        $removeLogo = $this->request->getPost('hapus_logo');
        $removeFavicon = $this->request->getPost('hapus_favicon');
        
        // Ambil input semua komponen warna kustom
        $colors = [
            'primary_color'      => $this->request->getPost('primary_color'),
            'accent_color'       => $this->request->getPost('accent_color'),
            'accent_soft'        => $this->request->getPost('accent_soft'),
            'accent_border'      => $this->request->getPost('accent_border'),
            'accent_dark'        => $this->request->getPost('accent_dark'),
            'accent_soft_dark'   => $this->request->getPost('accent_soft_dark'),
            'accent_border_dark' => $this->request->getPost('accent_border_dark'),
        ];

        // Validasi tipe data dasar string
        $inputs = [$mode, $theme, $appName, $browserTitle, $removeLogo, $removeFavicon, ...array_values($colors)];
        foreach ($inputs as $val) {
            if ($val !== null && ! is_string($val)) {
                return redirect()->back()->withInput()->with('error', 'Nilai pengaturan website tidak valid.');
            }
        }

        $logo = $this->request->getFile('logo');
        $favicon = $this->request->getFile('favicon');
        helper('website');
        $themes = website_theme_options();
        
        $rules = [
            'mode' => 'required|in_list[light,dark]',
            'theme' => 'required|in_list[' . implode(',', array_keys($themes)) . ']',
            'app_name' => 'required|max_length[100]',
            'browser_title' => 'required|max_length[100]',
            'hapus_logo' => 'permit_empty|in_list[1]',
            'hapus_favicon' => 'permit_empty|in_list[1]',
        ];

        // --- 2. Validasi 7 warna jika tema Kustom dipilih ---
        if ($theme === 'custom') {
            $hexRegex = 'required|regex_match[/^#[0-9a-fA-F]{6}$/i]';
            $rules['primary_color']      = ['label' => 'Warna utama', 'rules' => $hexRegex];
            $rules['accent_color']       = ['label' => 'Warna aksen', 'rules' => $hexRegex];
            $rules['accent_soft']        = ['label' => 'Warna aksen soft', 'rules' => $hexRegex];
            $rules['accent_border']      = ['label' => 'Warna aksen border', 'rules' => $hexRegex];
            $rules['accent_dark']        = ['label' => 'Warna aksen gelap', 'rules' => $hexRegex];
            $rules['accent_soft_dark']   = ['label' => 'Warna aksen soft gelap', 'rules' => $hexRegex];
            $rules['accent_border_dark'] = ['label' => 'Warna aksen border gelap', 'rules' => $hexRegex];
        }

        if (trim((string) $appName) === '') return redirect()->back()->withInput()->with('error', 'Nama aplikasi tidak boleh kosong.');
        if (trim((string) $browserTitle) === '') return redirect()->back()->withInput()->with('error', 'Judul tab browser tidak boleh kosong.');

        if ($logo && $logo->getError() !== UPLOAD_ERR_NO_FILE) {
            $rules['logo'] = 'uploaded[logo]|max_size[logo,2048]|ext_in[logo,png,jpg,jpeg]|mime_in[logo,image/png,image/jpeg]';
        }
        if ($favicon && $favicon->getError() !== UPLOAD_ERR_NO_FILE) {
            $rules['favicon'] = 'uploaded[favicon]|max_size[favicon,512]|ext_in[favicon,png]|mime_in[favicon,image/png]';
        }
        
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // --- 3. Proses File Gambar ---
        $oldLogo = website_settings()['logo'];
        $oldFavicon = website_settings()['favicon'];
        $newLogo = null;
        $newFavicon = null;
        $directory = WRITEPATH . 'uploads/website-logo';
        
        if ($logo && $logo->getError() !== UPLOAD_ERR_NO_FILE) {
            if (! is_dir($directory) && ! mkdir($directory, 0750, true) && ! is_dir($directory)) {
                return redirect()->back()->withInput()->with('error', 'Folder penyimpanan logo tidak dapat dibuat.');
            }
            $extension = match ($logo->getMimeType()) {
                'image/png' => 'png',
                'image/jpeg' => 'jpg',
                default => null,
            };
            if ($extension === null) return redirect()->back()->withInput()->with('error', 'Logo harus berupa gambar PNG atau JPG.');
            $newLogo = 'website-logo-' . bin2hex(random_bytes(12)) . '.' . $extension;
            try {
                $logo->move($directory, $newLogo);
            } catch (\Throwable $e) {
                log_message('error', 'Gagal menyimpan logo: {message}', ['message' => $e->getMessage()]);
                return redirect()->back()->withInput()->with('error', 'Gagal menyimpan file logo website.');
            }
        }
        
        if ($favicon && $favicon->getError() !== UPLOAD_ERR_NO_FILE) {
            if (! is_dir($directory) && ! mkdir($directory, 0750, true) && ! is_dir($directory)) {
                if ($newLogo !== null) $this->hapusFileLogo($newLogo);
                return redirect()->back()->withInput()->with('error', 'Folder penyimpanan ikon website tidak dapat dibuat.');
            }
            $newFavicon = 'website-favicon-' . bin2hex(random_bytes(12)) . '.png';
            try {
                $favicon->move($directory, $newFavicon);
            } catch (\Throwable $e) {
                if ($newLogo !== null) $this->hapusFileLogo($newLogo);
                log_message('error', 'Gagal menyimpan favicon: {message}', ['message' => $e->getMessage()]);
                return redirect()->back()->withInput()->with('error', 'Gagal menyimpan ikon tab browser.');
            }
        }

        // --- 4. Tentukan warna yang akan disimpan ---
        $settings = [
            'mode'               => $mode,
            'theme'              => $theme,
            'app_name'           => trim($appName),
            'browser_title'      => trim($browserTitle),
            'logo'               => $newLogo ?? ($removeLogo === '1' ? null : $oldLogo),
            'favicon'            => $newFavicon ?? ($removeFavicon === '1' ? null : $oldFavicon),
        ];

        // Jika custom, simpan semua palet dari input. Jika tidak, ambil dari helper.
        $colorKeys = ['primary_color', 'accent_color', 'accent_soft', 'accent_border', 'accent_dark', 'accent_soft_dark', 'accent_border_dark'];
        foreach ($colorKeys as $k) {
            $settings[$k] = $theme === 'custom' ? strtoupper((string) $colors[$k]) : $themes[$theme][$k];
        }

        // --- 5. Tulis ke JSON ---
        try {
            $json = json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            $saved = file_put_contents(WRITEPATH . 'website-settings.json', $json, LOCK_EX);
        } catch (\Throwable $e) {
            if ($newLogo !== null) $this->hapusFileLogo($newLogo);
            if ($newFavicon !== null) $this->hapusFileLogo($newFavicon);
            log_message('error', 'Gagal menyimpan pengaturan: {message}', ['message' => $e->getMessage()]);
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan pengaturan website. Periksa izin folder writable.');
        }

        if ($saved === false) {
            if ($newLogo !== null) $this->hapusFileLogo($newLogo);
            if ($newFavicon !== null) $this->hapusFileLogo($newFavicon);
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan pengaturan website. Periksa izin folder writable.');
        }

        if ($oldLogo !== null && $oldLogo !== $settings['logo']) $this->hapusFileLogo($oldLogo);
        if ($oldFavicon !== null && $oldFavicon !== $settings['favicon']) $this->hapusFileLogo($oldFavicon);

        return redirect()->to(site_url('website/pengaturan'))->with('ok', 'Pengaturan website berhasil disimpan.');
    }

    private function hapusFileLogo(string $name): void
    {
        $path = WRITEPATH . 'uploads/website-logo/' . $name;
        if (is_file($path) && ! unlink($path)) {
            log_message('error', 'Gagal membersihkan file logo website {name}.', ['name' => $name]);
        }
    }
}
