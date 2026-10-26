<?php
$hari = rentang_hari($d['tgl_kegiatan'] ?: null, $d['tgl_kegiatan_akhir'] ?: null);
$tgl  = rentang_tgl($d['tgl_kegiatan'] ?: null, $d['tgl_kegiatan_akhir'] ?: null);
$baris = array_filter([
    'Hari / Tanggal' => $hari ? $hari . ', ' . $tgl : '',
    'Waktu'          => $d['waktu'] ?? '',
    'Tempat'         => $d['tmpk'] ?? '',
    'Keperluan'      => $d['perlu'] ?? '',
]);
// {kegiatan} dan {yth} diganti sesudah format diurai, jadi format di dalam isiannya ikut tampil.
$kodeIsi = ['{kegiatan}' => $d['kegiatan'] ?? '', '{yth}' => $d['yth'] ?? ''];
?>
<div class="kanan"><?= esc($d['tempat']) ?>, <?= tgl_id($d['tgl_surat']) ?></div>
<div class="meta">
  <div>Nomor</div><div>:</div><div><?= esc($nomor) ?></div>
  <div>Lamp</div><div>:</div><div><?= esc($d['lamp'] ?: '-') ?></div>
  <div>Hal</div><div>:</div><div><?= fm_teks($d['hal']) ?></div>
</div>
<div class="kpd">Kepada:<br>Yth. <b><?= esc($d['yth']) ?></b><?= $d['jab'] ? '<br>' . esc($d['jab']) : '' ?><br>di-<br>&nbsp;&nbsp;<?= esc($d['di']) ?></div>
<p>Dengan hormat,</p>
<?= paras($d['pembuka'] ?? '', $kodeIsi) ?>
<?php if ($baris): ?>
<div class="det">
  <?php foreach ($baris as $l => $val): ?><div><?= $l ?></div><div>:</div><div><?= fm_teks($val) ?></div><?php endforeach ?>
</div>
<?php endif ?>
<?= paras($d['penutup'] ?? '', $kodeIsi) ?>
<?= view('dokumen/_ttd', ['gaya' => 'hormat', 'ttd' => $ttd, 'tempat' => $d['tempat'], 'tgl' => $d['tgl_surat']]) ?>
