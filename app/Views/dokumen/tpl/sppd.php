<?php
/**
 * Format cetak SPPD (lembar depan + lembar belakang).
 * Data pegawai (nama, pangkat/golongan, jabatan) berasal dari master guru yang disalin ke $d saat disimpan.
 * Dokumen lama belum punya field baru, jadi semua pembacaan memakai $h() dengan nilai bawaan.
 */
$h     = static fn (string $k, string $def = ''): string => trim((string) ($d[$k] ?? $def));
$tgl   = static fn (string $v): string => $v !== '' ? tgl_id($v) : '';
$titik = static fn (string $v): string => $v !== '' ? esc($v) : '…………………………';
$titikTgl = static fn (string $v): string => $v !== '' ? esc(tgl_id($v)) : '…………………………';

// Baris "label : isi" memakai tabel agar titik dua dan isi selalu sejajar dalam satu baris.
$kv = static function (array $baris): string {
    $o = '<table class="sppd-kvt">';
    foreach ($baris as [$label, $isi]) {
        $o .= '<tr><td>' . $label . '</td><td>:</td><td>' . $isi . '</td></tr>';
    }

    return $o . '</table>';
};

$tglb    = $h('tglb');
$kembali = $h('tglk') ?: ($h('tglp') ?: ($h('tglt') ?: $tglb));
$lama    = '';
if ($tglb !== '' && $kembali !== '') {
    $selisih = (new DateTime($tglb))->diff(new DateTime($kembali));
    $lama    = $selisih->invert ? '' : ($selisih->days + 1) . ' Hari';
}

$nomorSppd = trim((string) ($nomor ?? ''));
if ($nomorSppd === '' || $nomorSppd === '-') {
    $nomorSppd = $h('sppd_no');
}
$asal      = $h('asal');
$tujuan    = $h('tujuan');
$pejabat   = mb_strtoupper((string) ($ttd['jabatan'] ?? 'Kepala Sekolah'));
$jabatanTtd = mb_strtoupper($h('kepala', 'Kepala ' . $sekolah));
$namaTtd   = $h('ttd');
$pangkatTtd = $h('pangkatttd');
$nipTtd    = $h('nipttd');

