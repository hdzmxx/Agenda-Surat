<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<?php
$r = $row ?? [];
$v = static fn (string $k, $d = '') => old($k, $r[$k] ?? $d);
$masuk = $arah === 'masuk';
?>
<h4 class="mb-3"><?= $row ? 'Ubah' : 'Tambah' ?> surat <?= $arah ?></h4>
<form method="post" action="<?= $action ?>" enctype="multipart/form-data" class="card card-body shadow-sm">
  <?= csrf_field() ?>
  <div class="row g-3">
    <div class="col-md-3"><label class="form-label">No. agenda <?= $row ? '' : '(otomatis)' ?></label>
      <input class="form-control" type="number" min="1" id="no_agenda" name="no_agenda" value="<?= esc($v('no_agenda', $next)) ?>" <?= $row ? '' : 'disabled' ?>>
      <?php if (! $row): ?>
        <div class="form-check mt-1"><input class="form-check-input" type="checkbox" id="manual"><label class="form-check-label small" for="manual">Isi manual</label></div>
      <?php endif ?>
    </div>
    <div class="col-md-3"><label class="form-label"><?= $masuk ? 'Tanggal diterima' : 'Tanggal surat' ?> *</label>
      <input class="form-control" type="date" name="tgl_agenda" value="<?= esc($v('tgl_agenda', date('Y-m-d'))) ?>" required></div>
    <?php if ($masuk): ?>
    <div class="col-md-3"><label class="form-label">Tanggal pada surat</label>
      <input class="form-control" type="date" name="tgl_surat" value="<?= esc($v('tgl_surat')) ?>"></div>
    <?php endif ?>
    <div class="col-md-3"><label class="form-label">Nomor surat<?= $masuk ? ' *' : '' ?></label>
      <input class="form-control" id="nomor_surat" name="nomor_surat" value="<?= esc($v('nomor_surat')) ?>" <?= $masuk ? 'required' : 'placeholder="Otomatis jika dikosongkan"' ?>></div>
    <div class="col-md-6"><label class="form-label"><?= $masuk ? 'Asal surat (pengirim)' : 'Tujuan surat' ?> *</label>
      <input class="form-control" name="pihak" value="<?= esc($v('pihak')) ?>" required></div>
    <div class="col-md-6"><label class="form-label">Perihal *</label>
      <input class="form-control" name="perihal" value="<?= esc($v('perihal')) ?>" required></div>
    <?php if ($masuk): ?>
    <?php $selectedGuruIds = old('guru_ids', $selectedGuruIds ?? []); if (! is_array($selectedGuruIds)) $selectedGuruIds = []; ?>
    <div class="col-md-6">
      <label class="form-label" for="cari-guru-disposisi">Diteruskan / disposisi kepada guru</label>
      <input class="form-control mb-2 cari-pilih" id="cari-guru-disposisi" type="search" placeholder="Cari nama atau NIP guru...">
      <div class="border rounded p-2 pilih" style="max-height:280px;overflow:auto">
        <?php foreach (($guruUsers ?? []) as $guru): ?>
          <label class="d-block"><input type="checkbox" name="guru_ids[]" value="<?= esc($guru['id']) ?>" <?= in_array((string) $guru['id'], array_map('strval', $selectedGuruIds), true) ? 'checked' : '' ?>> <?= esc($guru['nama']) ?> <small class="text-muted">(<?= esc($guru['username']) ?>)</small></label>
        <?php endforeach ?>
      </div>
      <div class="mt-1"><button type="button" class="btn btn-sm btn-outline-secondary pilih-semua">Pilih semua yang tampil</button> <button type="button" class="btn btn-sm btn-outline-secondary pilih-kosong">Kosongkan pilihan</button></div>
      <div class="form-text" id="guruHelp"><?= empty($guruUsers) ? 'Belum ada akun guru. Tambahkan data guru dengan NIP di Data master agar akunnya dibuat otomatis.' : 'Gunakan pencarian untuk memfilter, lalu centang satu atau beberapa guru.' ?></div>
      <?php if (! empty($disposisiLama) && empty($selectedGuruIds)): ?><div class="form-text text-warning">Disposisi lama tersimpan sebagai teks bebas dan belum terkait akun guru: <?= esc($disposisiLama) ?></div><?php endif ?>
    </div>
    <div class="col-md-6"><label class="form-label" for="catatan_disposisi">Catatan / instruksi disposisi</label>
      <textarea class="form-control" id="catatan_disposisi" name="catatan_disposisi" rows="5" maxlength="5000"><?= esc(old('catatan_disposisi', $disposisiCatatan ?? '')) ?></textarea>
      <div class="form-text">Catatan ini akan terlihat oleh semua guru yang dipilih.</div>
    </div>
    <?php else: ?>
    <div class="col-md-3"><label class="form-label">Jenis surat</label>
      <select class="form-select" name="jenis"><?php foreach ($jenis as $j): ?><option <?= $v('jenis') === $j ? 'selected' : '' ?>><?= $j ?></option><?php endforeach ?></select></div>
    <div class="col-md-3"><label class="form-label">Penandatangan</label>
      <input class="form-control" name="penandatangan" value="<?= esc($v('penandatangan')) ?>"></div>
    <?php endif ?>
    <div class="col-md-2"><label class="form-label">Sifat</label>
      <select class="form-select" name="sifat"><?php foreach ($sifat as $s): ?><option <?= $v('sifat') === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach ?></select></div>
    <div class="col-md-2"><label class="form-label">Lampiran</label>
      <input class="form-control" name="lampiran" value="<?= esc($v('lampiran')) ?>" placeholder="mis. 1 berkas"></div>
    <div class="col-md-2"><label class="form-label">Status</label>
      <select class="form-select" name="status"><?php foreach ($status as $s): ?><option <?= $v('status') === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach ?></select></div>
    <div class="col-12"><label class="form-label">Ringkasan isi</label><textarea class="form-control" name="isi" rows="3"><?= esc($v('isi')) ?></textarea></div>
    <div class="col-12"><label class="form-label">Keterangan</label><textarea class="form-control" name="keterangan" rows="2"><?= esc($v('keterangan')) ?></textarea></div>
    <div class="col-12"><label class="form-label">File surat (PDF, JPG, PNG, maks. 5 MB)</label>
      <input class="form-control" type="file" name="berkas" accept=".pdf,.jpg,.jpeg,.png">
      <?php if (! empty($r['berkas'])): ?>
        <div class="form-check mt-2"><input class="form-check-input" type="checkbox" name="hapus_berkas" value="1" id="hb">
          <label class="form-check-label" for="hb">Hapus file saat ini (<?= esc($r['berkas_asli']) ?>)</label></div>
        <div class="form-text">Mengunggah file baru akan menggantikan file saat ini.</div>
      <?php endif ?>
    </div>
  </div>
  <div class="mt-4 d-flex gap-2">
    <button class="btn btn-success">Simpan</button>
    <a class="btn btn-outline-secondary" href="<?= site_url("surat/$arah") ?>">Batal</a>
  </div>
