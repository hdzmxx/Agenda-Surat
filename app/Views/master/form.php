<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<?php $r = $row ?? []; ?>
<h4 class="mb-3"><?= esc($title) ?></h4>
<form method="post" action="<?= $action ?>" class="card card-body shadow-sm" style="max-width:640px">
  <?= csrf_field() ?>
  <?php foreach ($c['kolom'] as $i => $kol): [$f, $label] = $kol; $type = $kol[2] ?? 'text'; $val = old($f, $r[$f] ?? ($f === 'urut' && ! $row ? 999 : '')); ?>
  <div class="mb-3"><label class="form-label"><?= esc($label) ?><?= $f === 'nama' ? ' *' : '' ?></label>
    <?php if ($type === 'jk'): ?><select class="form-select" name="jk"><option value="">-</option><option value="L" <?= $val === 'L' ? 'selected' : '' ?>>Laki-laki</option><option value="P" <?= $val === 'P' ? 'selected' : '' ?>>Perempuan</option></select>
    <?php elseif ($type === 'textarea'): ?><textarea class="form-control" name="<?= $f ?>" rows="2"><?= esc($val) ?></textarea>
    <?php else: ?><input class="form-control" type="<?= $type ?>" name="<?= $f ?>" value="<?= esc($val) ?>" <?= $f === 'nama' ? 'required' : '' ?>><?php endif ?>
    <?php if ($f === 'urut'): ?><div class="form-text">Angka kecil tampil lebih dulu di surat tugas (mis. Kepala Sekolah = 1).</div><?php endif ?>
    <?php if ($t === 'guru' && $f === 'nip'): ?><div class="form-text">Akun login guru dibuat otomatis dengan username dan password awal sesuai NIP.</div><?php endif ?>
  </div>
  <?php endforeach ?>
  <div class="d-flex gap-2"><button class="btn btn-success">Simpan</button><a class="btn btn-outline-secondary" href="<?= site_url("data/$t") ?>">Batal</a></div>
</form>
<?= $this->endSection() ?>
