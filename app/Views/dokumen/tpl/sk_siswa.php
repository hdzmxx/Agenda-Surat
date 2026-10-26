<?php
$siswa  = $d['siswa'] ?? [];
$satu   = count($siswa) === 1;
$s      = $siswa[0] ?? [];
$nipd   = $s['nipd'] ?? ($s['nik'] ?? '');
$nisnik = trim($nipd . ($nipd && ($s['nisn'] ?? '') ? ' / ' : '') . ($s['nisn'] ?? ''));
?>
<div class="judul"><u>SURAT KETERANGAN AKTIF SISWA</u></div>
<div class="nomor">Nomor: <?= $satu ? '<b>' . esc($nomor) . '</b>' : esc($nomor) ?></div>

<?php if ($satu): ?>
<p>Yang bertanda tangan di bawah ini Kepala Sekolah <?= esc($sekolah) ?>:</p>
<div class="det det-siswa">
  <div>Nama</div><div>:</div><div><b><?= esc($ttd['nama']) ?></b></div>
  <div>NIP</div><div>:</div><div><?= esc($ttd['nip']) ?></div>
  <div>Pangkat/Gol</div><div>:</div><div><?= esc($ttd['pangkat']) ?></div>
  <div>Jabatan</div><div>:</div><div>Kepala Sekolah</div>
</div>
<p>Menerangkan bahwa:</p>
<div class="det det-siswa">
  <div>Nama</div><div>:</div><div><b class="nm-siswa"><?= esc($s['nama'] ?? '') ?></b></div>
  <div>NIPD/NISN</div><div>:</div><div><?= esc($nisnik) ?></div>
  <div>Kelas</div><div>:</div><div><?= esc($s['kelas'] ?? '') ?></div>
  <?php if (! empty($s['jk'])): ?><div>Jenis Kelamin</div><div>:</div><div><?= esc(jk_label($s['jk'])) ?></div><?php endif ?>
  <div>Tempat, Tanggal lahir</div><div>:</div><div><?= esc($s['tempat_lahir'] ?? '') ?>, <?= tgl_id($s['tgl_lahir'] ?? null) ?></div>
  <div>Alamat</div><div>:</div><div><?= esc($s['alamat'] ?? '') ?></div>
</div>
<p>adalah benar peserta didik <?= esc($sekolah) ?> pada kelas <b><?= esc($s['kelas'] ?? '') ?></b> Tahun Pelajaran <?= esc($d['tahun_pelajaran']) ?>.</p>
<?php else: ?>
<p>Yang bertanda tangan di bawah ini, Kepala <?= esc($sekolah) ?>, dengan ini menerangkan bahwa:</p>
<!-- Lebar tabel dikunci maksimal 100% agar tidak menembus margin kertas -->
<table class="tbl" style="width: 80%; max-width: 100%; margin: 0 auto 4mm; line-height: 1.2; border-collapse: collapse;">
  <thead>
    <tr>
      <!-- width: 1% & nowrap memaksa kolom menyusut pas sesuai teks/angka di dalamnya -->
      <th style="width: 1%; white-space: nowrap; padding: 1px 6px;">No.</th>
      <!-- Kolom nama dikosongkan width-nya agar otomatis melebar mengambil sisa ruang -->
      <th style="padding: 6px 6px; text-align: center;">Nama Siswa</th>
      <th style="width: 1%; white-space: nowrap; padding: 1px 10px;">JK</th>
      <th style="width: 1%; white-space: nowrap; padding: 1px 30px;">NISN</th>
      <th style="width: 1%; white-space: nowrap; padding: 1px 10px;">Kelas</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($siswa as $i => $x): ?>
      <?php 
        $jk_mentah = jk_label($x['jk']);
        $jk_singkat = (strcasecmp($jk_mentah, 'Laki-laki') === 0 || strcasecmp($jk_mentah, 'L') === 0) ? 'L' : 'P';
      ?>
      <tr>
        <td class="c" style="white-space: nowrap; padding: 1px 6px;"><?= $i + 1 ?></td>
        <td style="padding: 1px 6px;"><span class="nm-siswa"><?= esc($x['nama']) ?></span></td>
        <td class="c" style="white-space: nowrap; padding: 1px 6px;"><?= esc($jk_singkat) ?></td>
        <td class="c" style="white-space: nowrap; padding: 1px 6px;"><?= esc($x['nisn']) ?></td>
        <td class="c" style="white-space: nowrap; padding: 1px 6px;"><?= esc($x['kelas']) ?></td>
      </tr>
    <?php endforeach ?>
  </tbody>
</table>


<p>Adalah benar merupakan siswa aktif di <b><?= esc($sekolah) ?></b> Tahun Ajaran <?= esc($d['tahun_pelajaran']) ?>.</p>
<?php endif ?>
<?php if (! empty($d['keperluan'])): ?><?= paras($d['keperluan']) ?><?php endif ?>
<?= paras($d['penutup']) ?>
<?= view('dokumen/_ttd', ['gaya' => 'tanggal', 'ttd' => $ttd, 'tempat' => $d['tempat'], 'tgl' => $d['tgl_surat']]) ?>