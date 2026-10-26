<?php

namespace App\Controllers;

use App\Models\SuratModel;
use App\Models\SuratDisposisiModel;
use App\Models\UserModel;
use App\Models\DokumenModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Files\File;

class Surat extends BaseController
{
    protected $helpers = ['form', 'tanggal'];

    public const STATUS = [
        'masuk'  => ['Belum diproses', 'Diproses', 'Selesai'],
        'keluar' => ['Draft', 'Terkirim', 'Diarsipkan'],
    ];
    public const SIFAT = ['Biasa', 'Penting', 'Segera', 'Rahasia'];
    public const JENIS = ['Undangan', 'Permohonan', 'Pemberitahuan', 'Surat tugas', 'Surat keterangan', 'Lainnya'];

    private function cek(string $arah): void
    {
        if (! in_array($arah, ['masuk', 'keluar'], true)) {
            throw PageNotFoundException::forPageNotFound();
        }
    }

    private function baris(string $arah, int $id): array
    {
        $r = (new SuratModel())->where('arah', $arah)->find($id);
        if (! $r) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $r;
    }

    private function tampil(string $view, string $arah, array $extra = [])
    {
        return view($view, $extra + [
            'arah'   => $arah,
            'status' => self::STATUS[$arah],
            'sifat'  => self::SIFAT,
            'jenis'  => self::JENIS,
        ]);
    }

    public function index(string $arah)
    {
        $this->cek($arah);
        $m     = new SuratModel();
        $f     = $this->request->getGet(['q', 'tahun', 'bulan', 'status']);
        [$sort, $dir] = SuratModel::urutan($this->request->getGet('sort'), $this->request->getGet('dir'));
        helper('ui');
        $rows  = $m->cari($arah, $f + ['sort' => $sort, 'dir' => $dir])->paginate(per_halaman(15));
        if ($arah === 'masuk') {
            $map = (new SuratDisposisiModel())->petaNama(array_column($rows, 'id'));
            foreach ($rows as &$row) {
                if (isset($map[$row['id']])) {
                    $row['disposisi'] = implode(', ', $map[$row['id']]);
                }
            }
            unset($row);
        }
        $pager = $m->pager;
        $pager->only(['q', 'tahun', 'bulan', 'status', 'sort', 'dir']);

        return $this->tampil('surat/index', $arah, [
            'title' => 'Surat ' . $arah, 'rows' => $rows, 'pager' => $pager, 'f' => $f, 'tahun' => $m->tahunList($arah), 'sort' => $sort, 'dir' => $dir,
        ]);
    }

    public function baru(string $arah)
    {
        $this->cek($arah);

        $extra = [
            'title' => 'Tambah surat ' . $arah, 'row' => null, 'action' => site_url("surat/$arah"),
            'next' => (new SuratModel())->nextNo($arah, (int) date('Y')),
        ];
        if ($arah === 'masuk') {
            $extra['guruUsers'] = (new UserModel())->where('role', 'guru')->orderBy('nama')->findAll();
            $extra['selectedGuruIds'] = [];
            $extra['disposisiCatatan'] = '';
        }

        return $this->tampil('surat/form', $arah, $extra);
    }

    public function ubah(string $arah, int $id)
    {
        $this->cek($arah);

        $extra = [
            'title' => 'Ubah surat ' . $arah, 'row' => $this->baris($arah, $id), 'action' => site_url("surat/$arah/$id"), 'next' => null,
        ];
        if ($arah === 'masuk') {
            $disposisi = (new SuratDisposisiModel())->untukSurat($id);
            $extra['guruUsers'] = (new UserModel())->where('role', 'guru')->orderBy('nama')->findAll();
            $extra['selectedGuruIds'] = array_map('intval', array_column($disposisi, 'user_id'));
            $extra['disposisiCatatan'] = $disposisi[0]['catatan'] ?? '';
            $extra['disposisiLama'] = $extra['row']['disposisi'];
        }

        return $this->tampil('surat/form', $arah, $extra);
    }

