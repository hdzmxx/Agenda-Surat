<?= $this->extend('layout') ?>

<?= $this->section('head') ?>
<style>
  /* Mengatur warna highlight saat kursor mengambang (hover) */
  .list-group-item-action:hover {
    background-color: rgba(0, 0, 0, 0.08) !important; 
  }
  
  /* Otomatis menyesuaikan jika website berganti ke mode gelap */
  [data-bs-theme=dark] .list-group-item-action:hover {
    background-color: rgba(255, 255, 255, 0.1) !important; 
  }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h4 class="mb-3 fw-semibold">Dashboard <small class="text-muted fs-6 fw-normal"><?= esc(env('NAMA_SEKOLAH', '')) ?></small></h4>
<div class="row g-3 mb-4">
  <?php 
  // Menambahkan ikon spesifik untuk setiap kotak pada elemen ke-4 di dalam array
  $stats = [
      ['Total surat masuk', $total_masuk, 'info', '📥'],
      ['Total surat keluar', $total_keluar, 'success', '📤'],
      ['Masuk belum diproses', $belum, 'primary', '⏳'],
      ['Bulan ini (masuk / keluar)', $bln_masuk . ' / ' . $bln_keluar, 'danger', '📅']
  ];
  foreach ($stats as [$l, $v, $color, $icon]): 
  ?>
  <div class="col-6 col-xl-3">
    <div class="card dashboard-stat dashboard-stat-<?= esc($color) ?> border-0 shadow-sm h-100">
      <div class="card-body position-relative overflow-hidden">
        <div class="fs-1 fw-bold lh-1 mb-2"><?= esc($v) ?></div>
        <div class="small fw-semibold"><?= esc($l) ?></div>
        <!-- Menampilkan ikon dinamis dan menaikkan opacity menjadi 0.35 agar lebih kontras -->
        <span class="dashboard-stat-icon" aria-hidden="true" style="opacity: 0.75;"><?= $icon ?></span>
      </div>
      <a class="dashboard-stat-link" href="<?= site_url($color === 'info' ? 'surat/masuk' : 'surat/keluar') ?>">Lihat surat <span aria-hidden="true">➜</span></a>
    </div>
  </div>
  <?php endforeach ?>
</div>
<div class="row g-3">
  <?php 
  foreach (['masuk' => $terbaru_masuk, 'keluar' => $terbaru_keluar] as $a => $rows): 
  ?>
  <div class="col-lg-6">
    <div class="card shadow-sm h-100">
      <div class="card-header d-flex justify-content-between align-items-center">Surat <?= $a ?> terbaru
        <a class="btn btn-sm btn-success" href="<?= site_url("surat/$a/baru") ?>">+ Tambah</a>
      </div>
      <ul class="list-group list-group-flush">
        <?php foreach ($rows as $r): ?>
        
        <!-- 1. Tambahkan list-group-item-action (untuk efek highlight abu-abu) dan position-relative -->
        <li class="list-group-item list-group-item-action d-flex justify-content-between gap-2 position-relative">
          
          <!-- 2. Tambahkan stretched-link agar klik berlaku untuk seluruh baris, dan color:inherit agar warna teks tidak berubah -->
          <a class="text-decoration-none stretched-link" href="<?= site_url("surat/$a/" . $r['id']) ?>" style="color: inherit;">
            <?= esc($r['perihal']) ?><br>
            <small class="text-muted"><?= esc($r['pihak']) ?>, <?= tgl_id($r['tgl_agenda']) ?></small>
          </a>
          
          <span class="badge <?= badge_class($r['status']) ?> align-self-start"><?= esc($r['status']) ?></span>
        </li>
        
        <?php endforeach ?>
        <?php if (! $rows): ?><li class="list-group-item text-muted">Belum ada data.</li><?php endif ?>
      </ul>
    </div>
  </div>
  <?php endforeach ?>
</div>
<?= $this->endSection() ?>