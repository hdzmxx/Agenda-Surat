<?= $this->extend('layout') ?>
<?= $this->section('head') ?>
<style>
  .rekap-angka{font-size:1.9rem;font-weight:650;line-height:1.1;letter-spacing:-.02em}
  .rekap-label{color:var(--bs-secondary-color);font-size:.85rem}
  .rekap-judul{margin:1.4rem 0 .6rem;font-size:.78rem;font-weight:650;letter-spacing:.06em;text-transform:uppercase;color:var(--bs-secondary-color)}
  .rekap-tabel td,.rekap-tabel th{white-space:nowrap}
  .rekap-bln{text-align:center;min-width:2.6rem}
  .rekap-nol{color:var(--bs-secondary-color);opacity:.45}
  .rekap-jml td{font-weight:650;background:var(--bs-tertiary-bg)}
</style>
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<?php
helper(['ui', 'tanggal']);
$tahunIni = (int) date('Y');
$adaFilter = $f['bulan'] > 0 || $f['guru'] > 0 || $f['tahun'] !== $tahunIni;
$kueriExcel = http_build_query(['tahun' => $f['tahun'], 'bulan' => $f['bulan'], 'guru' => $f['guru']]);
?>
<div class="page-sticky">
  <div class="page-head">
    <div>
      <h1 class="page-title">Rekap perjalanan dinas</h1>
      <p class="page-sub">Ringkasan SPPD per pegawai dan per bulan, berdasarkan tanggal berangkat.</p>
    </div>
    <div class="page-actions">
      <a class="btn btn-success btn-pill" href="<?= site_url('rekap/sppd/excel') . '?' . $kueriExcel ?>"><?= ui_icon('download') ?>Unduh Excel</a>
    </div>
  </div>
  <form method="get" class="toolbar" role="search">
    <select class="form-select tb-select" name="tahun" aria-label="Tahun">
      <option value="0" <?= $f['tahun'] === 0 ? 'selected' : '' ?>>Semua tahun</option>
      <?php foreach ($tahunList as $t): ?><option value="<?= $t ?>" <?= $f['tahun'] === $t ? 'selected' : '' ?>><?= $t ?></option><?php endforeach ?>
    </select>
    <select class="form-select tb-select" name="bulan" aria-label="Bulan">
      <option value="0">Semua bulan</option>
      <?php foreach ($bulanNama as $i => $b): ?><option value="<?= $i + 1 ?>" <?= $f['bulan'] === $i + 1 ? 'selected' : '' ?>><?= $b ?></option><?php endforeach ?>
    </select>
    <select class="form-select tb-select" name="guru" aria-label="Pegawai" style="flex-basis:260px">
      <option value="0">Semua pegawai</option>
      <?php foreach ($guru as $g): ?><option value="<?= $g['id'] ?>" <?= $f['guru'] === (int) $g['id'] ? 'selected' : '' ?>><?= esc($g['nama']) ?></option><?php endforeach ?>
    </select>
    <button class="btn btn-success btn-pill" type="submit"><?= ui_icon('filter') ?>Terapkan</button>
    <?php if ($adaFilter): ?><a class="btn btn-link btn-reset" href="<?= site_url('rekap/sppd') ?>"><?= ui_icon('x') ?>Reset</a><?php endif ?>
  </form>
</div>

<div class="row g-3 mb-2">
  <div class="col-md-4"><div class="card shadow-sm"><div class="card-body"><div class="rekap-angka"><?= $total['kali'] ?></div><div class="rekap-label">Perjalanan dinas &middot; <?= esc($periode) ?></div></div></div></div>
  <div class="col-md-4"><div class="card shadow-sm"><div class="card-body"><div class="rekap-angka"><?= $total['hari'] ?></div><div class="rekap-label">Total hari perjalanan</div></div></div></div>
  <div class="col-md-4"><div class="card shadow-sm"><div class="card-body"><div class="rekap-angka"><?= $total['pegawai'] ?></div><div class="rekap-label">Pegawai yang berangkat</div></div></div></div>
</div>

<?php if ($total['kali'] === 0): ?>
  <div class="alert alert-secondary mt-3">Belum ada SPPD pada periode ini. Ubah tahun, bulan, atau pegawai di atas.</div>
<?php else: ?>
  <div class="rekap-judul">Ringkasan per pegawai (jumlah perjalanan tiap bulan)</div>
  <div class="card shadow-sm table-responsive">
    <table class="table table-hover align-middle mb-0 rekap-tabel">
      <thead class="table-light"><tr>
        <th>No</th><th>Pegawai</th>
        <?php foreach ($bulanPendek as $b): ?><th class="rekap-bln"><?= $b ?></th><?php endforeach ?>
        <th class="text-center">Kali</th><th class="text-center">Hari</th><th>Tujuan</th>
      </tr></thead>
      <tbody>
      <?php foreach ($ringkasan as $no => $r): ?>
        <tr>
          <td><?= $no + 1 ?></td>
          <td><div class="fw-semibold"><?= esc($r['nama']) ?></div><?php if ($r['jabatan'] !== ''): ?><div class="small text-muted"><?= esc($r['jabatan']) ?></div><?php endif ?></td>
          <?php for ($b = 1; $b <= 12; $b++): ?><td class="rekap-bln <?= $r['bulan'][$b] ? '' : 'rekap-nol' ?>"><?= $r['bulan'][$b] ?: '&middot;' ?></td><?php endfor ?>
          <td class="text-center fw-semibold"><?= $r['kali'] ?></td>
          <td class="text-center fw-semibold"><?= $r['hari'] ?></td>
          <td class="small text-muted"><?= esc(implode(', ', array_keys($r['tujuan']))) ?></td>
        </tr>
      <?php endforeach ?>
        <tr class="rekap-jml">
          <td></td><td>Jumlah</td>
          <?php for ($b = 1; $b <= 12; $b++): $s = array_sum(array_column(array_column($ringkasan, 'bulan'), $b)); ?><td class="rekap-bln"><?= $s ?: '' ?></td><?php endfor ?>
          <td class="text-center"><?= $total['kali'] ?></td><td class="text-center"><?= $total['hari'] ?></td><td></td>
        </tr>
      </tbody>
    </table>
  </div>

  <div class="rekap-judul">Rincian perjalanan</div>
  <div class="card shadow-sm table-responsive">
    <table class="table table-hover align-middle mb-0 rekap-tabel">
      <thead class="table-light"><tr><th>No</th><th>Tanggal</th><th>Pegawai</th><th>Tujuan</th><th>Maksud</th><th class="text-center">Lama</th><th>Nomor SPPD</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($perjalanan as $no => $p): ?>
        <tr>
          <td><?= $no + 1 ?></td>
          <td><?= esc($p['rentang']) ?></td>
          <td><?= esc($p['nama']) ?></td>
          <td><?= esc($p['tujuan']) ?></td>
          <td><?= esc($p['maksud']) ?></td>
          <td class="text-center"><?= $p['hari'] ?> hari</td>
          <td><?= esc($p['nomor']) ?></td>
          <td class="text-end"><a class="btn btn-soft btn-sm btn-pill" href="<?= site_url('dokumen/' . $p['id'] . '/cetak') ?>">Lihat</a></td>
        </tr>
      <?php endforeach ?>
      </tbody>
    </table>
  </div>
<?php endif ?>
<?= $this->endSection() ?>
