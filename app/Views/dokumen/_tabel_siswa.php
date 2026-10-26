<?php
/**
 * Tabel daftar siswa: No., Nama Siswa, JK, NISN, Kelas.
 * $siswa: baris master siswa; $besar: judul kolom huruf besar (gaya surat dispensasi).
 * Kolom JK dan NISN bisa disembunyikan lewat bilah cetak (kelas .col-jk dan .col-nisn).
 */
$besar = $besar ?? false;
$judul = $besar ? ['NO', 'NAMA', 'JK', 'NISN', 'KELAS'] : ['No.', 'Nama Siswa', 'JK', 'NISN', 'Kelas'];
?>
<table class="tbl" style="width: 80%; max-width: 100%; margin: 0 auto 4mm; line-height: 1.2; border-collapse: collapse;">
  <thead>
    <tr>
      <!-- width: 1% & nowrap memaksa kolom menyusut pas sesuai teks/angka di dalamnya -->
      <th style="width: 1%; white-space: nowrap; padding: 1px 6px;"><?= $judul[0] ?></th>
      <!-- Kolom nama dikosongkan width-nya agar otomatis melebar mengambil sisa ruang -->
      <th style="padding: 6px 6px; text-align: center;"><?= $judul[1] ?></th>
      <th class="col-jk" style="width: 1%; white-space: nowrap; padding: 1px 10px;"><?= $judul[2] ?></th>
      <th class="col-nisn" style="width: 1%; white-space: nowrap; padding: 1px 30px;"><?= $judul[3] ?></th>
      <th style="width: 1%; white-space: nowrap; padding: 1px 10px;"><?= $judul[4] ?></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($siswa as $i => $x): ?>
      <?php
        $jk_mentah  = jk_label($x['jk'] ?? '');
        $jk_singkat = $jk_mentah === 'Laki-laki' ? 'L' : ($jk_mentah === 'Perempuan' ? 'P' : '');
      ?>
      <tr>
        <td class="c" style="white-space: nowrap; padding: 1px 6px;"><?= $i + 1 ?></td>
        <td style="padding: 1px 6px;"><span class="nm-siswa"><?= esc($x['nama']) ?></span></td>
        <td class="c col-jk" style="white-space: nowrap; padding: 1px 6px;"><?= esc($jk_singkat) ?></td>
        <td class="c col-nisn" style="white-space: nowrap; padding: 1px 6px;"><?= esc($x['nisn'] ?? '') ?></td>
        <td class="c" style="white-space: nowrap; padding: 1px 6px;"><?= esc($x['kelas']) ?></td>
      </tr>
    <?php endforeach ?>
  </tbody>
</table>
