<?php

namespace App\Controllers;

class Kop extends BaseController
{
    public function index()
    {
        if (session('role') !== 'admin') {
            return redirect()->to(site_url('/'))->with('error', 'Hanya admin yang dapat mengubah pengaturan kop surat.');
        }

        helper('kop');

        return view('kop/pengaturan', [
            'title'    => 'Pengaturan kop surat',
            'settings' => kop_settings(),
        ]);
    }

    public function upload()
    {
        if (session('role') !== 'admin') {
            return redirect()->to(site_url('/'))->with('error', 'Hanya admin yang dapat mengubah pengaturan kop surat.');
        }

        $file = $this->request->getFile('logo');
        $rules = [
            'pemerintah' => 'permit_empty|max_length[200]',
            'dinas'      => 'permit_empty|max_length[200]',
            'instansi'   => 'permit_empty|max_length[200]',
            'alamat'     => 'permit_empty|max_length[2000]',
            'ttd_jabatan' => 'permit_empty|max_length[100]',
            'ttd_nama'    => 'permit_empty|max_length[150]',
            'ttd_nip'     => 'permit_empty|max_length[60]',
            'ttd_pangkat' => 'permit_empty|max_length[100]',
        ];
        if ($file && $file->getError() !== UPLOAD_ERR_NO_FILE) {
            $rules['logo'] = 'uploaded[logo]|max_size[logo,2048]|ext_in[logo,png,jpg,jpeg]|mime_in[logo,image/png,image/jpeg]';
        }

        if (! $this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        helper('kop');
        $settings = kop_settings();
        foreach (['pemerintah', 'dinas', 'instansi', 'ttd_jabatan', 'ttd_nama', 'ttd_nip', 'ttd_pangkat'] as $field) {
            $value = $this->request->getPost($field);
            if ($value !== null) {
                $settings[$field] = trim((string) $value);
            }
        }
        $alamatPost = $this->request->getPost('alamat');
        if ($alamatPost !== null) {
            $alamat = preg_split('/\R/', (string) $alamatPost);
            $settings['alamat'] = array_values(array_filter(array_map('trim', $alamat ?: []), static fn ($line) => $line !== ''));
        }

        if ($file && $file->getError() !== UPLOAD_ERR_NO_FILE) {
            $mime = $file->getMimeType();
            $name = match ($mime) {
                'image/png'  => 'kop-logo.png',
                'image/jpeg' => 'kop-logo.jpg',
                default      => null,
            };
            if ($name === null) {
                return redirect()->back()->with('error', 'Format logo harus PNG atau JPG.');
            }

            try {
                $file->move(FCPATH, $name, true);
            } catch (\Throwable $e) {
                log_message('error', 'Gagal menyimpan logo kop surat: {message}', ['message' => $e->getMessage()]);

                return redirect()->back()->with('error', 'Gagal menyimpan file logo.');
            }
            $settings['logo'] = $name;
        }

        try {
            $json = json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return redirect()->back()->with('error', 'Isi pengaturan kop surat mengandung karakter yang tidak valid.');
        }
        try {
            $saved = file_put_contents(WRITEPATH . 'kop-settings.json', $json, LOCK_EX);
        } catch (\Throwable $e) {
            log_message('error', 'Gagal menyimpan pengaturan kop surat: {message}', ['message' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Gagal menyimpan pengaturan kop surat. Periksa izin folder writable.');
        }
        if ($saved === false) {
            return redirect()->back()->with('error', 'Gagal menyimpan pengaturan kop surat. Periksa izin folder writable.');
        }

        return redirect()->to(site_url('kop/pengaturan'))->with('ok', 'Pengaturan kop surat berhasil disimpan.');
    }
}
