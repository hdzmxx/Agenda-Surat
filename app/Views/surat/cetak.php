<?= $this->extend('layout') ?>
<?= $this->section('head') ?>
  <style id="pg"></style>
  <style>
    :root{--w:215.9mm;--h:330.2mm;--mt:12mm;--mr:12mm;--mb:12mm;--ml:12mm}
    *{box-sizing:border-box}
    /* sembunyikan ikon debug toolbar CodeIgniter (mode development) */
    #debug-icon,#debug-bar,#debug-bar-link{display:none!important}
    .print-bar{position:sticky;top:56px;z-index:5;background:var(--bs-body-bg);border:1px solid var(--bs-border-color);border-radius:8px;padding:12px;margin-bottom:14px;display:flex;flex-wrap:wrap;gap:10px 14px;align-items:end;font-size:13px}
    .print-bar label{display:flex;flex-direction:column;gap:3px}
    .print-bar label.cb{flex-direction:row;align-items:center;gap:6px;padding-bottom:6px}
    .print-bar select,.print-bar input{padding:6px 8px;font:inherit}
    .print-bar .cb input{padding:0}
    .print-bar small{color:var(--bs-secondary-color);flex-basis:100%}
    .margin-set{display:flex;gap:5px}
    .margin-set label{font-size:11px}
    .margin-set input{width:66px}
    .wrap{overflow-x:auto;padding:0 0 14px}
    .sheet{width:var(--w);min-height:var(--h);padding:var(--mt) var(--mr) var(--mb) var(--ml);margin:0 auto;background:#fff;box-shadow:0 1px 4px rgba(0,0,0,.3);color:#000}
    /* Kop surat */
    .kop{display:grid;grid-template-columns:24mm minmax(0,1fr) 0;align-items:center;border-bottom:2px solid #000;padding-bottom:2mm;margin-bottom:4mm;text-align:center}
    .kop img{width:20mm;height:auto;justify-self:start;transform:translateX(3mm)}
    .kop>div:nth-child(2){transform:translateX(-3mm)}
    .kop .a{font-size:12pt;line-height:1.1}.kop .b{font-size:15pt;font-weight:700;line-height:1.15}.kop .c{font-size:9pt;line-height:1.1}
    h3{text-align:center;font-size:12pt;margin:0 0 1mm}
    .per{text-align:center;font-size:10pt;margin:0 0 4mm}
    table{width:100%;border-collapse:collapse;font-size:9.5pt}
    th,td{border:1px solid #000;padding:3px 5px;vertical-align:top}
    td .tujuan-list{margin:0;padding-left:5mm}
    td .tujuan-list li{padding-left:1mm}
    th{background:#eee;text-align:center}
    thead{display:table-header-group}
    tr{break-inside:avoid;page-break-inside:avoid}
    .ttd{margin:8mm 0 0 auto;width:78mm;text-align:center;break-inside:avoid;page-break-inside:avoid}
    .ttd .sp{height:22mm}
    @media print{
      .app-topbar,.app-sidebar,.app-backdrop,.print-bar{display:none!important}
      .app-main{margin:0!important;padding:0!important;min-height:0!important}
      .app-content{max-width:none!important}
      body{background:#fff!important}.wrap{padding:0;overflow:visible}
      .sheet{width:auto;min-height:0;padding:0;margin:0;box-shadow:none}
    }
  </style>
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<?php
$bln   = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$per   = trim(($f['bulan'] ? $bln[(int) $f['bulan'] - 1] . ' ' : '') . ($f['tahun'] ?: ''));
$masuk = $arah === 'masuk';
helper('kop');
$kop = kop_settings();
?>
<div class="print-bar">
  <label>Ukuran kertas
    <select id="kertas">
      <option value="a4">A4 (210 x 297 mm)</option>
      <option value="f4" selected>F4 / Folio (215,9 x 330,2 mm)</option>
      <option value="legal">Legal (215,9 x 355,6 mm)</option>
      <option value="letter">Letter (215,9 x 279,4 mm)</option>
    </select></label>
  <label>Orientasi
    <select id="orient"><option value="landscape">Landscape (mendatar)</option><option value="portrait">Portrait (tegak)</option></select></label>
  <div class="margin-set" aria-label="Margin halaman dalam milimeter">
    <?php foreach (['mt' => 'Atas', 'mr' => 'Kanan', 'mb' => 'Bawah', 'ml' => 'Kiri'] as $marginId => $label): ?>
      <label><?= $label ?><input class="form-control form-control-sm" type="number" id="<?= $marginId ?>" min="0" max="50" step="1" value="12" aria-label="Margin <?= strtolower($label) ?> (mm)"></label>
    <?php endforeach ?>
  </div>
  <label>Jenis huruf
    <select id="font"><option value="Arial, Helvetica, sans-serif">Arial</option><option value="'Times New Roman', Times, serif">Times New Roman</option><option value="Calibri, Arial, sans-serif">Calibri</option><option value="Georgia, serif">Georgia</option><option value="Tahoma, sans-serif">Tahoma</option></select>
  </label>
  <label>Urutan tanggal
    <select id="urutTanggal"><option value="newest">Tanggal termuda</option><option value="oldest">Tanggal tertua</option></select>
  </label>
  <label class="cb"><input type="checkbox" id="showkop" checked> Kop surat</label>
  <label class="cb"><input type="checkbox" id="showttd" checked> Tanda tangan</label>
  <button class="btn btn-success" type="button" onclick="window.print()">Cetak</button>
  <small>Judul kolom tabel otomatis diulang di setiap halaman. Pilih ukuran kertas yang sama di dialog cetak jika diminta.</small>
</div>

<div class="wrap"><div class="sheet" id="sheet">
  <div class="kop" id="kop">
    <img src="<?= base_url(kop_logo_path()) ?>" alt="Logo" onerror="this.style.visibility='hidden'">
    <div>
      <div class="a"><?= esc($kop['pemerintah']) ?></div>
      <div class="a"><?= esc($kop['dinas']) ?></div>
      <div class="b"><?= esc(strtoupper($kop['instansi'])) ?></div>
      <?php foreach ($kop['alamat'] as $baris): ?><div class="c"><?= esc($baris) ?></div><?php endforeach ?>
    </div>
    <div></div>
  </div>

  <h3>AGENDA SURAT <?= strtoupper($arah) ?><?= $f['status'] ? ' (' . esc($f['status']) . ')' : '' ?></h3>
  <p class="per"><?= $per ? 'Periode ' . esc($per) : 'Semua periode' ?></p>

  <table id="agendaTable">
    <colgroup>
      <?php if ($masuk): ?>
        <col style="width:5%"><col style="width:11%"><col style="width:17%"><col style="width:16%"><col style="width:30%"><col style="width:12%"><col style="width:9%">
      <?php else: ?>
        <col style="width:6%"><col style="width:12%"><col style="width:18%"><col style="width:17%"><col style="width:34%"><col style="width:13%">
      <?php endif ?>
    </colgroup>
    <thead><tr>
      <th>No.</th><th><?= $masuk ? 'Tgl Diterima' : 'Tgl Surat' ?></th><th>Nomor Surat</th><th><?= $masuk ? 'Asal Surat' : 'Tujuan' ?></th><th>Perihal</th><?php if ($masuk): ?><th>Disposisi</th><?php endif ?><th>Status</th>
    </tr></thead>
    <tbody>
    <?php foreach ($rows as $nomorUrut => $r): ?>
      <tr data-date="<?= esc($r['tgl_agenda']) ?>">
        <td style="text-align:center"><?= $masuk ? $nomorUrut + 1 : no_agenda($r['no_agenda']) ?></td>
        <td><?= tgl_id($r['tgl_agenda']) ?></td>
        <td><?= esc($r['nomor_surat']) ?><?= $masuk && $r['tgl_surat'] ? '<br>' . tgl_id($r['tgl_surat']) : '' ?></td>
        <td><?php if (! $masuk && ! empty($r['tujuan_list']) && count($r['tujuan_list']) > 1): ?><ol class="tujuan-list"><?php foreach ($r['tujuan_list'] as $nama): ?><li><?= esc($nama) ?></li><?php endforeach ?></ol><?php else: ?><?= esc($r['pihak']) ?><?php endif ?></td>
        <td><?= esc($r['perihal']) ?></td>
        <?php if ($masuk): ?><td><?= esc($r['disposisi']) ?></td><?php endif ?>
        <td><?= esc($r['status']) ?></td>
      </tr>
    <?php endforeach ?>
    <?php if (! $rows): ?><tr><td colspan="<?= $masuk ? 7 : 6 ?>" style="text-align:center">Tidak ada data.</td></tr><?php endif ?>
    </tbody>
  </table>

  <?php $ttd = \App\Libraries\JenisSurat::penandatangan(); ?>
  <div class="ttd" id="ttd">
    <?= esc($ttd['jabatan']) ?>
    <div class="sp"></div>
    <?= esc($ttd['nama']) ?><?php if ($ttd['nip'] !== ''): ?><br>NIP. <?= esc($ttd['nip']) ?><?php endif ?>
  </div>
</div></div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
(function () {
  var P = {a4: [210, 297], f4: [215.9, 330.2], legal: [215.9, 355.6], letter: [215.9, 279.4]},
      $ = function (id) { return document.getElementById(id); },
      sheet = $('sheet'), KEY = 'cetakopsi';
  var ids = ['kertas', 'orient', 'mt', 'mr', 'mb', 'ml', 'font', 'urutTanggal', 'showkop', 'showttd'];
  function simpan() {
    var state = {};
    ids.forEach(function (id) { state[id] = $(id).type === 'checkbox' ? $(id).checked : $(id).value; });
    try { localStorage.setItem(KEY, JSON.stringify(state)); } catch (e) {}
  }
  function terap() {
    var p = P[$('kertas').value], land = $('orient').value === 'landscape',
        W = land ? p[1] : p[0], H = land ? p[0] : p[1],
        mt = Math.max(0, Math.min(50, +$('mt').value || 0)),
        mr = Math.max(0, Math.min(50, +$('mr').value || 0)),
        mb = Math.max(0, Math.min(50, +$('mb').value || 0)),
        ml = Math.max(0, Math.min(50, +$('ml').value || 0));
    $('pg').textContent = '@page{size:' + W + 'mm ' + H + 'mm;margin:' + mt + 'mm ' + mr + 'mm ' + mb + 'mm ' + ml + 'mm}';
    sheet.style.setProperty('--w', W + 'mm');
    sheet.style.setProperty('--h', H + 'mm');
    sheet.style.setProperty('--mt', mt + 'mm');
    sheet.style.setProperty('--mr', mr + 'mm');
    sheet.style.setProperty('--mb', mb + 'mm');
    sheet.style.setProperty('--ml', ml + 'mm');
    sheet.style.fontFamily = $('font').value;
    $('kop').style.display = $('showkop').checked ? '' : 'none';
    $('ttd').style.display = $('showttd').checked ? '' : 'none';
    var body = $('agendaTable').tBodies[0],
        rows = Array.prototype.slice.call(body.querySelectorAll('tr[data-date]'));
    rows.sort(function (a, b) {
      var order = a.getAttribute('data-date').localeCompare(b.getAttribute('data-date'));
      return $('urutTanggal').value === 'oldest' ? order : -order;
    });
    rows.forEach(function (row) { body.appendChild(row); });
    simpan();
  }
  try {
    var s = JSON.parse(localStorage.getItem(KEY) || 'null');
    if (s) {
      ids.forEach(function (id) {
        if (s[id] !== undefined) {
          if ($(id).type === 'checkbox') $(id).checked = s[id];
          else $(id).value = s[id];
        }
      });
      if (s.m && !s.mt) ['mt', 'mr', 'mb', 'ml'].forEach(function (id) { $(id).value = s.m; });
      if (s.kop !== undefined && s.showkop === undefined) $('showkop').checked = s.kop;
      if (s.ttd !== undefined && s.showttd === undefined) $('showttd').checked = s.ttd;
    }
  } catch (e) {}
  ids.forEach(function (id) { $(id).addEventListener('change', terap); });
  terap();
})();
</script>
<?= $this->endSection() ?>
