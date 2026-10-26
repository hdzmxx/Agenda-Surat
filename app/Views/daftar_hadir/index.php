<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<?php
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
    <h1 class="page-title">Daftar hadir kegiatan</h1>
    <p class="page-sub">Daftar hadir yang dibuat tersimpan di sini, jadi bisa dibuka, diubah, dan dicetak ulang kapan saja.</p>
  </div>
  <div class="page-actions">
    <a class="btn btn-success btn-pill" href="<?= site_url('daftar-hadir/baru') ?>"><?= ui_icon('plus') ?>Buat daftar hadir</a>
  </div>
</div>
<?php if ($tabelAda): ?>
<form method="get" class="toolbar" role="search">
  <?php if ($sort !== ''): ?><input type="hidden" name="sort" value="<?= esc($sort) ?>"><input type="hidden" name="dir" value="<?= esc($dir) ?>"><?php endif ?>
  <label class="tb-search"><?= ui_icon('search') ?><input class="form-control" type="search" name="q" value="<?= esc($q) ?>" placeholder="Cari judul atau tema kegiatan" aria-label="Cari daftar hadir"></label>
  <button class="btn btn-success btn-pill" type="submit"><?= ui_icon('search') ?>Cari</button>
  <?php if ($q !== ''): ?><a class="btn btn-link btn-reset" href="<?= site_url('daftar-hadir') ?>"><?= ui_icon('x') ?>Reset</a><?php endif ?>
</form>
<?php endif ?>
</div>

<?php if (! $tabelAda): ?>
  <div class="alert alert-warning">
    Tabel <code>daftar_hadir</code> belum ada di database. Jalankan <code>php spark migrate</code> dari folder proyek,
    atau impor file <code>daftar-hadir.sql</code> lewat phpMyAdmin, lalu muat ulang halaman ini.
  </div>
<?php else: ?>
<div class="card shadow-sm table-responsive"><table class="table table-hover align-middle mb-0">
  <thead class="table-light"><tr><?= $urut('Tanggal', 'tgl') ?><?= $urut('Kegiatan', 'judul') ?><th>Peserta</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <?php
    $dt     = json_decode((string) $r['data'], true) ?: [];
    $jumlah = count($dt['peserta'] ?? []);
    $jenis  = array_filter([
        ! empty($dt['peserta_guru']) ? 'Guru/Karyawan' : null,
        ! empty($dt['peserta_siswa']) ? 'Siswa' : null,
        ! empty($dt['peserta_tamu']) ? 'Tamu' : null,
    ]);
    ?>
    <tr>
      <td class="text-nowrap"><?= tgl_id($r['tanggal']) ?></td>
      <td><?= esc($r['judul']) ?><?php if ($r['tema'] !== ''): ?><br><small>"<?= esc($r['tema']) ?>"</small><?php endif ?></td>
      <td><?= $jumlah ?> orang<?php if ($jenis): ?><br><small><?= esc(implode(', ', $jenis)) ?></small><?php endif ?></td>
      <td class="text-nowrap">
        <div class="d-flex align-items-center gap-2">
          <a class="btn btn-icon-circle btn-action-view" href="<?= site_url('daftar-hadir/' . $r['id'] . '/cetak') ?>" title="Lihat / cetak" aria-label="Lihat atau cetak daftar hadir">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
          </a>
          <a class="btn btn-icon-circle btn-action-edit" href="<?= site_url('daftar-hadir/' . $r['id'] . '/ubah') ?>" title="Ubah daftar hadir" aria-label="Ubah daftar hadir">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 5 4 4M4 20l4-.8L19 8a2.1 2.1 0 0 0-3-3L5 16l-1 4Z"/></svg>
          </a>
          <form method="post" action="<?= site_url('daftar-hadir/' . $r['id'] . '/hapus') ?>" onsubmit="return confirm('Hapus daftar hadir ini?')"><?= csrf_field() ?>
            <button class="btn btn-icon-circle btn-action-delete" type="submit" title="Hapus daftar hadir" aria-label="Hapus daftar hadir">
              <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M10 11v6M14 11v6M5 7l1 14h12l1-14M9 7V4h6v3"/></svg>
            </button>
          </form>
        </div>
      </td>
    </tr>
  <?php endforeach ?>
  <?php if (! $rows): ?><tr><td colspan="4" class="text-center text-muted py-4">Belum ada daftar hadir. Klik "Buat daftar hadir".</td></tr><?php endif ?>
  </tbody></table></div>
<?= view('partials/_list_footer', ['pager' => $pager]) ?>
<?php endif ?>
<?= $this->endSection() ?>
