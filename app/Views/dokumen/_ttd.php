<?php
/** Blok tanda tangan. $gaya: 'hormat' (Hormat kami,) atau 'tanggal' (Kota, tanggal). */
$atas = $gaya === 'hormat' ? 'Hormat kami,' : esc($tempat) . ', ' . tgl_id($tgl);
?>
<div class="ttd <?= $gaya ?>">
  <div><?= $atas ?></div>
  <div><?= esc($ttd['jabatan']) ?></div>
  <div class="sp"></div>
  <div class="nm"><?= esc($ttd['nama']) ?></div>
  <?php if ($ttd['nip']): ?><div>NIP. <?= esc($ttd['nip']) ?></div><?php endif ?>
</div>