// Lembar belakang: II (tempat tujuan) terisi bila tanggal tiba/pulang diisi; III dan IV dari persinggahan.
$adaTujuan = $h('tglt') !== '' || $h('tglp') !== '';
$rute = [
    'II'  => [
        'tiba' => $adaTujuan ? $tujuan : '', 'tglTiba' => $h('tglt'),
        'dari' => $adaTujuan ? $tujuan : '', 'ke' => $adaTujuan ? $asal : '', 'tglPergi' => $h('tglp'),
    ],
    'III' => ['tiba' => $h('s2a'), 'tglTiba' => $h('s2b'), 'dari' => $h('s2c'), 'ke' => $h('s2d'), 'tglPergi' => $h('s2e')],
    'IV'  => ['tiba' => $h('s3a'), 'tglTiba' => $h('s3b'), 'dari' => $h('s3c'), 'ke' => $h('s3d'), 'tglPergi' => $h('s3e')],
];
?>
<div class="sppd">

  <!-- ===== LEMBAR DEPAN ===== -->
  <div class="sppd-depan">
    <div class="sppd-judul">SURAT PERINTAH PERJALANAN DINAS</div>
    <div class="sppd-nomor">Nomor : <?= esc($nomorSppd) ?></div>

    <table class="sppd-t">
      <colgroup><col style="width:10mm"><col style="width:8mm"><col><col style="width:46%"></colgroup>
      <tr class="ak"><td>1.</td><td colspan="2">Pejabat yang memberi perintah</td><td class="v"><?= esc($pejabat) ?></td></tr>
      <tr class="ak"><td>2.</td><td colspan="2">Nama pegawai yang diperintahkan</td><td class="v"><?= esc($h('nama')) ?></td></tr>
      <tr><td>3.</td><td>a.</td><td>Pangkat dan golongan menurut PP No.6 Th.1997</td><td class="v"><?= esc($h('pangkat')) ?></td></tr>
      <tr><td></td><td>b.</td><td>Jabatan</td><td class="v"><?= esc($h('jab')) ?></td></tr>
      <tr class="ak"><td></td><td>c.</td><td>Tingkat menurut perjalanan dinas</td><td class="v"><?= esc($h('tingkat')) ?></td></tr>
      <tr class="ak"><td>4.</td><td colspan="2">Maksud perjalanan dinas</td><td class="v"><?= esc($h('maksud')) ?></td></tr>
      <tr class="ak"><td>5.</td><td colspan="2">Alat angkut yang dipergunakan</td><td class="v"><?= esc($h('alat')) ?></td></tr>
      <tr><td>6.</td><td>a.</td><td>Tempat berangkat</td><td class="v"><?= esc($asal) ?></td></tr>
      <tr class="ak"><td></td><td>b.</td><td>Tempat tujuan</td><td class="v"><?= esc($tujuan) ?></td></tr>
      <tr><td>7.</td><td>a.</td><td>Lamanya perjalanan dinas</td><td class="v"><?= esc($lama) ?></td></tr>
      <tr><td></td><td>b.</td><td>Tanggal berangkat</td><td class="v"><?= esc($tgl($tglb)) ?></td></tr>
      <tr class="ak"><td></td><td>c.</td><td>Tanggal harus kembali</td><td class="v"><?= esc($tgl($kembali)) ?></td></tr>
      <tr class="ak tinggi"><td>8.</td><td colspan="2">Pengikut</td><td class="v"><?= esc($h('pengikut', '-')) ?></td></tr>
      <tr class="ak"><td>9.</td><td colspan="2">Pembebanan anggaran</td><td class="v"><?= esc($h('anggaran')) ?></td></tr>
      <tr><td></td><td>a.</td><td>Instansi</td><td class="v"><?= esc($sekolah) ?></td></tr>
      <tr class="ak"><td></td><td>b.</td><td>Mata anggaran</td><td class="v"><?= esc($h('mata_anggaran')) ?></td></tr>
      <tr class="ak"><td>10.</td><td colspan="2">Keterangan lain-lain</td><td class="v"><?= esc($h('keterangan', '-')) ?></td></tr>
    </table>

    <div class="sppd-ttd">
      <?= $kv([['Dikeluarkan di', esc($h('tempat', 'Kebumen'))], ['Pada tanggal', esc($tgl($h('tgl_surat')))]]) ?>
      <div class="sppd-ttd-isi">
        <div><?= esc($jabatanTtd) ?></div>
        <div class="sppd-ttd-spasi"></div>
        <div class="sppd-nm"><?= esc($namaTtd) ?></div>
        <?php if ($pangkatTtd !== ''): ?><div><?= esc($pangkatTtd) ?></div><?php endif ?>
        <?php if ($nipTtd !== ''): ?><div>NIP. <?= esc($nipTtd) ?></div><?php endif ?>
      </div>
    </div>
  </div>

  <!-- ===== LEMBAR BELAKANG ===== -->
  <div class="sppd-belakang">
    <div class="sppd-meta">
      <div>SPPD No.</div><div>: <?= esc($nomorSppd) ?></div>
      <div>Berangkat dari</div><div>: <?= esc($asal) ?></div>
      <div>Pada tanggal</div><div>: <?= esc($tgl($tglb)) ?></div>
      <div>Ke</div><div>: <?= esc($tujuan) ?></div>
    </div>

    <?php foreach ($rute as $romawi => $r): ?>
      <div class="sppd-rute">
        <div class="sppd-rute-kiri">
          <div class="sppd-rno"><?= $romawi ?>.</div>
          <?= $kv([['Tiba di', $titik($r['tiba'])], ['Pada tanggal', $titikTgl($r['tglTiba'])], ['Kepala', '']]) ?>
        </div>
        <div class="sppd-rute-kanan">
          <?= $kv([['Berangkat dari', $titik($r['dari'])], ['Ke', $titik($r['ke'])], ['Pada tanggal', $titikTgl($r['tglPergi'])]]) ?>
          <div class="sppd-kepala">Kepala</div>
        </div>
      </div>
    <?php endforeach ?>

    <div class="sppd-verif">
      <div></div>
      <div class="sppd-rno">V.</div>
      <div>
        <?= $kv([['Tiba kembali di', $titik($h('tglk') !== '' ? $asal : '')], ['Pada tanggal', $titikTgl($h('tglk'))]]) ?>
        <p>Telah diperiksa, dengan keterangan bahwa perjalanan tersebut di atas benar dilakukan atas perintahnya dan semata-mata untuk kepentingan jabatan dalam waktu yang sesingkat-singkatnya.</p>
        <div class="sppd-ttd-isi sppd-ttd-akhir">
          <div><?= esc($jabatanTtd) ?></div>
          <div class="sppd-ttd-spasi"></div>
          <div class="sppd-nm"><?= esc($namaTtd) ?></div>
          <?php if ($pangkatTtd !== ''): ?><div><?= esc($pangkatTtd) ?></div><?php endif ?>
          <?php if ($nipTtd !== ''): ?><div>NIP. <?= esc($nipTtd) ?></div><?php endif ?>
        </div>
      </div>
    </div>

    <div class="sppd-catatan">
      <div><span class="sppd-rno">VI.</span> CATATAN LAIN-LAIN</div>
      <div class="sppd-garis"></div>
      <div class="sppd-garis"></div>
    </div>
  </div>
</div>
