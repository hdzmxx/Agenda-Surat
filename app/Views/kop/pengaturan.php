<?= $this->extend('layout') ?> <!-- Menggunakan master layout utama aplikasi -->
<?= $this->section('content') ?> <!-- Memulai blok konten utama halaman -->

<h4 class="mb-3">Pengaturan kop surat</h4>

<!-- Kontainer utama dengan batasan lebar maksimal agar tampilan proporsional -->
<div class="card" style="max-width:720px">
  <div class="card-body">
    <p class="text-muted">Perubahan diterapkan pada kop surat di cetak agenda dan dokumen surat. Logo opsional; jika tidak memilih file, logo yang digunakan saat ini tidak berubah.</p>
    
    <!-- Form pengiriman data. enctype diperlukan karena form memiliki inputan file/logo -->
    <form method="post" action="<?= site_url('kop/pengaturan') ?>" enctype="multipart/form-data">
      <?= csrf_field() ?> <!-- Fitur keamanan untuk mencegah serangan Cross-Site Request Forgery (CSRF) -->
      
      <!-- Input 1: Baris Pemerintah (Misal: PEMERINTAH PROVINSI...) -->
      <div class="mb-3">
        <label class="form-label" for="pemerintah">Baris pemerintah</label>
        <input class="form-control" id="pemerintah" name="pemerintah" maxlength="200" value="<?= esc($settings['pemerintah']) ?>">
      </div>
      
      <!-- Input 2: Baris Dinas/Lembaga (Misal: DINAS PENDIDIKAN...) -->
      <div class="mb-3">
        <label class="form-label" for="dinas">Baris dinas</label>
        <input class="form-control" id="dinas" name="dinas" maxlength="200" value="<?= esc($settings['dinas']) ?>">
      </div>
      
      <!-- Input 3: Nama Instansi/Sekolah (Misal: SMA NEGERI 1...) -->
      <div class="mb-3">
        <label class="form-label" for="instansi">Nama instansi</label>
        <input class="form-control" id="instansi" name="instansi" maxlength="200" value="<?= esc($settings['instansi']) ?>">
      </div>
      
      <!-- Input 4: Textarea Alamat. Array alamat digabung kembali menjadi baris baru (\n) -->
      <div class="mb-3">
        <label class="form-label" for="alamat">Alamat dan informasi kontak</label>
        <textarea class="form-control" id="alamat" name="alamat" rows="4" maxlength="2000" aria-describedby="alamatHelp"><?= esc(implode("\n", $settings['alamat'])) ?></textarea>
        <div class="form-text" id="alamatHelp">Pisahkan setiap baris alamat atau informasi kontak dengan baris baru.</div>
      </div>
      
      <!-- Penandatangan surat: dipakai di semua surat, SPPD, daftar hadir, dan cetak agenda -->
      <hr>
      <h6 class="mb-1">Penandatangan surat</h6>
      <p class="text-muted small">Nama, NIP, pangkat, dan jabatan di bagian tanda tangan semua surat. Berlaku untuk surat yang dibuat atau dicetak sesudah disimpan; surat SPPD lama mempertahankan isian penandatangannya sendiri.</p>
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label" for="ttd_jabatan">Jabatan</label>
          <input class="form-control" id="ttd_jabatan" name="ttd_jabatan" maxlength="100" value="<?= esc($settings['ttd_jabatan']) ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label" for="ttd_nama">Nama (beserta gelar)</label>
          <input class="form-control" id="ttd_nama" name="ttd_nama" maxlength="150" value="<?= esc($settings['ttd_nama']) ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label" for="ttd_nip">NIP</label>
          <input class="form-control" id="ttd_nip" name="ttd_nip" maxlength="60" value="<?= esc($settings['ttd_nip']) ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label" for="ttd_pangkat">Pangkat / golongan</label>
          <input class="form-control" id="ttd_pangkat" name="ttd_pangkat" maxlength="100" value="<?= esc($settings['ttd_pangkat']) ?>">
        </div>
      </div>
      <hr>

      <!-- Preview: Menampilkan gambar logo yang saat ini aktif di sistem -->
      <div class="mb-3">
        <label class="form-label">Logo saat ini</label>
        <div class="border rounded bg-white p-3 text-center">
          <!-- Menggunakan timestamp berkas (filemtime) sebagai cache buster agar logo langsung berubah saat di-update -->
          <img src="<?= base_url($settings['logo']) ?>?v=<?= is_file(FCPATH . $settings['logo']) ? filemtime(FCPATH . $settings['logo']) : time() ?>" alt="Logo kop surat saat ini" style="max-width:100%;max-height:180px">
        </div>
      </div>
      
      <!-- Input 5: Unggah berkas gambar logo baru -->
      <div class="mb-3">
        <label class="form-label" for="logo">Ganti logo (PNG atau JPG, maksimal 2 MB)</label>
        <input class="form-control" type="file" id="logo" name="logo" accept="image/png,image/jpeg">
      </div>
      
      <!-- Tombol aksi untuk mengirimkan/menyimpan data formulir -->
      <button class="btn btn-success">Simpan pengaturan kop</button>
    </form>
  </div>
</div>

<?= $this->endSection() ?> <!-- Menutup blok konten utama halaman -->
