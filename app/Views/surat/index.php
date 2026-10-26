<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<?php $judul = $arah === 'masuk' ? 'Surat Masuk' : 'Surat Keluar'; $bln = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$urut = static function (string $label, string $kunci) use ($sort, $dir): string {
    $aktif = $sort === $kunci;
    $baru  = $aktif && $dir === 'asc' ? 'desc' : 'asc';
    $qs    = array_filter(array_diff_key((array) service('request')->getGet(), ['page' => 1, 'sort' => 1, 'dir' => 1]), static fn ($v) => $v !== '' && $v !== null) + ['sort' => $kunci, 'dir' => $baru];
    $ikon  = $aktif ? ($dir === 'asc' ? '&#9650;' : '&#9660;') : '<span class="text-muted opacity-50">&#8645;</span>';

    return '<th aria-sort="' . ($aktif ? ($dir === 'asc' ? 'ascending' : 'descending') : 'none') . '"><a class="text-reset text-decoration-none text-nowrap" href="' . esc(current_url() . '?' . http_build_query($qs)) . '" title="Urutkan ' . esc($label, 'attr') . '">' . esc($label) . ' ' . $ikon . '</a></th>';
};
?>
<?php helper('ui'); $adaFilter = (bool) array_filter($f ?? []); ?>
<div class="page-sticky">
<div class="page-head">
  <div>
    <h1 class="page-title">Agenda <?= $judul ?></h1>
    <p class="page-sub"><?= $arah === 'masuk' ? 'Catatan surat yang diterima sekolah.' : 'Catatan surat yang dikirim sekolah.' ?></p>
  </div>
  <div class="page-actions">
    <a class="btn btn-soft btn-pill" href="<?= site_url("surat/$arah/cetak") . '?' . http_build_query(array_filter($f)) ?>"><?= ui_icon('printer') ?>Cetak agenda</a>
    <a class="btn btn-success btn-pill" href="<?= site_url("surat/$arah/baru") ?>"><?= ui_icon('plus') ?>Tambah surat</a>
  </div>
</div>
<form method="get" class="toolbar" role="search">
  <?php if ($sort !== ''): ?><input type="hidden" name="sort" value="<?= esc($sort) ?>"><input type="hidden" name="dir" value="<?= esc($dir) ?>"><?php endif ?>
  <label class="tb-search"><?= ui_icon('search') ?><input class="form-control" type="search" name="q" value="<?= esc($f['q'] ?? '') ?>" placeholder="Cari nomor, asal/tujuan, perihal" aria-label="Cari surat"></label>
  <select class="form-select tb-select" name="tahun" aria-label="Tahun"><option value="">Semua tahun</option>
    <?php foreach ($tahun as $t): ?><option <?= ($f['tahun'] ?? '') == $t ? 'selected' : '' ?>><?= $t ?></option><?php endforeach ?></select>
  <select class="form-select tb-select" name="bulan" aria-label="Bulan"><option value="">Semua bulan</option>
    <?php foreach ($bln as $i => $b): ?><option value="<?= $i + 1 ?>" <?= ($f['bulan'] ?? '') == $i + 1 ? 'selected' : '' ?>><?= $b ?></option><?php endforeach ?></select>
  <select class="form-select tb-select" name="status" aria-label="Status"><option value="">Semua status</option>
    <?php foreach ($status as $s): ?><option <?= ($f['status'] ?? '') === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach ?></select>
  <button class="btn btn-success btn-pill" type="submit"><?= ui_icon('filter') ?>Filter</button>
  <?php if ($adaFilter): ?><a class="btn btn-link btn-reset" href="<?= site_url("surat/$arah") ?>"><?= ui_icon('x') ?>Reset</a><?php endif ?>
</form>
</div>
<div class="table-responsive card shadow-sm">
<table class="table table-hover align-middle mb-0">
  <thead class="table-light"><tr>
    <?= $urut('No.', 'no') ?><?= $urut($arah === 'masuk' ? 'Diterima' : 'Tanggal', 'tgl') ?><?= $urut('Nomor surat', 'nomor') ?><?= $urut($arah === 'masuk' ? 'Asal' : 'Tujuan', 'pihak') ?><?= $urut('Perihal', 'perihal') ?><?= $urut('Sifat', 'sifat') ?><?= $urut('Status', 'status') ?><th></th><th></th>
  </tr></thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td><?= no_agenda($r['no_agenda']) ?></td>
      <td class="text-nowrap"><?= tgl_id($r['tgl_agenda']) ?></td>
      <td><?= esc($r['nomor_surat']) ?><?php if ($arah === 'masuk' && $r['tgl_surat']): ?><br><small>tgl surat <?= tgl_id($r['tgl_surat']) ?></small><?php endif ?></td>
      <td><?= esc($r['pihak']) ?></td>
      <td><?= esc($r['perihal']) ?></td>
      <td><span class="badge <?= badge_class($r['sifat']) ?>"><?= esc($r['sifat']) ?></span></td>
      <td><span class="badge <?= badge_class($r['status']) ?>"><?= esc($r['status']) ?></span></td>
      <td><?= $r['berkas'] ? '<span title="Ada lampiran file">&#128206;</span>' : '' ?></td>
      <td class="text-nowrap">
        <div class="d-flex align-items-center gap-2">
          <a class="btn btn-icon-circle btn-action-view" href="<?= site_url("surat/$arah/" . $r['id']) ?>" title="Lihat detail" aria-label="Lihat detail surat">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
          </a>
          <a class="btn btn-icon-circle btn-action-edit" href="<?= site_url("surat/$arah/" . $r['id'] . '/ubah') ?>" title="Ubah surat" aria-label="Ubah surat">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 5 4 4M4 20l4-.8L19 8a2.1 2.1 0 0 0-3-3L5 16l-1 4Z"/></svg>
          </a>
          <form method="post" action="<?= site_url("surat/$arah/" . $r['id'] . '/hapus') ?>" onsubmit="return confirm('Hapus surat ini beserta lampirannya?')">
            <?= csrf_field() ?>
            <button class="btn btn-icon-circle btn-action-delete" type="submit" title="Hapus surat" aria-label="Hapus surat">
              <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M10 11v6M14 11v6M5 7l1 14h12l1-14M9 7V4h6v3"/></svg>
            </button>
          </form>
        </div>
      </td>
    </tr>
  <?php endforeach ?>
  <?php if (! $rows): ?><tr><td colspan="9" class="text-center text-muted py-4">Tidak ada data.</td></tr><?php endif ?>
  </tbody>
</table>
</div>
<?= view('partials/_list_footer', ['pager' => $pager]) ?>
<?= $this->endSection() ?>