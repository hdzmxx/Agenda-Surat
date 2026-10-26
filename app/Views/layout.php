<!doctype html>
<?php
helper('website');
$website = website_settings();
$path = trim(service('uri')->getPath(), '/');
$path = preg_replace('#^index\.php/?#', '', $path);
$isAdmin = session('role') === 'admin';
$isGuru = session('role') === 'guru';
$roleLabel = trim((string) session('jabatan')) ?: ($isAdmin ? 'Administrator' : ($isGuru ? 'Guru / Staf TU' : 'Staf'));
$appName = $website['app_name'];
$profilePhoto = session('profile_photo');
$profilePhotoUrl = $profilePhoto ? site_url('akun/foto') . '?v=' . rawurlencode($profilePhoto) : '';
$isActive = static fn (string $target): bool => $target === ''
    ? $path === ''
    : ($path === $target || str_starts_with($path, $target . '/'));
?>
<html lang="id" data-bs-theme="<?= esc($website['mode']) ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= esc($title ?? $judul ?? $appName) ?> | <?= esc($website['browser_title']) ?></title>
  <?php if ($website['favicon'] !== null): ?>
    <link rel="icon" type="image/png" href="<?= site_url('website/favicon') ?>?v=<?= esc(rawurlencode($website['favicon'])) ?>">
  <?php endif ?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    html[data-bs-theme] {
      --bs-success: <?= esc($website['primary_color']) ?>;
      --bs-success-rgb: <?= esc(website_color_rgb($website['primary_color'])) ?>;
      --bs-link-color: <?= esc($website['primary_color']) ?>;
      --bs-link-color-rgb: <?= esc(website_color_rgb($website['primary_color'])) ?>;
      --bs-link-hover-color: <?= esc($website['primary_color']) ?>;
      --bs-link-hover-color-rgb: <?= esc(website_color_rgb($website['primary_color'])) ?>;
      --website-on-primary: <?= esc(website_contrast_color($website['primary_color'])) ?>;
      --website-accent: <?= esc($website['accent_color']) ?>;
      --website-accent-soft: <?= esc($website['accent_soft']) ?>;
      --website-accent-border: <?= esc($website['accent_border']) ?>;
    }
    html[data-bs-theme=dark] {
      --website-accent: <?= esc($website['accent_dark']) ?>;
      --website-accent-soft: <?= esc($website['accent_soft_dark']) ?>;
      --website-accent-border: <?= esc($website['accent_border_dark']) ?>;
    }
    /* Mode gelap: hitam netral. Latar halaman #0F0F0F, permukaan (kartu, tabel, kolom isian) #171717, teks #E3E3E3. */
    html[data-bs-theme=dark] {
      --bs-body-bg: #171717;
      --bs-body-bg-rgb: 23, 23, 23;
      --bs-tertiary-bg: #0F0F0F;
      --bs-tertiary-bg-rgb: 15, 15, 15;
      --bs-secondary-bg: #1F1F1F;
      --bs-secondary-bg-rgb: 31, 31, 31;
      --bs-body-color: #E3E3E3;
      --bs-body-color-rgb: 227, 227, 227;
      --bs-border-color: #2E2E2E;
      --bs-border-color-translucent: rgba(255, 255, 255, .1);
    }
    html[data-bs-theme=dark] .app-sidebar{background:#171717}
    body{min-height:100vh;background:var(--bs-tertiary-bg)}
    .app-topbar{height:56px;position:sticky;top:0;z-index:1040;background:var(--bs-success);color:var(--website-on-primary);box-shadow:0 2px 8px rgba(0,0,0,.12)}
    .app-brand{color:var(--website-on-primary);font-size:1.2rem;font-weight:700;text-decoration:none;white-space:nowrap;display:flex;align-items:center;gap:9px}
    .app-brand img{width:32px;height:32px;object-fit:contain}
    .app-toggle{border:0;background:transparent;color:var(--website-on-primary);font-size:1.35rem;line-height:1;padding:7px 12px}
    .app-topbar .btn{color:var(--website-on-primary);border-color:rgba(255,255,255,.55)}
    .app-topbar .btn:hover{background:rgba(255,255,255,.15)}
    .app-sidebar{position:fixed;z-index:1035;top:56px;bottom:0;left:0;width:260px;overflow-y:auto;background:#202c34;color:#d5dfe5;transition:transform .22s ease}
    .sidebar-user{padding:20px 18px;border-bottom:1px solid rgba(255,255,255,.1);display:flex;align-items:center;gap:12px}
    .sidebar-avatar{width:46px;height:46px;flex:0 0 46px;border-radius:50%;display:grid;place-items:center;background:#e9eef2;color:#667985;font-size:1.25rem;font-weight:700;object-fit:cover}
    .topbar-avatar{width:30px;height:30px;border-radius:50%;object-fit:cover;background:#e9eef2}
    .sidebar-name{color:#47b5ff;font-size:.9rem;font-weight:700;overflow-wrap:anywhere}
    .sidebar-role{color:#d5dfe5;font-size:.78rem;margin-top:3px}
    .sidebar-caption{padding:16px 18px 7px;color:#748b99;font-size:.69rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase}
    .sidebar-nav{list-style:none;padding:0 8px;margin:0}
    .sidebar-nav a{min-height:44px;display:flex;align-items:center;gap:12px;padding:10px 12px;margin:2px 0;border-radius:5px;color:#c6d3db;text-decoration:none;font-size:.9rem;transition:background .15s,color .15s}
    .sidebar-nav a:hover{background:rgba(255,255,255,.08);color:#fff}
    .sidebar-nav a.active{background:linear-gradient(100deg,rgba(var(--bs-success-rgb),.42),rgba(48,157,203,.55));color:#fff;font-weight:600}
    .sidebar-icon{width:20px;text-align:center;font-size:1.05rem;opacity:.9}
    .sidebar-divider{height:1px;margin:10px 12px;background:rgba(255,255,255,.1)}
    .app-main{min-height:calc(100vh - 56px);margin-left:260px;padding:26px 28px 44px;transition:margin-left .22s ease}
    .app-content{max-width:1500px;margin:0 auto}
    .app-backdrop{display:none}
    .app-content .card{border-radius:.55rem}
    .app-content .card-header{background:var(--bs-tertiary-bg)}
    .sidebar-caption{list-style:none}
    .sidebar-divider{list-style:none}
    .dashboard-stat{color:#fff;overflow:hidden}
    .dashboard-stat-info{background:#08b6d2}
    .dashboard-stat-success{background:#00a65a}
    .dashboard-stat-primary{background:#2196f3}
    .dashboard-stat-danger{background:#f44336}
    .dashboard-stat .card-body{padding:20px 18px 14px}
    .dashboard-stat-icon{position:absolute;right:16px;top:16px;font-size:4rem;line-height:1;opacity:.16}
    .dashboard-stat-link{display:flex;justify-content:center;align-items:center;gap:6px;padding:7px 10px;background:rgba(0,0,0,.11);color:rgba(255,255,255,.9);text-decoration:none;font-size:.82rem}
    .dashboard-stat-link:hover{background:rgba(0,0,0,.2);color:#fff}
    .btn-success{--bs-btn-color:var(--website-on-primary);--bs-btn-bg:var(--bs-success);--bs-btn-border-color:var(--bs-success);--bs-btn-hover-bg:var(--bs-success);--bs-btn-hover-border-color:var(--bs-success);--bs-btn-hover-color:var(--website-on-primary);--bs-btn-active-bg:var(--bs-success);--bs-btn-active-border-color:var(--bs-success);--bs-btn-active-color:var(--website-on-primary)}
    .btn-outline-success{--bs-btn-color:var(--bs-success);--bs-btn-border-color:var(--bs-success);--bs-btn-hover-bg:var(--bs-success);--bs-btn-hover-border-color:var(--bs-success);--bs-btn-hover-color:var(--website-on-primary);--bs-btn-active-bg:var(--bs-success);--bs-btn-active-border-color:var(--bs-success);--bs-btn-active-color:var(--website-on-primary)}
    .btn-pill{border-radius:999px;padding-inline:1rem}
    .btn-icon-circle{width:38px;height:38px;padding:0;display:inline-flex;align-items:center;justify-content:center;border-radius:50%}
    .btn-icon-circle svg,.btn-pill svg{width:17px;height:17px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
    .btn-action-view{--action-view:#0D6EFD;--bs-btn-color:var(--action-view);--bs-btn-bg:color-mix(in srgb,var(--action-view) 10%,var(--bs-body-bg));--bs-btn-border-color:color-mix(in srgb,var(--action-view) 24%,var(--bs-body-bg));--bs-btn-hover-color:#fff;--bs-btn-hover-bg:var(--action-view);--bs-btn-hover-border-color:var(--action-view)}
    .btn-action-edit{--bs-btn-color:var(--website-accent);--bs-btn-bg:var(--website-accent-soft);--bs-btn-border-color:var(--website-accent-border);--bs-btn-hover-color:#fff;--bs-btn-hover-bg:var(--website-accent);--bs-btn-hover-border-color:var(--website-accent)}
    .btn-action-delete{--action-delete:color-mix(in srgb,#b4434b 82%,var(--bs-success));--action-delete-soft:color-mix(in srgb,#b4434b 12%,var(--bs-body-bg));--action-delete-border:color-mix(in srgb,#b4434b 27%,var(--bs-body-bg));--bs-btn-color:var(--action-delete);--bs-btn-bg:var(--action-delete-soft);--bs-btn-border-color:var(--action-delete-border);--bs-btn-hover-color:#fff;--bs-btn-hover-bg:var(--action-delete);--bs-btn-hover-border-color:var(--action-delete)}
    [data-bs-theme=dark] .btn-action-view{--action-view:#6EA8FE;--bs-btn-color:var(--action-view);--bs-btn-bg:color-mix(in srgb,var(--action-view) 18%,var(--bs-body-bg));--bs-btn-border-color:color-mix(in srgb,var(--action-view) 35%,var(--bs-body-bg));--bs-btn-hover-color:#0B1B33;--bs-btn-hover-bg:var(--action-view);--bs-btn-hover-border-color:var(--action-view)}
    [data-bs-theme=dark] .btn-action-edit{--bs-btn-color:var(--website-accent);--bs-btn-bg:var(--website-accent-soft);--bs-btn-border-color:var(--website-accent-border);--bs-btn-hover-color:#2b2111;--bs-btn-hover-bg:var(--website-accent);--bs-btn-hover-border-color:var(--website-accent)}
    [data-bs-theme=dark] .btn-action-delete{--action-delete:color-mix(in srgb,#f09ca2 82%,var(--bs-success));--action-delete-soft:color-mix(in srgb,#b4434b 18%,var(--bs-body-bg));--action-delete-border:color-mix(in srgb,#f09ca2 32%,var(--bs-body-bg));--bs-btn-color:var(--action-delete);--bs-btn-bg:var(--action-delete-soft);--bs-btn-border-color:var(--action-delete-border);--bs-btn-hover-color:#321a1d;--bs-btn-hover-bg:var(--action-delete);--bs-btn-hover-border-color:var(--action-delete)}
    .text-success{color:var(--bs-success)!important}
    /* Status selesai: hijau tetap, tidak ikut warna tema situs */
    .badge-hijau{background-color:#198754!important;color:#fff!important}
    .pagination{list-style:none;display:flex;gap:4px;padding:0;margin:0;flex-wrap:wrap}
    .pagination a{display:block;padding:4px 11px;border:1px solid var(--bs-border-color);border-radius:4px;text-decoration:none;color:var(--bs-success)}
    .pagination .active a{background:var(--bs-success);border-color:var(--bs-success);color:var(--website-on-primary)}
    td small{color:var(--bs-secondary-color)}
    .pilih label[hidden]{display:none!important}
    /* ===== Pola halaman: judul, aksi, dan bilah filter ===== */
    .ico{width:17px;height:17px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;flex:0 0 auto}
    .page-head{display:flex;flex-wrap:wrap;align-items:flex-end;justify-content:space-between;gap:.75rem 1rem;margin-bottom:1.1rem}
    .page-title{margin:0;font-size:1.5rem;font-weight:650;letter-spacing:-.01em;line-height:1.2}
    .page-title .count{display:inline-block;vertical-align:middle;margin-left:.45rem;padding:.18rem .6rem;border-radius:999px;background:var(--bs-secondary-bg);color:var(--bs-secondary-color);font-size:.78rem;font-weight:600;letter-spacing:0}
    .page-sub{margin:.35rem 0 0;color:var(--bs-secondary-color);font-size:.92rem}
    .page-actions{display:flex;flex-wrap:wrap;gap:.5rem}
    .page-actions .btn,.toolbar .btn{display:inline-flex;align-items:center;gap:.45rem}
    .btn-soft{--bs-btn-color:var(--bs-body-color);--bs-btn-bg:var(--bs-body-bg);--bs-btn-border-color:var(--bs-border-color);--bs-btn-hover-color:var(--bs-body-color);--bs-btn-hover-bg:var(--bs-secondary-bg);--bs-btn-hover-border-color:var(--bs-border-color);--bs-btn-active-color:var(--bs-body-color);--bs-btn-active-bg:var(--bs-secondary-bg);--bs-btn-active-border-color:var(--bs-border-color)}
    .toolbar{display:flex;flex-wrap:wrap;align-items:center;gap:.5rem;padding:.55rem;margin:0 0 1rem;background:var(--bs-body-bg);border:1px solid var(--bs-border-color);border-radius:.9rem}
    .toolbar .tb-search{position:relative;flex:1 1 320px;min-width:220px;margin:0}
    .toolbar .tb-search .ico{position:absolute;left:.9rem;top:50%;transform:translateY(-50%);color:var(--bs-secondary-color);pointer-events:none}
    .toolbar .tb-search .form-control{padding-left:2.6rem}
    .toolbar .tb-select{flex:0 1 165px}
    .toolbar .form-control,.toolbar .form-select{border-radius:999px;background-color:var(--bs-tertiary-bg);border-color:transparent}
    .toolbar .form-select{padding-left:1rem}
    .toolbar .form-control:focus,.toolbar .form-select:focus{background-color:var(--bs-body-bg);border-color:var(--bs-success);box-shadow:0 0 0 .2rem rgba(var(--bs-success-rgb),.18)}
    .btn-reset{color:var(--bs-secondary-color);text-decoration:none}
    .btn-reset:hover{color:var(--bs-body-color)}
    .app-content .table thead.table-light{--bs-table-bg:var(--bs-tertiary-bg);--bs-table-color:var(--bs-secondary-color);--bs-table-border-color:var(--bs-border-color)}
    .app-content .table thead th{font-size:.74rem;font-weight:650;text-transform:uppercase;letter-spacing:.05em;white-space:nowrap}
    .app-content .card.table-responsive{border-radius:.9rem}
    .app-content .dropdown-menu{min-width:15rem;padding:.4rem;border:1px solid var(--bs-border-color);border-radius:.9rem;box-shadow:0 10px 30px rgba(0,0,0,.18)}
    .app-content .dropdown-item{padding:.5rem .75rem;border-radius:.55rem;font-size:.92rem}
    /* Pencarian pegawai (formulir SPPD) */
    .peg-picker{position:relative}
    .peg-box{position:relative}
    .peg-box .ico{position:absolute;left:.9rem;top:50%;transform:translateY(-50%);color:var(--bs-secondary-color);pointer-events:none}
    .peg-box .form-control{padding-left:2.6rem;padding-right:2.6rem}
    .peg-clear{position:absolute;right:.4rem;top:50%;transform:translateY(-50%);width:30px;height:30px;border:0;border-radius:50%;background:transparent;color:var(--bs-secondary-color);display:grid;place-items:center}
    .peg-clear:hover{background:var(--bs-secondary-bg);color:var(--bs-body-color)}
    .peg-list{position:absolute;z-index:1050;left:0;right:0;top:calc(100% + 4px);max-height:280px;overflow:auto;padding:.3rem;background:var(--bs-body-bg);border:1px solid var(--bs-border-color);border-radius:.8rem;box-shadow:0 10px 30px rgba(0,0,0,.18)}
    .peg-item{display:block;width:100%;padding:.5rem .7rem;border:0;border-radius:.55rem;background:transparent;color:inherit;text-align:left;cursor:pointer}
    .peg-item small{display:block;color:var(--bs-secondary-color)}
    .peg-item:hover,.peg-item.aktif{background:var(--bs-secondary-bg)}
    .peg-item.dipilih .peg-nama::after{content:" \2713";color:var(--bs-success)}
    .peg-kosong{padding:.8rem;text-align:center;color:var(--bs-secondary-color)}
    .peg-chips{display:flex;flex-wrap:wrap;gap:.4rem;margin-top:.6rem}
    .peg-chips:empty{display:none}
    .peg-chip{display:inline-flex;align-items:center;gap:.35rem;padding:.2rem .35rem .2rem .8rem;border-radius:999px;background:var(--bs-secondary-bg);font-size:.9rem}
    .peg-chip button{display:grid;place-items:center;width:22px;height:22px;border:0;border-radius:50%;background:transparent;color:var(--bs-secondary-color);line-height:1}
    .peg-chip button:hover{background:var(--bs-tertiary-bg);color:var(--bs-body-color)}
    /* Judul + bilah filter tetap terlihat saat daftar digulir */
    .page-sticky{position:sticky;top:56px;z-index:30;margin:-26px -28px 1rem;padding:26px 28px .05rem;background:var(--bs-tertiary-bg)}
    .page-sticky .page-head{margin-bottom:.9rem}
    .page-sticky .toolbar{margin-bottom:.85rem}
    /* Bagian bawah daftar: ringkasan, navigasi halaman, jumlah per halaman */
    .list-foot{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:.75rem 1.25rem;margin-top:1rem}
    .list-info{color:var(--bs-secondary-color);font-size:.88rem}
    .list-pager{flex:1 1 auto;display:flex;justify-content:center}
    .list-per{display:flex;align-items:center;gap:.5rem;margin:0;color:var(--bs-secondary-color);font-size:.88rem}
    .list-per label{margin:0;white-space:nowrap}
    .list-per .form-select{width:auto;border-radius:999px;padding-left:.8rem}
    .list-per-lain{display:inline-flex;align-items:center;gap:.4rem}
    .list-per-lain .form-control{width:5.2rem;border-radius:999px}
    @media(max-width:991.98px){.page-sticky{margin:-20px -15px 1rem;padding:20px 15px .05rem}}
    @media(max-width:767.98px){.page-sticky{position:static}.list-pager{justify-content:flex-start;order:3;flex-basis:100%}}
    @media(max-width:1199.98px){html{font-size:15px}}
    @media(max-width:991.98px){html{font-size:14px}}
    @media(max-width:767.98px){html{font-size:13.5px}}
    @media(max-width:575.98px){html{font-size:13px}}
    @media(max-width:575.98px){
      .app-content>.d-flex.justify-content-between.align-items-center{flex-wrap:wrap;gap:.75rem!important}
      .app-content>.d-flex.justify-content-between.align-items-center>.d-flex{width:100%;flex-wrap:wrap}
      .app-content>.d-flex.justify-content-between.align-items-center>.d-flex>.btn{flex:1 1 auto;white-space:nowrap}
    }
    @media(min-width:992px){
      body.sidebar-collapsed .app-sidebar{transform:translateX(-100%)}
      body.sidebar-collapsed .app-main{margin-left:0}
    }
    @media(max-width:991.98px){
      .app-sidebar{transform:translateX(-100%);width:min(290px,85vw)}
      body.sidebar-open .app-sidebar{transform:translateX(0)}
      body.sidebar-open .app-backdrop{display:block;position:fixed;inset:56px 0 0;z-index:1030;background:rgba(0,0,0,.45)}
      .app-main{margin-left:0;padding:20px 15px 36px}
    }
  </style>
  <?= $this->renderSection('head') ?>
</head>
<body>
<header class="app-topbar">
  <div class="container-fluid h-100 d-flex align-items-center gap-2 px-3 px-lg-4">
    <button class="app-toggle" type="button" id="sidebarToggle" aria-label="Buka atau tutup menu" aria-expanded="true">&#9776;</button>
    <a class="app-brand" href="<?= site_url('/') ?>">
      <?php if ($website['logo'] !== null): ?><img src="<?= site_url('website/logo') ?>" alt="Logo"><?php endif ?>
      <?= esc($appName) ?>
    </a>
    <div class="ms-auto dropdown">
      <div class="d-flex align-items-center gap-2 dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer; user-select: none;">
        <?php if ($profilePhotoUrl): ?>
          <img class="topbar-avatar" src="<?= esc($profilePhotoUrl) ?>" alt="Foto profil">
        <?php else: ?>
          <div class="topbar-avatar d-flex align-items-center justify-content-center fw-bold text-decoration-none" style="color: #667985; font-size: 0.9rem;">
            <?= esc(mb_strtoupper(mb_substr((string) session('nama'), 0, 1))) ?>
          </div>
        <?php endif ?>
        <span class="d-none d-sm-inline small fw-semibold"><?= esc(session('nama')) ?></span>
      </div>
      <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" style="font-size: 0.9rem;">
        <li><a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?= site_url('akun') ?>">👤 Akun Saya</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item py-2 d-flex align-items-center gap-2 text-danger" href="<?= site_url('logout') ?>">🚪 Keluar</a></li>
      </ul>
    </div>
  </div>
</header>

<aside class="app-sidebar" id="appSidebar" aria-label="Navigasi utama">
  <div class="sidebar-user">
    <?php if ($profilePhotoUrl): ?>
      <img class="sidebar-avatar" src="<?= esc($profilePhotoUrl) ?>" alt="Foto profil">
    <?php else: ?>
      <div class="sidebar-avatar" aria-hidden="true"><?= esc(mb_strtoupper(mb_substr((string) session('nama'), 0, 1))) ?></div>
    <?php endif ?>
    <div class="min-w-0">
      <div class="sidebar-name"><?= esc(session('nama')) ?></div>
      <div class="sidebar-role"><?= esc($roleLabel) ?></div>
    </div>
  </div>
  <div class="sidebar-caption">Menu</div>
  <ul class="sidebar-nav">
    <li><a href="<?= site_url('/') ?>" class="<?= $isActive('') ? 'active' : '' ?>"><span class="sidebar-icon">🏠</span>Dashboard</a></li>
    <?php if ($isGuru): ?>
      <li><a href="<?= site_url('disposisi') ?>" class="<?= $isActive('disposisi') ? 'active' : '' ?>"><span class="sidebar-icon">✉</span>Disposisi saya</a></li>
    <?php else: ?>
      <li><a href="<?= site_url('surat/masuk') ?>" class="<?= $isActive('surat/masuk') ? 'active' : '' ?>"><span class="sidebar-icon">📬</span>Surat masuk</a></li>
      <li><a href="<?= site_url('surat/keluar') ?>" class="<?= $isActive('surat/keluar') ? 'active' : '' ?>"><span class="sidebar-icon">📤</span>Surat keluar</a></li>
      <li><a href="<?= site_url('daftar-hadir') ?>" class="<?= $isActive('daftar-hadir') ? 'active' : '' ?>"><span class="sidebar-icon">📋</span>Daftar hadir kegiatan</a></li>
      <li><a href="<?= site_url('dokumen') ?>" class="<?= $isActive('dokumen') ? 'active' : '' ?>"><span class="sidebar-icon">📝</span>Buat surat</a></li>
      <li><a href="<?= site_url('rekap/sppd') ?>" class="<?= $isActive('rekap') ? 'active' : '' ?>"><span class="sidebar-icon">📊</span>Rekap perjalanan dinas</a></li>
      <li class="sidebar-divider"></li>
      <li class="sidebar-caption">Data master</li>
      <li><a href="<?= site_url('data/guru') ?>" class="<?= $isActive('data/guru') ? 'active' : '' ?>"><span class="sidebar-icon">👨🏻‍🏫</span>Data guru</a></li>
      <li><a href="<?= site_url('data/siswa') ?>" class="<?= $isActive('data/siswa') ? 'active' : '' ?>"><span class="sidebar-icon">👨🏻‍🎓</span>Data siswa</a></li>
      <?php if ($isAdmin): ?>
        <li class="sidebar-divider"></li>
        <li class="sidebar-caption">Administrasi</li>
        <li><a href="<?= site_url('kop/pengaturan') ?>" class="<?= $isActive('kop') ? 'active' : '' ?>"><span class="sidebar-icon">▧</span>Pengaturan kop surat</a></li>
        <li><a href="<?= site_url('website/pengaturan') ?>" class="<?= $isActive('website') ? 'active' : '' ?>"><span class="sidebar-icon">⚙</span>Pengaturan website</a></li>
        <li><a href="<?= site_url('cadangan') ?>" class="<?= $isActive('cadangan') ? 'active' : '' ?>"><span class="sidebar-icon">💾</span>Cadangan database</a></li>
        <li><a href="<?= site_url('users') ?>" class="<?= $isActive('users') ? 'active' : '' ?>"><span class="sidebar-icon">🥷🏼</span>Pengguna</a></li>
      <?php endif ?>
    <?php endif ?>
  </ul>
</aside>
<div class="app-backdrop" id="appBackdrop"></div>

<main class="app-main">
  <div class="app-content">
    <?php if ($m = session()->getFlashdata('ok')): ?><div class="alert alert-success"><?= esc($m) ?></div><?php endif ?>
    <?php if ($m = session()->getFlashdata('error')): ?><div class="alert alert-danger"><?= esc($m) ?></div><?php endif ?>
    <?php if ($errs = session()->getFlashdata('errors')): ?>
      <div class="alert alert-danger"><ul class="mb-0"><?php foreach ((array) $errs as $e): ?><li><?= esc($e) ?></li><?php endforeach ?></ul></div>
    <?php endif ?>
    <?= $this->renderSection('content') ?>
  </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?= $this->renderSection('scripts') ?>
<script>
(function () {
  var button = document.getElementById('sidebarToggle'),
      backdrop = document.getElementById('appBackdrop'),
      mobile = window.matchMedia('(max-width: 991.98px)');
  button.setAttribute('aria-expanded', mobile.matches ? 'false' : 'true');
  function closeSidebar() {
    document.body.classList.remove('sidebar-open');
    button.setAttribute('aria-expanded', 'false');
  }
  button.addEventListener('click', function () {
    if (mobile.matches) {
      var open = document.body.classList.toggle('sidebar-open');
      button.setAttribute('aria-expanded', open ? 'true' : 'false');
    } else {
      var collapsed = document.body.classList.toggle('sidebar-collapsed');
      button.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
    }
  });
  backdrop.addEventListener('click', closeSidebar);
  mobile.addEventListener('change', closeSidebar);
})();
</script>
</body>
</html>