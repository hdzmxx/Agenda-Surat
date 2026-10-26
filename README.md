# Agenda Surat

Aplikasi web untuk mengelola surat sekolah: agenda surat masuk dan keluar, pembuatan surat siap cetak,
daftar hadir, dan cadangan database. Dibangun dengan **CodeIgniter 4** dan **MySQL/MariaDB**.

## Fitur

- **Agenda surat masuk dan keluar**: nomor agenda otomatis per tahun, unggah berkas surat (PDF/JPG/PNG, maks. 5 MB),
  pencarian dan filter, cetak agenda, rekap ke Excel.
- **Disposisi** surat masuk kepada beberapa guru, lengkap dengan catatan.
- **Buat surat** siap cetak dan unduh Word (.docx): undangan, permohonan narasumber, surat keterangan siswa,
  surat keterangan prestasi, surat tugas guru, surat tugas siswa, dispensasi, dan SPPD. Nomor surat otomatis
  dan tercatat ke agenda surat keluar.
- **Format teks di dalam surat**: blok kata, lalu klik tombol **B** (tebal), **I** (miring), atau **U** (garis bawah)
  di atas kolom isian, atau tekan `Ctrl+B`, `Ctrl+I`, `Ctrl+U`.
- **Daftar hadir** guru dan siswa, dengan pilihan **blok per kelas** untuk siswa.
- **Data guru dan siswa** (tambah, ubah, impor dari Excel).
- **Pengaturan kop surat** (pemerintah, dinas, instansi, alamat, logo) dan **penandatangan surat**.
- **Pengguna dan akun**: peran admin dan staf, akun guru otomatis berbasis NIP, foto profil, ganti kata sandi.
- **Cadangan database**: cadangan manual dan otomatis, unduh, pulihkan, dan unggah berkas cadangan.
- **Pengaturan tampilan**: nama aplikasi, logo, mode terang/gelap, warna utama.

## Kebutuhan

- PHP **8.2 atau lebih baru** dengan ekstensi `intl`, `mbstring`, `mysqlnd`, `fileinfo`, `gd`, `zip`, `xml`
  (semuanya sudah ada di XAMPP; cukup pastikan aktif di `php.ini`)
