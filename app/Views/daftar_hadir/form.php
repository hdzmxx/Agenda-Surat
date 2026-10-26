<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<?php
$d = $d ?? [];
$jenisTerpilih = old('peserta', $d['jenis'] ?? ['guru', 'siswa']);
$jenisTerpilih = is_array($jenisTerpilih) ? $jenisTerpilih : [];
$guruTerpilih = array_map('strval', (array) old('guru_ids', $d['guru_ids'] ?? []));
$siswaTerpilih = array_map('strval', (array) old('siswa_ids', $d['siswa_ids'] ?? []));
$manualNama = (array) old('manual_nama', $d['manual_nama'] ?? ['']);
$manualJabatan = (array) old('manual_jabatan', $d['manual_jabatan'] ?? ['']);
$manualNip = (array) old('manual_nip', $d['manual_nip'] ?? ['']);
$manualJenis = (array) old('manual_jenis', $d['manual_jenis'] ?? ['nip']);
$manualCount = max(count($manualNama), count($manualJabatan), count($manualNip), count($manualJenis), 1);
?>
<h4 class="mb-3"><?= $id ? 'Ubah daftar hadir' : 'Daftar hadir kegiatan' ?></h4>
<p class="text-muted">Isi informasi kegiatan, pilih jenis peserta, lalu centang orang yang akan hadir. Data peserta diambil dari Data Guru dan Data Siswa. Daftar hadir yang dibuat disimpan dan bisa dicetak ulang kapan saja.</p>
<form method="post" action="<?= $aksi ?>">
  <?= csrf_field() ?>
  <div class="card shadow-sm mb-3">
    <div class="card-header fw-semibold">Informasi kegiatan</div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-lg-6">
          <label class="form-label" for="judul">Nama / judul kegiatan *</label>
          <input class="form-control" id="judul" name="judul" maxlength="200" value="<?= esc(old('judul', $d['judul'] ?? '')) ?>" required>
        </div>
        <div class="col-lg-6">
          <label class="form-label" for="tema">Tema atau keterangan kegiatan (opsional)</label>
          <input class="form-control" id="tema" name="tema" maxlength="250" value="<?= esc(old('tema', $d['tema'] ?? '')) ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label" for="tanggal">Tanggal kegiatan *</label>
          <input class="form-control" type="date" id="tanggal" name="tanggal" value="<?= esc(old('tanggal', $d['tanggal'] ?? $defaults['tanggal'])) ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label" for="kota">Kota / tempat tanda tangan *</label>
          <input class="form-control" id="kota" name="kota" maxlength="100" value="<?= esc(old('kota', $d['kota'] ?? 'Kebumen')) ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label" for="jabatan">Jabatan penanggung jawab *</label>
          <input class="form-control" id="jabatan" name="jabatan" maxlength="100" value="<?= esc(old('jabatan', $d['jabatan'] ?? $defaults['jabatan'])) ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label" for="nama">Nama penanggung jawab *</label>
          <input class="form-control" id="nama" name="nama" maxlength="150" value="<?= esc(old('nama', $d['nama'] ?? $defaults['nama'])) ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label" for="nip">NIP penanggung jawab (opsional)</label>
          <input class="form-control" id="nip" name="nip" maxlength="30" value="<?= esc(old('nip', $d['nip'] ?? $defaults['nip'])) ?>">
        </div>
      </div>
    </div>
  </div>

  <div class="card shadow-sm mb-3">
    <div class="card-header fw-semibold">Jenis peserta</div>
    <div class="card-body d-flex flex-wrap gap-4">
      <div class="form-check">
        <input class="form-check-input group-toggle" type="checkbox" name="peserta[]" id="jenis-guru" value="guru" <?= in_array('guru', $jenisTerpilih, true) ? 'checked' : '' ?>>
        <label class="form-check-label" for="jenis-guru">Guru / Karyawan (<?= count($guru) ?>)</label>
      </div>
      <div class="form-check">
        <input class="form-check-input group-toggle" type="checkbox" name="peserta[]" id="jenis-siswa" value="siswa" <?= in_array('siswa', $jenisTerpilih, true) ? 'checked' : '' ?>>
        <label class="form-check-label" for="jenis-siswa">Siswa (<?= count($siswa) ?>)</label>
      </div>
    </div>
  </div>

  <div class="card shadow-sm mb-3">
    <div class="card-header fw-semibold">Tamu / peserta manual</div>
    <div class="card-body">
      <p class="text-muted small">Tambahkan tamu dari luar sekolah jika diperlukan. Peserta manual akan ditampilkan sebelum peserta dari database.</p>
      <div id="guest-list">
        <?php for ($i = 0; $i < $manualCount; $i++): ?>
          <div class="row g-2 align-items-end guest-row mb-2">
            <div class="col-md-5">
              <label class="form-label">Nama tamu</label>
              <input class="form-control" name="manual_nama[]" maxlength="150" value="<?= esc($manualNama[$i] ?? '') ?>" placeholder="Nama peserta">
            </div>
            <div class="col-md-3">
              <label class="form-label">Jabatan / keterangan</label>
              <input class="form-control" name="manual_jabatan[]" maxlength="100" value="<?= esc($manualJabatan[$i] ?? '') ?>" placeholder="Opsional">
            </div>
            <div class="col-md-3">
              <label class="form-label">Tampilan identitas</label>
              <select class="form-select" name="manual_jenis[]">
                <option value="nip" <?= ($manualJenis[$i] ?? 'nip') === 'nip' ? 'selected' : '' ?>>NIP</option>
                <option value="manual" <?= ($manualJenis[$i] ?? '') === 'manual' ? 'selected' : '' ?>>Manual</option>
                <option value="kosong" <?= ($manualJenis[$i] ?? '') === 'kosong' ? 'selected' : '' ?>>Kosongkan</option>
              </select>
              <label class="form-label small mt-1 identity-value-label">Nomor NIP</label>
              <input class="form-control" name="manual_nip[]" maxlength="30" value="<?= esc($manualNip[$i] ?? '') ?>" placeholder="Masukkan NIP">
            </div>
            <div class="col-md-1 d-grid">
              <button class="btn btn-outline-danger remove-guest" type="button" aria-label="Hapus tamu">Hapus</button>
            </div>
          </div>
        <?php endfor ?>
      </div>
      <button class="btn btn-outline-primary btn-sm" id="add-guest" type="button">Tambah peserta manual</button>
      <template id="guest-template">
        <div class="row g-2 align-items-end guest-row mb-2">
          <div class="col-md-5">
            <label class="form-label">Nama tamu</label>
            <input class="form-control" name="manual_nama[]" maxlength="150" placeholder="Nama peserta">
          </div>
          <div class="col-md-3">
            <label class="form-label">Jabatan / keterangan</label>
            <input class="form-control" name="manual_jabatan[]" maxlength="100" placeholder="Opsional">
          </div>
          <div class="col-md-3">
            <label class="form-label">Tampilan identitas</label>
            <select class="form-select" name="manual_jenis[]">
              <option value="nip" selected>NIP</option>
              <option value="manual">Manual</option>
              <option value="kosong">Kosongkan</option>
            </select>
            <label class="form-label small mt-1 identity-value-label">Nomor NIP</label>
            <input class="form-control" name="manual_nip[]" maxlength="30" placeholder="Masukkan NIP">
          </div>
          <div class="col-md-1 d-grid">
            <button class="btn btn-outline-danger remove-guest" type="button" aria-label="Hapus tamu">Hapus</button>
          </div>
        </div>
      </template>
    </div>
  </div>

  <?php foreach ([
      'guru' => ['title' => 'Daftar guru / karyawan', 'rows' => $guru, 'selected' => $guruTerpilih],
      'siswa' => ['title' => 'Daftar siswa', 'rows' => $siswa, 'selected' => $siswaTerpilih],
  ] as $group => $list): ?>
    <section class="card shadow-sm mb-3 participant-group" id="group-<?= $group ?>">
      <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="mb-0 fs-6"><?= $list['title'] ?></h5>
        <span class="badge text-bg-secondary"><?= count($list['rows']) ?> data</span>
      </div>
      <div class="card-body">
        <?php if (! $list['rows']): ?>
          <p class="text-muted mb-0">Belum ada data. Tambahkan melalui menu Data <?= $group === 'guru' ? 'guru' : 'siswa' ?>.</p>
        <?php else: ?>
          <input class="form-control mb-2 participant-search" type="search" placeholder="Cari nama<?= $group === 'siswa' ? ' atau kelas...' : ' guru...' ?>" aria-label="Cari <?= strtolower($list['title']) ?>" data-group="<?= $group ?>">
          <?php if ($group === 'siswa'): ?>
            <?php
            $perKelas = [];
            foreach ($list['rows'] as $person) {
                $kls = (string) ($person['kelas'] ?? '');
                $perKelas[$kls] = ($perKelas[$kls] ?? 0) + 1;
            }
            ?>
            <div class="mb-2">
              <div class="small text-muted mb-1">Blok per kelas: centang satu kelas untuk memilih semua siswanya, hapus centang untuk melepas.</div>
              <div class="d-flex flex-wrap gap-1">
                <?php foreach ($perKelas as $kls => $jumlah): ?>
                  <label class="btn btn-sm btn-outline-secondary mb-0 kelas-chip">
                    <input class="form-check-input me-1 kelas-toggle" type="checkbox" data-kelas="<?= esc((string) $kls, 'attr') ?>">
                    <?= esc((string) $kls !== '' ? (string) $kls : 'Tanpa kelas') ?> <small>(<?= $jumlah ?>)</small>
                  </label>
                <?php endforeach ?>
              </div>
            </div>
          <?php endif ?>
          <div class="participant-list border rounded p-2" id="list-<?= $group ?>">
            <?php foreach ($list['rows'] as $person): ?>
              <?php
              $id = (string) $person['id'];
              $detail = $group === 'guru' ? ($person['jabatan'] ?: 'Jabatan belum diisi') : ($person['kelas'] ?: '-');
              ?>
              <label class="participant-option d-block" data-kelas="<?= esc((string) ($person['kelas'] ?? ''), 'attr') ?>" data-search="<?= esc(mb_strtolower($person['nama'] . ' ' . $detail)) ?>">
                <input type="checkbox" name="<?= $group ?>_ids[]" value="<?= esc($id) ?>" <?= in_array($id, $list['selected'], true) ? 'checked' : '' ?>>
                <?= esc($person['nama']) ?> <small class="text-muted">(<?= esc($detail) ?>)</small>
              </label>
            <?php endforeach ?>
          </div>
          <p class="text-muted small mb-0 mt-2 no-matches" hidden>Tidak ada peserta yang cocok dengan pencarian.</p>
          <div class="mt-1 d-flex flex-wrap gap-1">
            <button class="btn btn-sm btn-outline-secondary select-visible" type="button" data-group="<?= $group ?>">Pilih semua yang tampil</button>
            <button class="btn btn-sm btn-outline-secondary clear-visible" type="button" data-group="<?= $group ?>">Kosongkan pilihan</button>
          </div>
        <?php endif ?>
      </div>
    </section>
  <?php endforeach ?>

  <div class="d-flex flex-wrap gap-2 mb-4">
    <button class="btn btn-success btn-pill" type="submit"><?= $id ? 'Simpan perubahan' : 'Simpan dan cetak' ?></button>
    <a class="btn btn-outline-secondary btn-pill" href="<?= site_url('daftar-hadir') ?>">Batal</a>
  </div>
