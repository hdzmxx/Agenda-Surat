<?php

namespace App\Controllers;

use App\Models\SuratDisposisiModel;
use CodeIgniter\Files\File;
use CodeIgniter\Exceptions\PageNotFoundException;

class Disposisi extends BaseController
{
    protected $helpers = ['form', 'tanggal'];

    private function aksesGuru(): int
    {
        $userId = (int) session('uid');
        if (session('role') !== 'guru' || $userId < 1) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $userId;
    }

    private function baris(int $id): array
    {
        $row = db_connect()->table('surat_disposisi d')
            ->select('d.id AS disposisi_id, d.catatan, d.created_at AS disposisi_at, s.*')
            ->join('surat s', 's.id = d.surat_id')
            ->where('d.id', $id)
            ->where('d.user_id', $this->aksesGuru())
            ->get()->getRowArray();
        if (! $row) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $row;
    }

    public function index()
    {
        $this->aksesGuru();

        return view('disposisi/index', [
            'title' => 'Disposisi saya',
            'rows'  => (new SuratDisposisiModel())->untukGuru((int) session('uid')),
        ]);
    }

    public function lihat(int $id)
    {
        return view('disposisi/show', ['title' => 'Detail disposisi', 'row' => $this->baris($id)]);
    }

    public function berkas(int $id)
    {
        $row = $this->baris($id);
        $path = WRITEPATH . 'uploads/surat/' . ($row['berkas'] ?? '');
        if (! $row['berkas'] || ! is_file($path)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $mode = $this->request->getGet('unduh') ? 'attachment' : 'inline';

        return $this->response
            ->setHeader('Content-Type', (new File($path))->getMimeType())
            ->setHeader('Content-Disposition', $mode . '; filename="' . addslashes($row['berkas_asli'] ?? $row['berkas']) . '"')
            ->setBody(file_get_contents($path));
    }
}
