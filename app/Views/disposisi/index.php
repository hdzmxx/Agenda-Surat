<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<h4 class="mb-3">Surat yang didisposisikan kepada saya</h4>
<div class="card shadow-sm table-responsive">
  <table class="table table-hover align-middle mb-0">
    <thead class="table-light"><tr><th>Tanggal diterima</th><th>Nomor surat</th><th>Asal surat</th><th>Perihal</th><th>Catatan disposisi</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $row): ?>
      <tr>
        <td class="text-nowrap"><?= tgl_id($row['tgl_agenda']) ?></td>
        <td><?= esc($row['nomor_surat']) ?></td>
        <td><?= esc($row['pihak']) ?></td>
        <td><?= esc($row['perihal']) ?></td>
        <td style="white-space:pre-wrap"><?= esc($row['catatan'] ?: '-') ?></td>
        <td><a class="btn btn-sm btn-outline-primary" href="<?= site_url('disposisi/' . $row['disposisi_id']) ?>">Lihat surat</a></td>
      </tr>
    <?php endforeach ?>
    <?php if (! $rows): ?><tr><td colspan="6" class="text-center text-muted py-4">Belum ada surat yang didisposisikan kepada Anda.</td></tr><?php endif ?>
    </tbody>
  </table>
</div>
<?= $this->endSection() ?>
