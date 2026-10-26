<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<?php $r = $row ?? []; ?>
<h4 class="mb-3"><?= $row ? 'Ubah' : 'Tambah' ?> pengguna</h4>
<form method="post" action="<?= $action ?>" class="card card-body shadow-sm" style="max-width:520px">
  <?= csrf_field() ?>
  <div class="mb-3"><label class="form-label">Nama lengkap *</label><input class="form-control" name="nama" value="<?= esc(old('nama', $r['nama'] ?? '')) ?>" required></div>
  <div class="mb-3"><label class="form-label">Username * (huruf dan angka)</label><input class="form-control" name="username" value="<?= esc(old('username', $r['username'] ?? '')) ?>" required></div>
  <div class="mb-3"><label class="form-label">Password<?= $row ? ' (kosongkan jika tidak diganti)' : ' *' ?></label><input class="form-control" type="password" name="password" <?= $row ? '' : 'required' ?>></div>
  <div class="mb-3"><label class="form-label">Peran</label>
    <select class="form-select" name="role"><?php foreach (['staf' => 'Staf (kelola surat)', 'admin' => 'Admin (kelola surat dan pengguna)'] as $k => $l): ?><option value="<?= $k ?>" <?= old('role', $r['role'] ?? 'staf') === $k ? 'selected' : '' ?>><?= $l ?></option><?php endforeach ?></select></div>
  <div class="d-flex gap-2"><button class="btn btn-success">Simpan</button><a class="btn btn-outline-secondary" href="<?= site_url('users') ?>">Batal</a></div>
</form>
<?= $this->endSection() ?>
