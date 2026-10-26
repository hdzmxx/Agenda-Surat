<?php

namespace App\Controllers;

use App\Models\DaftarHadirModel;
use App\Models\GuruModel;
use App\Models\SiswaModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

class DaftarHadir extends BaseController
{
    protected $helpers = ['form', 'tanggal', 'kop'];

    /** Kolom yang boleh dipakai untuk mengurutkan daftar (kunci = parameter ?sort=). */
    private const URUTAN = ['tgl' => ['tanggal'], 'judul' => ['judul']];

    public function index()
    {
        $q    = trim((string) $this->request->getGet('q'));
        $sort = (string) $this->request->getGet('sort');
        $sort = isset(self::URUTAN[$sort]) ? $sort : '';
        $dir  = strtolower((string) $this->request->getGet('dir')) === 'asc' ? 'asc' : 'desc';
        $dasar = ['title' => 'Daftar hadir kegiatan', 'q' => $q, 'sort' => $sort, 'dir' => $dir];

        if (! db_connect()->tableExists('daftar_hadir')) {
            return view('daftar_hadir/index', $dasar + ['tabelAda' => false, 'rows' => [], 'pager' => null]);
        }

        $m = new DaftarHadirModel();
        if ($q !== '') {
            $m->groupStart()->like('judul', $q)->orLike('tema', $q)->groupEnd();
        }
        if ($sort !== '') {
            foreach (self::URUTAN[$sort] as $kolom) {
                $m->orderBy($kolom, $dir);
            }
            $m->orderBy('id', $dir);
        } else {
            $m->orderBy('tanggal', 'DESC')->orderBy('id', 'DESC');
        }
        helper('ui');
        $rows  = $m->paginate(per_halaman(15));
        $pager = $m->pager;
        $pager->only(['q', 'sort', 'dir']);

        return view('daftar_hadir/index', $dasar + ['tabelAda' => true, 'rows' => $rows, 'pager' => $pager]);
    }

    public function baru()
    {
        return $this->formView(null);
    }

    public function simpan()
    {
        $baris = $this->susun();
        if ($baris instanceof RedirectResponse) {
            return $baris;
        }
        $baris['created_by'] = session()->get('uid');

        try {
            $id = (new DaftarHadirModel())->insert($baris);
        } catch (\Throwable $e) {
            log_message('error', 'Gagal menyimpan daftar hadir: ' . $e->getMessage());
            $id = false;
        }
        if (! $id) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan daftar hadir. Pastikan tabel daftar_hadir sudah dibuat (php spark migrate atau impor daftar-hadir.sql).');
        }

