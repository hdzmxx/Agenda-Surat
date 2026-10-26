<?php
$guru  = $d['guru'] ?? [];
$hari  = rentang_hari($d['tgl_mulai'] ?: null, $d['tgl_selesai'] ?: null);
$tgl   = rentang_tgl($d['tgl_mulai'] ?: null, $d['tgl_selesai'] ?: null);
$poin2 = array_values(array_filter(array_map('trim', preg_split('/\R/', (string) ($d['poin2'] ?? '')))));
?>
<div class="judul">SURAT TUGAS</div>
<div class="nomor">NOMOR : <?= esc($nomor) ?></div>

<?php if (! empty($d['dasar'])): ?>
<div class="tg"><div>Dasar</div><div>:</div><div class="jus"><?= fm_teks($d['dasar']) ?></div></div>
<?php endif ?>

<div class="judul2">MEMERINTAHKAN</div>

<div class="tg"><div>Kepada</div><div>:</div><div>
  <?php foreach ($guru as $i => $g): ?>
  <div class="org" style="margin-bottom: 3mm;"><div><?= $i + 1 ?>.</div>
    <div class="kv2" style="row-gap: 0mm; line-height: 1.3;">
      <div>Nama</div><div>:</div><div><?= esc($g['nama']) ?></div>
      <div>NIP</div><div>:</div><div><?= esc($g['nip'] ?: '-') ?></div>
      <div>Pangkat / Gol Ruang</div><div>:</div><div><?= esc($g['pangkat_gol'] ?: '-') ?></div>
      <div>Jabatan</div><div>:</div><div><?= esc($g['jabatan'] ?: '-') ?></div>
    </div></div>
  <?php endforeach ?>
</div></div>

<div class="tg"><div>Untuk</div><div>:</div><div>
  <div class="org"><div>1.</div><div>
    <div class="jus">Melaksanakan tugas sebagai <?= fm_teks($d['tugas']) ?>, pada:</div>
    <div class="kv2">
      <div>Hari</div><div>:</div><div><?= esc($hari) ?></div>
      <div>Tanggal</div><div>:</div><div><?= esc($tgl) ?></div>
      <?php if (! empty($d['tmpk'])): ?><div>Tempat</div><div>:</div><div><?= fm_teks($d['tmpk']) ?></div><?php endif ?>
      <?php if (! empty($d['acara'])): ?><div>Acara</div><div>:</div><div><?= fm_teks($d['acara']) ?></div><?php endif ?>
    </div></div></div>
  <?php foreach ($poin2 as $i => $p): ?>
  <div class="org"><div><?= $i + 2 ?>.</div><div class="jus"><?= fm_teks($p) ?></div></div>
  <?php endforeach ?>
</div></div>
<?= view('dokumen/_ttd', ['gaya' => 'tanggal', 'ttd' => $ttd, 'tempat' => $d['tempat'], 'tgl' => $d['tgl_surat']]) ?>
