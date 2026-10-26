<?php
$siswa   = $d['siswa'] ?? [];
$hari    = rentang_hari($d['tgl_mulai'] ?: null, $d['tgl_selesai'] ?: null);
$tgl     = rentang_tgl($d['tgl_mulai'] ?: null, $d['tgl_selesai'] ?: null);
$hariTgl = trim($hari . ($hari !== '' && $tgl !== '' ? ', ' : '') . $tgl);
$poin2   = array_values(array_filter(array_map('trim', preg_split('/\R/', (string) ($d['poin2'] ?? '')))));

// Memanggil helper kop untuk digunakan di halaman kedua
helper('kop'); 
$kop = kop_settings();
?>

<!-- ================= HALAMAN 1: SURAT TUGAS ================= -->
<div class="judul" style="text-align: center; font-weight: bold; margin-bottom: 0; line-height: 1.2;"><u>SURAT TUGAS</u></div>
<div class="nomor" style="text-align: center; margin-top: 0; margin-bottom: 20px; line-height: 1.2;">NOMOR : <?= esc($nomor) ?></div>

<p>Yang bertanda tangan di bawah ini Kepala Sekolah <?= esc($sekolah) ?></p>

<div class="judul2">MEMERINTAHKAN</div>
<p>Kepada daftar nama berikut:</p>
<?= view('dokumen/_tabel_siswa', ['siswa' => $siswa]) ?>

<div class="tg"><div>Untuk</div><div>:</div><div>
  
  <div class="org" style="margin-bottom: 2mm;"><div>1.</div><div>
    <div class="jus"><?= fm_teks(rtrim((string) $d['kegiatan'], ' .,;:')) ?> yang akan dilaksanakan pada:</div>
    <div class="kv2" style="grid-template-columns:30mm 4mm 1fr; margin-top: 1.5mm;">
      <div>Hari/Tanggal</div><div>:</div><div><?= esc($hariTgl) ?></div>
      <?php if (! empty($d['waktu'])): ?><div>Waktu</div><div>:</div><div><?= fm_teks($d['waktu']) ?></div><?php endif ?>
      <?php if (! empty($d['tmpk'])): ?><div>Tempat</div><div>:</div><div><?= fm_teks($d['tmpk']) ?></div><?php endif ?>
    </div></div></div>
  
  <?php foreach ($poin2 as $i => $p): ?>
  <div class="org"><div><?= $i + 2 ?>.</div><div class="jus"><?= fm_teks($p) ?></div></div>
  <?php endforeach ?>

</div></div>  

<div style="line-height: 1.2;">
    <?= view('dokumen/_ttd', ['gaya' => 'tanggal', 'ttd' => $ttd, 'tempat' => $d['tempat'], 'tgl' => $d['tgl_surat']]) ?>
</div>


<!-- ================= HALAMAN 2: SURAT DISPENSASI ================= -->
<!-- Perintah CSS untuk memaksa berganti halaman baru saat dicetak -->
<div style="page-break-before: always; padding-top: 20px;"></div>

<!-- Mencetak ulang Kop Surat untuk halaman Dispensasi -->
<div class="kop" id="kop2">
  <img src="<?= base_url(kop_logo_path()) ?>" alt="Logo" onerror="this.style.visibility='hidden'">
  <div>
    <div class="a"><?= esc($kop['pemerintah']) ?></div>
    <div class="a"><?= esc($kop['dinas']) ?></div>
    <div class="b"><?= esc(strtoupper($kop['instansi'])) ?></div>
    <?php foreach ($kop['alamat'] as $baris): ?><div class="c"><?= esc($baris) ?></div><?php endforeach ?>
  </div>
  <div></div>
</div>

<!-- Isi Surat Dispensasi -->
<div class="judul" style="text-align: center; font-weight: bold; margin-bottom: 0; line-height: 1.2;"><u>SURAT DISPENSASI</u></div>
<div class="nomor" style="text-align: center; margin-top: 0; margin-bottom: 20px; line-height: 1.2;">NOMOR : <?= esc($nomor) ?></div>

<p>Yang bertanda tangan di bawah ini Kepala Sekolah <?= esc($sekolah) ?>,</p>

<p class="jus">dengan ini memberikan dispensasi kepada siswa-siswi <?= esc($sekolah) ?> sebagaimana tercantum dalam daftar berikut:</p>
<?= view('dokumen/_tabel_siswa', ['siswa' => $siswa, 'besar' => true]) ?>

<p>Adapun kegiatan adalah <?= fm_teks(rtrim((string) $d['kegiatan'], ' .,;:')) ?> dilaksanakan pada:</p>
<div class="det" style="grid-template-columns:30mm 3mm 1fr;row-gap:0;margin-left:14mm">
  <div>Hari/Tanggal</div><div>:</div><div><?= esc($hariTgl) ?></div>
  <?php if (! empty($d['waktu'])): ?><div>Waktu</div><div>:</div><div><?= fm_teks($d['waktu']) ?></div><?php endif ?>
  <?php if (! empty($d['tmpk'])): ?><div>Tempat</div><div>:</div><div><?= fm_teks($d['tmpk']) ?></div><?php endif ?>
</div>

<p class="jus">Selama mengikuti kegiatan tersebut, siswa yang bersangkutan dibebaskan dari kegiatan pembelajaran di sekolah pada waktu yang telah ditentukan dan tetap berkewajiban untuk mengikuti serta menyelesaikan pembelajaran atau tugas yang ditinggalkan.</p>
<p class="jus">Demikian surat dispensasi ini dibuat untuk dapat dipergunakan sebagaimana mestinya.</p>
<div style="line-height: 1.2;">
    <?= view('dokumen/_ttd', ['gaya' => 'tanggal', 'ttd' => $ttd, 'tempat' => $d['tempat'], 'tgl' => $d['tgl_surat']]) ?>
</div>