        return redirect()->to(site_url("daftar-hadir/$id/cetak"))->with('ok', 'Daftar hadir disimpan.');
    }

    public function ubah(int $id)
    {
        return $this->formView($this->muat($id));
    }

    public function perbarui(int $id)
    {
        $this->muat($id);
        $baris = $this->susun();
        if ($baris instanceof RedirectResponse) {
            return $baris;
        }
        if (! (new DaftarHadirModel())->update($id, $baris)) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan perubahan. Silakan coba lagi.');
        }

        return redirect()->to(site_url("daftar-hadir/$id/cetak"))->with('ok', 'Perubahan disimpan.');
    }

    public function hapus(int $id)
    {
        $this->muat($id);
        (new DaftarHadirModel())->delete($id);

        return redirect()->to(site_url('daftar-hadir'))->with('ok', 'Daftar hadir dihapus.');
    }

    /** Menyimpan hasil edit manual daftar peserta dari halaman cetak. */
    public function manual(int $id)
    {
        $r    = $this->muat($id);
        $rows = json_decode((string) $this->request->getPost('rows'), true);
        $balik = static fn (string $pesan) => redirect()->to(site_url("daftar-hadir/$id/cetak"))->with('error', $pesan);
        if (! is_array($rows) || count($rows) > 500) {
            return $balik('Data hasil edit tidak valid.');
        }
        $peserta = [];
        foreach ($rows as $x) {
            if (! is_array($x)) {
                return $balik('Data hasil edit tidak valid.');
            }
            $nama = trim((string) ($x['nama'] ?? ''));
            $jab  = trim((string) ($x['jabatan'] ?? ''));
            $idn  = trim((string) ($x['identitas'] ?? ''));
            $tipe = in_array($x['tipe'] ?? '', ['guru', 'siswa', 'tamu'], true) ? $x['tipe'] : '';
            if ($nama === '') {
                continue; // baris kosong diabaikan
            }
            if (mb_strlen($nama) > 150 || mb_strlen($jab) > 100 || mb_strlen($idn) > 60) {
                return $balik('Nama maksimal 150 karakter, jabatan/kelas 100 karakter, dan identitas 60 karakter.');
            }
            $peserta[] = ['nama' => $nama, 'jabatan' => $jab, 'identitas' => $idn, 'jenis_identitas' => 'teks', 'tipe' => $tipe];
        }
        if (! $peserta) {
            return $balik('Daftar peserta tidak boleh kosong.');
        }

        $teks   = [];
        $teksIn = json_decode((string) $this->request->getPost('teks'), true);
        if (is_array($teksIn)) {
            foreach (['h_judul', 'h_tema', 'h_tanggal', 't_kota', 't_jabatan', 't_nama', 't_nip'] as $k) {
                if (isset($teksIn[$k]) && is_string($teksIn[$k])) {
                    $v = trim((string) preg_replace('/\s+/', ' ', $teksIn[$k]));
                    if (mb_strlen($v) > 300) {
                        return $balik('Teks judul atau tanda tangan maksimal 300 karakter.');
                    }
                    $teks[$k] = $v;
                }
            }
        }

        $d = self::dekode($r);
        $d['peserta'] = $peserta;
        $d['teks']    = $teks;
        $tipeAda = array_column($peserta, 'tipe');
        if (! in_array('', $tipeAda, true)) {
            $d['peserta_guru']  = in_array('guru', $tipeAda, true);
            $d['peserta_siswa'] = in_array('siswa', $tipeAda, true);
            $d['peserta_tamu']  = in_array('tamu', $tipeAda, true);
        }
        if (! (new DaftarHadirModel())->update($id, ['data' => json_encode($d, JSON_UNESCAPED_UNICODE)])) {
            return $balik('Gagal menyimpan perubahan. Silakan coba lagi.');
        }

        return redirect()->to(site_url("daftar-hadir/$id/cetak"))->with('ok', 'Perubahan manual disimpan.');
    }

    public function cetak(int $id)
    {
        $r = $this->muat($id);
        $d = self::dekode($r);

        return view('daftar_hadir/cetak', [
            'title' => 'Cetak daftar hadir',
            'id'    => $id,
            'data'  => [
                'judul'         => $r['judul'],
                'tema'          => $r['tema'],
                'tanggal'       => $r['tanggal'],
                'kota'          => $r['kota'],
                'jabatan'       => $r['jabatan'],
                'nama'          => $r['nama'],
                'nip'           => $r['nip'],
                'peserta_guru'  => (bool) ($d['peserta_guru'] ?? false),
                'peserta_siswa' => (bool) ($d['peserta_siswa'] ?? false),
                'peserta_tamu'  => (bool) ($d['peserta_tamu'] ?? false),
            ],
            'peserta' => $d['peserta'] ?? [],
            'teks'    => is_array($d['teks'] ?? null) ? $d['teks'] : [],
        ]);
    }

    private function muat(int $id): array
    {
        $r = (new DaftarHadirModel())->find($id);
        if (! $r) {
            throw PageNotFoundException::forPageNotFound('Daftar hadir tidak ditemukan.');
        }

        return $r;
    }

    private static function dekode(array $r): array
    {
        $d = json_decode((string) ($r['data'] ?? ''), true);

        return is_array($d) ? $d : [];
    }

    private function formView(?array $r)
    {
        $d = [];
        if ($r) {
            $x = self::dekode($r);
            $d = [
                'judul' => $r['judul'], 'tema' => $r['tema'], 'tanggal' => $r['tanggal'], 'kota' => $r['kota'],
                'jabatan' => $r['jabatan'], 'nama' => $r['nama'], 'nip' => $r['nip'],
                'jenis'    => $x['jenis'] ?? [],
                'guru_ids' => $x['guru_ids'] ?? [],
                'siswa_ids' => $x['siswa_ids'] ?? [],
            ];
            $tamu = $x['tamu'] ?? [];
            $d['manual_nama']    = $tamu ? array_column($tamu, 'nama') : [''];
            $d['manual_jabatan'] = $tamu ? array_column($tamu, 'jabatan') : [''];
            $d['manual_nip']     = $tamu ? array_column($tamu, 'identitas') : [''];
            $d['manual_jenis']   = $tamu ? array_column($tamu, 'jenis_identitas') : ['nip'];
        }

        $ttd = \App\Libraries\JenisSurat::penandatangan();

        return view('daftar_hadir/form', [
            'title'    => $r ? 'Ubah daftar hadir' : 'Daftar hadir kegiatan',
            'guru'     => self::sortGuru((new GuruModel())->findAll()),
            'siswa'    => (new SiswaModel())->orderBy('kelas')->orderBy('nama')->findAll(),
            'defaults' => [
                'tanggal' => date('Y-m-d'),
                'jabatan' => (string) $ttd['jabatan'],
                'nama'    => (string) $ttd['nama'],
                'nip'     => (string) $ttd['nip'],
            ],
            'd'    => $d,
            'id'   => $r['id'] ?? null,
            'aksi' => $r ? site_url('daftar-hadir/' . $r['id']) : site_url('daftar-hadir'),
        ]);
    }

    /** @return array|RedirectResponse baris siap simpan, atau redirect berisi pesan galat */
    private function susun()
    {
        $judul = trim((string) $this->request->getPost('judul'));
        $tema = trim((string) $this->request->getPost('tema'));
        $tanggal = (string) $this->request->getPost('tanggal');
        $kota = trim((string) $this->request->getPost('kota'));
        $jabatan = trim((string) $this->request->getPost('jabatan'));
        $nama = trim((string) $this->request->getPost('nama'));
        $nip = trim((string) $this->request->getPost('nip'));
        $jenisPeserta = $this->request->getPost('peserta') ?? [];
        $guruIds = $this->request->getPost('guru_ids') ?? [];
        $siswaIds = $this->request->getPost('siswa_ids') ?? [];
        $manualNama = $this->request->getPost('manual_nama') ?? [];
        $manualJabatan = $this->request->getPost('manual_jabatan') ?? [];
        $manualNip = $this->request->getPost('manual_nip') ?? [];
        $manualJenis = $this->request->getPost('manual_jenis') ?? [];

        $rules = [
            'judul' => 'required|max_length[200]',
            'tema' => 'permit_empty|max_length[250]',
            'tanggal' => 'required|valid_date[Y-m-d]',
            'kota' => 'required|max_length[100]',
            'jabatan' => 'required|max_length[100]',
            'nama' => 'required|max_length[150]',
            'nip' => 'permit_empty|max_length[30]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        if (! is_array($manualNama) || ! is_array($manualJabatan) || ! is_array($manualNip) || ! is_array($manualJenis)
            || count($manualNama) > 100 || count($manualJabatan) !== count($manualNama)
            || count($manualNip) !== count($manualNama) || count($manualJenis) !== count($manualNama)) {
            return redirect()->back()->withInput()->with('error', 'Daftar tamu manual tidak valid.');
        }
        $tamu = [];
        foreach ($manualNama as $index => $namaTamu) {
            if (! is_string($namaTamu) || ! is_string($manualJabatan[$index]) || ! is_string($manualNip[$index])
                || ! is_string($manualJenis[$index]) || ! in_array($manualJenis[$index], ['nip', 'manual', 'kosong'], true)) {
                return redirect()->back()->withInput()->with('error', 'Data tamu manual tidak valid.');
            }
            $namaTamu = trim($namaTamu);
            $jabatanTamu = trim($manualJabatan[$index]);
            $nipTamu = trim($manualNip[$index]);
            $jenisIdentitas = $manualJenis[$index];
            $identitas = $jenisIdentitas === 'kosong' ? '' : $nipTamu;
            if ($namaTamu === '' && $jabatanTamu === '' && $identitas === '') {
                continue;
            }
            if ($namaTamu === '' || mb_strlen($namaTamu) > 150 || mb_strlen($jabatanTamu) > 100 || mb_strlen($nipTamu) > 30) {
                return redirect()->back()->withInput()->with('error', 'Isi nama setiap tamu. Nama maksimal 150 karakter, jabatan 100 karakter, dan NIP 30 karakter.');
            }
            $tamu[] = [
                'nama' => $namaTamu,
                'jabatan' => $jabatanTamu ?: 'Tamu',
                'identitas' => $identitas,
                'jenis_identitas' => $jenisIdentitas,
                'tipe' => 'tamu',
            ];
        }
        if (! is_array($jenisPeserta)) {
            return redirect()->back()->withInput()->with('error', 'Pilih jenis peserta yang valid.');
        }
        foreach ($jenisPeserta as $jenis) {
            if (! is_string($jenis) || ! in_array($jenis, ['guru', 'siswa'], true)) {
                return redirect()->back()->withInput()->with('error', 'Pilih jenis peserta yang valid.');
            }
        }
        if (! $jenisPeserta && ! $tamu) {
            return redirect()->back()->withInput()->with('error', 'Pilih jenis peserta atau tambahkan setidaknya satu tamu.');
        }
        if (! is_array($guruIds) || ! is_array($siswaIds)
            || ($guruIds && ! $this->idsValid($guruIds))
            || ($siswaIds && ! $this->idsValid($siswaIds))) {
            return redirect()->back()->withInput()->with('error', 'Daftar peserta tidak valid. Silakan pilih ulang peserta.');
        }
        if (! in_array('guru', $jenisPeserta, true)) {
            $guruIds = [];
        }
        if (! in_array('siswa', $jenisPeserta, true)) {
            $siswaIds = [];
        }
        if (! $guruIds && ! $siswaIds && ! $tamu) {
            return redirect()->back()->withInput()->with('error', 'Pilih setidaknya satu peserta yang akan mengikuti kegiatan.');
        }

        $peserta = $tamu;
        if ($guruIds) {
            $guru = (new GuruModel())->whereIn('id', $guruIds)->findAll();
            if (count($guru) !== count($guruIds)) {
                return redirect()->back()->withInput()->with('error', 'Sebagian data guru/karyawan sudah tidak tersedia. Muat ulang daftar peserta.');
            }
            $guru = self::sortGuru($guru);
            foreach ($guru as $orang) {
                $peserta[] = [
                    'nama' => $orang['nama'],
                    'jabatan' => $orang['jabatan'] ?: 'Guru / Karyawan',
                    'identitas' => (string) ($orang['nip'] ?? ''),
                    'jenis_identitas' => 'nip',
                    'tipe' => 'guru',
                ];
            }
        }
        if ($siswaIds) {
            $siswa = (new SiswaModel())->whereIn('id', $siswaIds)->orderBy('kelas')->orderBy('nama')->findAll();
            if (count($siswa) !== count($siswaIds)) {
                return redirect()->back()->withInput()->with('error', 'Sebagian data siswa sudah tidak tersedia. Muat ulang daftar peserta.');
            }
            foreach ($siswa as $orang) {
                $peserta[] = [
                    'nama' => $orang['nama'],
                    'jabatan' => 'Siswa' . ($orang['kelas'] ? ' - ' . $orang['kelas'] : ''),
                    'identitas' => (string) ($orang['nisn'] ?: ($orang['nipd'] ?? '')),
                    'jenis_identitas' => $orang['nisn'] ? 'nisn' : ($orang['nipd'] ? 'nipd' : 'nisn'),
                    'tipe' => 'siswa',
                ];
            }
        }

        $data = [
            'jenis'         => array_values($jenisPeserta),
            'guru_ids'      => array_map('intval', $guruIds),
            'siswa_ids'     => array_map('intval', $siswaIds),
            'tamu'          => $tamu,
            'peserta'       => $peserta,
            'peserta_guru'  => (bool) $guruIds,
            'peserta_siswa' => (bool) $siswaIds,
            'peserta_tamu'  => (bool) $tamu,
        ];

        return [
            'judul'   => $judul,
            'tema'    => $tema,
            'tanggal' => $tanggal,
            'kota'    => $kota,
            'jabatan' => $jabatan,
            'nama'    => $nama,
            'nip'     => $nip,
            'data'    => json_encode($data, JSON_UNESCAPED_UNICODE),
        ];
    }

    private static function sortGuru(array $guru): array
    {
        usort($guru, static function (array $a, array $b): int {
            $kategori = self::kategoriGuru($a) <=> self::kategoriGuru($b);
            if ($kategori !== 0) {
                return $kategori;
            }
            $rank = self::pangkatScore($b) <=> self::pangkatScore($a);
            if ($rank !== 0) {
                return $rank;
            }

            return ((int) ($a['urut'] ?? 999) <=> (int) ($b['urut'] ?? 999))
                ?: strcasecmp((string) ($a['nama'] ?? ''), (string) ($b['nama'] ?? ''));
        });

        return $guru;
    }

    /** 0 = kepala sekolah, 1 = guru dan lainnya, 2 = pustakawan, 3 = tenaga administrasi. */
    private static function kategoriGuru(array $g): int
    {
        $j = (string) ($g['jabatan'] ?? '');
        if (stripos($j, 'kepala sekolah') !== false) {
            return 0;
        }
        if (stripos($j, 'administrasi') !== false) {
            return 3;
        }
        if (stripos($j, 'pustaka') !== false && stripos($j, 'guru') === false) {
            return 2;
        }

        return 1;
    }

    private static function pangkatScore(array $guru): int
    {
        $pangkat = (string) ($guru['pangkat_gol'] ?? '');
        if (preg_match('/(IV|III|II|I)\s*\/\s*([A-E])/i', $pangkat, $match)) {
            $golongan = ['I' => 1, 'II' => 2, 'III' => 3, 'IV' => 4][strtoupper($match[1])];

            return $golongan * 100 + (ord(strtoupper($match[2])) - ord('A') + 1) * 10;
        }
        if (preg_match('/\b(IX|V)\b/i', $pangkat, $match)) {
            return strtoupper($match[1]) === 'IX' ? 290 : 280;
        }
        if (stripos($pangkat, 'PPPK') !== false) {
            return 270;
        }

        return 0;
    }

    private function idsValid(array $ids): bool
    {
        if (! $ids || count($ids) > 1000) {
            return false;
        }
        foreach ($ids as $id) {
            if (! is_string($id) && ! is_int($id)) {
                return false;
            }
            if (! ctype_digit((string) $id) || (int) $id < 1) {
                return false;
            }
        }

        return count(array_unique(array_map('intval', $ids))) === count($ids);
    }
}
