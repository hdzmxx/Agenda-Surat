<?php

namespace App\Libraries;

use Config\Database;

/**
 * Cadangan database (dump SQL) yang disimpan di writable/backups.
 * - Manual: tombol "Buat cadangan sekarang" di halaman Cadangan database.
 * - Otomatis: dibuat saat ada yang membuka aplikasi dan jadwal (harian/mingguan) sudah jatuh tempo,
 *   atau terjadwal lewat `php spark db:backup` (Task Scheduler / cron).
 * Hanya database yang dicadangkan; berkas unggahan (writable/uploads) tidak ikut.
 */
class DbBackup
{
    public const FREKUENSI = ['mati' => 'Mati', 'harian' => 'Harian', 'mingguan' => 'Mingguan'];

    /** Pola nama berkas yang sah (juga mencegah path traversal saat unduh/hapus). */
    private const POLA = '/^(manual|otomatis|sebelum-pulih|unggahan)-\d{8}-\d{6}-[A-Za-z0-9_]*\.sql(\.gz)?$/';

    public static function folder(): string
    {
        $p = WRITEPATH . 'backups' . DIRECTORY_SEPARATOR;
        if (! is_dir($p)) {
            @mkdir($p, 0775, true);
        }
        if (is_dir($p) && ! is_file($p . '.htaccess')) {
            @file_put_contents($p . '.htaccess', "<IfModule authz_core_module>\n    Require all denied\n</IfModule>\n<IfModule !authz_core_module>\n    Deny from all\n</IfModule>\n");
        }
        if (is_dir($p) && ! is_file($p . 'index.html')) {
            @file_put_contents($p . 'index.html', '');
        }

        return $p;
    }

    /** @return array{frekuensi: string, simpan: int} */
    public static function pengaturan(): array
    {
        $p = ['frekuensi' => 'mingguan', 'simpan' => 8];
        $f = self::folder() . 'pengaturan.json';
        if (is_file($f)) {
            $j = json_decode((string) file_get_contents($f), true);
            if (is_array($j)) {
                $p = array_merge($p, array_intersect_key($j, $p));
            }
        }
        if (! isset(self::FREKUENSI[$p['frekuensi']])) {
            $p['frekuensi'] = 'mingguan';
        }
        $p['simpan'] = max(1, min(60, (int) $p['simpan']));

        return $p;
    }

