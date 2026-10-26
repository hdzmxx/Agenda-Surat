<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<?php
helper(['ui', 'tanggal']);
$ukuran = static function (int $b): string {
    if ($b >= 1048576) {
        return number_format($b / 1048576, 1, ',', '.') . ' MB';
    }

    return number_format(max(1, $b) / 1024, 0, ',', '.') . ' KB';
};
?>
<div class="page-head">
  <div>
    <h1 class="page-title">Cadangan database</h1>
    <p class="page-sub">Salinan seluruh data aplikasi (surat, guru, siswa, pengguna) yang bisa diunduh dan dipulihkan kapan saja.</p>
  </div>
  <div class="page-actions">
    <form method="post" action="<?= site_url('cadangan/buat') ?>" class="m-0">
      <?= csrf_field() ?>
      <button class="btn btn-success btn-pill" type="submit"><?= ui_icon('download') ?>Buat cadangan sekarang</button>
    </form>
  </div>
</div>

<div class="card shadow-sm mb-3">
  <div class="card-body">
    <form method="post" action="<?= site_url('cadangan/pengaturan') ?>" class="row g-3 align-items-end">
      <?= csrf_field() ?>
      <div class="col-md-3">
        <label class="form-label" for="frek">Cadangan otomatis</label>
        <select class="form-select" id="frek" name="frekuensi">
          <?php foreach ($frekuensi as $k => $nama): ?><option value="<?= $k ?>" <?= $pengaturan['frekuensi'] === $k ? 'selected' : '' ?>><?= $nama ?></option><?php endforeach ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label" for="simpan">Simpan cadangan otomatis terakhir</label>
        <input class="form-control" type="number" id="simpan" name="simpan" min="1" max="60" value="<?= $pengaturan['simpan'] ?>">
      </div>
      <div class="col-md-2"><button class="btn btn-soft btn-pill" type="submit">Simpan pengaturan</button></div>
    </form>
    <p class="text-muted small mt-3 mb-0">
      Cadangan otomatis dibuat saat ada yang membuka aplikasi dan jadwal sudah jatuh tempo, jadi tidak perlu pengaturan di server.
      Untuk waktu yang tepat, jadwalkan perintah <code>php spark db:backup</code> di Task Scheduler (Windows) atau cron.
      Cadangan manual tidak pernah dihapus otomatis.
    </p>
  </div>
</div>

<div class="card shadow-sm table-responsive">
  <table class="table table-hover align-middle mb-0 text-nowrap">
    <thead class="table-light"><tr><th>Berkas</th><th>Jenis</th><th>Dibuat</th><th>Ukuran</th><th class="text-end"></th></tr></thead>
    <tbody>
    <?php if (! $berkas): ?>
      <tr><td colspan="5" class="text-center text-muted py-4">Belum ada cadangan. Klik &ldquo;Buat cadangan sekarang&rdquo;.</td></tr>
    <?php endif ?>
    <?php foreach ($berkas as $b): ?>
      <tr>
        <td><?= esc($b['nama']) ?></td>
        <td><span class="badge text-bg-<?= ['Otomatis' => 'secondary', 'Manual' => 'success', 'Sebelum pulih' => 'warning', 'Unggahan' => 'info'][$b['jenis']] ?? 'secondary' ?>"><?= $b['jenis'] ?></span></td>
        <td><?= esc(tgl_id(date('Y-m-d', $b['waktu']))) ?>, <?= date('H:i', $b['waktu']) ?></td>
        <td><?= $ukuran($b['ukuran']) ?></td>
        <td class="text-end">
          <a class="btn btn-soft btn-sm btn-pill" href="<?= site_url('cadangan/unduh/' . rawurlencode($b['nama'])) ?>"><?= ui_icon('download') ?>Unduh</a>
          <a class="btn btn-soft btn-sm btn-pill" href="<?= site_url('cadangan/pulihkan/' . rawurlencode($b['nama'])) ?>"><?= ui_icon('upload') ?>Pulihkan</a>
          <form method="post" action="<?= site_url('cadangan/hapus/' . rawurlencode($b['nama'])) ?>" class="d-inline" onsubmit="return confirm('Hapus cadangan ini?')">
            <?= csrf_field() ?>
            <button class="btn btn-outline-danger btn-sm btn-pill" type="submit">Hapus</button>
          </form>
        </td>
      </tr>
    <?php endforeach ?>
    </tbody>
  </table>
</div>

<div class="card shadow-sm mt-3">
  <div class="card-body">
    <form method="post" action="<?= site_url('cadangan/unggah') ?>" enctype="multipart/form-data" class="row g-2 align-items-end">
      <?= csrf_field() ?>
      <div class="col-md-6">
        <label class="form-label" for="berkas">Pulihkan dari berkas lain (.sql atau .sql.gz)</label>
        <input class="form-control" type="file" id="berkas" name="berkas" accept=".sql,.gz" required>
      </div>
      <div class="col-md-3"><button class="btn btn-soft btn-pill" type="submit"><?= ui_icon('upload') ?>Unggah dan lanjutkan</button></div>
    </form>
    <p class="text-muted small mt-2 mb-0">Berkas hanya disimpan; pemulihan baru berjalan setelah Anda mengonfirmasi di halaman berikutnya.</p>
  </div>
</div>

<div class="text-muted small mt-3">
  <strong>Memulihkan:</strong> klik &ldquo;Pulihkan&rdquo; pada cadangan di atas. Sebelum menimpa data, aplikasi otomatis membuat cadangan pengaman bertanda &ldquo;Sebelum pulih&rdquo;.
  Cara lain: phpMyAdmin &rarr; Import<?= $gzip ? ' (.sql.gz bisa diimpor langsung)' : '' ?>.
  Cadangan hanya berisi database; berkas unggahan di folder <code>writable/uploads</code> perlu disalin terpisah.
  Berkas disimpan di <code>writable/backups</code>.
</div>
<?= $this->endSection() ?>
