<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3"><h4 class="mb-0">Pengguna</h4><a class="btn btn-success" href="<?= site_url('users/baru') ?>">+ Tambah pengguna</a></div>
<div class="card shadow-sm table-responsive"><table class="table align-middle mb-0">
  <thead class="table-light"><tr><th>Nama</th><th>Username</th><th>Jabatan / peran</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td><?= esc($r['nama']) ?></td>
      <td><?= esc($r['username']) ?></td>
      <td><span class="badge text-bg-secondary"><?= esc(trim((string) ($r['jabatan'] ?? '')) ?: match ($r['role']) {
          'admin' => 'Administrator',
          'guru' => 'Guru / Staf TU',
          default => 'Staf',
      }) ?></span></td>
      <td class="text-end text-nowrap">
        <?php if (! empty($r['guru_id'])): ?><span class="text-muted small">Dikelola dari Data guru</span>
        <?php else: ?>
          <a class="btn btn-sm btn-outline-secondary" href="<?= site_url('users/' . $r['id'] . '/ubah') ?>">Ubah</a>
          <?php if ((int) $r['id'] !== (int) session('uid')): ?>
            <form class="d-inline" method="post" action="<?= site_url('users/' . $r['id'] . '/hapus') ?>" onsubmit="return confirm('Hapus pengguna ini?')"><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger">Hapus</button></form>
          <?php endif ?>
        <?php endif ?>
      </td>
    </tr>
  <?php endforeach ?>
  </tbody></table></div>
<?= $this->endSection() ?>
