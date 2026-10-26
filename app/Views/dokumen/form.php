<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<?php
$nama  = \App\Libraries\JenisSurat::DAFTAR[$jenis]['nama'];
$biasa = in_array($jenis, ['undangan', 'narasumber'], true);
$v     = static fn (string $k) => old($k, $d[$k] ?? '');
$in    = static fn (string $k, string $label, string $type = 'text', string $col = 'col-md-4', bool $req = false, string $ph = '', bool $fm = false) =>
    '<div class="' . $col . '"><label class="form-label">' . $label . ($req ? ' *' : '') . '</label><input class="form-control" type="' . $type . '" name="' . $k . '" value="' . esc(old($k, $d[$k] ?? '')) . '"' . ($req ? ' required' : '') . ($ph ? ' placeholder="' . esc($ph) . '"' : '') . ($fm ? ' data-fm' : '') . '></div>';
$ta    = static fn (string $k, string $label, int $rows = 3, string $help = '', bool $fm = true) =>
    '<div class="col-12"><label class="form-label">' . $label . '</label><textarea class="form-control" name="' . $k . '" rows="' . $rows . '"' . ($fm ? ' data-fm' : '') . '>' . esc(old($k, $d[$k] ?? '')) . '</textarea>' . ($help ? '<div class="form-text">' . $help . '</div>' : '') . '</div>';
?>
<style>
  .fm-kepala{display:flex;align-items:flex-end;justify-content:space-between;gap:.5rem;margin-bottom:.35rem}
  .fm-kepala>.form-label{margin-bottom:0}
  .fm-bar .fm-btn{padding:0 .5rem;font-size:.8rem;line-height:1.5;min-width:1.9rem}
