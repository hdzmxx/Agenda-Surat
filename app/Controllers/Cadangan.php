<?php

namespace App\Controllers;

use App\Libraries\DbBackup;

/** Halaman cadangan database (khusus admin). */
class Cadangan extends BaseController
{
    protected $helpers = ['form', 'tanggal', 'ui'];

    private function bukanAdmin()
    {
        if (session('role') !== 'admin') {
            return redirect()->to(site_url('/'))->with('error', 'Hanya admin yang dapat mengelola cadangan database.');
        }

        return null;
    }

    public function index()
    {
        if ($r = $this->bukanAdmin()) {
            return $r;
        }

        return view('cadangan/index', [
            'title'      => 'Cadangan database',
            'berkas'     => DbBackup::daftar(),
            'pengaturan' => DbBackup::pengaturan(),
            'frekuensi'  => DbBackup::FREKUENSI,
            'gzip'       => function_exists('gzopen'),
        ]);
    }

    public function buat()
    {
        if ($r = $this->bukanAdmin()) {
            return $r;
        }
        try {
            $nama = DbBackup::buat('manual');
        } catch (\Throwable $e) {
            return redirect()->to(site_url('cadangan'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('cadangan'))->with('ok', 'Cadangan dibuat: ' . $nama);
    }

    public function pengaturan()
    {
        if ($r = $this->bukanAdmin()) {
            return $r;
        }
        DbBackup::simpanPengaturan((string) $this->request->getPost('frekuensi'), (int) $this->request->getPost('simpan'));

        return redirect()->to(site_url('cadangan'))->with('ok', 'Pengaturan cadangan otomatis disimpan.');
    }

    public function unduh(string $nama)
    {
        if ($r = $this->bukanAdmin()) {
            return $r;
        }
        $path = DbBackup::path($nama);
        if ($path === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $this->response->download($path, null)->setFileName($nama);
    }

    /** Halaman konfirmasi sebelum memulihkan. */
    public function pulihkan(string $nama)
    {
        if ($r = $this->bukanAdmin()) {
            return $r;
        }
        $berkas = null;
        foreach (DbBackup::daftar() as $b) {
            if ($b['nama'] === $nama) {
                $berkas = $b;
            }
        }
        if ($berkas === null) {
            return redirect()->to(site_url('cadangan'))->with('error', 'Berkas cadangan tidak ditemukan.');
        }

        return view('cadangan/pulihkan', ['title' => 'Pulihkan database', 'berkas' => $berkas]);
    }

    public function pulihkanProses(string $nama)
    {
        if ($r = $this->bukanAdmin()) {
            return $r;
        }
        if (trim((string) $this->request->getPost('konfirmasi')) !== 'PULIHKAN') {
            return redirect()->to(site_url('cadangan/pulihkan/' . rawurlencode($nama)))->with('error', 'Ketik PULIHKAN (huruf kapital) untuk melanjutkan.');
        }
        try {
            $hasil = DbBackup::pulihkan($nama);
        } catch (\Throwable $e) {
            return redirect()->to(site_url('cadangan'))->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('cadangan'))->with('ok', 'Database dipulihkan dari ' . $nama . ' (' . $hasil['pernyataan'] . ' pernyataan). Cadangan pengaman: ' . $hasil['pengaman'] . '.');
    }

    /** Mengunggah berkas .sql / .sql.gz lalu menuju halaman konfirmasi pemulihan. */
    public function unggah()
    {
        if ($r = $this->bukanAdmin()) {
            return $r;
        }
        $f = $this->request->getFile('berkas');
        if (! $f || ! $f->isValid() || $f->hasMoved()) {
            return redirect()->to(site_url('cadangan'))->with('error', 'Pilih berkas .sql atau .sql.gz terlebih dahulu.');
        }
        $asli = strtolower($f->getClientName());
        $gz   = str_ends_with($asli, '.sql.gz');
        if (! $gz && ! str_ends_with($asli, '.sql')) {
            return redirect()->to(site_url('cadangan'))->with('error', 'Format harus .sql atau .sql.gz.');
        }
        $nama = 'unggahan-' . date('Ymd-His') . '-berkas.sql' . ($gz ? '.gz' : '');
        $f->move(DbBackup::folder(), $nama);
        $path = DbBackup::path($nama);
        if ($path === null || ! DbBackup::tampakSeperti($path)) {
            DbBackup::hapus($nama);

            return redirect()->to(site_url('cadangan'))->with('error', 'Berkas tidak terlihat seperti cadangan database (tidak ada CREATE TABLE/INSERT INTO).');
        }

        return redirect()->to(site_url('cadangan/pulihkan/' . rawurlencode($nama)));
    }

    public function hapus(string $nama)
    {
        if ($r = $this->bukanAdmin()) {
            return $r;
        }
        $ok = DbBackup::hapus($nama);

        return redirect()->to(site_url('cadangan'))->with($ok ? 'ok' : 'error', $ok ? 'Cadangan dihapus.' : 'Berkas cadangan tidak ditemukan.');
    }
}
