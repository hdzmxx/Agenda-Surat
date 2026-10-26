<?php

if (! function_exists('tgl_id')) {
    function tgl_id(?string $d): string
    {
        if (! $d || str_starts_with($d, '0000')) {
            return '-';
        }
        $b = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        [$y, $m, $t] = explode('-', substr($d, 0, 10));

        return (int) $t . ' ' . $b[(int) $m] . ' ' . $y;
    }
}

if (! function_exists('no_agenda')) {
    function no_agenda($n): string
    {
        return str_pad((string) $n, 3, '0', STR_PAD_LEFT);
    }
}

if (! function_exists('badge_class')) {
    function badge_class(?string $v): string
    {
        return match ($v) {
            // Hijau tetap (tidak mengikuti warna tema situs), didefinisikan di layout sebagai .badge-hijau.
            'Selesai', 'Terkirim', 'Diarsipkan' => 'badge-hijau',
            'Diproses' => 'text-bg-primary',
            'Belum diproses', 'Draft', 'Penting' => 'text-bg-warning',
            'Segera', 'Rahasia' => 'text-bg-danger',
            default => 'text-bg-secondary',
        };
    }
}

if (! function_exists('hari_id')) {
    function hari_id(?string $d): string
    {
        if (! $d) {
            return '';
        }

        return ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][(int) date('w', strtotime($d))];
    }
}

if (! function_exists('rentang_hari')) {
    function rentang_hari(?string $a, ?string $b = null): string
    {
        if (! $a) {
            return '';
        }

        return (! $b || $b === $a) ? hari_id($a) : hari_id($a) . ' s.d. ' . hari_id($b);
    }
}

if (! function_exists('rentang_tgl')) {
    function rentang_tgl(?string $a, ?string $b = null): string
    {
        if (! $a) {
            return '';
        }
        if (! $b || $b === $a) {
            return tgl_id($a);
        }
        $bln = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        [$y1, $m1, $t1] = explode('-', $a);
        [$y2, $m2, $t2] = explode('-', $b);
        if ($y1 === $y2 && $m1 === $m2) {
            return (int) $t1 . ' s.d. ' . (int) $t2 . ' ' . $bln[(int) $m1] . ' ' . $y1;
        }
        if ($y1 === $y2) {
            return (int) $t1 . ' ' . $bln[(int) $m1] . ' s.d. ' . tgl_id($b);
        }

        return tgl_id($a) . ' s.d. ' . tgl_id($b);
    }
}