</style>
<h4 class="mb-3"><?= $ubah ? 'Ubah' : 'Buat' ?> <?= esc(strtolower($nama)) ?></h4>
<form method="post" action="<?= $action ?>" class="card card-body shadow-sm">
  <?= csrf_field() ?>
  <div class="row g-3">
    <?php if ($jenis !== 'sppd'): ?>
    <div class="col-12 form-text mt-0">Untuk menebalkan, memiringkan, atau menggarisbawahi kata di dalam surat: blok katanya di kolom isian, lalu klik tombol <b>B</b>, <i>I</i>, atau <u>U</u> di atas kolom itu (atau tekan Ctrl+B, Ctrl+I, Ctrl+U). Tanpa blok, tombol berlaku untuk kata di posisi kursor. Klik lagi untuk membatalkan.</div>
    <?php endif ?>
    <?= $in('tempat', 'Tempat surat', 'text', 'col-md-3') ?>
    <?= $in('tgl_surat', 'Tanggal surat', 'date', 'col-md-3', true) ?>
    <?= $in('kode', 'Kode klasifikasi', 'text', 'col-md-3', true, 'mis. 800') ?>
    <?= $in('nomor_manual', 'Nomor surat (opsional)', 'text', 'col-md-3', false, 'mis. 553') ?>
    <?php if (! $ubah): ?><div class="col-12 form-text mt-0" id="nomor-hint">Format: kode klasifikasi / nomor surat / tahun. Kosongkan nomor surat untuk memakai nomor agenda otomatis.</div><?php endif ?>

    <?php if ($biasa): ?>
      <?= $in('hal', 'Hal', 'text', 'col-md-4', false, '', true) ?>
      <?= $in('lamp', 'Lampiran', 'text', 'col-md-2') ?>
      <?= $in('kegiatan', 'Nama kegiatan', 'text', 'col-md-6', true, '', true) ?>
      <?= $in('yth', 'Yth. (nama / sebutan penerima)', 'text', 'col-md-5', true, 'mis. Bapak Toto Prabawa, M.Pd.') ?>
      <?= $in('jab', 'Jabatan / instansi penerima', 'text', 'col-md-4', false, 'mis. Pengawas Sekolah') ?>
      <?= $in('di', 'Di (kota)', 'text', 'col-md-3') ?>
      <?= $ta('pembuka', 'Paragraf pembuka', 4, 'Kode {kegiatan} dan {yth} otomatis diganti.') ?>
      <?= $in('tgl_kegiatan', 'Tanggal kegiatan', 'date', 'col-md-3') ?>
      <?= $in('tgl_kegiatan_akhir', 'Sampai tanggal (jika lebih dari sehari)', 'date', 'col-md-3') ?>
      <?= $in('waktu', 'Waktu', 'text', 'col-md-3', false, 'mis. 07.00 WIB s.d. 08.00 WIB', true) ?>
      <?= $in('tmpk', 'Tempat kegiatan', 'text', 'col-md-3', false, '', true) ?>
      <?= $ta('perlu', 'Keperluan / acara (opsional)', 2) ?>
      <?= $ta('penutup', 'Paragraf penutup', 3) ?>

    <?php elseif ($jenis === 'sk_siswa'): ?>
      <?= $in('tahun_pelajaran', 'Tahun pelajaran', 'text', 'col-md-3') ?>
      <div class="col-12">
        <label class="form-label">Pilih siswa * <span class="text-muted small">(1 siswa: format rinci. Lebih dari 1: format daftar tabel)</span></label>
        <?php if (! $siswa): ?><div class="alert alert-warning">Data siswa masih kosong. <a href="<?= site_url('data/siswa/import') ?>">Impor dari Excel</a> atau <a href="<?= site_url('data/siswa/baru') ?>">tambah manual</a> dulu.</div><?php endif ?>
        <input class="form-control mb-2 cari-pilih" type="search" placeholder="Cari nama atau kelas...">
        <div class="border rounded p-2 pilih" style="max-height:280px;overflow:auto">
          <?php $sel = array_map('intval', (array) old('siswa_ids', $d['siswa_ids'] ?? [])); foreach ($siswa as $s): ?>
          <label class="d-block"><input type="checkbox" name="siswa_ids[]" value="<?= $s['id'] ?>" <?= in_array((int) $s['id'], $sel, true) ? 'checked' : '' ?>> <?= esc($s['nama']) ?> <small class="text-muted">(<?= esc($s['kelas']) ?>)</small></label>
          <?php endforeach ?>
        </div>
        <div class="mt-1"><button type="button" class="btn btn-sm btn-outline-secondary pilih-semua">Pilih semua yang tampil</button> <button type="button" class="btn btn-sm btn-outline-secondary pilih-kosong">Kosongkan pilihan</button></div>
      </div>
      <?= $ta('keperluan', 'Keperluan (opsional)', 2, 'Jika diisi, tampil sebagai kalimat tambahan sebelum penutup.') ?>
      <?= $ta('penutup', 'Paragraf penutup', 2) ?>

    <?php elseif ($jenis === 'prestasi'): ?>
      <?= $in('tahun_pelajaran', 'Tahun pelajaran', 'text', 'col-md-3', true) ?>
      <div class="col-12">
        <label class="form-label">Siswa (dari data siswa) *</label>
        <?php if (! $siswa): ?><div class="alert alert-warning">Data siswa masih kosong. <a href="<?= site_url('data/siswa/import') ?>">Impor dari Excel</a> atau <a href="<?= site_url('data/siswa/baru') ?>">tambah manual</a> dulu.</div><?php endif ?>
        <input class="form-control mb-2 cari-pilih" type="search" placeholder="Cari nama atau kelas...">
        <div class="border rounded p-2 pilih" style="max-height:280px;overflow:auto">
          <?php $selS = (string) old('siswa_id', $d['siswa_id'] ?? ''); foreach ($siswa as $s): ?>
          <label class="d-block"><input type="radio" name="siswa_id" value="<?= $s['id'] ?>" <?= $selS === (string) $s['id'] ? 'checked' : '' ?> required> <?= esc($s['nama']) ?> <small class="text-muted">(<?= esc($s['kelas']) ?>)</small></label>
          <?php endforeach ?>
        </div>
        <div class="form-text">Nama, NISN, tempat dan tanggal lahir diambil dari Data siswa. Asal sekolah mengikuti nama sekolah.</div>
      </div>
      <?= $in('kejuaraan', 'Kejuaraan / perlombaan', 'text', 'col-12', true, 'mis. Festival Lomba Seni dan Sastra Siswa Nasional (FLS3N) Kreativitas Musik Tradisional SMP Negeri dan Swasta Tingkat Kabupaten Kebumen Tahun 2025', true) ?>
      <?= $in('tahun', 'Pada tahun', 'text', 'col-md-3', true, 'mis. 2025') ?>
      <?= $in('penyelenggara', 'Diselenggarakan oleh', 'text', 'col-md-9', true, 'mis. Dinas Pendidikan, Kepemudaan dan Olahraga Kabupaten Kebumen', true) ?>
      <?= $in('prestasi', 'Prestasi yang dicapai', 'text', 'col-12', true, 'mis. Juara 3 Ensambel Campuran 3 Alat Musik', true) ?>
    <?php elseif ($jenis === 'sppd'): ?>
      <?= $in('asal', 'Berangkat dari (tempat kedudukan)', 'text', 'col-md-6', true) ?>
      <?= $in('tujuan', 'Tujuan perjalanan', 'text', 'col-md-6', true) ?>
      <?= $in('tglb', 'Tanggal berangkat', 'date', 'col-md-3', true) ?>
      <?= $in('tglt', 'Tanggal tiba', 'date', 'col-md-3') ?>
      <?= $in('tglp', 'Tanggal berangkat pulang', 'date', 'col-md-3') ?>
      <?= $in('tglk', 'Tanggal tiba kembali', 'date', 'col-md-3') ?>
      <?php
        $gSel   = (string) old('guru_id', $d['guru_id'] ?? '');
        $legacy = $gSel === '' && ($d['nama'] ?? '') !== '';
      ?>
      <?php if ($ubah): ?>
      <?php helper('ui'); ?>
      <div class="col-12">
        <label class="form-label" for="pegCari">Pegawai yang diperintahkan *</label>
        <?php if (! $guru): ?><div class="alert alert-warning py-1 mb-1">Data guru masih kosong. <a href="<?= site_url('data/guru/import') ?>">Impor dari Excel</a> atau <a href="<?= site_url('data/guru/baru') ?>">tambah manual</a> dulu.</div><?php endif ?>
        <input type="hidden" name="guru_id" id="sppd-guru" value="<?= esc($gSel) ?>" data-legacy="<?= $legacy ? '1' : '' ?>" data-legacy-nama="<?= esc($legacy ? ($d['nama'] ?? '') : '', 'attr') ?>">
        <div class="peg-picker" id="pegPicker">
          <div class="peg-box">
            <?= ui_icon('search') ?>
            <input class="form-control" type="text" id="pegCari" autocomplete="off" role="combobox" aria-expanded="false" aria-controls="pegList" placeholder="Ketik nama, NIP, atau jabatan untuk mencari pegawai">
            <button type="button" class="peg-clear" id="pegHapus" aria-label="Hapus pilihan" hidden><?= ui_icon('x') ?></button>
          </div>
          <div class="peg-list" id="pegList" role="listbox" hidden></div>
        </div>
        <div class="invalid-feedback" id="pegError">Pilih pegawai dari daftar.</div>
        <?php if ($legacy): ?><div class="form-text">Surat lama atas nama <strong><?= esc($d['nama']) ?></strong> belum tertaut ke data guru. Cari dan pilih pegawai untuk menautkan, atau biarkan untuk mempertahankan data lama.</div><?php endif ?>
        <script type="application/json" id="pegData"><?= json_encode(array_map(static fn ($g) => [
            'id' => (int) $g['id'], 'nama' => (string) $g['nama'], 'nip' => (string) ($g['nip'] ?? ''),
            'jab' => (string) ($g['jabatan'] ?? ''), 'pangkat' => (string) ($g['pangkat_gol'] ?? ''),
        ], $guru), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
      </div>
      <div class="col-md-3"><label class="form-label">NIP</label><input class="form-control" id="sppd-nip" value="<?= esc($d['nip'] ?? '') ?>" readonly></div>
      <div class="col-md-5"><label class="form-label">Jabatan</label><input class="form-control" id="sppd-jab" value="<?= esc($d['jab'] ?? '') ?>" readonly></div>
      <div class="col-md-4"><label class="form-label">Pangkat / golongan</label><input class="form-control" id="sppd-pangkat" value="<?= esc($d['pangkat'] ?? '') ?>" readonly></div>
      <?php else: ?>
      <?php
  helper('ui');
  $terpilih = array_values(array_filter(array_map('intval', (array) old('guru_ids', []))));
?>
      <div class="col-12">
        <label class="form-label" for="pegCari">Pegawai yang diperintahkan *</label>
        <?php if (! $guru): ?><div class="alert alert-warning py-1 mb-1">Data guru masih kosong. <a href="<?= site_url('data/guru/import') ?>">Impor dari Excel</a> atau <a href="<?= site_url('data/guru/baru') ?>">tambah manual</a> dulu.</div><?php endif ?>
        <div class="peg-picker" id="pegPicker">
          <div class="peg-box">
            <?= ui_icon('search') ?>
            <input class="form-control" type="text" id="pegCari" autocomplete="off" role="combobox" aria-expanded="false" aria-controls="pegList" placeholder="Ketik nama, NIP, atau jabatan, lalu klik untuk menambahkan">
          </div>
          <div class="peg-list" id="pegList" role="listbox" hidden></div>
        </div>
        <div class="peg-chips" id="pegChips"></div>
        <div id="pegHidden"></div>
        <div class="form-text" id="pegInfo">Pilih satu atau beberapa pegawai. Setiap pegawai mendapat satu SPPD, nomornya berurutan.</div>
        <div class="invalid-feedback" id="pegError">Pilih minimal satu pegawai dari daftar.</div>
        <script type="application/json" id="pegData"><?= json_encode(array_map(static fn ($g) => [
            'id' => (int) $g['id'], 'nama' => (string) $g['nama'], 'nip' => (string) ($g['nip'] ?? ''),
            'jab' => (string) ($g['jabatan'] ?? ''),
        ], $guru), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
        <script type="application/json" id="pegAwal"><?= json_encode($terpilih) ?></script>
      </div>
      <?php endif ?>
      <?= $ta('maksud', 'Maksud perjalanan', 2, '', false) ?>
      <?= $in('tingkat', 'Tingkat menurut perjalanan dinas', 'text', 'col-md-4') ?>
      <?= $in('alat', 'Alat angkut yang dipergunakan', 'text', 'col-md-4') ?>
      <?= $in('pengikut', 'Pengikut', 'text', 'col-md-4') ?>
      <?= $in('anggaran', 'Pembebanan anggaran', 'text', 'col-md-4', false, 'mis. Dana Komite') ?>
      <?= $in('mata_anggaran', 'Mata anggaran', 'text', 'col-md-4') ?>
      <?= $in('keterangan', 'Keterangan lain-lain', 'text', 'col-md-4') ?>
      <?= $in('kepala', 'Jabatan penandatangan', 'text', 'col-md-6', true) ?>
      <?= $in('ttd', 'Nama penandatangan', 'text', 'col-md-6', true) ?>
      <?= $in('pangkatttd', 'Pangkat / golongan penandatangan', 'text', 'col-md-6') ?>
      <?= $in('nipttd', 'NIP penandatangan', 'text', 'col-md-6') ?>
      <div class="col-12">
        <label class="form-label">Persinggahan lembar belakang III-IV (opsional)</label>
        <?php foreach (['III' => 2, 'IV' => 3] as $label => $n): ?>
          <details class="border rounded p-2 mb-2">
            <summary>Persinggahan <?= $label ?></summary>
            <div class="row g-2 mt-1">
              <?= $in('s' . $n . 'a', 'Tiba di', 'text', 'col-md-6') ?>
              <?= $in('s' . $n . 'b', 'Tanggal tiba', 'date', 'col-md-6') ?>
              <?= $in('s' . $n . 'c', 'Berangkat dari', 'text', 'col-md-4') ?>
              <?= $in('s' . $n . 'd', 'Ke', 'text', 'col-md-4') ?>
              <?= $in('s' . $n . 'e', 'Tanggal berangkat', 'date', 'col-md-4') ?>
            </div>
          </details>
        <?php endforeach ?>
      </div>

    <?php elseif ($jenis === 'tugas'): ?>
      <?= $ta('dasar', 'Dasar penugasan', 3, 'Contoh: Surat undangan Dinas Pendidikan ... Nomor: ... perihal: ...') ?>
      <div class="col-12">
        <label class="form-label">Kepada (guru yang ditugaskan) *</label>
        <?php if (! $guru): ?><div class="alert alert-warning">Data guru masih kosong. <a href="<?= site_url('data/guru/import') ?>">Impor dari Excel</a> atau <a href="<?= site_url('data/guru/baru') ?>">tambah manual</a> dulu.</div><?php endif ?>
        <input class="form-control mb-2 cari-pilih" type="search" placeholder="Cari nama guru...">
        <div class="border rounded p-2 pilih" style="max-height:240px;overflow:auto">
          <?php $sel = array_map('intval', (array) old('guru_ids', $d['guru_ids'] ?? [])); foreach ($guru as $g): ?>
          <label class="d-block"><input type="checkbox" name="guru_ids[]" value="<?= $g['id'] ?>" <?= in_array((int) $g['id'], $sel, true) ? 'checked' : '' ?>> <?= esc($g['nama']) ?> <small class="text-muted">(<?= esc($g['jabatan']) ?>)</small></label>
          <?php endforeach ?>
        </div>
        <div class="form-text">Urutan di surat mengikuti kolom Urutan pada Data guru.</div>
      </div>
      <?= $in('tugas', 'Tugas (lanjutan kalimat "Melaksanakan tugas sebagai ...")', 'text', 'col-12', true, 'mis. peserta Desiminasi Bimtek Optimalisasi Digitalisasi Pembelajaran', true) ?>
      <?= $in('tgl_mulai', 'Tanggal mulai', 'date', 'col-md-3', true) ?>
      <?= $in('tgl_selesai', 'Tanggal selesai (opsional)', 'date', 'col-md-3') ?>
      <?= $in('tmpk', 'Tempat pelaksanaan', 'text', 'col-md-3', false, '', true) ?>
      <?= $in('acara', 'Acara', 'text', 'col-md-3', false, '', true) ?>
      <?= $ta('poin2', 'Butir tugas berikutnya (satu baris = satu butir)', 2) ?>

    <?php elseif (in_array($jenis, ['tugas_siswa', 'dispensasi'], true)): ?>
      <?php $disp = $jenis === 'dispensasi'; ?>
      <div class="col-12">
        <label class="form-label"><?= $disp ? 'Siswa yang diberi dispensasi' : 'Kepada (siswa yang ditugaskan)' ?> *</label>
        <?php if (! $siswa): ?><div class="alert alert-warning">Data siswa masih kosong. <a href="<?= site_url('data/siswa/import') ?>">Impor dari Excel</a> atau <a href="<?= site_url('data/siswa/baru') ?>">tambah manual</a> dulu.</div><?php endif ?>
        <input class="form-control mb-2 cari-pilih" type="search" placeholder="Cari nama atau kelas...">
        <div class="border rounded p-2 pilih" style="max-height:280px;overflow:auto">
          <?php $sel = array_map('intval', (array) old('siswa_ids', $d['siswa_ids'] ?? [])); foreach ($siswa as $s): ?>
          <label class="d-block"><input type="checkbox" name="siswa_ids[]" value="<?= $s['id'] ?>" <?= in_array((int) $s['id'], $sel, true) ? 'checked' : '' ?>> <?= esc($s['nama']) ?> <small class="text-muted">(<?= esc($s['kelas']) ?>)</small></label>
          <?php endforeach ?>
        </div>
        <div class="mt-1"><button type="button" class="btn btn-sm btn-outline-secondary pilih-semua">Pilih semua yang tampil</button> <button type="button" class="btn btn-sm btn-outline-secondary pilih-kosong">Kosongkan pilihan</button></div>
        <div class="form-text">Data siswa diambil dari Data siswa. Urutan di surat: kelas, lalu nama.</div>
      </div>
      <?= $disp
          ? $in('kegiatan', 'Kegiatan (kalimat: "Adapun kegiatan ... dilaksanakan pada:")', 'text', 'col-12', true, 'mis. pelatihan teatrikal dalam rangka HUT RI ke 81', true)
          : $in('kegiatan', 'Untuk (butir 1, kalimat dilanjutkan otomatis "... yang akan dilaksanakan pada:")', 'text', 'col-12', true, 'mis. Turnamen Futsal antar SMP/sederajat pada kegiatan Futsal Competition 2026', true) ?>
      <?= $in('tgl_mulai', 'Tanggal mulai', 'date', 'col-md-3', true) ?>
      <?= $in('tgl_selesai', 'Tanggal selesai (opsional)', 'date', 'col-md-3') ?>
      <?= $in('waktu', 'Waktu', 'text', 'col-md-3', false, $disp ? 'mis. pukul 08.00 WIB s.d Selesai' : 'mis. 09.00 WIB - selesai', true) ?>
      <?= $in('tmpk', 'Tempat', 'text', 'col-md-3', false, '', true) ?>
      <?php if (! $disp): ?><?= $ta('poin2', 'Butir tugas berikutnya (satu baris = satu butir)', 2) ?><?php endif ?>
    <?php endif ?>
  </div>
  <div class="mt-4 d-flex gap-2"><button class="btn btn-success">Simpan dan lihat surat</button><a class="btn btn-outline-secondary" href="<?= site_url('dokumen') ?>">Batal</a></div>
</form>
<script>
document.querySelectorAll('.cari-pilih').forEach(function (inp) {
  var box = inp.nextElementSibling;
  inp.addEventListener('input', function () {
    var q = inp.value.trim().toLocaleLowerCase();
    box.querySelectorAll('label').forEach(function (l) {
      l.hidden = !l.textContent.toLocaleLowerCase().includes(q);
    });
  });
});
document.querySelectorAll('.pilih-semua').forEach(function (button) {
  button.addEventListener('click', function () {
    button.closest('.col-12').querySelectorAll('.pilih label:not([hidden]) input').forEach(function (input) { input.checked = true; });
  });
});
document.querySelectorAll('.pilih-kosong').forEach(function (button) {
  button.addEventListener('click', function () {
    button.closest('.col-12').querySelectorAll('.pilih input').forEach(function (input) { input.checked = false; });
  });
});
(function () {
  var hid = document.getElementById('sppd-guru');
  if (!hid) return;
  var cari = document.getElementById('pegCari'), daftar = document.getElementById('pegList'), hapus = document.getElementById('pegHapus'),
      nip = document.getElementById('sppd-nip'), jab = document.getElementById('sppd-jab'), pkt = document.getElementById('sppd-pangkat'),
      data = JSON.parse(document.getElementById('pegData').textContent || '[]'),
      lama = {nip: nip.value, jab: jab.value, pkt: pkt.value, nama: hid.dataset.legacyNama || ''},
      aktif = -1, tampil = [];
  function cek(id) { for (var i = 0; i < data.length; i++) if (String(data[i].id) === String(id)) return data[i]; return null; }
  function label() { var g = cek(hid.value); return g ? g.nama : lama.nama; }
  function segarkan() {
    var g = cek(hid.value);
    nip.value = g ? g.nip : lama.nip; jab.value = g ? g.jab : lama.jab; pkt.value = g ? g.pangkat : lama.pkt;
    cari.value = label(); hapus.hidden = !g;
  }
  function tutup() { daftar.hidden = true; cari.setAttribute('aria-expanded', 'false'); aktif = -1; }
  function buka() {
    var k = cari.dataset.mode === 'ketik' ? cari.value.trim().toLowerCase() : '';
    tampil = data.filter(function (g) { return !k || (g.nama + ' ' + g.nip + ' ' + g.jab).toLowerCase().indexOf(k) !== -1; }).slice(0, 60);
    daftar.innerHTML = '';
    if (!tampil.length) {
      var kosong = document.createElement('div'); kosong.className = 'peg-kosong'; kosong.textContent = 'Pegawai tidak ditemukan.'; daftar.appendChild(kosong);
    }
    tampil.forEach(function (g, i) {
      var b = document.createElement('button'); b.type = 'button'; b.className = 'peg-item' + (String(g.id) === hid.value ? ' dipilih' : ''); b.setAttribute('role', 'option');
      var n = document.createElement('span'); n.className = 'peg-nama'; n.textContent = g.nama;
      var s = document.createElement('small'); s.textContent = [g.nip ? 'NIP ' + g.nip : '', g.jab].filter(Boolean).join(' \u2022 ');
      b.appendChild(n); b.appendChild(s);
      b.addEventListener('mousedown', function (e) { e.preventDefault(); pilih(g); });
      daftar.appendChild(b);
    });
    daftar.hidden = false; cari.setAttribute('aria-expanded', 'true'); aktif = -1;
  }
  function sorot(i) {
    var item = daftar.querySelectorAll('.peg-item');
    if (!item.length) return;
    aktif = (i + item.length) % item.length;
    item.forEach(function (el, n) { el.classList.toggle('aktif', n === aktif); });
    item[aktif].scrollIntoView({block: 'nearest'});
  }
  function pilih(g) {
    hid.value = g.id; cari.classList.remove('is-invalid'); document.getElementById('pegError').style.display = '';
    segarkan(); tutup();
  }
  cari.addEventListener('focus', function () { cari.dataset.mode = ''; cari.select(); buka(); });
  cari.addEventListener('click', function () { if (daftar.hidden) { cari.dataset.mode = ''; buka(); } });
  cari.addEventListener('input', function () { cari.dataset.mode = 'ketik'; buka(); });
  cari.addEventListener('blur', function () { tutup(); cari.value = label(); });
  cari.addEventListener('keydown', function (e) {
    if (e.key === 'ArrowDown') { e.preventDefault(); if (daftar.hidden) buka(); sorot(aktif + 1); }
    else if (e.key === 'ArrowUp') { e.preventDefault(); sorot(aktif - 1); }
    else if (e.key === 'Enter') { if (!daftar.hidden) { e.preventDefault(); if (aktif < 0 && tampil.length === 1) aktif = 0; if (aktif >= 0) pilih(tampil[aktif]); } }
    else if (e.key === 'Escape') { tutup(); cari.value = label(); }
  });
  hapus.addEventListener('mousedown', function (e) {
    e.preventDefault(); hid.value = ''; segarkan(); cari.value = ''; cari.dataset.mode = ''; cari.focus();
  });
  daftar.addEventListener('mousedown', function (e) { e.preventDefault(); });
  var form = hid.closest('form');
  if (form) form.addEventListener('submit', function (e) {
    if (!hid.value && !hid.dataset.legacy) {
      e.preventDefault(); cari.classList.add('is-invalid'); document.getElementById('pegError').style.display = 'block'; cari.focus();
    }
  });
  segarkan();
})();
(function () {
  var kotak = document.getElementById('pegHidden');
  if (!kotak) return;
  var cari = document.getElementById('pegCari'), daftar = document.getElementById('pegList'), chips = document.getElementById('pegChips'),
      info = document.getElementById('pegInfo'), galat = document.getElementById('pegError'),
      data = JSON.parse(document.getElementById('pegData').textContent || '[]'),
      awal = JSON.parse(document.getElementById('pegAwal').textContent || '[]'),
      pilih = [], aktif = -1, tampil = [], ketik = false;
  function cek(id) { for (var i = 0; i < data.length; i++) if (data[i].id === id) return data[i]; return null; }
  awal.forEach(function (id) { if (cek(id) && pilih.indexOf(id) < 0) pilih.push(id); });
  function gambar() {
    chips.innerHTML = ''; kotak.innerHTML = '';
    pilih.forEach(function (id, i) {
      var g = cek(id); if (!g) return;
      var c = document.createElement('span'); c.className = 'peg-chip';
      c.appendChild(document.createTextNode((i + 1) + '. ' + g.nama));
      var x = document.createElement('button'); x.type = 'button'; x.setAttribute('aria-label', 'Hapus ' + g.nama); x.textContent = '\u00d7';
      x.addEventListener('click', function () { pilih.splice(pilih.indexOf(id), 1); gambar(); });
      c.appendChild(x); chips.appendChild(c);
      var h = document.createElement('input'); h.type = 'hidden'; h.name = 'guru_ids[]'; h.value = id; kotak.appendChild(h);
    });
    info.textContent = pilih.length > 1
      ? pilih.length + ' pegawai dipilih: akan dibuat ' + pilih.length + ' SPPD dengan nomor berurutan sesuai urutan di atas.'
      : 'Pilih satu atau beberapa pegawai. Setiap pegawai mendapat satu SPPD, nomornya berurutan.';
    if (pilih.length) { cari.classList.remove('is-invalid'); galat.style.display = ''; }
  }
  function tutup() { daftar.hidden = true; cari.setAttribute('aria-expanded', 'false'); aktif = -1; }
  function buka() {
    var k = ketik ? cari.value.trim().toLowerCase() : '';
    tampil = data.filter(function (g) { return !k || (g.nama + ' ' + g.nip + ' ' + g.jab).toLowerCase().indexOf(k) !== -1; }).slice(0, 60);
    daftar.innerHTML = '';
    if (!tampil.length) { var e = document.createElement('div'); e.className = 'peg-kosong'; e.textContent = 'Pegawai tidak ditemukan.'; daftar.appendChild(e); }
    tampil.forEach(function (g) {
      var b = document.createElement('button'); b.type = 'button'; b.className = 'peg-item' + (pilih.indexOf(g.id) >= 0 ? ' dipilih' : ''); b.setAttribute('role', 'option');
      var n = document.createElement('span'); n.className = 'peg-nama'; n.textContent = g.nama;
      var s = document.createElement('small'); s.textContent = [g.nip ? 'NIP ' + g.nip : '', g.jab].filter(Boolean).join(' \u2022 ');
      b.appendChild(n); b.appendChild(s);
      b.addEventListener('mousedown', function (ev) { ev.preventDefault(); ganti(g); });
      daftar.appendChild(b);
    });
    daftar.hidden = false; cari.setAttribute('aria-expanded', 'true'); aktif = -1;
  }
  function ganti(g) {
    var i = pilih.indexOf(g.id);
    if (i >= 0) pilih.splice(i, 1); else pilih.push(g.id);
    gambar(); cari.value = ''; ketik = false; buka();
  }
  function sorot(i) {
    var item = daftar.querySelectorAll('.peg-item'); if (!item.length) return;
    aktif = (i + item.length) % item.length;
    item.forEach(function (el, n) { el.classList.toggle('aktif', n === aktif); });
    item[aktif].scrollIntoView({ block: 'nearest' });
  }
  cari.addEventListener('focus', function () { ketik = false; buka(); });
  cari.addEventListener('click', function () { if (daftar.hidden) { ketik = false; buka(); } });
  cari.addEventListener('input', function () { ketik = true; buka(); });
  cari.addEventListener('blur', function () { tutup(); cari.value = ''; });
  daftar.addEventListener('mousedown', function (e) { e.preventDefault(); });
  cari.addEventListener('keydown', function (e) {
    if (e.key === 'ArrowDown') { e.preventDefault(); if (daftar.hidden) buka(); sorot(aktif + 1); }
    else if (e.key === 'ArrowUp') { e.preventDefault(); sorot(aktif - 1); }
    else if (e.key === 'Enter') { if (!daftar.hidden) { e.preventDefault(); if (aktif < 0 && tampil.length === 1) aktif = 0; if (aktif >= 0) ganti(tampil[aktif]); } }
    else if (e.key === 'Escape') { tutup(); cari.value = ''; }
    else if (e.key === 'Backspace' && !cari.value && pilih.length) { pilih.pop(); gambar(); }
  });
  var form = kotak.closest('form');
  if (form) form.addEventListener('submit', function (e) {
    if (!pilih.length) { e.preventDefault(); cari.classList.add('is-invalid'); galat.style.display = 'block'; cari.focus(); }
  });
  gambar();
})();
/* ---- Bilah format teks: tebal (B), miring (I), garis bawah (U). Tanda: **tebal**  *miring*  __garis bawah__ ---- */
(function () {
  var kolomFm = document.querySelectorAll('[data-fm]');
  if (!kolomFm.length) return;

  var TANDA = { b: '**', i: '*', u: '__' };
  var JUDUL = { b: 'Tebal (Ctrl+B)', i: 'Miring (Ctrl+I)', u: 'Garis bawah (Ctrl+U)' };

  function spasi(c) { return /\s/.test(c); }

  /* Banyaknya karakter c berurutan, mundur dari sebelum posisi p (arah < 0) atau maju mulai dari p. */
  function deret(v, p, c, arah) {
    var n = 0;
    if (arah < 0) { while (p - 1 - n >= 0 && v.charAt(p - 1 - n) === c) n++; }
    else { while (p + n < v.length && v.charAt(p + n) === c) n++; }
    return n;
  }

  /* Apakah deret tanda g (mis. '**' atau '__') mengandung jenis k? Hasil: banyak karakter yang harus dibuang (0 = tidak). */
  function cocok(g, k) {
    var n = g.length;
    if (k === 'u') return (g.charAt(0) === '_' && n === 2) ? 2 : 0;
    if (g.charAt(0) !== '*') return 0;
    if (k === 'b') return n >= 2 ? 2 : 0;
    return (n === 1 || n === 3) ? 1 : 0;
  }

  /*
   * Apakah v[s..e) sudah diapit tanda jenis k di luarnya? Lapisan tanda jenis lain (mis. ***__teks__***)
   * dilewati. Bila ya, hasilnya penggantian yang melepas tanda itu di kedua sisi; bila tidak, null.
   */
  function luar(v, s, e, k) {
    var p = s, q = e, kiri, kanan, i, j, d, dari;
    while (p > 0 && /[*_]/.test(v.charAt(p - 1))) p--;
    while (q < v.length && /[*_]/.test(v.charAt(q))) q++;
    kiri = (v.slice(p, s).match(/\*+|_+/g) || []).reverse();      // dari yang terdalam
    kanan = v.slice(e, q).match(/\*+|_+/g) || [];
    for (i = 0; i < kiri.length && !cocok(kiri[i], k); i++);
    for (j = 0; j < kanan.length && !cocok(kanan[j], k); j++);
    if (i === kiri.length || j === kanan.length) return null;
    kiri[i] = kiri[i].slice(cocok(kiri[i], k));
    kanan[j] = kanan[j].slice(cocok(kanan[j], k));
    kiri = kiri.reverse().join('');
    kanan = kanan.join('');
    dari = p;
    return { dari: p, sampai: q, teks: kiri + v.slice(s, e) + kanan, s: dari + kiri.length, e: dari + kiri.length + (e - s) };
  }

  /* Apakah teks x memuat tanda jenis k? (deret * 1 atau 3 = miring, 2 atau 3 = tebal) */
  function punyaTanda(x, k) {
    if (k === 'u') return x.indexOf('__') >= 0;
    var r = x.match(/\*+/g) || [], a, n;
    for (a = 0; a < r.length; a++) {
      n = r[a].length;
      if (k === 'b' ? (n === 2 || n === 3) : (n === 1 || n === 3)) return true;
    }
    return false;
  }

  /* Buang semua tanda jenis k dari x (supaya tidak dobel saat dibungkus ulang). */
  function buang(x, k) {
    if (k === 'u') return x.replace(/__/g, '');
    return x.replace(/\*+/g, function (r) {
      var n = r.length;
      if (k === 'b') return n === 2 ? '' : (n === 3 ? '*' : r);
      return n === 1 ? '' : (n === 3 ? '**' : r);
    });
  }

  /* Teks c yang sudah memuat tanda pembungkusnya sendiri: kembalikan isinya tanpa tanda itu, atau null. */
  function lepasDalam(c, k) {
    var m;
    if (k === 'u') {
      m = /^__([^_\s](?:[\s\S]*[^_\s])?)__$/.exec(c);
      return (m && !punyaTanda(m[1], 'u')) ? m[1] : null;
    }
    m = /^(\*{1,3})([^*\s](?:[\s\S]*[^*\s])?)(\*{1,3})$/.exec(c);
    if (!m) return null;
    var n = Math.min(m[1].length, m[3].length), w = (k === 'b') ? 2 : 1;
    if (k === 'b' ? n < 2 : !(n === 1 || n === 3)) return null;
    if (punyaTanda(m[2], k)) return null;
    return m[1].slice(w) + m[2] + m[3].slice(w);
  }

  /*
   * Memasang atau melepas format k pada pilihan v[s..e).
   * Hasil: { dari, sampai, teks, s, e } = ganti v[dari..sampai) dengan teks, lalu pilih s..e. Atau null bila tidak ada sasaran.
   */
  function ubah(v, s, e, k) {
    var M = TANDA[k], t, baris;
    while (s < e && spasi(v.charAt(s))) s++;
    while (e > s && spasi(v.charAt(e - 1))) e--;
    if (s === e) {                                   // tanpa pilihan: pakai kata di posisi kursor
      while (s > 0 && !spasi(v.charAt(s - 1))) s--;
      while (e < v.length && !spasi(v.charAt(e))) e++;
      while (e > s && /[.,;:!?)\]}"\u201d'\u2019]/.test(v.charAt(e - 1))) e--;
      while (s < e && /[(\[{"\u201c'\u2018]/.test(v.charAt(s))) s++;
      if (s === e) return null;
    }
    t = v.slice(s, e);
    baris = t.split('\n');

    if (baris.length === 1) {
      var m = luar(v, s, e, k);                      // tanda sudah ada di luar pilihan: lepas
      if (m) return m;
    }

    var ada = false, belum = false;
    baris.forEach(function (b) {
      var c = b.trim();
      if (c === '') return;
      ada = true;
      if (lepasDalam(c, k) === null) belum = true;
    });
    var lepas = ada && !belum;
    if (!ada) return null;

    var baru = baris.map(function (b) {
      var c = b.trim();
      if (c === '') return b;
      var i0 = b.indexOf(c), awal = b.slice(0, i0), akhir = b.slice(i0 + c.length);
      return awal + (lepas ? lepasDalam(c, k) : M + buang(c, k) + M) + akhir;
    }).join('\n');

    if (baris.length === 1 && !lepas) {              // satu baris: pilih isinya saja (di dalam tanda)
      return { dari: s, sampai: e, teks: baru, s: s + M.length, e: s + baru.length - M.length };
    }
    return { dari: s, sampai: e, teks: baru, s: s, e: s + baru.length };
  }

  /* Ganti lewat execCommand agar tetap bisa dibatalkan dengan Ctrl+Z; cadangan: setRangeText. */
  function tulis(f, r) {
    f.focus();
    f.setSelectionRange(r.dari, r.sampai);
    var ok = false;
    try { ok = document.execCommand('insertText', false, r.teks); } catch (x) { ok = false; }
    if (!ok || f.value.slice(r.dari, r.dari + r.teks.length) !== r.teks) f.setRangeText(r.teks, r.dari, r.sampai, 'end');
    f.setSelectionRange(r.s, r.e);
  }

  function terapkan(f, k) {
    var r = ubah(f.value, f.selectionStart, f.selectionEnd, k);
    if (r) tulis(f, r);
  }

  kolomFm.forEach(function (f) {
    var kolom = f.closest('[class*="col-"]'), label = kolom && kolom.querySelector('label');
    if (!label) return;
    var kepala = document.createElement('div'), bar = document.createElement('div');
    kepala.className = 'fm-kepala';
    bar.className = 'fm-bar btn-group btn-group-sm';
    bar.setAttribute('role', 'group');
    bar.setAttribute('aria-label', 'Format teks');
    ['b', 'i', 'u'].forEach(function (k) {
      var tb = document.createElement('button'), huruf = document.createElement(k);
      tb.type = 'button';
      tb.className = 'btn btn-outline-secondary fm-btn';
      tb.title = JUDUL[k];
      tb.setAttribute('aria-label', JUDUL[k]);
      huruf.textContent = k.toUpperCase();
      tb.appendChild(huruf);
      tb.addEventListener('mousedown', function (e) { e.preventDefault(); });   // jaga fokus dan pilihan di kolom
      tb.addEventListener('click', function () { terapkan(f, k); });
      bar.appendChild(tb);
    });
    label.parentNode.insertBefore(kepala, label);
    kepala.appendChild(label);
    kepala.appendChild(bar);
    f.addEventListener('keydown', function (e) {
      if (!(e.ctrlKey || e.metaKey) || e.altKey || e.shiftKey || !e.key) return;
      var k = e.key.toLowerCase();
      if (k === 'b' || k === 'i' || k === 'u') { e.preventDefault(); terapkan(f, k); }
    });
  });
})();
<?php if (! $ubah): ?>
(function () {
  var tgl = document.querySelector('[name=tgl_surat]'),
      kode = document.querySelector('[name=kode]'),
      nomor = document.querySelector('[name=nomor_manual]'),
      hint = document.getElementById('nomor-hint'),
      petunjuk = hint.textContent;
  function ambilNomor() {
    var url = '<?= site_url('dokumen/nomor') ?>?tgl=' + encodeURIComponent(tgl.value) + '&kode=' + encodeURIComponent(kode ? kode.value : '');
    fetch(url)
      .then(function (response) {
        if (!response.ok) throw new Error('Gagal memuat saran nomor surat.');
        return response.json();
      })
      .then(function (data) {
        nomor.placeholder = 'mis. ' + data.nomor_urut;
        hint.textContent = petunjuk + ' Nomor otomatis saat ini: ' + data.nomor_surat + '.';
      })
      .catch(function () {
        nomor.placeholder = 'Nomor otomatis tidak dapat dimuat';
        hint.textContent = 'Saran nomor otomatis gagal dimuat. Nomor tetap akan ditentukan saat surat disimpan.';
      });
  }
  tgl.addEventListener('change', ambilNomor);
  if (kode) kode.addEventListener('input', ambilNomor);
  ambilNomor();
})();
<?php endif ?>
</script>
<?= $this->endSection() ?>
