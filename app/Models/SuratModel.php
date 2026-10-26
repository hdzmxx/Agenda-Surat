<?php

namespace App\Models;

use CodeIgniter\Model;

class SuratModel extends Model
{
    protected $table         = 'surat';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'arah', 'tahun', 'no_agenda', 'tgl_agenda', 'tgl_surat', 'nomor_surat', 'pihak', 'perihal',
        'disposisi', 'jenis', 'penandatangan', 'sifat', 'lampiran', 'status', 'isi', 'keterangan',
        'berkas', 'berkas_asli', 'created_by',
    ];

    /** Kolom yang boleh dipakai untuk mengurutkan daftar (kunci = parameter ?sort=). */
    public const URUTAN = [
        'no'      => ['tahun', 'no_agenda'],
        'tgl'     => ['tgl_agenda', 'no_agenda'],
        'nomor'   => ['nomor_surat'],
        'pihak'   => ['pihak'],
        'perihal' => ['perihal'],
        'sifat'   => ['sifat'],
        'status'  => ['status'],
    ];

    /** @return array{string, string} [kunci urutan yang sah atau '', 'asc'|'desc'] */
    public static function urutan($sort, $dir): array
    {
        $sort = is_string($sort) && isset(self::URUTAN[$sort]) ? $sort : '';
        $dir  = is_string($dir) && strtolower($dir) === 'asc' ? 'asc' : 'desc';

        return [$sort, $dir];
    }

    public function nextNo(string $arah, int $tahun): int
    {
        $r = $this->selectMax('no_agenda', 'm')->where(['arah' => $arah, 'tahun' => $tahun])->first();

        return (int) ($r['m'] ?? 0) + 1;
    }

    /** Nomor agenda berikutnya. Dengan $kunci = true, baris dikunci agar aman dari bentrok. */
    public function nomorBerikut(string $arah, int $tahun, bool $kunci = false): int
    {
        $sql = 'SELECT COALESCE(MAX(no_agenda), 0) + 1 AS n FROM surat WHERE arah = ? AND tahun = ?'
            . ($kunci ? ' FOR UPDATE' : '');

        return (int) $this->db->query($sql, [$arah, $tahun])->getRow()->n;
    }

    /** Membuat nomor surat dari format di .env */
    public static function formatNomor(int $no, string $tglIso): string
    {
        $t      = strtotime($tglIso) ?: time();
        $romawi = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'][(int) date('n', $t)];

        return strtr(env('NOMOR_FORMAT', '{kode}/{no3}/{romawi}/{tahun}'), [
            '{kode}'   => env('NOMOR_KODE', '421.3'),
            '{no}'     => $no,
            '{no3}'    => str_pad((string) $no, 3, '0', STR_PAD_LEFT),
            '{bulan}'  => date('n', $t),
            '{romawi}' => $romawi,
            '{tahun}'  => date('Y', $t),
        ]);
    }

    /**
     * Menentukan nomor surat keluar berikutnya berdasarkan tanggal surat.
     * Nomor numerik terakhir pada tanggal yang sama mendapat akhiran .1, .2, dst.
     */
    public function nomorSuratBerikutnya(string $tgl, string $fallback): string
    {
        $hariIni = $this->db->table($this->table)
            ->select('nomor_surat')
            ->where('arah', 'keluar')
            ->where('tgl_agenda', $tgl)
            ->orderBy('no_agenda', 'DESC')
            ->get()
            ->getResultArray();
        $akar = null;
        foreach ($hariIni as $row) {
            $nomor = self::bagianNomorNumerik((string) $row['nomor_surat']);
            if ($nomor !== null) {
                $akar = $nomor[0];
                break;
            }
        }
        if ($akar !== null) {
            $akhiran = 0;
            foreach ($hariIni as $row) {
                $nomor = self::bagianNomorNumerik((string) $row['nomor_surat']);
                if ($nomor !== null && $nomor[0] === $akar) {
                    $akhiran = max($akhiran, $nomor[1]);
                }
            }

            return $akar . '.' . ($akhiran + 1);
        }

        $sebelumnya = $this->db->table($this->table)
            ->select('nomor_surat')
            ->where('arah', 'keluar')
            ->where('tgl_agenda <', $tgl)
            ->orderBy('tgl_agenda', 'DESC')
            ->orderBy('no_agenda', 'DESC')
            ->get()
            ->getResultArray();
        foreach ($sebelumnya as $row) {
            $nomor = self::bagianNomorNumerik((string) $row['nomor_surat']);
            if ($nomor !== null) {
                return (string) ($nomor[0] + 1);
            }
        }

        return $fallback;
    }

    /** @return array{int, int}|null */
    private static function bagianNomorNumerik(string $nomor): ?array
    {
        if (! preg_match('/^(\d+)(?:\.(\d+))?$/', trim($nomor), $matches)) {
            return null;
        }

        return [(int) $matches[1], isset($matches[2]) ? (int) $matches[2] : 0];
    }

    /**
     * Menyimpan surat dengan nomor agenda otomatis (atau manual jika $noManual > 0).
     * Mengembalikan nomor agenda, atau 0 jika gagal.
     */
    public function simpanOtomatis(array $d, int $noManual = 0): int
    {
        for ($i = 0; $i < 5; $i++) {
            $this->db->transStart();
            $no               = $noManual > 0 ? $noManual : $this->nomorBerikut($d['arah'], (int) $d['tahun'], true);
            $row              = $d;
            $row['no_agenda'] = $no;
            if ($d['arah'] === 'keluar' && empty($row['nomor_surat'])) {
                $fallback = self::formatNomor($no, $d['tgl_agenda']);
                $row['nomor_surat'] = $this->nomorSuratBerikutnya($d['tgl_agenda'], $fallback);
            }
            try {
                $ok = $this->insert($row);
            } catch (\Throwable $e) {
                $ok = false;
            }
            if ($ok) {
                $this->db->transComplete();

                return $no;
            }
            $this->db->transRollback();
            if ($noManual > 0) {
                break; // nomor manual bentrok, jangan dicoba ulang
            }
        }

        return 0;
    }

    public function tahunList(string $arah): array
    {
        return $this->select('tahun')->where('arah', $arah)->groupBy('tahun')->orderBy('tahun', 'DESC')->findColumn('tahun') ?? [];
    }

    /** Menyiapkan query dengan filter: q, tahun, bulan, status; urutan opsional: sort, dir. */
    public function cari(string $arah, array $f): self
    {
        $this->where('arah', $arah);
        if (! empty($f['tahun'])) {
            $this->where('tahun', (int) $f['tahun']);
        }
        if (! empty($f['bulan'])) {
            $this->where('MONTH(tgl_agenda) = ' . (int) $f['bulan'], null, false);
        }
        if (! empty($f['status'])) {
            $this->where('status', $f['status']);
        }
        if (! empty($f['q'])) {
            $this->groupStart()
                ->like('nomor_surat', $f['q'])->orLike('pihak', $f['q'])
                ->orLike('perihal', $f['q'])->orLike('isi', $f['q'])
                ->groupEnd();
        }

        [$sort, $dir] = self::urutan($f['sort'] ?? null, $f['dir'] ?? null);
        if ($sort !== '') {
            foreach (self::URUTAN[$sort] as $kolom) {
                $this->orderBy($kolom, $dir);
            }

            return $this->orderBy('id', $dir);
        }

        return $this->orderBy('tgl_agenda', 'DESC')->orderBy('no_agenda', 'DESC');
    }
}