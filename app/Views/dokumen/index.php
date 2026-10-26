<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<?php
$daftar = \App\Libraries\JenisSurat::DAFTAR;
$urut = static function (string $label, string $kunci) use ($sort, $dir): string {
    $aktif = $sort === $kunci;
    $baru  = $aktif && $dir === 'asc' ? 'desc' : 'asc';
    $qs    = array_filter(array_diff_key((array) service('request')->getGet(), ['page' => 1, 'sort' => 1, 'dir' => 1]), static fn ($v) => $v !== '' && $v !== null) + ['sort' => $kunci, 'dir' => $baru];
    $ikon  = $aktif ? ($dir === 'asc' ? '&#9650;' : '&#9660;') : '<span class="text-muted opacity-50">&#8645;</span>';

    return '<th aria-sort="' . ($aktif ? ($dir === 'asc' ? 'ascending' : 'descending') : 'none') . '"><a class="text-reset text-decoration-none text-nowrap" href="' . esc(current_url() . '?' . http_build_query($qs)) . '" title="Urutkan ' . esc($label, 'attr') . '">' . esc($label) . ' ' . $ikon . '</a></th>';
};
?>
<?php helper('ui'); ?>
<div class="page-sticky">
<div class="page-head">
  <div>
    <h1 class="page-title">Buat surat</h1>
    <p class="page-sub">Surat yang dibuat di sini otomatis tercatat di agenda surat keluar dengan nomor agenda berurutan.</p>
  </div>
  <div class="page-actions dropdown">
    <button class="btn btn-success btn-pill dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><?= ui_icon('plus') ?>Buat surat baru</button>
    <ul class="dropdown-menu dropdown-menu-end">
      <?php foreach ($daftar as $k => $jn): ?><li><a class="dropdown-item" href="<?= site_url("dokumen/baru/$k") ?>"><?= esc($jn['nama']) ?></a></li><?php endforeach ?>
    </ul>
  </div>
</div>
<form method="get" class="toolbar" role="search">
  <?php if ($sort !== ''): ?><input type="hidden" name="sort" value="<?= esc($sort) ?>"><input type="hidden" name="dir" value="<?= esc($dir) ?>"><?php endif ?>
  <label class="tb-search"><?= ui_icon('search') ?><input class="form-control" type="search" name="q" value="<?= esc($q) ?>" placeholder="Cari perihal, nomor, atau isi surat" aria-label="Cari surat"></label>
  <select class="form-select tb-select" name="jenis" aria-label="Jenis surat" style="flex-basis:230px"><option value="">Semua jenis</option>
    <?php foreach ($daftar as $k => $jn): ?><option value="<?= $k ?>" <?= $j === $k ? 'selected' : '' ?>><?= esc($jn['nama']) ?></option><?php endforeach ?></select>
  <button class="btn btn-success btn-pill" type="submit"><?= ui_icon('filter') ?>Filter</button>
  <?php if ($q !== '' || $j !== ''): ?><a class="btn btn-link btn-reset" href="<?= site_url('dokumen') ?>"><?= ui_icon('x') ?>Reset</a><?php endif ?>
</form>
</div>
<div class="card shadow-sm table-responsive"><table class="table table-hover align-middle mb-0">
  <thead class="table-light"><tr><?= $urut('Agenda', 'agenda') ?><?= $urut('Jenis', 'jenis') ?><?= $urut('Nomor surat', 'nomor') ?><?= $urut('Perihal', 'perihal') ?><?= $urut('Tanggal', 'tgl') ?><th></th></tr></thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td><?= $r['no_agenda'] ? no_agenda($r['no_agenda']) : '-' ?></td>
      <td><span class="badge text-bg-secondary"><?= esc($daftar[$r['jenis']]['nama'] ?? $r['jenis']) ?></span></td>
      <td><?= esc($r['nomor_surat'] ?? '-') ?></td>
      <td><?= esc($r['perihal'] ?? '(catatan agenda terhapus)') ?></td>
      <td class="text-nowrap"><?= tgl_id($r['tgl_agenda'] ?? null) ?></td>
      <td class="text-nowrap">
        <div class="d-flex align-items-center gap-2">
          <a class="btn btn-icon-circle btn-action-view" href="<?= site_url('dokumen/' . $r['id'] . '/cetak') ?>" title="Lihat / cetak" aria-label="Lihat atau cetak surat">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
          </a>
          <a class="btn btn-icon-circle btn-action-edit" href="<?= site_url('dokumen/' . $r['id'] . '/ubah') ?>" title="Ubah surat" aria-label="Ubah surat">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 5 4 4M4 20l4-.8L19 8a2.1 2.1 0 0 0-3-3L5 16l-1 4Z"/></svg>
          </a>
          <form method="post" action="<?= site_url('dokumen/' . $r['id'] . '/hapus') ?>" onsubmit="return confirm('Hapus surat ini beserta catatannya di agenda surat keluar?')"><?= csrf_field() ?>
            <button class="btn btn-icon-circle btn-action-delete" type="submit" title="Hapus surat" aria-label="Hapus surat">
              <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M10 11v6M14 11v6M5 7l1 14h12l1-14M9 7V4h6v3"/></svg>
            </button>
          </form>
        </div>
      </td>
    </tr>
  <?php endforeach ?>
  <?php if (! $rows): ?><tr><td colspan="6" class="text-center text-muted py-4">Belum ada surat. Klik "Buat surat baru".</td></tr><?php endif ?>
  </tbody></table></div>
<?= view('partials/_list_footer', ['pager' => $pager]) ?>
<?= $this->endSection() ?>