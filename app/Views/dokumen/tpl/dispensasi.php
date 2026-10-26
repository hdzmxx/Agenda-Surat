<?php
$siswa = $d['siswa'] ?? [];
$tgl   = rentang_tgl($d['tgl_mulai'] ?: null, $d['tgl_selesai'] ?: null);
?>
<div class="judul">SURAT DISPENSASI</div>
<div class="nomor"><b>Nomor: <?= esc($nomor) ?></b></div>

<p>Yang bertanda tangan di bawah ini:</p>
<div class="det" style="grid-template-columns:22mm 3mm 1fr;row-gap:2mm;margin-left:12mm">
  <div><b>Nama</b></div><div>:</div><div><?= esc($ttd['nama']) ?></div>
  <div><b>NIP</b></div><div>:</div><div><?= esc($ttd['nip']) ?></div>
  <div><b>Jabatan</b></div><div>:</div><div><?= esc($ttd['jabatan']) ?></div>
</div>

<p class="jus">dengan ini memberikan dispensasi kepada siswa-siswi <?= esc($sekolah) ?> sebagaimana tercantum dalam daftar berikut:</p>
<?= view('dokumen/_tabel_siswa', ['siswa' => $siswa, 'besar' => true]) ?>

<p>Adapun kegiatan <?= fm_teks(rtrim((string) $d['kegiatan'], ' .,;:')) ?> dilaksanakan pada:</p>
<div class="det" style="grid-template-columns:23mm 3mm 1fr;row-gap:2mm;margin-left:14mm">
  <div>tanggal</div><div>:</div><div><?= esc($tgl) ?></div>
  <?php if (! empty($d['waktu'])): ?><div>waktu</div><div>:</div><div><?= fm_teks($d['waktu']) ?></div><?php endif ?>
  <?php if (! empty($d['tmpk'])): ?><div>tempat</div><div>:</div><div><?= fm_teks($d['tmpk']) ?></div><?php endif ?>
</div>

<p class="jus">Selama mengikuti kegiatan tersebut, siswa yang bersangkutan dibebaskan dari kegiatan pembelajaran di sekolah pada waktu yang telah ditentukan dan tetap berkewajiban untuk mengikuti serta menyelesaikan pembelajaran atau tugas yang ditinggalkan.</p>
<p class="jus">Demikian surat dispensasi ini dibuat untuk dapat dipergunakan sebagaimana mestinya.</p>
<?= view('dokumen/_ttd', ['gaya' => 'tanggal', 'ttd' => $ttd, 'tempat' => $d['tempat'], 'tgl' => $d['tgl_surat']]) ?>