    public function lihat(string $arah, int $id)
    {
        $this->cek($arah);
        $row = $this->baris($arah, $id);
        $extra = ['title' => 'Detail surat', 'row' => $row];
        if ($arah === 'masuk') {
            $extra['disposisi'] = (new SuratDisposisiModel())->untukSurat($id);
            if ($extra['disposisi']) {
                $row['disposisi'] = implode(', ', array_column($extra['disposisi'], 'nama'));
                $extra['row'] = $row;
            }
        }

        return $this->tampil('surat/show', $arah, $extra);
    }

    /** Dipakai formulir untuk menampilkan nomor berikutnya (JSON). */
    public function nomor(string $arah)
    {
        $this->cek($arah);
        $tgl = (string) $this->request->getGet('tgl');
        $tgl = preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl) ? $tgl : date('Y-m-d');
        $no  = (new SuratModel())->nomorBerikut($arah, (int) substr($tgl, 0, 4));
        $nomor = '';
        if ($arah === 'keluar') {
            $m = new SuratModel();
            $nomor = $m->nomorSuratBerikutnya($tgl, SuratModel::formatNomor($no, $tgl));
        }

        return $this->response->setJSON([
            'no_agenda'   => $no,
            'nomor_surat' => $nomor,
        ]);
    }

    private function ambil(string $arah): array
    {
        $kolom = ['tgl_agenda', 'nomor_surat', 'pihak', 'perihal', 'sifat', 'lampiran', 'status', 'isi', 'keterangan'];
        $kolom = array_merge($kolom, $arah === 'masuk' ? ['tgl_surat'] : ['jenis', 'penandatangan']);
        $d     = [];
        foreach ($kolom as $c) {
            $v     = trim((string) $this->request->getPost($c));
            $d[$c] = $v === '' ? null : $v;
        }

        return $d;
    }

    private function aturan(string $arah, $file): array
    {
        $r = [
            'tgl_agenda'  => 'required|valid_date[Y-m-d]',
            'nomor_surat' => $arah === 'masuk' ? 'required|max_length[100]' : 'permit_empty|max_length[100]',
            'pihak'       => 'required|max_length[150]',
            'perihal'     => 'required|max_length[255]',
            'sifat'       => 'required|in_list[' . implode(',', self::SIFAT) . ']',
            'status'      => 'required|in_list[' . implode(',', self::STATUS[$arah]) . ']',
            'no_agenda'   => 'permit_empty|is_natural_no_zero',
        ];
        if ($arah === 'masuk') {
            $r['tgl_surat'] = 'permit_empty|valid_date[Y-m-d]';
        }
        if ($file && $file->getError() !== UPLOAD_ERR_NO_FILE) {
            $r['berkas'] = 'max_size[berkas,5120]|ext_in[berkas,pdf,jpg,jpeg,png]|mime_in[berkas,application/pdf,image/jpeg,image/png]';
        }

        return $r;
    }

    private function simpanBerkas($file, array &$d, ?array $lama = null): void
    {
        if ($file && $file->isValid() && ! $file->hasMoved()) {
            $nama = $file->getRandomName();
            $asli = $file->getClientName();
            $file->move(WRITEPATH . 'uploads/surat', $nama);
            if ($lama && $lama['berkas']) {
                @unlink(WRITEPATH . 'uploads/surat/' . $lama['berkas']);
            }
            $d['berkas']      = $nama;
            $d['berkas_asli'] = $asli;
        }
    }

    private function dataDisposisi(): ?array
    {
        $ids = $this->request->getPost('guru_ids') ?? [];
        if (! is_array($ids)) {
            return null;
        }
        $selectedIds = [];
        foreach ($ids as $id) {
            if (! is_string($id) && ! is_int($id)) {
                return null;
            }
            $validatedId = filter_var($id, FILTER_VALIDATE_INT);
            if ($validatedId === false || $validatedId < 1) {
                return null;
            }
            $selectedIds[] = $validatedId;
        }
        $ids = array_values(array_unique($selectedIds));
        $catatanPost = $this->request->getPost('catatan_disposisi');
        if ($catatanPost !== null && ! is_scalar($catatanPost)) {
            return null;
        }
        $catatan = trim((string) $catatanPost);
        if (mb_strlen($catatan) > 5000) {
            return null;
        }

        $users = [];
        if ($ids !== []) {
            $users = (new UserModel())->where('role', 'guru')->whereIn('id', $ids)->findAll();
            if (count($users) !== count($ids)) {
                return null;
            }
        }

        return ['ids' => $ids, 'catatan' => $catatan, 'nama' => array_column($users, 'nama')];
    }

    private function simpanDisposisi(int $suratId, array $data): bool
    {
        $db = db_connect();
        $db->transBegin();
        try {
            $db->table('surat_disposisi')->where('surat_id', $suratId)->delete();
            $model = new SuratDisposisiModel();
            foreach ($data['ids'] as $userId) {
                if (! $model->insert([
                    'surat_id'  => $suratId,
                    'user_id'   => $userId,
                    'catatan'   => $data['catatan'] === '' ? null : $data['catatan'],
                    'created_by'=> session('uid'),
                ])) {
                    $db->transRollback();

                    return false;
                }
            }
            if (! $db->transStatus()) {
                $db->transRollback();

                return false;
            }
            $db->transCommit();

            return true;
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Gagal menyimpan disposisi surat {id}: {message}', ['id' => $suratId, 'message' => $e->getMessage()]);

            return false;
        }
    }

    public function simpan(string $arah)
    {
        $this->cek($arah);
        $disposisi = $arah === 'masuk' ? $this->dataDisposisi() : null;
        if ($arah === 'masuk' && $disposisi === null) {
            return redirect()->back()->withInput()->with('error', 'Pilih guru yang valid dan pastikan catatan disposisi tidak melebihi 5.000 karakter.');
        }
        $m    = new SuratModel();
        $file = $this->request->getFile('berkas');
        $rules = $this->aturan($arah, $file);
        if ($arah === 'masuk') {
            $rules['catatan_disposisi'] = 'permit_empty|max_length[5000]';
        }
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $d          = $this->ambil($arah);
        $d['arah']  = $arah;
        if ($arah === 'masuk') {
            $d['disposisi'] = implode(', ', $disposisi['nama']);
        }
        $d['tahun'] = (int) substr($d['tgl_agenda'], 0, 4);
        $manual     = (int) $this->request->getPost('no_agenda');
        if ($manual > 0 && $m->where(['arah' => $arah, 'tahun' => $d['tahun'], 'no_agenda' => $manual])->first()) {
            return redirect()->back()->withInput()->with('error', 'Nomor agenda ' . $manual . ' sudah dipakai pada tahun ' . $d['tahun'] . '.');
        }
        $d['created_by'] = session()->get('uid');
        $this->simpanBerkas($file, $d);
        $no = $m->simpanOtomatis($d, $manual);
        if (! $no) {
            if (! empty($d['berkas'])) {
                @unlink(WRITEPATH . 'uploads/surat/' . $d['berkas']);
            }

            return redirect()->back()->withInput()->with('error', 'Nomor agenda gagal dibuat atau sudah dipakai. Silakan simpan ulang.');
        }
        if ($arah === 'masuk') {
            $saved = $m->where(['arah' => $arah, 'tahun' => $d['tahun'], 'no_agenda' => $no])->first();
            if (! $saved || ! $this->simpanDisposisi((int) $saved['id'], $disposisi)) {
                return redirect()->to(site_url("surat/$arah"))->with('error', 'Surat tersimpan, tetapi disposisi guru gagal disimpan. Buka surat dan simpan ulang disposisinya.');
            }
        }

        return redirect()->to(site_url("surat/$arah"))->with('ok', 'Surat berhasil disimpan dengan nomor agenda ' . no_agenda($no) . '.');
    }

    public function perbarui(string $arah, int $id)
    {
        $this->cek($arah);
        $lama = $this->baris($arah, $id);
        $disposisiSebelumnya = $arah === 'masuk' ? (new SuratDisposisiModel())->untukSurat($id) : [];
        $disposisi = $arah === 'masuk' ? $this->dataDisposisi() : null;
        if ($arah === 'masuk' && $disposisi === null) {
            return redirect()->back()->withInput()->with('error', 'Pilih guru yang valid dan pastikan catatan disposisi tidak melebihi 5.000 karakter.');
        }
        $m    = new SuratModel();
        $file = $this->request->getFile('berkas');
        $rules = $this->aturan($arah, $file);
        if ($arah === 'masuk') {
            $rules['catatan_disposisi'] = 'permit_empty|max_length[5000]';
        }
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $d          = $this->ambil($arah);
        $d['tahun'] = (int) substr($d['tgl_agenda'], 0, 4);
        if ($arah === 'masuk') {
            $d['disposisi'] = $disposisi['nama']
                ? implode(', ', $disposisi['nama'])
                : ($disposisiSebelumnya ? null : $lama['disposisi']);
        }
        $no         = (int) $this->request->getPost('no_agenda') ?: ($d['tahun'] === (int) $lama['tahun'] ? (int) $lama['no_agenda'] : $m->nextNo($arah, $d['tahun']));
        if ($m->where(['arah' => $arah, 'tahun' => $d['tahun'], 'no_agenda' => $no])->where('id !=', $id)->first()) {
            return redirect()->back()->withInput()->with('error', 'Nomor agenda ' . $no . ' sudah dipakai pada tahun ' . $d['tahun'] . '.');
        }
        $d['no_agenda'] = $no;
        if ($arah === 'keluar' && empty($d['nomor_surat'])) {
            $d['nomor_surat'] = SuratModel::formatNomor($no, $d['tgl_agenda']);
        }
        if ($this->request->getPost('hapus_berkas') && $lama['berkas']) {
            @unlink(WRITEPATH . 'uploads/surat/' . $lama['berkas']);
            $d['berkas'] = $d['berkas_asli'] = null;
        }
        $this->simpanBerkas($file, $d, $lama);
        $m->update($id, $d);
        if ($arah === 'masuk' && ! $this->simpanDisposisi($id, $disposisi)) {
            return redirect()->to(site_url("surat/$arah/$id"))->with('error', 'Perubahan surat tersimpan, tetapi disposisi gagal diperbarui.');
        }

        return redirect()->to(site_url("surat/$arah/$id"))->with('ok', 'Perubahan disimpan.');
    }

    public function hapus(string $arah, int $id)
    {
        $this->cek($arah);
        $r = $this->baris($arah, $id);
        if ($r['berkas']) {
            @unlink(WRITEPATH . 'uploads/surat/' . $r['berkas']);
        }
        (new SuratModel())->delete($id);

        return redirect()->to(site_url("surat/$arah"))->with('ok', 'Surat dihapus.');
    }

    public function berkas(string $arah, int $id)
    {
        $this->cek($arah);
        $r = $this->baris($arah, $id);
        $p = WRITEPATH . 'uploads/surat/' . ($r['berkas'] ?? '');
        if (! $r['berkas'] || ! is_file($p)) {
            throw PageNotFoundException::forPageNotFound();
        }
        $mode = $this->request->getGet('unduh') ? 'attachment' : 'inline';

        return $this->response
            ->setHeader('Content-Type', (new File($p))->getMimeType())
            ->setHeader('Content-Disposition', $mode . '; filename="' . addslashes($r['berkas_asli'] ?? $r['berkas']) . '"')
            ->setBody(file_get_contents($p));
    }

    public function cetak(string $arah)
    {
        $this->cek($arah);
        $f    = $this->request->getGet(['q', 'tahun', 'bulan', 'status']);
        $rows = (new SuratModel())->cari($arah, $f)->findAll();
        if ($arah === 'masuk') {
            $map = (new SuratDisposisiModel())->petaNama(array_column($rows, 'id'));
            foreach ($rows as &$row) {
                if (isset($map[$row['id']])) {
                    $row['disposisi'] = implode(', ', $map[$row['id']]);
                }
            }
            unset($row);
        } else {
            $map = (new DokumenModel())->daftarTujuanTugas(array_column($rows, 'id'));
            foreach ($rows as &$row) {
                if (isset($map[$row['id']])) {
                    $row['tujuan_list'] = $map[$row['id']];
                }
            }
            unset($row);
        }

        return $this->tampil('surat/cetak', $arah, [
            'title' => 'Cetak agenda surat ' . $arah,
            'rows' => $rows,
            'f' => $f,
        ]);
    }
}