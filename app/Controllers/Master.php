<?php

namespace App\Controllers;

use App\Models\GuruModel;
use App\Models\SiswaModel;
use App\Models\UserModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as XlDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Master extends BaseController
{
    protected $helpers = ['form', 'tanggal'];

    private const CFG = [
        'guru' => [
            'judul' => 'Data Guru', 'model' => GuruModel::class,
            'kolom' => [['nama', 'Nama lengkap dan gelar'], ['nip', 'NIP'], ['pangkat_gol', 'Pangkat / Gol. Ruang'], ['jabatan', 'Jabatan'], ['urut', 'Urutan', 'number']],
            'cari'  => ['nama', 'nip', 'jabatan'],
            'urut'  => ['urut', 'nama'],
            'contoh' => [['CONTOH Budi Santoso, S.Pd., M.Pd.', '196801011990031001', 'Pembina Tingkat I (IV/b)', 'Kepala Sekolah', 1], ['CONTOH Siti Aminah, S.Pd.', '198501012010012001', 'Penata Tingkat I (III/d)', 'Guru', 2]],
        ],
        'siswa' => [
            'judul' => 'Data Siswa', 'model' => SiswaModel::class,
            'kolom' => [['nama', 'Nama'], ['nisn', 'NISN'], ['nipd', 'NIPD (No. induk siswa)'], ['kelas', 'Kelas'], ['jk', 'Jenis kelamin', 'jk'], ['tempat_lahir', 'Tempat lahir'], ['tgl_lahir', 'Tanggal lahir', 'date'], ['alamat', 'Alamat', 'textarea']],
            'cari'  => ['nama', 'nisn', 'nipd', 'kelas', 'tempat_lahir', 'alamat'],
            'urut'  => ['kelas', 'nama'],
            'contoh' => [['CONTOH Ani Lestari', '0012345678', '2025001', '7A', 'P', 'Kota Contoh', '2012-01-01', 'Jl. Contoh No. 1, Kec. Contoh']],
        ],
    ];

    private function cfg(string $t): array
    {
        if (! isset(self::CFG[$t])) {
            throw PageNotFoundException::forPageNotFound();
        }

        return self::CFG[$t];
    }

    private function mdl(string $t)
    {
        $c = $this->cfg($t)['model'];

        return new $c();
    }

    private function baris(string $t, int $id): array
    {
        $r = $this->mdl($t)->find($id);
        if (! $r) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $r;
    }

    public function index(string $t)
    {
        $c = $this->cfg($t);
        $m = $this->mdl($t);
        $q = trim((string) $this->request->getGet('q'));
        $k = (string) $this->request->getGet('kelas');
        if ($q !== '') {
            $m->groupStart();
            foreach ($c['cari'] as $i => $f) {
                $i === 0 ? $m->like($f, $q) : $m->orLike($f, $q);
            }
            $m->groupEnd();
        }
        if ($t === 'siswa' && $k !== '') {
            $m->where('kelas', $k);
        }
        foreach ($c['urut'] as $f) {
            $m->orderBy($f);
        }
        helper('ui');
        $rows  = $m->paginate(per_halaman(20));
        $pager = $m->pager;
        $pager->only(['q', 'kelas']);
        $kelas = $t === 'siswa' ? ($this->mdl('siswa')->select('kelas')->where("kelas <> ''")->groupBy('kelas')->orderBy('kelas')->findColumn('kelas') ?? []) : [];

        return view('master/index', ['title' => $c['judul'], 't' => $t, 'c' => $c, 'rows' => $rows, 'pager' => $pager, 'q' => $q, 'k' => $k, 'kelas' => $kelas, 'total' => $m->pager->getTotal()]);
    }

    public function baru(string $t)
    {
        $c = $this->cfg($t);

        return view('master/form', ['title' => 'Tambah ' . strtolower($c['judul']), 't' => $t, 'c' => $c, 'row' => null, 'action' => site_url("data/$t")]);
    }

    public function ubah(string $t, int $id)
    {
        $c = $this->cfg($t);

        return view('master/form', ['title' => 'Ubah ' . strtolower($c['judul']), 't' => $t, 'c' => $c, 'row' => $this->baris($t, $id), 'action' => site_url("data/$t/$id")]);
    }

    private function aturan(string $t, ?int $id = null): array
    {
        $r = ['nama' => 'required|max_length[150]'];
        if ($t === 'guru') {
            $nip = 'permit_empty|max_length[30]' . ($id ? "|is_unique[guru.nip,id,$id]" : '|is_unique[guru.nip]');
            $r += ['nip' => $nip, 'pangkat_gol' => 'permit_empty|max_length[100]', 'jabatan' => 'permit_empty|max_length[100]', 'urut' => 'permit_empty|is_natural'];
        } else {
            $r += ['nisn' => 'permit_empty|max_length[20]', 'nipd' => 'permit_empty|max_length[20]', 'kelas' => 'permit_empty|max_length[20]', 'jk' => 'permit_empty|in_list[L,P]', 'tempat_lahir' => 'permit_empty|max_length[100]', 'tgl_lahir' => 'permit_empty|valid_date[Y-m-d]', 'alamat' => 'permit_empty|max_length[255]'];
        }

        return $r;
    }

    private function ambil(string $t): array
    {
        $d = [];
        foreach ($this->cfg($t)['kolom'] as $k) {
            $v       = trim((string) $this->request->getPost($k[0]));
            // Jika diketik '-', anggap kosong (null) agar tidak bentrok NIP ganda
            $d[$k[0]] = ($v === '' || $v === '-') ? null : $v; 
        }
        if ($t === 'guru') {
            $d['urut'] = $d['urut'] ?? 999;
        }

        return $d;
    }

    private function sinkronkanAkunGuru(int $guruId): bool
    {
        $guru = (new GuruModel())->find($guruId);
        if (! $guru) {
            return false;
        }

        $nip = trim((string) ($guru['nip'] ?? ''));
        $users = new UserModel();
        $akun = $users->where('guru_id', $guruId)->first();
        if ($nip === '') {
            return true; // Ubah menjadi true agar diabaikan dan tidak dihitung sebagai error
        }
        if (! preg_match('/^[a-zA-Z0-9]{1,50}$/', $nip)) {
            return false;
        }

        $nama = mb_substr(trim((string) $guru['nama']), 0, 100);
        if ($akun) {
            $duplikat = $users->where('username', $nip)->where('id !=', $akun['id'])->first();
            if ($duplikat) {
                return false;
            }
            $data = ['nama' => $nama, 'username' => $nip, 'role' => 'guru'];
            if ($akun['username'] !== $nip) {
                $data['password'] = password_hash($nip, PASSWORD_DEFAULT);
            }

            return $users->update($akun['id'], $data);
        }

        $akun = $users->where('username', $nip)->first();
        if ($akun) {
            if ($akun['role'] !== 'guru' || ! empty($akun['guru_id'])) {
                return false;
            }

            return $users->update($akun['id'], ['nama' => $nama, 'role' => 'guru', 'guru_id' => $guruId]);
        }

        return (bool) $users->insert([
            'nama'     => $nama,
            'username' => $nip,
            'password' => password_hash($nip, PASSWORD_DEFAULT),
            'role'     => 'guru',
            'guru_id'  => $guruId,
        ]);
    }

    public function simpan(string $t)
    {
        $this->cfg($t);
        if (! $this->validate($this->aturan($t))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $db = db_connect();
        $db->transBegin();
        try {
            $this->mdl($t)->insert($this->ambil($t));
            if ($t === 'guru') {
                $this->sinkronkanAkunGuru((int) $db->insertID());
            }
            if (! $db->transStatus()) {
                $db->transRollback();

                return redirect()->back()->withInput()->with('error', 'Data gagal disimpan.');
            }
            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Gagal menyimpan data master {jenis}: {message}', ['jenis' => $t, 'message' => $e->getMessage()]);

            return redirect()->back()->withInput()->with('error', 'Data gagal disimpan. Periksa kembali NIP dan username akun.');
        }

        return redirect()->to(site_url("data/$t"))->with('ok', 'Data ditambahkan.');
    }

    public function perbarui(string $t, int $id)
    {
        $this->baris($t, $id);
        if (! $this->validate($this->aturan($t, $id))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $db = db_connect();
        $db->transBegin();
        try {
            $this->mdl($t)->update($id, $this->ambil($t));
            if ($t === 'guru') {
                $this->sinkronkanAkunGuru($id);
            }
            if (! $db->transStatus()) {
                $db->transRollback();

                return redirect()->back()->withInput()->with('error', 'Perubahan gagal disimpan.');
            }
            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Gagal memperbarui data master {jenis}: {message}', ['jenis' => $t, 'message' => $e->getMessage()]);

            return redirect()->back()->withInput()->with('error', 'Perubahan gagal disimpan. Periksa kembali NIP dan username akun.');
        }

        return redirect()->to(site_url("data/$t"))->with('ok', 'Perubahan disimpan.');
    }

    public function hapus(string $t, int $id)
    {
        $this->baris($t, $id);
        $db = db_connect();
        $db->transBegin();
        try {
            if ($t === 'guru') {
                $akun = (new UserModel())->where('guru_id', $id)->first();
                if ($akun) {
                    (new UserModel())->delete($akun['id']);
                }
            }
            $this->mdl($t)->delete($id);
            if (! $db->transStatus()) {
                $db->transRollback();

                return redirect()->to(site_url("data/$t"))->with('error', 'Data gagal dihapus.');
            }
            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Gagal menghapus data master {jenis}: {message}', ['jenis' => $t, 'message' => $e->getMessage()]);

            return redirect()->to(site_url("data/$t"))->with('error', 'Data gagal dihapus.');
        }

        return redirect()->to(site_url("data/$t"))->with('ok', 'Data dihapus.');
    }

    /* ---------- Template dan impor Excel ---------- */

    public function template(string $t)
    {
        $c    = $this->cfg($t);
        $head = array_column($c['kolom'], 1);
        if (! class_exists(Spreadsheet::class)) {
            $out = "\xEF\xBB\xBF" . implode(';', $head) . "\n";
            foreach ($c['contoh'] as $r) {
                $out .= implode(';', $r) . "\n";
            }

            return $this->response->setHeader('Content-Type', 'text/csv; charset=utf-8')
                ->setHeader('Content-Disposition', 'attachment; filename="template-' . $t . '.csv"')->setBody($out);
        }
        $ss = new Spreadsheet();
        $sh = $ss->getActiveSheet();
        $sh->setTitle(ucfirst($t));
        $n = count($head);
        foreach ($head as $i => $h) {
            $col = chr(65 + $i);
            $sh->setCellValue($col . '1', $h);
            $sh->getColumnDimension($col)->setAutoSize(true);
            $sh->getStyle($col . '2:' . $col . '2000')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
        }
        $sh->getStyle('A1:' . chr(64 + $n) . '1')->getFont()->setBold(true);
        foreach ($c['contoh'] as $ri => $r) {
            foreach ($r as $i => $v) {
                $sh->setCellValueExplicit(chr(65 + $i) . ($ri + 2), (string) $v, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            }
        }
        $sh->freezePane('A2');
        ob_start();
        (new Xlsx($ss))->save('php://output');
        $bin = ob_get_clean();

        return $this->response->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="template-' . $t . '.xlsx"')->setBody($bin);
    }

    public function importForm(string $t)
    {
        $c = $this->cfg($t);

        return view('master/import', ['title' => 'Impor ' . strtolower($c['judul']), 't' => $t, 'c' => $c, 'lib' => class_exists(Spreadsheet::class)]);
    }

    private function bacaFile($file): array
    {
        $ext  = strtolower($file->getClientExtension());
        $path = $file->getTempName();
        if ($ext === 'csv') {
            $h     = fopen($path, 'r');
            $first = (string) fgets($h);
            rewind($h);
            $sep  = substr_count($first, ';') > substr_count($first, ',') ? ';' : ',';
            $rows = [];
            while (($r = fgetcsv($h, 0, $sep, '"', '\\')) !== false) {
                $rows[] = $r;
            }
            fclose($h);
            if ($rows && isset($rows[0][0])) {
                $rows[0][0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $rows[0][0]);
            }

            return $rows;
        }
        if (! class_exists(IOFactory::class)) {
            throw new \RuntimeException('File Excel membutuhkan pustaka PhpSpreadsheet (composer require phpoffice/phpspreadsheet). Atau simpan file sebagai CSV.');
        }

        return IOFactory::load($path)->getActiveSheet()->toArray(null, true, false, false);
    }

    private function norm($v): string
    {
        if ($v === null) {
            return '';
        }
        if (! is_string($v) && is_numeric($v)) {
            return sprintf('%.0f', $v);
        }

        $val = trim((string) $v);
        // Jika dari excel diisi '-', ubah jadi string kosong
        return $val === '-' ? '' : $val;
    }

    private function tgl($v): ?string
    {
        if ($v === null || $v === '') {
            return null;
        }
        if (! is_string($v) && is_numeric($v) && $v > 1000 && $v < 90000 && class_exists(XlDate::class)) {
            return XlDate::excelToDateTimeObject($v)->format('Y-m-d');
        }
        $s = strtr(strtolower(trim((string) $v)), [
            'januari' => 'january', 'februari' => 'february', 'maret' => 'march', 'april' => 'april', 'mei' => 'may', 'juni' => 'june', 'juli' => 'july',
            'agustus' => 'august', 'september' => 'september', 'oktober' => 'october', 'november' => 'november', 'desember' => 'december',
        ]);
        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'j F Y', 'd.m.Y'] as $f) {
            $dt = \DateTime::createFromFormat($f, $s);
            $le = \DateTime::getLastErrors();
            if ($dt && (! $le || ($le['warning_count'] === 0 && $le['error_count'] === 0))) {
                return $dt->format('Y-m-d');
            }
        }
        $ts = strtotime($s);

        return $ts ? date('Y-m-d', $ts) : null;
    }

    private const ALIAS = [
        'nama' => ['nama'], 'nip' => ['nip'], 'pangkat_gol' => ['pangkat', 'golongan'], 'jabatan' => ['jabatan'], 'urut' => ['urut'],
        'nisn' => ['nisn'], 'nipd' => ['nipd', 'nik', 'nomorinduk', 'noinduk'], 'kelas' => ['kelas', 'rombel'],
        'jk' => ['jeniskelamin', 'jk', 'lp', 'kelamin', 'gender'], 'tempat_lahir' => ['tempatlahir', 'tempat'],
        'tgl_lahir' => ['tanggallahir', 'tgllahir', 'tanggal'], 'alamat' => ['alamat'],
    ];

    /** Memetakan kolom file berdasarkan judul kolom, sehingga urutan kolom boleh berbeda. */
    private function petaKolom(string $t, array $row): ?array
    {
        $fields = array_column($this->cfg($t)['kolom'], 0);
        $peta   = [];
        foreach ($row as $i => $h) {
            $n = preg_replace('/[^a-z0-9]/', '', strtolower((string) $h));
            if ($n === '') {
                continue;
            }
            foreach ($fields as $f) {
                if (isset($peta[$f])) {
                    continue;
                }
                foreach (self::ALIAS[$f] as $a) {
                    if (str_starts_with($n, $a)) {
                        $peta[$f] = $i;
                        break 2;
                    }
                }
            }
        }

        return isset($peta['nama']) && count($peta) >= 2 ? $peta : null;
    }

    private function jk($v): ?string
    {
        $s = strtolower(trim((string) $v));
        if ($s === '') {
            return null;
        }
        if (in_array($s, ['l', 'laki-laki', 'laki laki', 'lakilaki', 'pria', 'male', 'm', '1'], true)) {
            return 'L';
        }
        if (in_array($s, ['p', 'perempuan', 'wanita', 'female', 'f', '2'], true)) {
            return 'P';
        }

        return null;
    }

    public function import(string $t)
    {
        $c    = $this->cfg($t);
        $file = $this->request->getFile('berkas');
        if (! $file || ! $file->isValid() || ! in_array(strtolower($file->getClientExtension()), ['xlsx', 'xls', 'csv'], true)) {
            return redirect()->back()->with('error', 'Pilih file .xlsx, .xls, atau .csv.');
        }
        try {
            $rows = $this->bacaFile($file);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal membaca file: ' . $e->getMessage());
        }

        // Cari baris judul pada 5 baris pertama; jika tidak ada, pakai urutan kolom template.
        $peta  = null;
        $awal  = 0;
        foreach (array_slice($rows, 0, 5, true) as $i => $r) {
            if ($pk = $this->petaKolom($t, $r)) {
                $peta = $pk;
                $awal = $i + 1;
                break;
            }
        }
        if ($peta === null) {
            $peta = array_flip(array_column($c['kolom'], 0));
        }

        $m = $this->mdl($t);
        if ($this->request->getPost('ganti')) {
            if ($t === 'guru') {
                $m->where('id >', 0)->delete();
            } else {
                $m->builder()->emptyTable();
            }
        }
        $baru = $ubah = $lewat = $gagalAkun = 0;
        foreach ($rows as $i => $r) {
            if ($i < $awal) {
                continue;
            }
            $ambil = static fn (string $f) => isset($peta[$f]) ? ($r[$peta[$f]] ?? null) : null;
            $nama  = $this->norm($ambil('nama'));
            if ($nama === '' || stripos($nama, 'CONTOH') === 0) {
                $lewat++;
                continue;
            }
            $d = ['nama' => $nama];
            foreach (array_column($c['kolom'], 0) as $f) {
                if ($f === 'nama' || ! isset($peta[$f])) {
                    continue;
                }
                $d[$f] = match ($f) {
                    'urut'      => (int) $this->norm($ambil($f)) ?: 999,
                    'jk'        => $this->jk($ambil($f)),
                    'tgl_lahir' => $this->tgl($ambil($f)),
                    default     => $this->norm($ambil($f)) ?: null,
                };
            }
            $kunci = $t === 'guru' ? 'nip' : 'nisn';
            if (empty($d[$kunci])) {
                $kunci = 'nama';
            }
            $ada = $this->mdl($t)->where($kunci, $d[$kunci])->first();
            if ($ada) {
                $this->mdl($t)->update($ada['id'], $d);
                if ($t === 'guru' && ! $this->sinkronkanAkunGuru((int) $ada['id'])) {
                    $gagalAkun++;
                }
                $ubah++;
            } else {
                if ($t === 'guru' && ! isset($d['urut'])) {
                    $d['urut'] = 999;
                }
                $this->mdl($t)->insert($d);
                if ($t === 'guru' && ! $this->sinkronkanAkunGuru((int) $this->mdl($t)->getInsertID())) {
                    $gagalAkun++;
                }
                $baru++;
            }
        }

        $pesan = "Impor selesai: $baru data baru, $ubah diperbarui, $lewat baris dilewati.";
        if ($gagalAkun > 0) {
            $pesan .= " $gagalAkun akun guru gagal dibuat/diperbarui; pastikan NIP valid dan belum dipakai akun lain.";
        }

        return redirect()->to(site_url("data/$t"))->with($gagalAkun > 0 ? 'error' : 'ok', $pesan);
    }
}
