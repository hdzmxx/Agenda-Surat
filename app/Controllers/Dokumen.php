<?php

namespace App\Controllers;

use App\Libraries\JenisSurat;
use App\Models\DokumenModel;
use App\Models\GuruModel;
use App\Models\SiswaModel;
use App\Models\SuratModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Dokumen extends BaseController
{
    protected $helpers = ['form', 'tanggal'];

    /** Kolom yang boleh dipakai untuk mengurutkan daftar (kunci = parameter ?sort=). */
    private const URUTAN = [
        'agenda'  => ['surat.tahun', 'surat.no_agenda'],
        'jenis'   => ['dokumen.jenis'],
        'nomor'   => ['surat.nomor_surat'],
        'perihal' => ['surat.perihal'],
        'tgl'     => ['surat.tgl_agenda'],
    ];

    private function cekJenis(string $j): void
    {
        if (! isset(JenisSurat::DAFTAR[$j])) {
            throw PageNotFoundException::forPageNotFound();
        }
    }

    private function muat(int $id): array
    {
        $r = (new DokumenModel())->find($id);
        if (! $r) {
            throw PageNotFoundException::forPageNotFound();
        }
        $r['d'] = json_decode($r['data'], true) ?: [];

        return $r;
    }

    public function index()
    {
        $m  = new DokumenModel();
        $q  = trim((string) $this->request->getGet('q'));
        $j  = (string) $this->request->getGet('jenis');
        $m->select('dokumen.*, surat.nomor_surat, surat.perihal, surat.tgl_agenda, surat.no_agenda')
            ->join('surat', 'surat.id = dokumen.surat_id', 'left');
        if ($j !== '') {
            $m->where('dokumen.jenis', $j);
        }
        if ($q !== '') {
            $m->groupStart()->like('surat.perihal', $q)->orLike('surat.nomor_surat', $q)->orLike('dokumen.data', $q)->groupEnd();
        }
        $sort = (string) $this->request->getGet('sort');
        $sort = isset(self::URUTAN[$sort]) ? $sort : '';
        $dir  = strtolower((string) $this->request->getGet('dir')) === 'asc' ? 'asc' : 'desc';
        if ($sort !== '') {
            foreach (self::URUTAN[$sort] as $kolom) {
                $m->orderBy($kolom, $dir);
            }
            $m->orderBy('dokumen.id', $dir);
        } else {
            $m->orderBy('dokumen.id', 'DESC');
        }
        helper('ui');
        $rows  = $m->paginate(per_halaman(15));
        $pager = $m->pager;
        $pager->only(['q', 'jenis', 'sort', 'dir']);

        return view('dokumen/index', ['title' => 'Buat surat', 'rows' => $rows, 'pager' => $pager, 'q' => $q, 'j' => $j, 'sort' => $sort, 'dir' => $dir]);
    }

    private function formView(string $jenis, array $d, string $action, bool $ubah)
    {
        return view('dokumen/form', [
            'title'  => ($ubah ? 'Ubah ' : 'Buat ') . strtolower(JenisSurat::DAFTAR[$jenis]['nama']),
            'jenis'  => $jenis, 'd' => $d, 'action' => $action, 'ubah' => $ubah,
            'siswa'  => in_array($jenis, ['sk_siswa', 'tugas_siswa', 'dispensasi', 'prestasi'], true) ? (new SiswaModel())->orderBy('kelas')->orderBy('nama')->findAll() : [],
            'guru'   => in_array($jenis, ['tugas', 'sppd'], true) ? (new GuruModel())->orderBy('urut')->orderBy('nama')->findAll() : [],
        ]);
    }

    public function baru(string $jenis)
    {
        $this->cekJenis($jenis);

        return $this->formView($jenis, JenisSurat::awal($jenis), site_url("dokumen/baru/$jenis"), false);
    }

    public function nomor()
    {
        $tgl = (string) $this->request->getGet('tgl');
        if (! preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $tgl, $matches)
            || ! checkdate((int) $matches[2], (int) $matches[3], (int) $matches[1])) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Tanggal surat tidak valid.']);
        }
        $kode = trim((string) $this->request->getGet('kode'));
        if ($kode === '' || mb_strlen($kode) > 40) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Kode klasifikasi wajib diisi dan maksimal 40 karakter.']);
        }
        $sm = new SuratModel();
        $no = $sm->nomorBerikut('keluar', (int) $matches[1]);
        $model = new DokumenModel();
        $nomorUrut = $model->nomorUrutBerikutnya($tgl, $kode, (string) $no);
        $nomor = DokumenModel::nomorSurat($kode, $nomorUrut, $tgl);

        return $this->response->setJSON(['nomor_surat' => $nomor, 'nomor_urut' => $nomorUrut]);
    }

    public function ubah(int $id)
    {
        $r = $this->muat($id);
        $d = $r['d'] + JenisSurat::awal($r['jenis']);
        if ($r['surat_id'] && ($s = (new SuratModel())->find($r['surat_id']))) {
            $d['nomor_manual'] = DokumenModel::nomorUrut(
                (string) $s['nomor_surat'],
                (string) ($d['kode'] ?? ''),
                (string) ($d['tgl_surat'] ?? '')
            );
        }

        return $this->formView($r['jenis'], $d, site_url("dokumen/$id"), true);
    }

    private function aturan(string $jenis): array
    {
        $r = ['tgl_surat' => 'required|valid_date[Y-m-d]', 'kode' => 'required|max_length[40]', 'nomor_manual' => 'permit_empty|max_length[100]'];
        if (JenisSurat::DAFTAR[$jenis]['tpl'] === 'biasa') {
            $r += ['yth' => 'required|max_length[150]', 'kegiatan' => 'required|max_length[255]', 'tgl_kegiatan' => 'permit_empty|valid_date[Y-m-d]', 'tgl_kegiatan_akhir' => 'permit_empty|valid_date[Y-m-d]'];
        } elseif ($jenis === 'tugas') {
            $r += ['tugas' => 'required|max_length[255]', 'tgl_mulai' => 'required|valid_date[Y-m-d]', 'tgl_selesai' => 'permit_empty|valid_date[Y-m-d]'];
        } elseif (in_array($jenis, ['tugas_siswa', 'dispensasi'], true)) {
            $r += ['kegiatan' => 'required|max_length[255]', 'tgl_mulai' => 'required|valid_date[Y-m-d]', 'tgl_selesai' => 'permit_empty|valid_date[Y-m-d]'];
        } elseif ($jenis === 'prestasi') {
            $r += [
                'siswa_id'        => 'required|is_natural_no_zero',
                'tahun_pelajaran' => 'required|max_length[20]',
                'kejuaraan'       => 'required|max_length[255]',
                'tahun'           => 'required|max_length[20]',
                'penyelenggara'   => 'required|max_length[255]',
                'prestasi'        => 'required|max_length[255]',
            ];
        } elseif ($jenis === 'sppd') {
            $r += [
                'asal' => 'required|max_length[255]',
                'tujuan' => 'required|max_length[255]',
                'guru_id' => 'permit_empty|is_natural_no_zero',
                'guru_ids.*' => 'permit_empty|is_natural_no_zero',
                'tglb' => 'required|valid_date[Y-m-d]',
                'tglt' => 'permit_empty|valid_date[Y-m-d]',
                'tglp' => 'permit_empty|valid_date[Y-m-d]',
                'tglk' => 'permit_empty|valid_date[Y-m-d]',
                's2b' => 'permit_empty|valid_date[Y-m-d]', 's2e' => 'permit_empty|valid_date[Y-m-d]',
                's3b' => 'permit_empty|valid_date[Y-m-d]', 's3e' => 'permit_empty|valid_date[Y-m-d]',
                's4b' => 'permit_empty|valid_date[Y-m-d]', 's4e' => 'permit_empty|valid_date[Y-m-d]',
            ];
        }

        return $r;
    }

    /** Menyusun data dokumen dan baris agenda dari isian formulir. */
    /** Mengurutkan siswa menurut tingkat kelas (angka atau romawi), lalu rombel, lalu nama. */
    private function urutKelas(array $rows): array
    {
        $romawi = ['I' => 1, 'II' => 2, 'III' => 3, 'IV' => 4, 'V' => 5, 'VI' => 6, 'VII' => 7, 'VIII' => 8, 'IX' => 9, 'X' => 10, 'XI' => 11, 'XII' => 12];
        $kunci  = static function (string $k) use ($romawi): array {
            $k = strtoupper(trim($k));
            if (preg_match('/^(\d+)\s*(.*)$/', $k, $m)) {
                return [(int) $m[1], $m[2]];
            }
            if (preg_match('/^([IVX]+)\s*(.*)$/', $k, $m) && isset($romawi[$m[1]])) {
                return [$romawi[$m[1]], $m[2]];
            }

            return [999, $k];
        };
        usort($rows, static fn (array $a, array $b): int => [$kunci((string) $a['kelas']), mb_strtolower((string) $a['nama'])]
            <=> [$kunci((string) $b['kelas']), mb_strtolower((string) $b['nama'])]);

        return $rows;
    }

    private function susun(string $jenis, array $lama = [], array $timpa = []): array
    {
        $d = [];
        foreach (JenisSurat::awal($jenis) as $k => $def) {
            $d[$k] = is_array($def)
                ? array_values(array_filter(array_map('intval', (array) $this->request->getPost($k))))
                : trim((string) $this->request->getPost($k));
        }
        foreach ($timpa as $k => $v) {
            $d[$k] = $v;
        }
        $ttd = JenisSurat::penandatangan();
        $s   = [
            'tgl_agenda' => $d['tgl_surat'], 'jenis' => JenisSurat::DAFTAR[$jenis]['agenda'], 'penandatangan' => $ttd['nama'],
            'sifat' => 'Biasa', 'status' => 'Draft', 'created_by' => session()->get('uid'),
        ];
        $pot = static fn (string $x, int $n): string => mb_substr($x, 0, $n);

        if (JenisSurat::DAFTAR[$jenis]['tpl'] === 'biasa') {
            $s += [
                'pihak'    => $pot($d['yth'], 150),
                'perihal'  => $pot(($jenis === 'undangan' ? 'Undangan ' : 'Permohonan narasumber ') . fm_polos($d['kegiatan']), 255),
                'lampiran' => $d['lamp'] !== '' ? $d['lamp'] : null,
            ];
        } elseif ($jenis === 'sk_siswa') {
            $d['siswa'] = $d['siswa_ids'] ? (new SiswaModel())->whereIn('id', $d['siswa_ids'])->orderBy('kelas')->orderBy('nama')->findAll() : [];
            $nama       = array_column($d['siswa'], 'nama');
            $s += [
                'pihak'   => $pot(($nama[0] ?? '-') . (count($nama) > 1 ? ' dan ' . (count($nama) - 1) . ' siswa lain' : ''), 150),
                'perihal' => $pot('Surat keterangan aktif siswa ' . ($nama[0] ?? '') . (count($nama) > 1 ? ' dan lainnya' : ''), 255),
            ];
        } elseif ($jenis === 'tugas') {
            $d['guru'] = $d['guru_ids'] ? (new GuruModel())->whereIn('id', $d['guru_ids'])->orderBy('urut')->orderBy('nama')->findAll() : [];
            $s += [
                'pihak'   => $pot(implode(', ', array_column($d['guru'], 'nama')), 150),
                'perihal' => $pot('Surat tugas ' . fm_polos($d['tugas']), 255),
            ];
        } elseif ($jenis === 'tugas_siswa') {
            // Data siswa selalu diambil dari master siswa.
            $d['siswa'] = $d['siswa_ids'] ? (new SiswaModel())->whereIn('id', $d['siswa_ids'])->orderBy('kelas')->orderBy('nama')->findAll() : [];
            $nama       = array_column($d['siswa'], 'nama');
            $s += [
                'pihak'   => $pot(($nama[0] ?? '-') . (count($nama) > 1 ? ' dan ' . (count($nama) - 1) . ' siswa lain' : ''), 150),
                'perihal' => $pot('Surat tugas siswa ' . fm_polos($d['kegiatan']), 255),
            ];
        } elseif ($jenis === 'prestasi') {
            // Data siswa selalu diambil dari master siswa.
            $baris      = $d['siswa_id'] !== '' ? (new SiswaModel())->find((int) $d['siswa_id']) : null;
            $d['siswa'] = $baris ? [$baris] : [];
            $nama       = (string) ($baris['nama'] ?? '');
            $s += [
                'pihak'   => $pot($nama !== '' ? $nama : '-', 150),
                'perihal' => $pot('Surat keterangan prestasi ' . $nama, 255),
            ];
        } elseif ($jenis === 'dispensasi') {
            // Data siswa selalu diambil dari master siswa; urut kelas (VII, VIII, IX / 7, 8, 9), lalu nama.
            $d['siswa'] = $d['siswa_ids'] ? $this->urutKelas((new SiswaModel())->whereIn('id', $d['siswa_ids'])->findAll()) : [];
            $nama       = array_column($d['siswa'], 'nama');
            $s += [
                'pihak'   => $pot(($nama[0] ?? '-') . (count($nama) > 1 ? ' dan ' . (count($nama) - 1) . ' siswa lain' : ''), 150),
                'perihal' => $pot('Dispensasi siswa ' . fm_polos($d['kegiatan']), 255),
            ];
        } elseif ($jenis === 'sppd') {
            // Data pegawai selalu diambil dari master guru, bukan dari isian formulir.
            $g = $d['guru_id'] !== '' ? (new GuruModel())->find((int) $d['guru_id']) : null;
            if ($g) {
                $d['nama'] = (string) $g['nama'];
                $d['nip']  = (string) ($g['nip'] ?? '');
                $d['pangkat'] = (string) ($g['pangkat_gol'] ?? '');
                $d['jab']  = (string) ($g['jabatan'] ?? '');
            } elseif ($d['guru_id'] === '' && ! empty($lama['nama'])) {
                // Surat SPPD lama yang belum tertaut ke master guru: pertahankan datanya.
                $d['nama'] = (string) $lama['nama'];
                $d['nip']  = (string) ($lama['nip'] ?? '');
                $d['pangkat'] = (string) ($lama['pangkat'] ?? '');
                $d['jab']  = (string) ($lama['jab'] ?? '');
            } else {
                $d['guru_id'] = '';
                $d['nama']    = $d['nip'] = $d['jab'] = $d['pangkat'] = '';
            }
            $s += [
                'pihak'   => $pot($d['nama'], 150),
                'perihal' => $pot('SPPD ' . $d['nama'] . ' ke ' . $d['tujuan'], 255),
            ];
        }

        return [$d, $s];
    }

    private function cekIsi(string $jenis, array $d): ?string
    {
        if ($jenis === 'sk_siswa' && ! $d['siswa_ids']) {
            return 'Pilih minimal satu siswa.';
        }
        if ($jenis === 'prestasi' && empty($d['siswa'])) {
            return 'Pilih siswa dari data siswa.';
        }
        if (in_array($jenis, ['tugas_siswa', 'dispensasi'], true) && ! $d['siswa_ids']) {
            return 'Pilih minimal satu siswa yang ditugaskan.';
        }
        if ($jenis === 'tugas' && ! $d['guru_ids']) {
            return 'Pilih minimal satu guru yang ditugaskan.';
        }
        if ($jenis === 'sppd' && $d['nama'] === '') {
            return 'Pilih pegawai dari data guru.';
        }

        return null;
    }

    public function simpan(string $jenis)
    {
        $this->cekJenis($jenis);
        if (! $this->validate($this->aturan($jenis))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // SPPD baru boleh untuk beberapa pegawai sekaligus: satu SPPD per pegawai, nomor berurutan.
        $pegawai = [];
        if ($jenis === 'sppd') {
            $pegawai = array_values(array_unique(array_filter(array_map('intval', (array) $this->request->getPost('guru_ids')))));
        }
        if (count($pegawai) >= 1) {
            return $this->simpanBanyak($jenis, $pegawai);
        }

        [$d, $s] = $this->susun($jenis);
        if ($e = $this->cekIsi($jenis, $d)) {
            return redirect()->back()->withInput()->with('error', $e);
        }
        $id = (new DokumenModel())->buat($jenis, $d, $s);
        if (! $id) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan surat. Silakan coba lagi.');
        }

        return redirect()->to(site_url("dokumen/$id/cetak"))->with('ok', 'Surat dibuat dan otomatis dicatat di agenda surat keluar.');
    }

    /** Membuat satu SPPD per pegawai. Bila ada yang gagal, semua yang sudah dibuat dalam proses ini dibatalkan. */
    private function simpanBanyak(string $jenis, array $pegawai)
    {
        $model    = new DokumenModel();
        $dibuat   = [];
        $rombongan = count($pegawai) > 1 ? bin2hex(random_bytes(6)) : '';
        foreach ($pegawai as $i => $gid) {
            // Nomor manual (bila diisi) hanya untuk SPPD pertama; berikutnya mengikuti penomoran otomatis (mis. 559, 559.1, 559.2).
            $timpa = ['guru_id' => (string) $gid] + ($i > 0 ? ['nomor_manual' => ''] : []);
            [$d, $s] = $this->susun($jenis, [], $timpa);
            if ($rombongan !== '') {
                $d['rombongan'] = $rombongan;
            }
            if ($e = $this->cekIsi($jenis, $d)) {
                $this->batalkan($dibuat);

                return redirect()->back()->withInput()->with('error', $e);
            }
            $id = $model->buat($jenis, $d, $s);
            if (! $id) {
                $this->batalkan($dibuat);

                return redirect()->back()->withInput()->with('error', 'Gagal menyimpan SPPD. Tidak ada surat yang tersimpan; silakan coba lagi.');
            }
            $dibuat[] = $id;
        }

        $url = site_url('dokumen/' . $dibuat[0] . '/cetak');
        if (count($dibuat) > 1) {
            $url .= '?bersama=' . implode(',', array_slice($dibuat, 1));
        }
        $pesan = count($dibuat) > 1
            ? count($dibuat) . ' SPPD dibuat dan otomatis dicatat di agenda surat keluar. Semuanya tampil di halaman cetak ini.'
            : 'Surat dibuat dan otomatis dicatat di agenda surat keluar.';

        return redirect()->to($url)->with('ok', $pesan);
    }

    /** @param list<int> $ids */
    private function batalkan(array $ids): void
    {
        $model = new DokumenModel();
        $sm    = new SuratModel();
        foreach ($ids as $id) {
            $r = $model->find($id);
            $model->delete($id);
            if ($r && $r['surat_id']) {
                $sm->delete($r['surat_id']);
            }
        }
    }

    public function perbarui(int $id)
    {
        $r     = $this->muat($id);
        $jenis = $r['jenis'];
        if (! $this->validate($this->aturan($jenis))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        [$d, $s] = $this->susun($jenis, $r['d']);
        if ($e = $this->cekIsi($jenis, $d)) {
            return redirect()->back()->withInput()->with('error', $e);
        }
        $sm    = new SuratModel();
        $surat = $r['surat_id'] ? $sm->find($r['surat_id']) : null;
        if ($surat) {
            if ((int) substr($d['tgl_surat'], 0, 4) !== (int) $surat['tahun']) {
                return redirect()->back()->withInput()->with('error', 'Tahun tanggal surat tidak boleh diubah. Buat surat baru untuk tahun yang berbeda.');
            }
            $upd = [
                'tgl_agenda' => $s['tgl_agenda'], 'pihak' => $s['pihak'], 'perihal' => $s['perihal'],
                'jenis' => $s['jenis'], 'penandatangan' => $s['penandatangan'], 'lampiran' => $s['lampiran'] ?? null,
            ];
            $nomorUrut = trim((string) $d['nomor_manual']);
            $nomorUrut = $nomorUrut !== ''
                ? $nomorUrut
                : (new DokumenModel())->nomorUrutBerikutnya(
                    $d['tgl_surat'],
                    (string) ($d['kode'] ?? ''),
                    (string) $surat['no_agenda'],
                    (int) $surat['id']
                );
            $upd['nomor_surat'] = DokumenModel::nomorSurat((string) ($d['kode'] ?? ''), $nomorUrut, $d['tgl_surat']);
            $sm->update($surat['id'], $upd);
        }
        (new DokumenModel())->update($id, ['data' => json_encode($d, JSON_UNESCAPED_UNICODE)]);

        return redirect()->to(site_url("dokumen/$id/cetak"))->with('ok', 'Perubahan disimpan.');
    }

    public function hapus(int $id)
    {
        $r = $this->muat($id);
        (new DokumenModel())->delete($id);
        if ($r['surat_id']) {
            (new SuratModel())->delete($r['surat_id']);
        }

        return redirect()->to(site_url('dokumen'))->with('ok', 'Surat dihapus, termasuk catatannya di agenda surat keluar.');
    }

    /** Isi lembar cetak (HTML templat) untuk satu dokumen. */
    private function isiDokumen(array $r): string
    {
        $surat = $r['surat_id'] ? (new SuratModel())->find($r['surat_id']) : null;

        return view('dokumen/tpl/' . JenisSurat::DAFTAR[$r['jenis']]['tpl'], [
            'd'       => $r['d'],
            'jenis'   => $r['jenis'],
            'nomor'   => $surat['nomor_surat'] ?? ($r['d']['nomor_manual'] ?: '-'),
            'ttd'     => JenisSurat::penandatangan(),
            'sekolah' => JenisSurat::sekolah(),
        ]);
    }

    public function cetak(int $id)
    {
        $r   = $this->muat($id);
        $j   = JenisSurat::DAFTAR[$r['jenis']];
        $isi = $this->isiDokumen($r);

        // ?bersama=2,3,4 : SPPD lain (jenis sama) dicetak dalam satu berkas, masing-masing dengan kop sendiri.
        $bersama = [];
        $nama    = [(string) ($r['d']['nama'] ?? '')];
        if ($r['jenis'] === 'sppd') {
            $ids = array_slice(array_values(array_unique(array_filter(array_map('intval', explode(',', (string) $this->request->getGet('bersama')))))), 0, 50);
            foreach ($ids as $lain) {
                if ($lain === $id || ! ($x = (new DokumenModel())->find($lain)) || $x['jenis'] !== 'sppd') {
                    continue;
                }
                $x['d']    = json_decode($x['data'], true) ?: [];
                $bersama[] = $this->isiDokumen($x);
                $nama[]    = (string) ($x['d']['nama'] ?? '');
            }
        }

        return view('dokumen/cetak', [
            'judul' => $j['nama'], 'isi' => $isi, 'id' => $id, 'jenis' => $r['jenis'],
            'bersama' => $bersama, 'daftarNama' => array_filter($nama),
        ]);
    }
}
