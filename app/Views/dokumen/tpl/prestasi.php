<?php
$s    = $d['siswa'][0] ?? [];
$tglL = ! empty($s['tgl_lahir']) ? tgl_id($s['tgl_lahir']) : '';
$tmpt = (string) ($s['tempat_lahir'] ?? '');
$ttl  = $tmpt . ($tmpt !== '' && $tglL !== '' ? ', ' : '') . $tglL;
?>
<div class="judul"><u>SURAT KETERANGAN PRESTASI</u></div>
<div class="nomor">NOMOR : <?= esc($nomor) ?></div>

<p>Yang bertanda tangan di bawah ini Kepala Sekolah <?= esc($sekolah) ?>:</p>
<div class="det det-siswa">
  <div>Nama</div><div>:</div><div><b><?= esc($ttd['nama']) ?></b></div>
  <div>NIP</div><div>:</div><div><?= esc($ttd['nip']) ?></div>
  <div>Pangkat/Gol</div><div>:</div><div><?= esc($ttd['pangkat']) ?></div>
  <div>Jabatan</div><div>:</div><div>Kepala Sekolah</div>
</div>

<p class="jus">Menerangkan dengan sesungguhnya bahwa nama sebagaimana tersebut di bawah adalah benar-benar Murid pada Satuan Pendidikan yang saya pimpin pada Tahun Ajaran <?= esc($d['tahun_pelajaran']) ?>. Yang bersangkutan telah mengikuti Kejuaraan/Perlombaan <b><?= fm_teks($d['kejuaraan']) ?></b> pada tahun <b><?= esc($d['tahun']) ?></b> yang diselenggarakan oleh <b><?= fm_teks($d['penyelenggara']) ?></b> dengan prestasi yang dicapai sebagai <b><?= fm_teks(rtrim((string) $d['prestasi'], ' .')) ?>.</b></p>

<p>Data Murid dimaksud :</p>
<div class="det" style="grid-template-columns:46mm 3mm 1fr;margin-left:10mm">
  <div>nama</div><div>:</div><div><span class="nm-siswa"><?= esc($s['nama'] ?? '') ?></span></div>
  <div>NISN</div><div>:</div><div><?= esc($s['nisn'] ?? '') ?></div>
  <div>tempat, tanggal lahir</div><div>:</div><div><?= esc($ttl) ?></div>
  <div>asal sekolah</div><div>:</div><div><?= esc($sekolah) ?></div>
</div>

<p class="jus">Demikian Surat Keterangan ini kami buat dengan sebenar-benarnya, untuk dapat dipergunakan sebagaimana mestinya, dan kepada yang berkepentingan untuk menjadikan maklum.</p>
<?= view('dokumen/_ttd', ['gaya' => 'tanggal', 'ttd' => $ttd, 'tempat' => $d['tempat'], 'tgl' => $d['tgl_surat']]) ?>
