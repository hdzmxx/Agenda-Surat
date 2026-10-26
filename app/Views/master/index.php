<?= $this->extend('layout') ?>
<?= $this->section('head') ?>
<style>
  /* Teks panjang tidak dipotong ke baris baru; tabel melebar dan bisa digeser ke kanan. */
  .master-tabel td,.master-tabel th{white-space:nowrap}
  /* Kolom tombol Ubah/Hapus tetap terlihat saat tabel digeser. */
  .master-tabel .aksi{position:sticky;right:0;background-color:var(--bs-body-bg);box-shadow:-10px 0 10px -10px rgba(0,0,0,.35)}
  .master-tabel thead .aksi{background-color:var(--bs-tertiary-bg)}
</style>
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<?php helper('ui'); ?>
<div class="page-sticky">
<div class="page-head">
  <div>
    <h1 class="page-title"><?= esc($c['judul']) ?> <span class="count"><?= $total ?> data</span></h1>
  </div>
  <div class="page-actions">
    <a class="btn btn-soft btn-pill" href="<?= site_url("data/$t/template") ?>"><?= ui_icon('download') ?>Unduh template</a>
    <a class="btn btn-soft btn-pill" href="<?= site_url("data/$t/import") ?>"><?= ui_icon('upload') ?>Impor Excel</a>
    <a class="btn btn-success btn-pill" href="<?= site_url("data/$t/baru") ?>"><?= ui_icon('plus') ?>Tambah</a>
  </div>
</div>
<form method="get" class="toolbar" role="search">
  <label class="tb-search"><?= ui_icon('search') ?><input class="form-control" type="search" name="q" value="<?= esc($q) ?>" placeholder="<?= $t === 'siswa' ? 'Cari nama, NISN, NIPD, kelas, alamat...' : 'Cari nama, NIP, jabatan...' ?>" aria-label="Cari data"></label>
  <?php if ($t === 'siswa'): ?>
  <select class="form-select tb-select" name="kelas" aria-label="Kelas"><option value="">Semua kelas</option>
    <?php foreach ($kelas as $kl): ?><option <?= $k === $kl ? 'selected' : '' ?>><?= esc($kl) ?></option><?php endforeach ?></select>
  <?php endif ?>
  <button class="btn btn-success btn-pill" type="submit"><?= ui_icon('search') ?>Cari</button>
  <?php if ($q !== '' || ($t === 'siswa' && ($k ?? '') !== '')): ?><a class="btn btn-link btn-reset" href="<?= site_url("data/$t") ?>"><?= ui_icon('x') ?>Reset</a><?php endif ?>
</form>
</div>
<div class="card shadow-sm table-responsive"><table class="table table-hover align-middle mb-0 text-nowrap master-tabel">
  <thead class="table-light"><tr><?php foreach ($c['kolom'] as $kol): ?><th><?= esc($kol[1]) ?></th><?php endforeach ?><th class="aksi"></th></tr></thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr>
      <?php foreach ($c['kolom'] as $kol): ?><td><?= match ($kol[2] ?? '') { 'date' => tgl_id($r[$kol[0]]), 'jk' => esc(jk_label($r[$kol[0]])), default => esc($r[$kol[0]]) } ?></td><?php endforeach ?>
      <td class="aksi text-end">
        <a class="btn btn-sm btn-outline-secondary" href="<?= site_url("data/$t/" . $r['id'] . '/ubah') ?>">Ubah</a>
        <form class="d-inline" method="post" action="<?= site_url("data/$t/" . $r['id'] . '/hapus') ?>" onsubmit="return confirm('Hapus data ini?')"><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger">Hapus</button></form>
      </td>
    </tr>
  <?php endforeach ?>
  <?php if (! $rows): ?><tr><td colspan="<?= count($c['kolom']) + 1 ?>" class="text-center text-muted py-4">Belum ada data. Klik "Impor dari Excel" atau "+ Tambah".</td></tr><?php endif ?>
  </tbody></table></div>
<?= view('partials/_list_footer', ['pager' => $pager]) ?>
<?= $this->endSection() ?>
