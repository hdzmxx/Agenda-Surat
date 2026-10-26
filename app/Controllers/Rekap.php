<?php

namespace App\Controllers;

use App\Libraries\JenisSurat;
use App\Models\DokumenModel;
use App\Models\GuruModel;
use PhpOffice\PhpSpreadsheet\Shared\Date as XlDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Rekap perjalanan dinas (SPPD) per pegawai dan per bulan, dengan unduhan Excel.
 */
class Rekap extends BaseController
{
    protected $helpers = ['form', 'tanggal', 'ui'];

    private const BULAN = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    private const BULAN_SINGKAT = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    public function sppd()
    {
        $f     = $this->filter();
        $hasil = $this->kumpulkan($f['tahun'], $f['bulan'], $f['guru']);

        return view('rekap/sppd', [
            'title'      => 'Rekap perjalanan dinas',
            'f'          => $f,
            'ringkasan'  => $hasil['ringkasan'],
            'perjalanan' => $hasil['perjalanan'],
            'total'      => $hasil['total'],
            'tahunList'  => $hasil['tahunList'],
            'guru'       => (new GuruModel())->orderBy('urut')->orderBy('nama')->findAll(),
            'bulanNama'  => self::BULAN,
            'bulanPendek' => self::BULAN_SINGKAT,
            'periode'    => $this->periode($f),
        ]);
    }

