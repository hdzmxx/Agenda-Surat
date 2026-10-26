<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<?php
$masuk = $arah === 'masuk';
$baris = $masuk
    ? ['Tanggal diterima' => tgl_id($row['tgl_agenda']), 'Tanggal pada surat' => tgl_id($row['tgl_surat']), 'Nomor surat' => $row['nomor_surat'], 'Asal surat' => $row['pihak'], 'Perihal' => $row['perihal'], 'Disposisi kepada' => $row['disposisi'], 'Sifat' => $row['sifat'], 'Lampiran' => $row['lampiran'], 'Status' => $row['status'], 'Ringkasan isi' => $row['isi'], 'Keterangan' => $row['keterangan']]
    : ['Tanggal surat' => tgl_id($row['tgl_agenda']), 'Nomor surat' => $row['nomor_surat'], 'Tujuan' => $row['pihak'], 'Perihal' => $row['perihal'], 'Jenis' => $row['jenis'], 'Penandatangan' => $row['penandatangan'], 'Sifat' => $row['sifat'], 'Lampiran' => $row['lampiran'], 'Status' => $row['status'], 'Ringkasan isi' => $row['isi'], 'Keterangan' => $row['keterangan']];
$ext = strtolower(pathinfo((string) $row['berkas'], PATHINFO_EXTENSION));
$url = site_url("surat/$arah/" . $row['id'] . '/berkas');
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">Surat <?= $arah ?> No. agenda <?= no_agenda($row['no_agenda']) ?>/<?= $row['tahun'] ?></h4>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-secondary" href="<?= site_url("surat/$arah") ?>">Kembali</a>
    <a class="btn btn-success" href="<?= site_url("surat/$arah/" . $row['id'] . '/ubah') ?>">Ubah</a>
  </div>
</div>
<div class="card shadow-sm mb-3"><div class="card-body"><dl class="row mb-0">
  <?php foreach ($baris as $l => $val): ?>
    <dt class="col-sm-3"><?= $l ?></dt><dd class="col-sm-9" style="white-space:pre-wrap"><?= esc($val ?: '-') ?></dd>
  <?php endforeach ?>
</dl></div></div>
<?php if ($masuk && ! empty($disposisi)): ?>
<div class="card shadow-sm mb-3">
  <div class="card-header">Catatan disposisi</div>
  <ul class="list-group list-group-flush">
    <?php foreach ($disposisi as $item): ?>
      <li class="list-group-item">
        <strong><?= esc($item['nama']) ?></strong>
        <div style="white-space:pre-wrap"><?= esc($item['catatan'] ?: 'Tidak ada catatan.') ?></div>
      </li>
    <?php endforeach ?>
  </ul>
</div>
<?php endif ?>
<div class="card shadow-sm"><div class="card-header d-flex justify-content-between">File surat
  <?php if ($row['berkas']): ?><a href="<?= $url ?>?unduh=1">Unduh <?= esc($row['berkas_asli']) ?></a><?php endif ?></div>
  <div class="card-body">
  <?php if (! $row['berkas']): ?><span class="text-muted">Belum ada file yang diunggah.</span>
  <?php elseif ($ext === 'pdf'): ?><iframe src="<?= $url ?>" style="width:100%;height:650px;border:0"></iframe>
  <?php else: ?><img src="<?= $url ?>" class="img-fluid" alt="File surat"><?php endif ?>
  </div></div>
<?= $this->endSection() ?>
