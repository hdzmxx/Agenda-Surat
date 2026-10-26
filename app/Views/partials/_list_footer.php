<?php
/**
 * Bagian bawah daftar data: ringkasan jumlah, navigasi halaman, dan pilihan jumlah baris per halaman.
 * Pemakaian: view('partials/_list_footer', ['pager' => $pager]) dipanggil dari halaman daftar.
 */
$total  = (int) $pager->getTotal();
$per    = (int) $pager->getPerPage();
$hal    = max(1, (int) $pager->getCurrentPage());
$dari   = $total > 0 ? ($hal - 1) * $per + 1 : 0;
$sampai = min($total, $hal * $per);

$pilihan = [10, 20, 30, 40, 50, 100, 200];
if (! in_array($per, $pilihan, true)) {
    $pilihan[] = $per;
    sort($pilihan);
}
// Pertahankan pencarian/filter/urutan saat mengganti jumlah baris.
$query = array_diff_key((array) service('request')->getGet(), ['per' => 1, 'page' => 1]);
?>
<div class="list-foot">
  <div class="list-info">
    <?php if ($total > 0): ?>Menampilkan <strong><?= $dari ?>&ndash;<?= $sampai ?></strong> dari <strong><?= $total ?></strong> data<?php else: ?>Tidak ada data<?php endif ?>
  </div>
  <div class="list-pager"><?= $pager->links() ?></div>
  <form method="get" class="list-per" id="formPer">
    <?php foreach ($query as $k => $v): if (! is_scalar($v)) { continue; } ?>
      <input type="hidden" name="<?= esc($k, 'attr') ?>" value="<?= esc((string) $v, 'attr') ?>">
    <?php endforeach ?>
    <label for="perPilih">Tampilkan</label>
    <select class="form-select form-select-sm" id="perPilih" name="per" aria-label="Jumlah data per halaman">
      <?php foreach ($pilihan as $n): ?><option value="<?= $n ?>" <?= $n === $per ? 'selected' : '' ?>><?= $n ?></option><?php endforeach ?>
      <option value="lain">Lainnya&hellip;</option>
    </select>
    <span class="list-per-lain" id="perLain" hidden>
      <input class="form-control form-control-sm" type="number" id="perAngka" min="1" max="500" value="<?= $per ?>" aria-label="Jumlah data per halaman (angka bebas)">
      <button class="btn btn-success btn-sm btn-pill" type="submit">OK</button>
    </span>
  </form>
</div>
<script>
(function () {
  var sel = document.getElementById('perPilih'), lain = document.getElementById('perLain'), angka = document.getElementById('perAngka');
  if (!sel) return;
  sel.addEventListener('change', function () {
    if (sel.value === 'lain') {
      sel.removeAttribute('name'); angka.setAttribute('name', 'per'); lain.hidden = false; angka.focus(); angka.select();
    } else {
      document.getElementById('formPer').submit();
    }
  });
})();
</script>