- MySQL atau MariaDB
- [Composer](https://getcomposer.org/)
- Git (untuk mengambil kode)

## Pemasangan

```bash
# 1. Ambil kode dan pasang dependensi
git clone https://github.com/USERNAME/agenda-surat.git
cd agenda-surat
composer install

# 2. Buat berkas pengaturan
cp .env.example .env          # Windows: copy .env.example .env
```

3. Buka `.env`, lalu isi minimal `app.baseURL`, pengaturan `database.default.*`, dan identitas sekolah
   (lihat bagian [Pengaturan `.env`](#pengaturan-env)).

4. Buat database dan tabel dasar. Impor `database.sql` lewat phpMyAdmin (database `xagenda` dibuat otomatis),
   atau dari terminal:

   ```bash
   mysql -u root -p < database.sql
   ```

5. Jalankan migrasi untuk melengkapi tabel lainnya dan akun guru:

   ```bash
   php spark migrate
   ```

6. Jalankan aplikasi, lalu buka <http://localhost:8080>:

   ```bash
   php spark serve
   ```

   Dengan XAMPP/Apache, arahkan `DocumentRoot` ke folder `public`.

## Akun awal

| Akun | Username | Kata sandi awal | Keterangan |
| --- | --- | --- | --- |
| Administrator | `admin` | `password` | Dibuat oleh `database.sql` |
| Guru | NIP guru | NIP guru | Dibuat otomatis dari data guru yang NIP-nya terisi |

> **Penting: ganti kata sandi sebelum aplikasi dipakai atau dibuka ke jaringan/internet.**
> Kata sandi `admin` tertulis di repository ini sehingga diketahui siapa pun. Masuk sebagai admin, lalu
> ubah lewat menu **Akun**. Kata sandi awal akun guru adalah NIP, yang bukan rahasia; minta setiap guru
> menggantinya setelah masuk pertama kali.

## Logo dan ikon

Logo dan ikon sekolah **tidak disertakan** di repository. Setelah aplikasi berjalan, unggah sendiri:

- **Logo kop surat**: menu *Pengaturan kop surat*.
- **Logo dan ikon (favicon) aplikasi**: menu *Pengaturan website*.

Selama belum diunggah, kop surat tetap tampil tanpa gambar.

## Pengaturan `.env`

Berkas `.env` berisi kata sandi database dan **tidak ikut di-commit** (sudah ada di `.gitignore`).
Contoh lengkapnya ada di [`.env.example`](.env.example).

| Kunci | Fungsi |
| --- | --- |
| `CI_ENVIRONMENT` | `development` saat mencoba, **`production`** saat dipakai sungguhan |
| `app.baseURL` | Alamat aplikasi, diakhiri `/` |
| `database.default.*` | Koneksi database (host, nama database, pengguna, kata sandi, port) |
| `NAMA_SEKOLAH`, `INSTANSI` | Nama sekolah pada surat |
| `KOP_PEMERINTAH`, `KOP_DINAS`, `ALAMAT`, `LOGO` (cadangan bila logo belum diunggah) | Nilai awal kop surat (garis tegak `\|` pada `ALAMAT` = pindah baris) |
| `TTD_JABATAN`, `TTD_NAMA`, `TTD_NIP`, `TTD_PANGKAT` | Nilai awal penandatangan surat |
| `NOMOR_KODE`, `NOMOR_FORMAT`, `NOMOR_FORMAT_DOK` | Kode dan format penomoran surat |

Kop surat dan penandatangan juga bisa diubah admin dari menu **Pengaturan kop surat** tanpa menyunting `.env`;
nilai yang disimpan di menu itu didahulukan.

## Penggunaan singkat

- **Membuat surat**: menu *Buat surat*, pilih jenis surat, isi, simpan, lalu *Cetak* atau *Unduh Word*.
- **Daftar hadir siswa**: di bagian daftar siswa, centang kotak kelas untuk memilih satu kelas sekaligus.
- **Impor data guru/siswa**: unduh contoh berkas Excel dari halaman Data Guru atau Data Siswa, isi, lalu unggah.
- **Cadangan**: menu *Cadangan* (khusus admin). Cadangan otomatis dibuat saat aplikasi dibuka dan jadwalnya jatuh tempo.
  Untuk waktu yang tepat, jadwalkan `php spark db:backup` di Task Scheduler (Windows) atau cron.
  Cadangan hanya berisi database; folder `writable/uploads` perlu disalin terpisah.

## Daftar periksa sebelum dipakai sungguhan

- [ ] Ganti kata sandi `admin` dan minta guru mengganti kata sandi awalnya
- [ ] `CI_ENVIRONMENT = production` di `.env`
- [ ] `app.baseURL` sesuai alamat sebenarnya; gunakan HTTPS bila diakses lewat internet
- [ ] `DocumentRoot` mengarah ke folder `public`, bukan ke akar proyek
- [ ] Folder `writable/` dapat ditulis oleh PHP
- [ ] Kata sandi database bukan kosong dan pengguna database bukan `root`
- [ ] Cadangan database terjadwal dan salinannya disimpan di luar server

## Memperbarui

```bash
git pull
composer install
php spark migrate
```

## Pemecahan masalah

- **"The action you requested is not allowed."** Token keamanan (CSRF) tidak terkirim atau kedaluwarsa.
  Muat ulang halaman; bila berulang, keluar lalu masuk kembali. Pada form buatan sendiri, tambahkan `<?= csrf_field() ?>`.
- **Unggah berkas besar gagal.** Naikkan `upload_max_filesize` dan `post_max_size` di `php.ini` (minimal `6M`).
- **Error "vendor/autoload.php tidak ditemukan".** Jalankan `composer install`.
- **Tabel `siswa`/`dokumen` tidak ditemukan.** Jalankan `php spark migrate`.
- **Tampilan tanpa gaya saat offline.** Bootstrap dimuat dari CDN; untuk dipakai tanpa internet, unduh Bootstrap dan
  ubah tautannya di `app/Views/layout.php`.

## Struktur

| Folder | Isi |
| --- | --- |
| `app/Controllers` | Auth, Dashboard, Surat, Disposisi, Dokumen, DaftarHadir, Master, Users, Kop, Cadangan, dan lainnya |
| `app/Models` | Model data (surat, guru, siswa, pengguna, dan seterusnya) |
| `app/Views` | Tampilan halaman dan templat cetak surat (`app/Views/dokumen/tpl`) |
| `app/Libraries`, `app/Helpers` | Jenis surat, cadangan database, format teks, pengaturan kop |
| `app/Database/Migrations` | Migrasi database |
| `database.sql` | Tabel dasar dan akun `admin` awal |
| `writable/` | Berkas unggahan, log, sesi, dan cadangan (isinya tidak masuk Git) |

## Lisensi

[MIT](LICENSE)
