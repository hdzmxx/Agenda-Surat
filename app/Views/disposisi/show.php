<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<?php
$url = site_url('disposisi/' . $row['disposisi_id'] . '/berkas');
$ext = strtolower(pathinfo((string) $row['berkas'], PATHINFO_EXTENSION));
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">Surat masuk No. agenda <?= no_agenda($row['no_agenda']) ?>/<?= $row['tahun'] ?></h4>
  <a class="btn btn-outline-secondary" href="<?= site_url('disposisi') ?>">Kembali ke disposisi saya</a>
</div>
<div class="card shadow-sm mb-3"><div class="card-body">
  <dl class="row mb-0">
    <?php foreach (['Tanggal diterima' => tgl_id($row['tgl_agenda']), 'Tanggal pada surat' => tgl_id($row['tgl_surat']), 'Nomor surat' => $row['nomor_surat'], 'Asal surat' => $row['pihak'], 'Perihal' => $row['perihal'], 'Sifat' => $row['sifat'], 'Lampiran' => $row['lampiran'], 'Status' => $row['status'], 'Ringkasan isi' => $row['isi'], 'Keterangan' => $row['keterangan']] as $label => $value): ?>
      <dt class="col-sm-3"><?= esc($label) ?></dt><dd class="col-sm-9" style="white-space:pre-wrap"><?= esc($value ?: '-') ?></dd>
    <?php endforeach ?>
    <dt class="col-sm-3">Catatan disposisi</dt><dd class="col-sm-9" style="white-space:pre-wrap"><?= esc($row['catatan'] ?: '-') ?></dd>
  </dl>
</div></div>
<div class="card shadow-sm"><div class="card-header">File surat
  <?php if ($row['berkas']): ?><a class="float-end" href="<?= $url ?>?unduh=1">Unduh <?= esc($row['berkas_asli']) ?></a><?php endif ?>
</div><div class="card-body">
  <?php if (! $row['berkas']): ?><span class="text-muted">Tidak ada file surat.</span>
  <?php elseif ($ext === 'pdf'): ?><iframe src="<?= $url ?>" style="width:100%;height:650px;border:0"></iframe>
  <?php else: ?><img src="<?= $url ?>" class="img-fluid" alt="File surat"><?php endif ?>
</div></div>
<?= $this->endSection() ?>
