<?= $this->extend('layout') ?>
<?php
$mode = old('mode', $settings['mode']);
$theme = old('theme', $settings['theme']);
$appName = old('app_name', $settings['app_name']);
$browserTitle = old('browser_title', $settings['browser_title']);

// Tarik semua warna lama untuk dipasang ke form
$c_primary = old('primary_color', $settings['primary_color']);
$c_accent = old('accent_color', $settings['accent_color']);
$c_soft = old('accent_soft', $settings['accent_soft']);
$c_border = old('accent_border', $settings['accent_border']);
$c_dark = old('accent_dark', $settings['accent_dark']);
$c_s_dark = old('accent_soft_dark', $settings['accent_soft_dark']);
$c_b_dark = old('accent_border_dark', $settings['accent_border_dark']);
?>
<?= $this->section('head') ?>
<style>
  .theme-choice:checked + label{border-color:var(--bs-success);box-shadow:0 0 0 .2rem rgba(var(--bs-success-rgb),.16)}
  .theme-choice:focus-visible + label{outline:2px solid var(--bs-success);outline-offset:2px}
  .color-label {font-size: 0.82rem; font-weight: 600; margin-bottom: 2px;}
</style>
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<h4 class="mb-3">Pengaturan website</h4>
<div class="card shadow-sm" style="max-width:760px">
  <div class="card-body">
    <p class="text-muted">Atur tampilan yang digunakan bersama oleh semua pengguna website.</p>
    <form method="post" action="<?= site_url('website/pengaturan') ?>" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <div class="mb-4">
        <label class="form-label" for="app_name">Nama aplikasi</label>
        <input class="form-control" type="text" id="app_name" name="app_name" maxlength="100" value="<?= esc($appName) ?>" required>
      </div>
      <div class="mb-4">
        <label class="form-label" for="browser_title">Judul tab browser</label>
        <input class="form-control" type="text" id="browser_title" name="browser_title" maxlength="100" value="<?= esc($browserTitle) ?>" required>
        <div class="form-text">Tampil di judul tab setelah nama halaman.</div>
      </div>
      <div class="mb-4">
        <label class="form-label" for="logo">Logo aplikasi (PNG/JPG, maksimal 2 MB)</label>
        <?php if ($settings['logo'] !== null): ?>
          <div class="d-flex align-items-center gap-3 mb-2">
            <img src="<?= site_url('website/logo') ?>?v=<?= esc(rawurlencode($settings['logo'])) ?>" alt="Logo aplikasi saat ini" style="max-width:100px;max-height:72px;object-fit:contain">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="hapus_logo" id="hapus_logo" value="1">
              <label class="form-check-label" for="hapus_logo">Hapus logo saat ini</label>
            </div>
          </div>
        <?php endif ?>
        <input class="form-control" type="file" id="logo" name="logo" accept="image/png,image/jpeg">
        <div class="form-text">Logo ditampilkan di bagian atas aplikasi.</div>
      </div>
      <div class="mb-4">
        <label class="form-label" for="favicon">Ikon tab browser (PNG, maksimal 512 KB)</label>
        <?php if ($settings['favicon'] !== null): ?>
          <div class="d-flex align-items-center gap-3 mb-2">
            <img src="<?= site_url('website/favicon') ?>?v=<?= esc(rawurlencode($settings['favicon'])) ?>" alt="Ikon tab browser saat ini" style="width:40px;height:40px;object-fit:contain">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="hapus_favicon" id="hapus_favicon" value="1">
              <label class="form-check-label" for="hapus_favicon">Hapus ikon tab saat ini</label>
            </div>
          </div>
        <?php endif ?>
        <input class="form-control" type="file" id="favicon" name="favicon" accept="image/png">
        <div class="form-text">Sebaiknya gunakan gambar persegi, misalnya 32 × 32 atau 64 × 64 piksel.</div>
      </div>
      <fieldset class="mb-4">
        <legend class="fs-6 fw-semibold">Tema website</legend>
        <div class="row g-2">
          <?php foreach ($themes as $key => $option): ?>
            <div class="col-sm-6 col-lg-4">
              <input class="btn-check theme-choice" type="radio" name="theme" id="theme-<?= esc($key) ?>" value="<?= esc($key) ?>" <?= $theme === $key ? 'checked' : '' ?>>
              <label class="form-control h-100 d-flex align-items-center gap-2" for="theme-<?= esc($key) ?>" style="cursor:pointer">
                <span class="d-flex flex-shrink-0 overflow-hidden rounded-circle border" style="width:30px;height:30px">
                  <span style="width:50%;background:<?= esc($option['primary_color'] ?? $c_primary) ?>"></span>
                  <span style="width:50%;background:<?= esc($key === 'custom' ? $c_accent : $option['accent_color']) ?>"></span>
                </span>
                <span><?= esc($option['name']) ?></span>
              </label>
            </div>
          <?php endforeach ?>
        </div>
      </fieldset>
      <fieldset class="mb-4">
        <legend class="fs-6 fw-semibold">Mode tampilan</legend>
        <div class="d-flex flex-wrap gap-3">
          <div class="form-check">
            <input class="form-check-input" type="radio" name="mode" id="mode-light" value="light" <?= $mode === 'light' ? 'checked' : '' ?>>
            <label class="form-check-label" for="mode-light">Terang</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="mode" id="mode-dark" value="dark" <?= $mode === 'dark' ? 'checked' : '' ?>>
            <label class="form-check-label" for="mode-dark">Gelap</label>
          </div>
        </div>
      </fieldset>

      <!-- BAGIAN BARU: SET PALET WARNA KUSTOM -->
      <fieldset class="mb-4 p-3 border rounded bg-body-tertiary">
        <legend class="fs-6 fw-semibold mb-3 float-none w-auto px-1">Palet Warna Kustom</legend>
        <div class="form-text mb-3 mt-0 text-muted">Hanya aktif dan dapat diubah jika Anda memilih opsi "Warna Kustom" di atas.</div>
        <div class="row g-3">
          <div class="col-6 col-md-4">
            <label class="color-label">Utama (Primary)</label>
            <input class="form-control form-control-color w-100 clr-input" type="color" name="primary_color" data-key="primary_color" value="<?= esc($c_primary) ?>" <?= $theme !== 'custom' ? 'disabled' : '' ?>>
          </div>
          <div class="col-6 col-md-4">
            <label class="color-label">Aksen (Hover/Aktif)</label>
            <input class="form-control form-control-color w-100 clr-input" type="color" name="accent_color" data-key="accent_color" value="<?= esc($c_accent) ?>" <?= $theme !== 'custom' ? 'disabled' : '' ?>>
          </div>
          <div class="col-6 col-md-4">
            <label class="color-label">Background (Soft)</label>
            <input class="form-control form-control-color w-100 clr-input" type="color" name="accent_soft" data-key="accent_soft" value="<?= esc($c_soft) ?>" <?= $theme !== 'custom' ? 'disabled' : '' ?>>
          </div>
          <div class="col-6 col-md-4">
            <label class="color-label">Garis Tepi (Border)</label>
            <input class="form-control form-control-color w-100 clr-input" type="color" name="accent_border" data-key="accent_border" value="<?= esc($c_border) ?>" <?= $theme !== 'custom' ? 'disabled' : '' ?>>
          </div>
        </div>

        <div class="fs-6 fw-semibold mt-4 mb-2">Palet Mode Gelap</div>
        <div class="row g-3">
          <div class="col-6 col-md-4">
            <label class="color-label">Aksen (Gelap)</label>
            <input class="form-control form-control-color w-100 clr-input" type="color" name="accent_dark" data-key="accent_dark" value="<?= esc($c_dark) ?>" <?= $theme !== 'custom' ? 'disabled' : '' ?>>
          </div>
          <div class="col-6 col-md-4">
            <label class="color-label">BG Soft (Gelap)</label>
            <input class="form-control form-control-color w-100 clr-input" type="color" name="accent_soft_dark" data-key="accent_soft_dark" value="<?= esc($c_s_dark) ?>" <?= $theme !== 'custom' ? 'disabled' : '' ?>>
          </div>
          <div class="col-6 col-md-4">
            <label class="color-label">Border (Gelap)</label>
            <input class="form-control form-control-color w-100 clr-input" type="color" name="accent_border_dark" data-key="accent_border_dark" value="<?= esc($c_b_dark) ?>" <?= $theme !== 'custom' ? 'disabled' : '' ?>>
          </div>
        </div>
      </fieldset>
      <!-- AKHIR BAGIAN BARU -->

      <button class="btn btn-success">Simpan pengaturan</button>
    </form>
  </div>
