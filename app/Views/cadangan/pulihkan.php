<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<?php helper(['ui', 'tanggal']); ?>
<div class="page-head">
  <div>
    <h1 class="page-title">Pulihkan database</h1>
    <p class="page-sub">Isi database akan diganti dengan isi berkas cadangan berikut.</p>
  </div>
</div>

<div class="card shadow-sm" style="max-width:720px">
  <div class="card-body">
    <dl class="row mb-3">
      <dt class="col-sm-3">Berkas</dt><dd class="col-sm-9"><?= esc($berkas['nama']) ?></dd>
      <dt class="col-sm-3">Jenis</dt><dd class="col-sm-9"><?= esc($berkas['jenis']) ?></dd>
      <dt class="col-sm-3">Dibuat</dt><dd class="col-sm-9"><?= esc(tgl_id(date('Y-m-d', $berkas['waktu']))) ?>, <?= date('H:i', $berkas['waktu']) ?></dd>
    </dl>

    <div class="alert alert-warning">
      <strong>Perhatian.</strong> Semua tabel dalam berkas ini akan ditimpa, sehingga surat, data guru/siswa, dan akun pengguna
      yang dibuat <em>setelah</em> cadangan ini hilang. Sebelum menimpa, aplikasi membuat cadangan pengaman bertanda &ldquo;Sebelum pulih&rdquo;
      yang bisa dipulihkan lagi bila perlu. Bila akun Anda tidak ada di cadangan, Anda perlu masuk ulang. Berkas unggahan (writable/uploads) tidak berubah.
    </div>

    <form method="post" action="<?= site_url('cadangan/pulihkan/' . rawurlencode($berkas['nama'])) ?>" class="row g-3 align-items-end" id="formPulih">
      <?= csrf_field() ?>
      <div class="col-md-6">
        <label class="form-label" for="konfirmasi">Ketik <code>PULIHKAN</code> untuk melanjutkan</label>
        <input class="form-control" id="konfirmasi" name="konfirmasi" autocomplete="off" autocapitalize="characters" required>
      </div>
      <div class="col-auto d-flex gap-2">
        <button class="btn btn-danger btn-pill" id="tombolPulih" type="submit" disabled>Pulihkan sekarang</button>
        <a class="btn btn-soft btn-pill" href="<?= site_url('cadangan') ?>">Batal</a>
      </div>
    </form>
  </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
(function () {
  var i = document.getElementById('konfirmasi'), t = document.getElementById('tombolPulih');
  i.addEventListener('input', function () { t.disabled = i.value.trim() !== 'PULIHKAN'; });
  document.getElementById('formPulih').addEventListener('submit', function () { t.disabled = true; t.textContent = 'Memulihkan...'; });
})();
</script>
<?= $this->endSection() ?>
