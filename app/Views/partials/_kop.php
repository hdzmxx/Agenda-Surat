<?php /** Kop surat. Pemakaian: view('partials/_kop', ['kop' => $kop]) */ ?>
  <div class="kop">
    <img src="<?= base_url(kop_logo_path()) ?>" alt="Logo" onerror="this.style.visibility='hidden'">
    <div>
      <div class="a"><?= esc($kop['pemerintah']) ?></div>
      <div class="a"><?= esc($kop['dinas']) ?></div>
      <div class="b"><?= esc(strtoupper($kop['instansi'])) ?></div>
      <?php foreach ($kop['alamat'] as $baris): ?><div class="c"><?= esc($baris) ?></div><?php endforeach ?>
    </div>
    <div></div>
  </div>