    public static function simpanPengaturan(string $frekuensi, int $simpan): void
    {
        $data = [
            'frekuensi' => isset(self::FREKUENSI[$frekuensi]) ? $frekuensi : 'mingguan',
            'simpan'    => max(1, min(60, $simpan)),
        ];
        file_put_contents(self::folder() . 'pengaturan.json', json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
    }

    /**
     * Membuat dump SQL seluruh tabel (struktur + isi). Mengembalikan nama berkas.
     *
     * @throws \RuntimeException bila folder tidak bisa ditulis atau dump gagal
     */
    public static function buat(string $jenis = 'manual'): string
    {
        @set_time_limit(0);
        $db  = Database::connect();
        $dir = self::folder();
        if (! is_dir($dir) || ! is_writable($dir)) {
            throw new \RuntimeException('Folder writable/backups tidak dapat ditulis.');
        }

        $awal  = in_array($jenis, ['otomatis', 'sebelum-pulih'], true) ? $jenis : 'manual';
        $nama  = $awal . '-' . date('Ymd-His') . '-' . preg_replace('/[^A-Za-z0-9_]/', '', (string) $db->getDatabase()) . '.sql';
        $gz    = function_exists('gzopen');
        $akhir = $dir . $nama . ($gz ? '.gz' : '');
        $tmp   = $akhir . '.tmp';

        $h = $gz ? @gzopen($tmp, 'wb6') : @fopen($tmp, 'wb');
        if (! $h) {
            throw new \RuntimeException('Tidak dapat membuat berkas cadangan.');
        }
        $tulis = static function (string $s) use ($h, $gz): void {
            $gz ? gzwrite($h, $s) : fwrite($h, $s);
        };

        try {
            $tulis('-- Cadangan database ' . $db->getDatabase() . "\n-- Dibuat: " . date('Y-m-d H:i:s') . "\n\n");
            $tulis("SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\nSET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n\n");

            foreach ($db->listTables() as $tabel) {
                $q   = '`' . str_replace('`', '``', $tabel) . '`';
                $ddl = $db->query('SHOW CREATE TABLE ' . $q)->getRowArray()['Create Table'] ?? null;
                if ($ddl === null) {
                    continue; // tampilan (view) dilewati
                }
                $tulis("-- Tabel {$tabel}\nDROP TABLE IF EXISTS {$q};\n{$ddl};\n\n");

                $offset = 0;
                while (true) {
                    $rows = $db->query('SELECT * FROM ' . $q . ' ORDER BY 1 LIMIT 500 OFFSET ' . $offset)->getResultArray();
                    if ($rows === []) {
                        break;
                    }
                    $kolom = '`' . implode('`, `', array_map(static fn ($k) => str_replace('`', '``', (string) $k), array_keys($rows[0]))) . '`';
                    foreach (array_chunk($rows, 100) as $paket) {
                        $nilai = array_map(static function (array $r) use ($db): string {
                            return '(' . implode(', ', array_map(static fn ($v) => $db->escape($v), $r)) . ')';
                        }, $paket);
                        $tulis("INSERT INTO {$q} ({$kolom}) VALUES\n" . implode(",\n", $nilai) . ";\n");
                    }
                    $offset += 500;
                }
                $tulis("\n");
            }
            $tulis("SET FOREIGN_KEY_CHECKS=1;\n");
        } catch (\Throwable $e) {
            $gz ? gzclose($h) : fclose($h);
            @unlink($tmp);

            throw new \RuntimeException('Cadangan gagal dibuat: ' . $e->getMessage(), 0, $e);
        }

        $gz ? gzclose($h) : fclose($h);
        if (! @rename($tmp, $akhir)) {
            @unlink($tmp);

            throw new \RuntimeException('Tidak dapat menyimpan berkas cadangan.');
        }

        return basename($akhir);
    }

    /** @return list<array{nama: string, jenis: string, ukuran: int, waktu: int}> terbaru dulu */
    public static function daftar(): array
    {
        $hasil = [];
        foreach (glob(self::folder() . '*.sql*') ?: [] as $f) {
            $n = basename($f);
            if (! preg_match(self::POLA, $n)) {
                continue;
            }
            $hasil[] = [
                'nama'   => $n,
                'jenis'  => match (true) {
                    str_starts_with($n, 'otomatis-')      => 'Otomatis',
                    str_starts_with($n, 'sebelum-pulih-') => 'Sebelum pulih',
                    str_starts_with($n, 'unggahan-')      => 'Unggahan',
                    default                               => 'Manual',
                },
                'ukuran' => (int) filesize($f),
                'waktu'  => (int) filemtime($f),
            ];
        }
        usort($hasil, static fn ($a, $b) => $b['waktu'] <=> $a['waktu']);

        return $hasil;
    }

    /** Path lengkap berkas cadangan bila namanya sah dan ada; selain itu null. */
    public static function path(string $nama): ?string
    {
        if (! preg_match(self::POLA, $nama)) {
            return null;
        }
        $p = self::folder() . $nama;

        return is_file($p) ? $p : null;
    }

    public static function hapus(string $nama): bool
    {
        $p = self::path($nama);

        return $p !== null && @unlink($p);
    }

    /** Hanya pernyataan yang lazim pada berkas cadangan yang boleh dijalankan saat pemulihan. */
    private static function bolehDijalankan(string $sql): bool
    {
        $s = ltrim(preg_replace('/^(\s|\/\*(?!!).*?\*\/)+/s', '', $sql));
        if (preg_match('/^\/\*!\d+\s+SET\s/i', $s)) {
            return true;
        }
        if (preg_match('/^SET\s+(GLOBAL|PERSIST|@@GLOBAL)/i', $s)) {
            return false;
        }

        return (bool) preg_match('/^(SET\s|DROP\s+TABLE\s|CREATE\s+TABLE\s|INSERT\s+(IGNORE\s+)?INTO\s|REPLACE\s+INTO\s|LOCK\s+TABLES\s|UNLOCK\s+TABLES|ALTER\s+TABLE\s|START\s+TRANSACTION|BEGIN|COMMIT)/i', $s);
    }

    /**
     * Memulihkan database dari berkas cadangan. Lebih dulu dibuat cadangan pengaman ("sebelum-pulih")
     * agar keadaan semula bisa dikembalikan bila pemulihan gagal di tengah jalan.
     *
     * @return array{pernyataan: int, pengaman: string}
     *
     * @throws \RuntimeException
     */
    public static function pulihkan(string $nama): array
    {
        $path = self::path($nama);
        if ($path === null) {
            throw new \RuntimeException('Berkas cadangan tidak ditemukan.');
        }
        @set_time_limit(0);
        $pengaman = self::buat('sebelum-pulih');
        $db       = Database::connect();
        $gz       = str_ends_with($path, '.gz');
        $h        = $gz ? @gzopen($path, 'rb') : @fopen($path, 'rb');
        if (! $h) {
            throw new \RuntimeException('Berkas cadangan tidak dapat dibaca.');
        }

        $buf = '';
        $kutip = '';
        $esc = false;
        $jumlah = 0;
        $baca = static fn () => $gz ? gzgets($h, 1048576) : fgets($h, 1048576);

        try {
            while (($baris = $baca()) !== false) {
                // Baris komentar penuh di luar string dilewati (komentar boleh memuat tanda kutip).
                if ($kutip === '' && trim($buf) === '' && preg_match('/^\s*(--|#)/', $baris)) {
                    continue;
                }
                $len = strlen($baris);
                for ($i = 0; $i < $len; $i++) {
                    $c    = $baris[$i];
                    $buf .= $c;
                    if ($kutip !== '') {
                        if ($esc) {
                            $esc = false;
                        } elseif ($c === '\\' && $kutip !== '`') {
                            $esc = true;
                        } elseif ($c === $kutip) {
                            $kutip = '';
                        }
                        continue;
                    }
                    if ($c === "'" || $c === '"' || $c === '`') {
                        $kutip = $c;
                    } elseif ($c === ';') {
                        $sql = trim($buf);
                        $buf = '';
                        if ($sql === ';' || $sql === '') {
                            continue;
                        }
                        if (! self::bolehDijalankan($sql)) {
                            throw new \RuntimeException('Pernyataan tidak diizinkan: ' . mb_substr(preg_replace('/\s+/', ' ', $sql), 0, 60));
                        }
                        if ($db->simpleQuery($sql) === false) {
                            $err = $db->error();
                            throw new \RuntimeException(($err['message'] ?? 'kesalahan database') . ' (pernyataan ke-' . ($jumlah + 1) . ')');
                        }
                        $jumlah++;
                    }
                }
            }
        } catch (\Throwable $e) {
            throw new \RuntimeException('Pemulihan berhenti: ' . $e->getMessage() . ' Cadangan pengaman tersimpan sebagai ' . $pengaman . '; pulihkan dari berkas itu untuk mengembalikan keadaan semula.', 0, $e);
        } finally {
            $gz ? gzclose($h) : fclose($h);
        }

        return ['pernyataan' => $jumlah, 'pengaman' => $pengaman];
    }

    /** Berkas SQL unggahan dianggap wajar bila memuat CREATE TABLE atau INSERT INTO di bagian awal. */
    public static function tampakSeperti(string $path): bool
    {
        $gz = str_ends_with($path, '.gz');
        $h  = $gz ? @gzopen($path, 'rb') : @fopen($path, 'rb');
        if (! $h) {
            return false;
        }
        $awal = $gz ? gzread($h, 65536) : fread($h, 65536);
        $gz ? gzclose($h) : fclose($h);

        return is_string($awal) && (bool) preg_match('/CREATE\s+TABLE|INSERT\s+INTO/i', $awal);
    }

    /** Menyisakan N cadangan otomatis terbaru (cadangan manual tidak pernah dihapus otomatis). */
    public static function bersihkan(): void
    {
        $simpan = self::pengaturan()['simpan'];
        $oto    = array_values(array_filter(self::daftar(), static fn ($b) => $b['jenis'] === 'Otomatis'));
        foreach (array_slice($oto, $simpan) as $lama) {
            self::hapus($lama['nama']);
        }
    }

    /** Membuat cadangan otomatis bila jadwal jatuh tempo. Mengembalikan nama berkas atau null. */
    public static function autoJikaJatuhTempo(): ?string
    {
        $p = self::pengaturan();
        if ($p['frekuensi'] === 'mati') {
            return null;
        }
        $terakhir = 0;
        foreach (self::daftar() as $b) {
            if ($b['jenis'] === 'Otomatis') {
                $terakhir = max($terakhir, $b['waktu']);
            }
        }
        $jatuhTempo = $terakhir === 0
            || ($p['frekuensi'] === 'harian' ? date('Y-m-d', $terakhir) !== date('Y-m-d') : (time() - $terakhir) >= 7 * 86400);
        if (! $jatuhTempo) {
            return null;
        }

        // Cegah dua permintaan serentak membuat cadangan ganda.
        $kunci = @fopen(self::folder() . '.lock', 'c');
        if (! $kunci || ! flock($kunci, LOCK_EX | LOCK_NB)) {
            return null;
        }
        try {
            $nama = self::buat('otomatis');
            self::bersihkan();

            return $nama;
        } finally {
            flock($kunci, LOCK_UN);
            fclose($kunci);
        }
    }
}