</form>
<style>
  .participant-list{max-height:280px;overflow:auto}
  .participant-option{margin:0;padding:2px 4px;cursor:pointer}
  .participant-option[hidden]{display:none}
  .participant-group[hidden]{display:none!important}
</style>
<script>
  (function () {
    var toggles = document.querySelectorAll('.group-toggle');
    function updateGroups() {
      toggles.forEach(function (toggle) {
        document.getElementById('group-' + toggle.value).hidden = !toggle.checked;
      });
    }
    toggles.forEach(function (toggle) { toggle.addEventListener('change', updateGroups); });
    updateGroups();

    var guestList = document.getElementById('guest-list'),
        guestTemplate = document.getElementById('guest-template');
    function updateGuestIdentity(row) {
      var type = row.querySelector('select[name="manual_jenis[]"]').value,
          label = row.querySelector('.identity-value-label'),
          input = row.querySelector('input[name="manual_nip[]"]');
      label.textContent = type === 'manual' ? 'Teks yang akan dicetak (isi sendiri)' : 'Nomor NIP';
      input.placeholder = type === 'manual' ? 'Contoh: No. KTP 123456789' : 'Masukkan NIP';
      label.hidden = type === 'kosong';
      input.hidden = type === 'kosong';
    }
    guestList.querySelectorAll('.guest-row').forEach(function (row) {
      updateGuestIdentity(row);
      row.querySelector('select[name="manual_jenis[]"]').addEventListener('change', function () {
        updateGuestIdentity(row);
      });
    });
    document.getElementById('add-guest').addEventListener('click', function () {
      var fragment = guestTemplate.content.cloneNode(true),
          row = fragment.querySelector('.guest-row');
      guestList.appendChild(fragment);
      updateGuestIdentity(row);
      row.querySelector('select[name="manual_jenis[]"]').addEventListener('change', function () {
        updateGuestIdentity(row);
      });
    });
    guestList.addEventListener('click', function (event) {
      if (event.target.closest('.remove-guest')) {
        var row = event.target.closest('.guest-row');
        if (guestList.querySelectorAll('.guest-row').length > 1) row.remove();
        else row.querySelectorAll('input').forEach(function (input) { input.value = ''; });
      }
    });

    document.querySelectorAll('.participant-search').forEach(function (search) {
      var group = search.dataset.group,
          list = document.getElementById('list-' + group),
          empty = document.querySelector('#group-' + group + ' .no-matches');
      if (!list) return;
      function filter() {
        var query = search.value.trim().toLocaleLowerCase(),
            visible = 0;
        list.querySelectorAll('.participant-option').forEach(function (option) {
          var match = option.dataset.search.indexOf(query) !== -1;
          option.hidden = !match;
          if (match) visible++;
        });
        empty.hidden = visible > 0;
      }
      search.addEventListener('input', filter);
      document.querySelectorAll('.select-visible[data-group="' + group + '"],.clear-visible[data-group="' + group + '"]').forEach(function (button) {
        button.addEventListener('click', function () {
          var checked = button.classList.contains('select-visible');
          if (checked) {
            list.querySelectorAll('.participant-option:not([hidden]) input[type=checkbox]').forEach(function (input) { input.checked = true; });
          } else {
            list.querySelectorAll('input[type=checkbox]').forEach(function (input) { input.checked = false; });
          }
        });
      });
    });

    // Blok per kelas: kotak kelas memilih/melepas semua siswa kelas itu; statusnya ikut berubah bila siswa dicentang satu per satu.
    var listSiswa = document.getElementById('list-siswa');
    if (listSiswa) {
      var chips = document.querySelectorAll('.kelas-toggle');
      function opsiKelas(kelas) {
        return Array.prototype.filter.call(listSiswa.querySelectorAll('.participant-option'), function (option) {
          return option.dataset.kelas === kelas;
        });
      }
      function segarkanKelas() {
        chips.forEach(function (chip) {
          var opsi = opsiKelas(chip.dataset.kelas), terpilih = 0;
          opsi.forEach(function (option) { if (option.querySelector('input').checked) terpilih++; });
          chip.checked = opsi.length > 0 && terpilih === opsi.length;
          chip.indeterminate = terpilih > 0 && terpilih < opsi.length;
          chip.closest('label').classList.toggle('active', chip.checked);
        });
      }
      chips.forEach(function (chip) {
        chip.addEventListener('change', function () {
          opsiKelas(chip.dataset.kelas).forEach(function (option) { option.querySelector('input').checked = chip.checked; });
          segarkanKelas();
        });
      });
      listSiswa.addEventListener('change', segarkanKelas);
      document.querySelectorAll('.select-visible[data-group="siswa"],.clear-visible[data-group="siswa"]').forEach(function (button) {
        button.addEventListener('click', segarkanKelas);
      });
      segarkanKelas();
    }
  })();
</script>
<?= $this->endSection() ?>