if (! function_exists('fm_olah')) {
    /**
     * Mengurai tanda format pada teks isian surat:
     *   **tebal**   *miring*   __garis bawah__
     * Ketiganya boleh digabung dan bersarang, mis. ***tebal miring*** atau **tebal *miring* tebal**.
     * Tanda hanya berlaku bila berpasangan dan menempel pada teks (bukan pada spasi), sehingga
     * tanda tunggal seperti "5 * 3" tetap tampil apa adanya.
     *
     * $html = true  : keluaran HTML aman dengan <b>, <i>, <u> yang selalu tertutup rapi.
     * $html = false : teks polos tanpa tanda (untuk agenda, rekap, dan sejenisnya).
     */
    function fm_olah(string $s, bool $html): string
    {
        // Indeks genap = teks, indeks ganjil = deret tanda (* atau _).
        $bagian = preg_split('/(\*+|_+)/', $s, -1, PREG_SPLIT_DELIM_CAPTURE);
        $jml    = count($bagian);

        // Tahap 1: ubah setiap deret tanda menjadi token.
        $token = [];
        foreach ($bagian as $i => $isi) {
            if ($i % 2 === 0) {
                $token[] = ['jenis' => 'teks', 'isi' => $isi];

                continue;
            }

            $sebelum = $bagian[$i - 1];
            $sesudah = $bagian[$i + 1];
            // Tanda boleh menutup bila karakter sebelumnya bukan spasi, boleh membuka bila karakter sesudahnya bukan spasi.
            $bisaTutup = $sebelum !== '' ? ! preg_match('/\s$/', $sebelum) : $i > 1;
            $bisaBuka  = $sesudah !== '' ? ! preg_match('/^\s/', $sesudah) : $i + 1 < $jml - 1;

            $n = strlen($isi);
            if ($isi[0] === '*') {
                $daftar = match ($n) {
                    1       => [['i', '*']],
                    2       => [['b', '**']],
                    3       => [['b', '**'], ['i', '*']],
                    default => [],
                };
            } else {
                $daftar = $n === 2 ? [['u', '__']] : [];
            }

            if ($daftar === []) {
                $token[] = ['jenis' => 'teks', 'isi' => $isi];   // deret yang tidak dikenal dibiarkan apa adanya

                continue;
            }
            foreach ($daftar as $d) {
                $token[] = ['jenis' => 'tanda', 'huruf' => $d[0], 'asli' => $d[1], 'buka' => $bisaBuka, 'tutup' => $bisaTutup, 'pasang' => false];
            }
        }

        // Tahap 2: pasangkan tanda pembuka dengan penutupnya (per jenis). Yang tidak berpasangan tetap menjadi teks biasa.
        $terbuka = ['b' => null, 'i' => null, 'u' => null];
        foreach ($token as $idx => $tk) {
            if ($tk['jenis'] !== 'tanda') {
                continue;
            }
            $h = $tk['huruf'];
            if ($terbuka[$h] === null) {
                if ($tk['buka']) {
                    $terbuka[$h] = $idx;
                }
            } elseif ($tk['tutup']) {
                $token[$terbuka[$h]]['pasang'] = true;
                $token[$idx]['pasang']         = true;
                $terbuka[$h]                   = null;
            } elseif ($tk['buka']) {
                $terbuka[$h] = $idx;
            }
        }

        // Tahap 3: susun keluaran. Tumpukan menjaga tag tetap bersarang benar walau tanda saling silang.
        $hasil  = '';
        $tumpuk = [];
        foreach ($token as $tk) {
            if ($tk['jenis'] === 'teks') {
                $hasil .= $html ? esc($tk['isi']) : $tk['isi'];

                continue;
            }
            if (! $tk['pasang']) {
                $hasil .= $tk['asli'];

                continue;
            }
            if (! $html) {
                continue;
            }
            $h = $tk['huruf'];
            if (in_array($h, $tumpuk, true)) {
                $balik = [];
                while (($atas = array_pop($tumpuk)) !== $h) {
                    $hasil .= '</' . $atas . '>';
                    $balik[] = $atas;
                }
                $hasil .= '</' . $h . '>';
                foreach (array_reverse($balik) as $lagi) {
                    $hasil .= '<' . $lagi . '>';
                    $tumpuk[] = $lagi;
                }
            } else {
                $tumpuk[] = $h;
                $hasil   .= '<' . $h . '>';
            }
        }
        while ($tumpuk !== []) {
            $hasil .= '</' . array_pop($tumpuk) . '>';
        }

        if ($html) {
            do {
                $hasil = preg_replace('~<(b|i|u)></\1>~', '', $hasil, -1, $ganti);
            } while ($ganti > 0);
        }

        return $hasil;
    }
}

if (! function_exists('fm_teks')) {
    /** Teks aman HTML dengan **tebal**, *miring*, __garis bawah__, dan baris baru. */
    function fm_teks(?string $s): string
    {
        return nl2br(fm_olah((string) $s, true), false);
    }
}

if (! function_exists('fm_polos')) {
    /** Teks tanpa tanda format, untuk tempat yang tidak menampilkan format (agenda, rekap). */
    function fm_polos(?string $s): string
    {
        return fm_olah((string) $s, false);
    }
}

if (! function_exists('paras')) {
    /**
     * Memecah teks menjadi paragraf (dipisah baris kosong).
     * $isi (opsional) = ['{kode}' => 'nilai']: kode diganti nilainya sesudah format diurai,
     * sehingga format di dalam nilai tidak bentrok dengan tanda di sekitar kode.
     */
    function paras(?string $s, array $isi = []): string
    {
        $s     = (string) $s;
        $ganti = [];
        $n     = 0;
        foreach ($isi as $tanda => $nilai) {
            $kode         = '@@' . $n . '@@';
            $s            = str_replace((string) $tanda, $kode, $s);
            $ganti[$kode] = fm_teks((string) $nilai);
            $n++;
        }

        $out = '';
        foreach (preg_split('/\R\s*\R/', trim($s)) as $p) {
            if (trim($p) !== '') {
                $out .= '<p>' . strtr(fm_teks(trim($p)), $ganti) . '</p>';
            }
        }

        return $out;
    }
}

if (! function_exists('jk_label')) {
    function jk_label(?string $v): string
    {
        return match (strtoupper((string) $v)) {
            'L' => 'Laki-laki',
            'P' => 'Perempuan',
            default => '',
        };
    }
}
