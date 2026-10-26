<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<h4 class="mb-3"><?= esc($title) ?></h4>
<div class="row g-3">
  <div class="col-lg-6"><div class="card card-body shadow-sm">
    <h6>1. Unduh template</h6>
    <p class="text-muted small">Isi data mulai baris ke-2 sesuai urutan kolom di template. Baris contoh (diawali "CONTOH") otomatis dilewati.</p>
    <a class="btn btn-outline-secondary mb-3" href="<?= site_url("data/$t/template") ?>">Unduh template <?= $lib ? '(.xlsx)' : '(.csv)' ?></a>
    <h6>2. Unggah file</h6>
    <form method="post" action="<?= site_url("data/$t/import") ?>" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input class="form-control mb-2" type="file" name="berkas" accept=".xlsx,.xls,.csv" required>
      <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="ganti" value="1" id="g"><label class="form-check-label" for="g">Hapus semua data lama sebelum impor (ganti total)</label></div>
      <button class="btn btn-success">Impor</button> <a class="btn btn-outline-secondary" href="<?= site_url("data/$t") ?>">Batal</a>
    </form>
  </div></div>
  <div class="col-lg-6"><div class="card card-body shadow-sm">
    <h6>Urutan kolom</h6>
    <ol class="mb-2"><?php foreach ($c['kolom'] as $kol): ?><li><?= esc($kol[1]) ?><?= match ($kol[2] ?? '') { 'date' => ' (format 2013-10-04 atau 04/10/2013)', 'jk' => ' (L / P atau Laki-laki / Perempuan)', default => '' } ?></li><?php endforeach ?></ol>
    <p class="small text-muted mb-1">Kolom dikenali dari judulnya, jadi urutan kolom boleh berbeda dan kolom yang tidak ada dilewati. Jika <?= $t === 'guru' ? 'NIP' : 'NISN' ?> sudah ada di data, barisnya memperbarui data tersebut (tidak dobel).</p>
    <?php if (! $lib): ?><div class="alert alert-warning small mb-0">Pustaka Excel belum terpasang, jadi hanya file <b>.csv</b> yang bisa diimpor. Untuk .xlsx jalankan <code>composer require phpoffice/phpspreadsheet</code> di folder proyek.</div><?php endif ?>
  </div></div>
</div>
<?= $this->endSection() ?>