    public function sppdExcel()
    {
        if (! class_exists(Spreadsheet::class)) {
            return redirect()->to(site_url('rekap/sppd'))->with('error', 'Pustaka Excel (phpoffice/phpspreadsheet) belum terpasang.');
        }
        $f     = $this->filter();
        $hasil = $this->kumpulkan($f['tahun'], $f['bulan'], $f['guru']);

        $ss = new Spreadsheet();
        $this->lembarRingkasan($ss->getActiveSheet(), $hasil, $f);
        $this->lembarRincian($ss->createSheet(), $hasil, $f);
        $ss->setActiveSheetIndex(0);

        $nama = 'rekap-perjalanan-dinas'
            . ($f['tahun'] > 0 ? '-' . $f['tahun'] : '-semua-tahun')
            . ($f['bulan'] > 0 ? '-' . str_pad((string) $f['bulan'], 2, '0', STR_PAD_LEFT) : '')
            . '.xlsx';

        ob_start();
        (new Xlsx($ss))->save('php://output');
        $isi = ob_get_clean();

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $nama . '"')
            ->setHeader('Cache-Control', 'max-age=0')
            ->setBody($isi);
    }

    /** Filter dari query string. tahun 0 = semua tahun, bulan 0 = semua bulan, guru 0 = semua pegawai. */
    private function filter(): array
    {
        $t     = $this->request->getGet('tahun');
        $tahun = ($t === null || $t === '') ? (int) date('Y') : max(0, (int) $t);
        $bulan = (int) $this->request->getGet('bulan');
        $bulan = ($bulan >= 1 && $bulan <= 12) ? $bulan : 0;
        $guru  = max(0, (int) $this->request->getGet('guru'));

        return ['tahun' => $tahun, 'bulan' => $bulan, 'guru' => $guru];
    }

    private function periode(array $f): string
    {
        $teks = $f['tahun'] > 0
            ? ($f['bulan'] > 0 ? self::BULAN[$f['bulan'] - 1] . ' ' . $f['tahun'] : 'Tahun ' . $f['tahun'])
            : ($f['bulan'] > 0 ? 'Bulan ' . self::BULAN[$f['bulan'] - 1] . ' (semua tahun)' : 'Semua periode');

        if ($f['guru'] > 0) {
            $g = (new GuruModel())->find($f['guru']);
            if ($g) {
                $teks .= ' - ' . $g['nama'];
            }
        }

        return $teks;
    }

    private function tanggal(?string $v): ?string
    {
        $v = substr(trim((string) $v), 0, 10);
        if (! preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $v, $m) || ! checkdate((int) $m[2], (int) $m[3], (int) $m[1])) {
            return null;
        }

        return $v;
    }

    private function rentang(string $a, string $b): string
    {
        if ($a === $b) {
            return tgl_id($a);
        }
        if (substr($a, 0, 7) === substr($b, 0, 7)) {
            return (int) substr($a, 8, 2) . ' - ' . tgl_id($b);
        }

        return tgl_id($a) . ' - ' . tgl_id($b);
    }

    /**
     * Membaca semua SPPD lalu menyaring per tahun/bulan/pegawai (berdasarkan tanggal berangkat).
     *
     * @return array{perjalanan: list<array>, ringkasan: list<array>, total: array, tahunList: list<int>}
     */
    private function kumpulkan(int $tahun, int $bulan, int $guruId): array
    {
        $baris = (new DokumenModel())
            ->select('dokumen.id, dokumen.data, surat.nomor_surat, surat.tgl_agenda')
            ->join('surat', 'surat.id = dokumen.surat_id', 'left')
            ->where('dokumen.jenis', 'sppd')
            ->orderBy('dokumen.id', 'ASC')
            ->findAll();

        $namaGuru = '';
        if ($guruId > 0) {
            $g        = (new GuruModel())->find($guruId);
            $namaGuru = $g ? mb_strtolower(trim((string) $g['nama'])) : '';
        }

        $perjalanan = [];
        $tahunAda   = [(int) date('Y') => true];

        foreach ($baris as $r) {
            $d = json_decode((string) $r['data'], true);
            if (! is_array($d)) {
                continue;
            }
            $berangkat = $this->tanggal($d['tglb'] ?? '') ?? $this->tanggal($r['tgl_agenda'] ?? '');
            if ($berangkat === null) {
                continue;
            }
            $kembali = $this->tanggal($d['tglk'] ?? '') ?? $this->tanggal($d['tglp'] ?? '') ?? $this->tanggal($d['tglt'] ?? '') ?? $berangkat;
            if ($kembali < $berangkat) {
                $kembali = $berangkat;
            }

            $th = (int) substr($berangkat, 0, 4);
            $tahunAda[$th] = true;
            if ($tahun > 0 && $th !== $tahun) {
                continue;
            }
            if ($bulan > 0 && (int) substr($berangkat, 5, 2) !== $bulan) {
                continue;
            }

            $gid  = (int) ($d['guru_id'] ?? 0);
            $nama = trim((string) ($d['nama'] ?? ''));
            if ($guruId > 0 && $gid !== $guruId && ! ($gid === 0 && $namaGuru !== '' && mb_strtolower($nama) === $namaGuru)) {
                continue;
            }

            $hari = (int) (new \DateTime($berangkat))->diff(new \DateTime($kembali))->days + 1;

            $perjalanan[] = [
                'id'        => (int) $r['id'],
                'nomor'     => (string) (($r['nomor_surat'] ?? '') !== '' ? $r['nomor_surat'] : ($d['sppd_no'] ?? '-')),
                'kunci'     => $gid > 0 ? 'g' . $gid : 'n' . mb_strtolower($nama),
                'nama'      => $nama !== '' ? $nama : '(tanpa nama)',
                'nip'       => trim((string) ($d['nip'] ?? '')),
                'jabatan'   => trim((string) ($d['jab'] ?? '')),
                'tujuan'    => trim((string) ($d['tujuan'] ?? '')),
                'maksud'    => trim((string) ($d['maksud'] ?? '')),
                'anggaran'  => trim((string) ($d['anggaran'] ?? '')),
                'berangkat' => $berangkat,
                'kembali'   => $kembali,
                'hari'      => $hari,
                'bulan'     => (int) substr($berangkat, 5, 2),
                'rentang'   => $this->rentang($berangkat, $kembali),
            ];
        }

        usort($perjalanan, static fn ($a, $b) => [$a['berangkat'], $a['id']] <=> [$b['berangkat'], $b['id']]);

        $peg = [];
        foreach ($perjalanan as $p) {
            $k = $p['kunci'];
            if (! isset($peg[$k])) {
                $peg[$k] = [
                    'nama' => $p['nama'], 'nip' => $p['nip'], 'jabatan' => $p['jabatan'],
                    'bulan' => array_fill(1, 12, 0), 'kali' => 0, 'hari' => 0, 'tujuan' => [],
                ];
            }
            $peg[$k]['bulan'][$p['bulan']]++;
            $peg[$k]['kali']++;
            $peg[$k]['hari'] += $p['hari'];
            if ($p['tujuan'] !== '') {
                $peg[$k]['tujuan'][$p['tujuan']] = true;
            }
        }
        $ringkasan = array_values($peg);
        usort($ringkasan, static fn ($a, $b) => strnatcasecmp($a['nama'], $b['nama']));

        $tahunList = array_keys($tahunAda);
        rsort($tahunList);

        return [
            'perjalanan' => $perjalanan,
            'ringkasan'  => $ringkasan,
            'total'      => [
                'kali'    => count($perjalanan),
                'hari'    => array_sum(array_column($perjalanan, 'hari')),
                'pegawai' => count($ringkasan),
            ],
            'tahunList'  => $tahunList,
        ];
    }

    private function kepala($sh, string $judul, array $f, string $akhirKolom): void
    {
        $sh->setCellValue('A1', 'REKAP PERJALANAN DINAS');
        $sh->setCellValue('A2', JenisSurat::sekolah());
        $sh->setCellValue('A3', 'Periode: ' . $this->periode($f));
        foreach (['A1', 'A2', 'A3'] as $i => $sel) {
            $sh->mergeCells($sel . ':' . $akhirKolom . ($i + 1));
        }
        $sh->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sh->getStyle('A2')->getFont()->setBold(true);
        $sh->setTitle($judul);
    }

    private function gayaTabel($sh, string $rentang, string $kepala): void
    {
        $sh->getStyle($kepala)->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0F766E']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);
        $sh->getStyle($rentang)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'B7C2CC']]],
        ]);
    }

    private function lembarRingkasan($sh, array $hasil, array $f): void
    {
        $this->kepala($sh, 'Ringkasan', $f, 'R');

        $judul = array_merge(['No', 'Nama', 'NIP', 'Jabatan'], self::BULAN_SINGKAT, ['Jumlah (kali)', 'Total (hari)']);
        foreach ($judul as $i => $h) {
            $sh->setCellValue([$i + 1, 5], $h);
        }
        $baris = 6;
        foreach ($hasil['ringkasan'] as $no => $r) {
            $sh->setCellValue([1, $baris], $no + 1);
            $sh->setCellValue([2, $baris], $r['nama']);
            $sh->setCellValueExplicit([3, $baris], $r['nip'], DataType::TYPE_STRING);
            $sh->setCellValue([4, $baris], $r['jabatan']);
            for ($b = 1; $b <= 12; $b++) {
                $sh->setCellValue([4 + $b, $baris], $r['bulan'][$b]);
            }
            $sh->setCellValue([17, $baris], $r['kali']);
            $sh->setCellValue([18, $baris], $r['hari']);
            $baris++;
        }
        // Baris jumlah
        $sh->setCellValue([2, $baris], 'JUMLAH');
        for ($b = 1; $b <= 12; $b++) {
            $sh->setCellValue([4 + $b, $baris], array_sum(array_column(array_column($hasil['ringkasan'], 'bulan'), $b)));
        }
        $sh->setCellValue([17, $baris], $hasil['total']['kali']);
        $sh->setCellValue([18, $baris], $hasil['total']['hari']);
        $sh->getStyle('A' . $baris . ':R' . $baris)->getFont()->setBold(true);
        $sh->getStyle('A' . $baris . ':R' . $baris)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRgb('E6F2F1');

        $this->gayaTabel($sh, 'A5:R' . $baris, 'A5:R5');
        $sh->getStyle('E6:R' . $baris)->getNumberFormat()->setFormatCode('0;-0;;@');
        $sh->getStyle('A6:A' . $baris)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sh->getStyle('E6:R' . $baris)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        foreach (['A' => 5, 'B' => 30, 'C' => 22, 'D' => 28, 'Q' => 14, 'R' => 13] as $kol => $lebar) {
            $sh->getColumnDimension($kol)->setWidth($lebar);
        }
        foreach (range('E', 'P') as $kol) {
            $sh->getColumnDimension($kol)->setWidth(5.5);
        }
        $sh->getRowDimension(5)->setRowHeight(30);
        $sh->freezePane('E6');
    }

    private function lembarRincian($sh, array $hasil, array $f): void
    {
        $this->kepala($sh, 'Rincian', $f, 'K');

        $judul = ['No', 'Nomor SPPD', 'Nama', 'NIP', 'Jabatan', 'Tanggal berangkat', 'Tanggal kembali', 'Lama (hari)', 'Tujuan', 'Maksud perjalanan', 'Pembebanan anggaran'];
        foreach ($judul as $i => $h) {
            $sh->setCellValue([$i + 1, 5], $h);
        }
        $baris = 6;
        foreach ($hasil['perjalanan'] as $no => $p) {
            $sh->setCellValue([1, $baris], $no + 1);
            $sh->setCellValueExplicit([2, $baris], $p['nomor'], DataType::TYPE_STRING);
            $sh->setCellValue([3, $baris], $p['nama']);
            $sh->setCellValueExplicit([4, $baris], $p['nip'], DataType::TYPE_STRING);
            $sh->setCellValue([5, $baris], $p['jabatan']);
            $sh->setCellValue([6, $baris], XlDate::PHPToExcel(new \DateTime($p['berangkat'])));
            $sh->setCellValue([7, $baris], XlDate::PHPToExcel(new \DateTime($p['kembali'])));
            $sh->setCellValue([8, $baris], $p['hari']);
            $sh->setCellValue([9, $baris], $p['tujuan']);
            $sh->setCellValue([10, $baris], $p['maksud']);
            $sh->setCellValue([11, $baris], $p['anggaran']);
            $baris++;
        }
        $akhir = max(6, $baris - 1);

        $this->gayaTabel($sh, 'A5:K' . $akhir, 'A5:K5');
        $sh->getStyle('F6:G' . $akhir)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);
        $sh->getStyle('A6:A' . $akhir)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sh->getStyle('F6:H' . $akhir)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sh->getStyle('A6:K' . $akhir)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
        $sh->getStyle('I6:K' . $akhir)->getAlignment()->setWrapText(true);

        foreach (['A' => 5, 'B' => 20, 'C' => 28, 'D' => 22, 'E' => 26, 'F' => 14, 'G' => 14, 'H' => 10, 'I' => 30, 'J' => 40, 'K' => 22] as $kol => $lebar) {
            $sh->getColumnDimension($kol)->setWidth($lebar);
        }
        $sh->getRowDimension(5)->setRowHeight(30);
        $sh->freezePane('A6');
        $sh->setAutoFilter('A5:K' . $akhir);
    }
}