</div>
<script>
  (function () {
    // Inject json dari helper agar JS tahu isi tiap-tiap palet warna preset
    var themes = <?= json_encode($themes, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var choices = document.querySelectorAll('.theme-choice');
    var clrInputs = document.querySelectorAll('.clr-input');

    choices.forEach(function (choice) {
      choice.addEventListener('change', function () {
        var isCustom = (choice.value === 'custom');
        
        // Aktifkan / Non-aktifkan semua input warna tergantung opsi Custom atau bukan
        clrInputs.forEach(function (inp) {
          inp.disabled = !isCustom;
          
          // Jika bukan custom, isi otomatis preview warnanya
          if (!isCustom && themes[choice.value]) {
            var key = inp.getAttribute('data-key');
            if (themes[choice.value][key]) {
              inp.value = themes[choice.value][key];
            }
          }
        });
      });
    });

    // Jika user iseng mengklik paksa kotak warna (misalnya saat baru dihapus label disable-nya via inspect)
    // otomatis pilih tema custom
    clrInputs.forEach(function (inp) {
      inp.addEventListener('input', function () {
        var customRad = document.getElementById('theme-custom');
        if (customRad && !customRad.checked) {
          customRad.checked = true;
          clrInputs.forEach(function(i) { i.disabled = false; });
        }
      });
    });
  })();
</script>
<?= $this->endSection() ?>