</form>
<script>
document.querySelectorAll('.cari-pilih').forEach(function (input) {
  var list = input.nextElementSibling;
  input.addEventListener('input', function () {
    var query = input.value.trim().toLocaleLowerCase();
    list.querySelectorAll('label').forEach(function (label) {
      label.hidden = !label.textContent.toLocaleLowerCase().includes(query);
    });
  });
});
document.querySelectorAll('.pilih-semua').forEach(function (button) {
  button.addEventListener('click', function () {
    button.closest('.col-md-6').querySelectorAll('.pilih label:not([hidden]) input').forEach(function (input) { input.checked = true; });
  });
});
document.querySelectorAll('.pilih-kosong').forEach(function (button) {
  button.addEventListener('click', function () {
    button.closest('.col-md-6').querySelectorAll('.pilih input').forEach(function (input) { input.checked = false; });
  });
});
</script>
<?php if (! $row): ?>
<script>
(function () {
  var tgl = document.querySelector('[name=tgl_agenda]'),
      no  = document.getElementById('no_agenda'),
      ns  = document.getElementById('nomor_surat'),
      man = document.getElementById('manual'),
      keluar = <?= $masuk ? 'false' : 'true' ?>;
  man.addEventListener('change', function () { no.disabled = !man.checked; });
  function ambil() {
    fetch('<?= site_url("surat/$arah/nomor") ?>?tgl=' + encodeURIComponent(tgl.value))
      .then(function (r) { return r.json(); })
      .then(function (j) {
        if (!man.checked) no.value = j.no_agenda;
        if (keluar) ns.placeholder = 'Otomatis: ' + j.nomor_surat;
      });
  }
  tgl.addEventListener('change', ambil);
  ambil();
})();
</script>
<?php endif ?>
<?= $this->endSection() ?>
