<?php

namespace App\Libraries;

class JenisSurat
{
    public const DAFTAR = [
        'undangan'   => ['nama' => 'Undangan', 'agenda' => 'Undangan', 'tpl' => 'biasa'],
        'narasumber' => ['nama' => 'Permohonan narasumber', 'agenda' => 'Permohonan', 'tpl' => 'biasa'],
        'sk_siswa'   => ['nama' => 'Surat keterangan aktif siswa', 'agenda' => 'Surat keterangan', 'tpl' => 'sk_siswa'],
        'prestasi'   => ['nama' => 'Surat keterangan prestasi', 'agenda' => 'Surat keterangan', 'tpl' => 'prestasi'],
        'tugas'      => ['nama' => 'Surat tugas guru', 'agenda' => 'Surat tugas', 'tpl' => 'tugas'],
        'tugas_siswa' => ['nama' => 'Surat tugas siswa', 'agenda' => 'Surat tugas', 'tpl' => 'tugas_siswa'],
        'dispensasi' => ['nama' => 'Surat dispensasi', 'agenda' => 'Surat keterangan', 'tpl' => 'dispensasi'],
        'sppd'       => ['nama' => 'Surat Perintah Perjalanan Dinas (SPPD)', 'agenda' => 'SPPD', 'tpl' => 'sppd'],
    ];

    public static function sekolah(): string
    {
        return env('NAMA_SEKOLAH', 'SMP Negeri 3 Kebumen');
    }

    public static function penandatangan(): array
    {
        helper('kop');

        return ttd_settings();
    }

    public static function tahunPelajaran(): string
    {
        $y = (int) date('Y');

        return (int) date('n') >= 7 ? $y . '/' . ($y + 1) : ($y - 1) . '/' . $y;
    }

    /** Nilai awal formulir. Kunci array = nama field yang disimpan. Nilai array = daftar id. */
    public static function awal(string $jenis): array
    {
        $umum = ['tgl_surat' => date('Y-m-d'), 'tempat' => 'Kebumen', 'nomor_manual' => ''];

        $acara = ['tgl_kegiatan' => '', 'tgl_kegiatan_akhir' => '', 'waktu' => '', 'tmpk' => '', 'perlu' => ''];

        return $umum + match ($jenis) {
            'undangan' => [
                'kode' => '005', 'lamp' => '-', 'hal' => 'Undangan', 'yth' => '', 'jab' => '', 'di' => 'KEBUMEN', 'kegiatan' => '',
                'pembuka' => 'Dalam rangka pelaksanaan kegiatan **{kegiatan}**, kami mengundang **{yth}** untuk berkenan hadir pada kegiatan yang akan dilaksanakan pada:',
            ] + $acara + [
                'penutup' => 'Demikian undangan ini kami sampaikan. Atas perhatian dan kehadiran Bapak/Ibu, kami mengucapkan terima kasih.',
            ],
            'narasumber' => [
                'kode' => '005', 'lamp' => '-', 'hal' => 'Permohonan Narasumber', 'yth' => '', 'jab' => '', 'di' => 'KEBUMEN', 'kegiatan' => '',
                'pembuka' => 'Dalam rangka pelaksanaan kegiatan **{kegiatan}**, ' . self::sekolah() . ' bermaksud memohon kesediaan **{yth}** untuk menjadi narasumber pada kegiatan tersebut yang akan dilaksanakan pada:',
            ] + $acara + [
                'penutup' => 'Demikian permohonan ini kami sampaikan. Atas kesediaan dan kerja sama Bapak/Ibu, kami mengucapkan terima kasih.',
            ],
            'sk_siswa' => [
                'kode' => '800', 'tahun_pelajaran' => self::tahunPelajaran(), 'keperluan' => '',
                'penutup' => 'Demikian surat keterangan ini dibuat untuk dapat digunakan sebagaimana mestinya.',
                'siswa_ids' => [],
            ],
            'prestasi' => [
                'kode' => '400.3.11', 'tahun_pelajaran' => self::tahunPelajaran(),
                'siswa_id' => '', 'kejuaraan' => '', 'tahun' => '', 'penyelenggara' => '', 'prestasi' => '',
            ],
            'tugas' => [
                'kode' => '800.1.11.1', 'dasar' => '', 'tugas' => '', 'tgl_mulai' => '', 'tgl_selesai' => '',
                'tmpk' => '', 'acara' => '',
                'poin2' => 'Melaksanakan dengan penuh tanggung jawab dan melaporkan hasilnya.',
                'guru_ids' => [],
            ],
            'tugas_siswa' => [
                'kode' => '800.1.11.1', 'kegiatan' => '', 'tgl_mulai' => '', 'tgl_selesai' => '',
                'waktu' => '', 'tmpk' => '',
                'poin2' => 'Melaksanakan dengan penuh tanggung jawab dan melaporkan hasilnya.',
                'siswa_ids' => [],
            ],
            'dispensasi' => [
                'kode' => '400.3.5', 'kegiatan' => '', 'tgl_mulai' => '', 'tgl_selesai' => '',
                'waktu' => '', 'tmpk' => '', 'siswa_ids' => [],
            ],
            'sppd' => [
                'kode' => '090', 'asal' => self::sekolah(), 'tujuan' => '',
                'tglb' => '', 'tglt' => '', 'tglp' => '', 'tglk' => '',
                'guru_id' => '', 'nama' => '', 'nip' => '', 'pangkat' => '', 'jab' => '', 'maksud' => '',
                'tingkat' => '', 'alat' => 'Sepeda Motor', 'pengikut' => '-',
                'anggaran' => '', 'mata_anggaran' => '', 'keterangan' => '-',
                'kepala' => 'Kepala ' . self::sekolah(),
                'ttd' => self::penandatangan()['nama'], 'pangkatttd' => self::penandatangan()['pangkat'], 'nipttd' => self::penandatangan()['nip'],
                's2a' => '', 's2b' => '', 's2c' => '', 's2d' => '', 's2e' => '',
                's3a' => '', 's3b' => '', 's3c' => '', 's3d' => '', 's3e' => '',
            ],
        };
    }
}
