<?php

namespace App\Models;

use CodeIgniter\Model;

class DokumenModel extends Model
{
    protected $table         = 'dokumen';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['jenis', 'surat_id', 'data', 'created_by'];

    public static function nomorSurat(string $kode, int|string $no, string $tgl): string
    {
        $nomor = (string) $no;

        return strtr(env('NOMOR_FORMAT_DOK', '{kode} / {no} / {tahun}'), [
            '{kode}'  => $kode,
          '{no}'    => $nomor,
          '{no3}'   => ctype_digit($nomor) ? str_pad($nomor, 3, '0', STR_PAD_LEFT) : $nomor,
            '{tahun}' => date('Y', strtotime($tgl)),
        ]);
    }

    public static function nomorUrut(string $nomorSurat, string $kode, string $tgl): string
    {
        $tahun = date('Y', strtotime($tgl));
        $formatLama = '/^\s*' . preg_quote($kode, '/') . '\s*\/\s*(.*?)\s*\/\s*' . preg_quote($tahun, '/') . '\s*$/u';
        if ($kode !== '' && preg_match($formatLama, $nomorSurat, $matches)) {
            return trim($matches[1]);
        }

        return self::nomorTengah($nomorSurat);
    }

    public function nomorUrutBerikutnya(string $tgl, string $kode, string $fallback, ?int $excludeSuratId = null): string
    {
        $hariIniQuery = $this->db->table('surat')
            ->select('nomor_surat')
            ->where('arah', 'keluar')
            ->where('tgl_agenda', $tgl)
            ->orderBy('no_agenda', 'DESC');
        if ($excludeSuratId !== null) {
            $hariIniQuery->where('id !=', $excludeSuratId);
        }
        $hariIni = $hariIniQuery->get()->getResultArray();

        $akar = null;
        foreach ($hariIni as $row) {
            $nomor = self::bagianNomorNumerik(self::nomorTengah((string) $row['nomor_surat']));
            if ($nomor !== null) {
                $akar = $nomor[0];
                break;
            }
        }
        if ($akar !== null) {
            $akhiran = 0;
            foreach ($hariIni as $row) {
                $nomor = self::bagianNomorNumerik(self::nomorTengah((string) $row['nomor_surat']));
                if ($nomor !== null && $nomor[0] === $akar) {
                    $akhiran = max($akhiran, $nomor[1]);
                }
            }

            return $akar . '.' . ($akhiran + 1);
        }

        $sebelumnyaQuery = $this->db->table('surat')
            ->select('nomor_surat')
            ->where('arah', 'keluar')
            ->where('tgl_agenda <', $tgl)
            ->orderBy('tgl_agenda', 'DESC')
            ->orderBy('no_agenda', 'DESC');
        if ($excludeSuratId !== null) {
            $sebelumnyaQuery->where('id !=', $excludeSuratId);
        }
        $sebelumnya = $sebelumnyaQuery->get()->getResultArray();
        foreach ($sebelumnya as $row) {
            $nomor = self::bagianNomorNumerik(self::nomorTengah((string) $row['nomor_surat']));
            if ($nomor !== null) {
                return (string) ($nomor[0] + 1);
            }
        }

        return $fallback;
    }

    private static function nomorTengah(string $nomor): string
    {
        if (preg_match('/^\s*.+?\s*\/\s*(.*?)\s*\/\s*\d{4}\s*$/u', $nomor, $matches)) {
            return trim($matches[1]);
        }

        return trim($nomor);
    }

    /** @return array{int, int}|null */
    private static function bagianNomorNumerik(string $nomor): ?array
    {
        if (! preg_match('/^(\d+)(?:\.(\d+))?$/', trim($nomor), $matches)) {
            return null;
        }

        return [(int) $matches[1], isset($matches[2]) ? (int) $matches[2] : 0];
    }

    public function daftarTujuanTugas(array $suratIds): array
    {
        if ($suratIds === []) {
            return [];
        }

        $rows = $this->select('surat_id, data')
            ->where('jenis', 'tugas')
            ->whereIn('surat_id', $suratIds)
            ->findAll();
        $tujuan = [];
        foreach ($rows as $row) {
            $data = json_decode((string) $row['data'], true);
            if (! is_array($data) || ! isset($data['guru']) || ! is_array($data['guru'])) {
                continue;
            }

            $nama = array_values(array_filter(array_map(
                static fn ($guru) => is_array($guru) && isset($guru['nama']) && is_string($guru['nama']) ? trim($guru['nama']) : '',
                $data['guru']
            ), static fn ($nama) => $nama !== ''));
            if ($nama !== []) {
                $tujuan[$row['surat_id']] = $nama;
            }
        }

        return $tujuan;
    }

    /**
     * Membuat dokumen sekaligus mencatatnya di agenda surat keluar dengan nomor otomatis.
     * Mengembalikan id dokumen, atau 0 jika gagal.
     */
    public function buat(string $jenis, array $d, array $surat): int
    {
        for ($i = 0; $i < 3; $i++) {
            $this->db->transStart();

            try {
                $tahun = (int) substr($surat['tgl_agenda'], 0, 4);
                $no    = (int) $this->db->query(
                    'SELECT COALESCE(MAX(no_agenda), 0) + 1 AS n FROM surat WHERE arah = ? AND tahun = ? FOR UPDATE',
                    ['keluar', $tahun]
                )->getRow()->n;
                $sm = new SuratModel();
                $nomorUrut = trim((string) ($d['nomor_manual'] ?? ''));
                $nomorUrut = $nomorUrut !== ''
                    ? $nomorUrut
                    : $this->nomorUrutBerikutnya($surat['tgl_agenda'], (string) ($d['kode'] ?? ''), (string) $no);
                $nomor = self::nomorSurat($d['kode'] ?? '', $nomorUrut, $surat['tgl_agenda']);
                $sm->insert($surat + ['arah' => 'keluar', 'tahun' => $tahun, 'no_agenda' => $no, 'nomor_surat' => $nomor]);
                $sid = $sm->getInsertID();

                $this->insert([
                    'jenis'      => $jenis,
                    'surat_id'   => $sid,
                    'data'       => json_encode($d, JSON_UNESCAPED_UNICODE),
                    'created_by' => session()->get('uid'),
                ]);
                $id = $this->getInsertID();
                $this->db->transComplete();
                if ($this->db->transStatus()) {
                    return (int) $id;
                }
            } catch (\Throwable $e) {
                $this->db->transRollback();
            }
        }

        return 0;
    }
}
