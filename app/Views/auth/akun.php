<?= $this->extend('layout') ?>

<!-- Tambahkan CSS Cropper.js di bagian head -->
<?= $this->section('head') ?>
<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" rel="stylesheet">
<style>
  /* Memastikan gambar dalam modal tidak melebihi layar */
  .img-container img { max-width: 100%; display: block; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h4 class="mb-3">Akun saya</h4>
<div class="row g-3">
  <div class="col-lg-5">
    <div class="card shadow-sm h-100"><div class="card-body">
      <h5 class="card-title">Foto profil</h5>
      <div class="d-flex align-items-center gap-3 mb-3">
        <!-- Wadah Preview Foto -->
        <div id="previewContainer">
          <?php if (! empty($user['profile_photo'])): ?>
            <img id="profilPreview" src="<?= site_url('akun/foto') ?>?v=<?= esc(rawurlencode($user['profile_photo'])) ?>" alt="Foto profil" class="rounded-circle border" style="width:84px;height:84px;object-fit:cover">
          <?php else: ?>
            <!-- Gambar disembunyikan jika belum ada foto, dan akan muncul saat foto dipilih -->
            <img id="profilPreview" src="" alt="Foto profil" class="rounded-circle border d-none" style="width:84px;height:84px;object-fit:cover">
            <div id="profilInitial" class="rounded-circle border bg-body-secondary d-grid place-items-center fw-bold fs-3" style="width:84px;height:84px;display:grid;place-items:center"><?= esc(mb_strtoupper(mb_substr((string) session('nama'), 0, 1))) ?></div>
          <?php endif ?>
        </div>
        <div><div class="fw-semibold"><?= esc(session('nama')) ?></div><div class="text-muted small"><?= esc(session('role')) ?></div></div>
      </div>
      
      <form method="post" action="<?= site_url('akun/foto') ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <label class="form-label" for="foto">Unggah foto baru (PNG/JPG, maks. 2 MB)</label>
        <input class="form-control mb-3" type="file" id="foto" name="foto" accept="image/png,image/jpeg" required>
        <button class="btn btn-success" type="submit">Simpan foto profil</button>
      </form>
    </div></div>
  </div>
  <div class="col-lg-7">
    <form method="post" action="<?= site_url('akun') ?>" class="card card-body shadow-sm">
      <?= csrf_field() ?>
      <h5 class="card-title">Ganti password</h5>
      <div class="mb-3"><label class="form-label">Password lama</label><input class="form-control" type="password" name="password_lama" required></div>
      <div class="mb-3"><label class="form-label">Password baru (min. 6 karakter)</label><input class="form-control" type="password" name="password_baru" required></div>
      <div class="mb-3"><label class="form-label">Ulangi password baru</label><input class="form-control" type="password" name="konfirmasi" required></div>
      <button class="btn btn-success align-self-start">Simpan password</button>
    </form>
  </div>
</div>

<!-- Modal untuk Cropper -->
<div class="modal fade" id="cropModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="cropModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="cropModalLabel">Sesuaikan Foto Profil</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
        <div class="img-container" style="max-height: 450px; background-color: #000;">
          <img id="imageToCrop" src="">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="btnCrop">Potong & Terapkan</button>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<!-- Tambahkan JS Cropper di bagian script -->
<?= $this->section('scripts') ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const fileInput = document.getElementById('foto');
  const imageToCrop = document.getElementById('imageToCrop');
  const cropModalEl = document.getElementById('cropModal');
  const cropModal = new bootstrap.Modal(cropModalEl);
  const btnCrop = document.getElementById('btnCrop');
  const profilPreview = document.getElementById('profilPreview');
  const profilInitial = document.getElementById('profilInitial');
  let cropper;

  // 1. Saat file dipilih, baca gambar dan buka modal
  fileInput.addEventListener('change', function (e) {
    const files = e.target.files;
    if (files && files.length > 0) {
      const file = files[0];
      // Pastikan yang dipilih adalah gambar
      if (/^image\/\w+/.test(file.type)) {
        const reader = new FileReader();
        reader.onload = function (event) {
          imageToCrop.src = event.target.result;
          cropModal.show();
        };
        reader.readAsDataURL(file);
      } else {
        alert('Harap pilih file gambar (JPG/PNG).');
        fileInput.value = '';
      }
    }
  });

  // 2. Inisialisasi Cropper.js saat modal selesai terbuka
  cropModalEl.addEventListener('shown.bs.modal', function () {
    cropper = new Cropper(imageToCrop, {
      aspectRatio: 1, // Memaksa rasio kotak (1:1)
      viewMode: 1,
      dragMode: 'move',
      autoCropArea: 0.9,
      restore: false,
      guides: true,
      center: true,
      highlight: false,
      cropBoxMovable: true,
      cropBoxResizable: true,
      toggleDragModeOnDblclick: false,
    });
  });

  // 3. Hancurkan instance cropper saat modal ditutup (Batal/Selesai)
  cropModalEl.addEventListener('hidden.bs.modal', function () {
    if (cropper) {
      cropper.destroy();
      cropper = null;
    }
    // Kosongkan input file jika batal (gambar belum di-crop)
    if (!imageToCrop.dataset.cropped) {
       fileInput.value = '';
    }
    imageToCrop.dataset.cropped = "";
  });

  // 4. Proses pemotongan saat tombol ditekan
  btnCrop.addEventListener('click', function () {
    if (!cropper) return;

    cropper.getCroppedCanvas({
      width: 400,
      height: 400,
      imageSmoothingEnabled: true,
      imageSmoothingQuality: 'high',
    }).toBlob(function (blob) {
      // Buat file baru dari hasil crop
      const file = new File([blob], 'profile_cropped.jpg', { type: 'image/jpeg', lastModified: Date.now() });

      // Ganti file di dalam input <input type="file"> menggunakan DataTransfer
      const dataTransfer = new DataTransfer();
      dataTransfer.items.add(file);
      fileInput.files = dataTransfer.files;

      // Update tampilan preview di layar
      const url = URL.createObjectURL(blob);
      profilPreview.src = url;
      profilPreview.classList.remove('d-none');
      if (profilInitial) profilInitial.classList.add('d-none');

      // Beri tanda bahwa crop berhasil agar file tidak dihapus saat modal ditutup
      imageToCrop.dataset.cropped = "true";
      cropModal.hide();
    }, 'image/jpeg', 0.9);
  });
});
</script>
<?= $this->endSection() ?>