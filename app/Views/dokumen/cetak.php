<?= $this->extend('layout') ?>
<?= $this->section('head') ?>
  <style id="pg"></style>
  <style>
    :root{--w:210mm;--h:297mm;--mt:15mm;--mr:15mm;--mb:15mm;--ml:15mm;--fs:11pt}
    *{box-sizing:border-box}
    #debug-icon,#debug-bar,#debug-bar-link{display:none!important}
    .print-bar { 
      position: sticky; 
      top: 56px; /* Menempel tepat di bawah topbar utama */
      z-index: 10; 
      background: var(--bs-body-bg); 
      border-bottom: 1px solid var(--bs-border-color);
      border-radius: 0; 
      padding: 10px 20px; 
      margin: -1rem -1rem 16px -1rem; /* Membuat lebar penuh kiri-kanan menembus padding content */
      box-shadow: 0 2px 2px rgba(236, 236, 236, 0.29); 
      font-size: 13px; 
    }
    .pb-row { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; justify-content: space-between; }
    .pb-group { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; }
    .pb-divider { width: 1px; height: 24px; background: var(--bs-border-color); margin: 0 2px; }
    .print-bar label { margin-bottom: 0; color: var(--bs-secondary-color); white-space: nowrap; font-weight: 500; }
    .print-bar .form-select-sm, .print-bar .form-control-sm { font-size: 13px; padding: 3px 20px 3px 8px; min-height: 28px; }
    .print-bar .form-control-sm { padding: 3px 6px; }
    .print-bar .margin-input { width: 44px; text-align: center; }
    .print-bar .form-check { margin-bottom: 0; display: flex; align-items: center; gap: 6px; }
    .print-bar .form-check-input { margin-top: 0; cursor: pointer; }
    .wrap{overflow-x:auto;padding:0 0 14px}
    .sheet{width:var(--w);min-height:var(--h);padding:var(--mt) var(--mr) var(--mb) var(--ml);margin:0 auto;background:#fff;box-shadow:0 1px 4px rgba(0,0,0,.3);font-size:var(--fs);color:#000}
    .kop{display:grid;grid-template-columns:24mm minmax(0,1fr) 0;align-items:center;border-bottom:2px solid #000;padding-bottom:2mm;margin-bottom:3mm;text-align:center}
    .kop img{width:auto;height:25mm;justify-self:start;transform:translateX(3mm)}
    .kop>div:nth-child(2){transform:translateX(-3mm)}
    .kop .a{font-size:1.1em;line-height:1.1}.kop .b{font-size:1.35em;font-weight:700;line-height:1.15}.kop .c{font-size:.82em;line-height:1.1}
    p{margin:0 0 3mm;text-align:justify}
    .kanan{text-align:right;margin:0 0 3mm}
    .meta{display:grid;grid-template-columns:18mm 4mm 1fr;margin:0 0 4mm 6mm}
    .kpd{margin:0 0 4mm 6mm}
    .det{display:grid;grid-template-columns:36mm 4mm 1fr;row-gap:1.2mm;margin:0 0 3mm 8mm}
    .det-siswa{grid-template-columns:44mm 4mm 1fr}
    .judul{text-align:center;font-weight:700;margin:2mm 0 0}.nomor{text-align:center;margin:0 0 5mm}
    .judul2{text-align:center;font-weight:700;margin:4mm 0}
    .tg{display:grid;grid-template-columns:20mm 4mm 1fr;margin-bottom:3mm}
    .org{display:grid;grid-template-columns:8mm 1fr;margin-bottom:2mm}
    .kv2{display:grid;grid-template-columns:44mm 4mm 1fr;row-gap:.6mm}
    .jus{text-align:justify}
    table.tbl{width:100%;border-collapse:collapse;margin:0 0 4mm}
    .tbl th,.tbl td{border:1px solid #000;padding:2px 6px}.tbl th{text-align:center;background:#f2f2f2}.tbl td.c{text-align:center}
    thead{display:table-header-group}tr{break-inside:avoid;page-break-inside:avoid}
    .ttd{margin:6mm 0 0 55%;break-inside:avoid;page-break-inside:avoid}
    .ttd .sp{height:20mm}
    .ttd.tanggal .nm{display:table;font-weight:700;border-bottom:1.5px solid #000;padding: bottom 0;margin-bottom:.2mm}
    .sppd{font-size:var(--fs);line-height:1.25}
    .sppd-judul{text-align:center;text-decoration:underline;font-size:1.1em;margin:4mm 0 1.5mm}
    .sppd-nomor{text-align:center;margin:0 0 3mm}
    table.sppd-t{width:100%;border-collapse:collapse;border-top:2.2px solid #000;table-layout:fixed}
    .sppd-t td{padding:.5mm 1.5mm;vertical-align:top;overflow-wrap:anywhere}
    .sppd-t tr.ak td{padding-bottom:1.2mm}
    .sppd-t tr.ak td{border-bottom:1px solid #000}
    .sppd-t td.v{border-left:1px solid #000}
    .sppd-t tr.tinggi td{height:14mm}
    table.sppd-kvt{border-collapse:collapse;width:auto}
    .sppd-kvt td{padding:0 1.5mm 0 0;line-height:1.1;vertical-align:top;border:0}
    .sppd-kvt td:first-child,.sppd-kvt td:nth-child(2){white-space:nowrap}
    .sppd-kvt td:nth-child(2){padding-right:1.5mm}
    .sppd-ttd{margin:5mm 0 0 52%;break-inside:avoid;page-break-inside:avoid}
    .sppd-ttd-isi{text-align:left;margin-top:4mm}
    .sppd-ttd-spasi{height:18mm}
    .sppd-nm{text-decoration:underline}
    .sppd-belakang{break-before:page;page-break-before:always}
    .sppd-meta{margin:0 0 3mm 49%;display:grid;grid-template-columns:max-content minmax(0,1fr);column-gap:3mm;row-gap:.3mm}
    .sppd-rute{display:grid;grid-template-columns:50% minmax(0,1fr);border-top:1px solid #000;padding:0;min-height:var(--sppd-rowh,52mm);break-inside:avoid;page-break-inside:avoid}
    .sppd-rute-kiri{display:grid;grid-template-columns:9mm minmax(0,1fr);align-content:start;padding:2mm 2mm 2mm 0}
    .sppd-rute-kanan{min-width:0;padding:2mm 0 2mm 2mm;border-left:1px solid #000}
    .sppd-kepala{margin-top:2mm;line-height:1.1}
    .sppd-verif{display:grid;grid-template-columns:calc(44% - 7mm) 7mm minmax(0,1fr);border-top:1px solid #000;padding-top:2mm;break-inside:avoid;page-break-inside:avoid}
    .sppd-verif .sppd-rno{text-align:right;padding-right:1mm}
    .sppd-verif p{margin:2mm 0 3mm;text-align:justify}
    .sppd-ttd-akhir{margin-top:3mm}
    .sppd-catatan{margin-top:6mm;break-inside:avoid;page-break-inside:avoid}
    .sppd-garis{border:1px solid #000;height:7mm}
    .sppd-garis+.sppd-garis{border-top:0}
    .sppd-catatan>div:first-child{margin-bottom:1mm}
    .sheet.editing #inner{outline:1px dashed #0d6efd;outline-offset:3px;cursor:text}
    .sheet.editing .sppd-kvt td{min-width:8mm}
    .sppd-set+.sppd-set{break-before:page;page-break-before:always}
    @media screen{.sppd-set+.sppd-set{margin-top:10mm;padding-top:6mm;border-top:3px dashed #999}}
    @media screen{.sppd-belakang{margin-top:8mm;padding-top:4mm;border-top:2px dashed #999}}
    @media print{
      .sheet.editing #inner{outline:0!important}
      html,body{min-height:0!important;background:#fff!important;background-image:none!important;color:#000!important}
      .app-topbar,.app-sidebar,.app-backdrop,.print-bar{display:none!important}
      .app-content>.alert{display:none!important}
      .app-main{margin:0!important;padding:0!important;min-height:0!important}
      .app-content{max-width:none!important;margin:0!important}
      .app-main,.app-content,.wrap,.sheet,#fitbox,#inner{background:#fff!important;background-image:none!important;color:#000!important}
      body,.sheet{-webkit-print-color-adjust:exact;print-color-adjust:exact}
      .wrap{padding:0;overflow:visible}
      .sheet{width:auto;min-height:0;padding:0;margin:0;box-shadow:none}
    }
  </style>
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<?php helper('kop'); $kop = kop_settings(); ?>
<div class="print-bar">
  <?php if (! empty($bersama)): ?><div class="small text-muted px-2 pb-1">Mencetak <?= count($bersama) + 1 ?> SPPD sekaligus: <?= esc(implode(', ', $daftarNama ?? [])) ?></div><?php endif ?>
  <!-- Baris Atas: Pengaturan Kertas, Margin, Font & Tombol -->
  <div class="pb-row">
    
    <div class="pb-group">
      <!-- Layout -->
      <select id="kertas" class="form-select form-select-sm" style="width: 110px;" title="Ukuran Kertas">
        <option value="a4" <?= $jenis === 'sppd' ? '' : 'selected' ?>>A4</option>
        <option value="f4" <?= $jenis === 'sppd' ? 'selected' : '' ?>>F4/Folio</option>
        <option value="legal">Legal</option>
        <option value="letter">Letter</option>
      </select>
      <select id="orient" class="form-select form-select-sm" style="width: 100px;" title="Orientasi">
        <option value="portrait" selected>Portrait</option>
        <option value="landscape">Landscape</option>
      </select>

      <div class="pb-divider"></div>

      <!-- Margin (Sangat Ringkas) -->
      <div class="d-flex align-items-center gap-1" title="Margin (Atas, Kanan, Bawah, Kiri) dalam milimeter">
        <label class="me-1">Margin:</label>
        <?php foreach (['mt' => 'Atas', 'mr' => 'Kanan', 'mb' => 'Bawah', 'ml' => 'Kiri'] as $marginId => $label): ?>
          <?php $marginDefault = $jenis === 'sppd' ? (in_array($marginId, ['mt', 'mb'], true) ? 10 : 16) : 15; ?>
          <input type="number" id="<?= $marginId ?>" min="0" max="50" step="1" value="<?= $marginDefault ?>" class="form-control form-control-sm margin-input" title="<?= $label ?> (mm)">
        <?php endforeach ?>
      </div>

      <div class="pb-divider"></div>

      <!-- Tipografi -->
      <select id="fs" class="form-select form-select-sm" style="width: 70px;" title="Ukuran Huruf">
        <option value="10">10 pt</option>
        <option value="11" <?= $jenis === 'sppd' ? '' : 'selected' ?>>11 pt</option>
        <option value="12" <?= $jenis === 'sppd' ? 'selected' : '' ?>>12 pt</option>
      </select>
      <select id="font" class="form-select form-select-sm" style="width: 135px;" title="Jenis Huruf">
        <option value="Arial, Helvetica, sans-serif" <?= $jenis === 'sppd' ? '' : 'selected' ?>>Arial</option>
        <option value="'Times New Roman', Tinos, Times, serif" <?= $jenis === 'sppd' ? 'selected' : '' ?>>Times New Roman</option>
        <option value="Calibri, Arial, sans-serif">Calibri</option>
      </select>
    </div>

    <!-- Tombol Aksi Kanan -->
    <div class="pb-group ms-auto">
      <a class="btn btn-outline-secondary btn-sm px-3" href="<?= site_url("dokumen/$id/ubah") ?>">Ubah isi</a>
      <div class="dropdown">
        <button class="btn btn-outline-secondary btn-sm px-3 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Unduh</button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><button class="dropdown-item" type="button" id="unduhPdf">PDF (.pdf)</button></li>
          <li><button class="dropdown-item" type="button" id="unduhWord">Word (.docx)</button></li>
        </ul>
      </div>
      <button type="button" class="btn btn-success btn-sm px-4 shadow-sm" onclick="window.print()">Cetak</button>
    </div>

  </div>

  <!-- Baris Bawah: Toggle & Opsi Khusus (Hanya muncul jika ada) -->
  <div class="pb-row mt-2 pt-2 border-top">
    <div class="pb-group w-100">
      
      <!-- Opsi Centang (Bentuk Switch agar lebih modern) -->
      <div class="form-check form-switch"><input type="checkbox" id="showkop" checked class="form-check-input"><label class="form-check-label" for="showkop">Kop</label></div>
      <div class="form-check form-switch"><input type="checkbox" id="showttd" checked class="form-check-input"><label class="form-check-label" for="showttd">Tanda tangan</label></div>
      <div class="form-check form-switch <?= $jenis === 'sppd' ? 'd-none' : '' ?>"><input type="checkbox" id="fit" checked class="form-check-input"><label class="form-check-label" for="fit">Pas 1 halaman</label></div>

      <!-- Opsi Khusus Dokumen Siswa -->
      <?php if (in_array($jenis, ['sk_siswa', 'tugas_siswa', 'dispensasi', 'prestasi'], true)): ?>
        <div class="pb-divider ms-2"></div>
        <label>Nama Siswa:</label>
        <select id="hurufsiswa" class="form-select form-select-sm" style="width: 120px;">
          <option value="asli" selected>Sesuai data</option>
          <option value="besar">HURUF BESAR</option>
          <option value="kapital">Huruf Kapital</option>
        </select>
        <?php if ($jenis !== 'prestasi'): ?>
          <div class="form-check form-switch ms-2"><input type="checkbox" id="kolomjk" checked class="form-check-input"><label class="form-check-label" for="kolomjk">Kolom JK</label></div>
          <div class="form-check form-switch"><input type="checkbox" id="kolomnisn" checked class="form-check-input"><label class="form-check-label" for="kolomnisn">Kolom NISN</label></div>
        <?php endif ?>
      <?php endif ?>

      <!-- Opsi Khusus SPPD: tinggi kolom II-IV (ruang cap dinas) -->
      <?php if ($jenis === 'sppd'): ?>
        <div class="pb-divider ms-2"></div>
        <div class="form-check form-switch"><input type="checkbox" id="editmanual" class="form-check-input"><label class="form-check-label" for="editmanual">Edit manual</label></div>
        <button type="button" id="resetedit" class="btn btn-outline-secondary btn-sm" style="display:none" title="Kembalikan isi lembar sesuai data surat">Reset</button>
        <span id="edithint" class="text-muted" style="display:none">Klik teks di lembar untuk mengubah. Perubahan hanya untuk cetak ini dan hilang saat halaman dimuat ulang.</span>
        <div class="pb-divider ms-2"></div>
        <label>Tinggi kolom cap: <output id="sppdRowhValue" class="fw-bold text-dark">52</output> mm</label>
        <input type="range" id="sppdRowh" class="form-range" min="30" max="58" step="1" value="52" style="width: 120px; height: 1.5rem;">
      <?php endif ?>

    </div>
  </div>
</div>

<?php $namaBerkas = trim(preg_replace('/[^A-Za-z0-9]+/', '-', (string) ($judul ?? 'Surat')), '-') . '-' . (int) $id; ?>
<span id="namaBerkas" data-nama="<?= esc($namaBerkas, 'attr') ?>" hidden></span>
<div class="wrap"><div class="sheet" id="sheet"><div id="fitbox"><div id="inner">
<?php if (! empty($bersama)): ?>
  <?php foreach (array_merge([$isi], $bersama) as $x): ?>
  <div class="sppd-set">
    <?= view('partials/_kop', ['kop' => $kop]) ?>
    <?= $x ?>
  </div>
  <?php endforeach ?>
<?php else: ?>
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
  <?= $isi ?>
<?php endif ?>
</div></div></div></div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
(function () {
  var P = {a4: [210, 297], f4: [215.9, 330.2], legal: [215.9, 355.6], letter: [215.9, 279.4]},
      $ = function (i) { return document.getElementById(i); }, sheet = $('sheet'), SPPD = !!document.querySelector('.sppd'), KEY = SPPD ? 'cetakdok-sppd2' : 'cetakdok',
      ids = ['kertas', 'orient', 'mt', 'mr', 'mb', 'ml', 'fs', 'font', 'showkop', 'showttd', 'fit'];
  if ($('sppdRowh')) ids.push('sppdRowh');
  if ($('hurufsiswa')) ids.push('hurufsiswa');
  if ($('kolomjk')) ids.push('kolomjk', 'kolomnisn');
  function kapital(t) {
    return t.toLowerCase().replace(/(^|[^\p{L}\p{N}'\u2019])(\p{L})/gu, function (m, a, b) { return a + b.toUpperCase(); });
  }
  function simpan() {
    var o = {}; ids.forEach(function (i) { o[i] = $(i).type === 'checkbox' ? $(i).checked : $(i).value; });
    try { localStorage.setItem(KEY, JSON.stringify(o)); } catch (e) {}
  }
  function terap() {
    var p = P[$('kertas').value], land = $('orient').value === 'landscape',
        W = land ? p[1] : p[0], H = land ? p[0] : p[1], mm = 96 / 25.4,
        mt = Math.max(0, Math.min(50, +$('mt').value || 0)),
        mr = Math.max(0, Math.min(50, +$('mr').value || 0)),
        mb = Math.max(0, Math.min(50, +$('mb').value || 0)),
        ml = Math.max(0, Math.min(50, +$('ml').value || 0));
    $('pg').textContent = '@page{size:' + W + 'mm ' + H + 'mm;margin:' + mt + 'mm ' + mr + 'mm ' + mb + 'mm ' + ml + 'mm}';
    sheet.style.setProperty('--w', W + 'mm'); sheet.style.setProperty('--h', H + 'mm');
    sheet.style.setProperty('--mt', mt + 'mm'); sheet.style.setProperty('--mr', mr + 'mm');
    sheet.style.setProperty('--mb', mb + 'mm'); sheet.style.setProperty('--ml', ml + 'mm');
    sheet.style.setProperty('--fs', $('fs').value + 'pt');
    $('inner').style.fontFamily = $('font').value;
    document.querySelectorAll('.kop').forEach(function (k) { k.style.display = $('showkop').checked ? '' : 'none'; });
    document.querySelectorAll('.ttd, .sppd-ttd-isi').forEach(function (e) { e.style.display = $('showttd').checked ? '' : 'none'; });
    if ($('sppdRowh')) {
      sheet.style.setProperty('--sppd-rowh', $('sppdRowh').value + 'mm');
      $('sppdRowhValue').textContent = $('sppdRowh').value;
    }
    if ($('hurufsiswa')) {
      var mode = $('hurufsiswa').value;
      document.querySelectorAll('.nm-siswa').forEach(function (e) {
        if (e.dataset.asli === undefined) e.dataset.asli = e.textContent;
        var t = e.dataset.asli;
        e.textContent = mode === 'besar' ? t.toUpperCase() : (mode === 'kapital' ? kapital(t) : t);
      });
    }
    if ($('kolomjk')) {
      [['kolomjk', '.col-jk'], ['kolomnisn', '.col-nisn']].forEach(function (k) {
        document.querySelectorAll(k[1]).forEach(function (e) { e.style.display = $(k[0]).checked ? '' : 'none'; });
      });
    }
    var box = $('fitbox'), inner = $('inner');
    inner.style.transform = ''; inner.style.width = ''; box.style.height = '';
    if ($('fit').checked && !SPPD) {
      var avail = (H - mt - mb) * mm * 0.97, nat = inner.offsetHeight, s = Math.min(1, avail / nat);
      if (s < 1 && s >= 0.6) {
        inner.style.transformOrigin = '0 0'; inner.style.transform = 'scale(' + s + ')';
        inner.style.width = (100 / s) + '%'; box.style.height = (nat * s) + 'px';
      }
    }
    simpan();
  }
  try {
    var s = JSON.parse(localStorage.getItem(KEY) || 'null');
    if (s) ids.forEach(function (i) {
      if (s[i] !== undefined && $(i)) {
        if ($(i).type === 'checkbox') $(i).checked = s[i];
        else $(i).value = s[i];
      }
    });
  } catch (e) {}
  ids.forEach(function (i) { $(i).addEventListener('change', terap); });
  if ($('editmanual')) {
    $('editmanual').addEventListener('change', function () {
      var on = this.checked, inner = $('inner');
      inner.contentEditable = on ? 'true' : 'false';
      inner.spellcheck = false;
      sheet.classList.toggle('editing', on);
      $('resetedit').style.display = $('edithint').style.display = on ? '' : 'none';
      if (on) inner.focus();
    });
    $('resetedit').addEventListener('click', function () {
      if (confirm('Kembalikan isi lembar sesuai data surat? Perubahan manual akan hilang.')) location.reload();
    });
  }
  window.addEventListener('beforeprint', terap);
  terap();
})();
</script>
<script>
/* Unduh PDF dan Word dari lembar cetak. Berjalan di browser; tidak mengubah data surat. */
(function () {
  var $ = function (i) { return document.getElementById(i); };
  if (!$('unduhPdf') || !$('unduhWord')) return;
  var MM = 96 / 25.4;
  var CDN_H2C = 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js';
  var CDN_PDF = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';

  /* ---------- umum ---------- */
  var overlay;
  function status(teks) {
    if (!teks) { if (overlay) overlay.style.display = 'none'; return; }
    if (!overlay) {
      overlay = document.createElement('div');
      overlay.style.cssText = 'position:fixed;inset:0;z-index:3000;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,.6);color:#fff;font-size:1.05rem';
      document.body.appendChild(overlay);
    }
    overlay.textContent = teks; overlay.style.display = 'flex';
  }
  function nama() { var e = $('namaBerkas'); return (e && e.dataset.nama) || 'surat'; }
  function simpanBlob(blob, file) {
    var a = document.createElement('a');
    a.href = URL.createObjectURL(blob); a.download = file; document.body.appendChild(a); a.click();
    setTimeout(function () { URL.revokeObjectURL(a.href); a.remove(); }, 1500);
  }
  function muat(src, siap) {
    return new Promise(function (ok, gagal) {
      if (siap()) return ok();
      var s = document.createElement('script'); s.src = src;
      s.onload = function () { siap() ? ok() : gagal(new Error('Pustaka tidak siap')); };
      s.onerror = function () { gagal(new Error('Tidak dapat memuat pustaka dari internet')); };
      document.head.appendChild(s);
    });
  }
  function kertas() {
    var cs = getComputedStyle($('sheet'));
    function v(n) { return parseFloat(cs.getPropertyValue(n)) || 0; }
    return { W: v('--w'), H: v('--h'), mt: v('--mt'), mr: v('--mr'), mb: v('--mb'), ml: v('--ml') };
  }

  /* ---------- PDF (gambar halaman, ketajaman 2x) ---------- */
  function unitHalaman() {
    var fitbox = $('fitbox'), hasil = [], jumlahSet = fitbox.querySelectorAll('.sppd-set').length;
    function salin(hapus, k) {
      var c = fitbox.cloneNode(true);
      c.removeAttribute('id');
      c.querySelectorAll('[id]').forEach(function (e) { e.removeAttribute('id'); });
      c.querySelectorAll('[contenteditable]').forEach(function (e) { e.removeAttribute('contenteditable'); });
      if (k !== undefined) {
        c.querySelectorAll('.sppd-set').forEach(function (e, i) {
          if (i !== k) e.remove(); else { e.style.margin = '0'; e.style.padding = '0'; e.style.border = '0'; }
        });
      }
      hapus.forEach(function (sel) { c.querySelectorAll(sel).forEach(function (e) { e.remove(); }); });
      c.querySelectorAll('.sppd-belakang').forEach(function (e) { e.style.margin = '0'; e.style.padding = '0'; e.style.border = '0'; });
      return c;
    }
    if (jumlahSet) {
      for (var k = 0; k < jumlahSet; k++) {           // tiap SPPD: lembar depan lalu lembar belakang
        hasil.push(salin(['.sppd-belakang'], k));
        hasil.push(salin(['.kop', '.sppd-depan'], k));
      }
    } else if (document.querySelector('.sppd-belakang')) {
      hasil.push(salin(['.sppd-belakang']));
      hasil.push(salin(['.kop', '.sppd-depan']));
    } else {
      hasil.push(salin([]));
    }
    return hasil;
  }
  function renderHalaman(isi, p) {
    var w = document.createElement('div');
    w.className = 'sheet';
    w.style.cssText = $('sheet').style.cssText;
    w.style.position = 'absolute'; w.style.left = '0'; w.style.top = '0'; w.style.zIndex = '-1';
    w.style.margin = '0'; w.style.boxShadow = 'none'; w.style.width = p.W + 'mm'; w.style.minHeight = p.H + 'mm';
    w.appendChild(isi); document.body.appendChild(w);
    return window.html2canvas(w, { scale: 2, backgroundColor: '#ffffff', useCORS: true, logging: false, scrollX: 0, scrollY: 0 })
      .then(function (cv) { w.remove(); return cv; }, function (e) { w.remove(); throw e; });
  }
  function susunPdf(kanvas, p) {
    var J = window.jspdf.jsPDF, land = p.W > p.H, pertama = true;
    var pdf = new J({ orientation: land ? 'landscape' : 'portrait', unit: 'mm', format: [Math.min(p.W, p.H), Math.max(p.W, p.H)], compress: true });
    kanvas.forEach(function (cv) {
      var rasio = cv.width / p.W, tinggi = Math.round(p.H * rasio), n = Math.max(1, Math.ceil((cv.height - 3) / tinggi));
      for (var i = 0; i < n; i++) {
        var h = Math.min(tinggi, cv.height - i * tinggi), s = document.createElement('canvas');
        s.width = cv.width; s.height = h;
        var g = s.getContext('2d'); g.fillStyle = '#fff'; g.fillRect(0, 0, s.width, s.height);
        g.drawImage(cv, 0, i * tinggi, cv.width, h, 0, 0, cv.width, h);
        if (!pertama) pdf.addPage();
        pdf.addImage(s.toDataURL('image/png'), 'PNG', 0, 0, p.W, h / rasio);
        pertama = false;
      }
    });
    return pdf;
  }
  function unduhPdf() {
    var y = window.scrollY, p = kertas();
    status('Menyiapkan PDF...');
    muat(CDN_H2C, function () { return !!window.html2canvas; })
      .then(function () { return muat(CDN_PDF, function () { return !!(window.jspdf && window.jspdf.jsPDF); }); })
      .then(function () {
        window.scrollTo({ top: 0, behavior: 'instant' });
        var unit = unitHalaman(), hasil = [];
        return unit.reduce(function (rantai, isi) {
          return rantai.then(function () { return renderHalaman(isi, p).then(function (cv) { hasil.push(cv); }); });
        }, Promise.resolve()).then(function () { return hasil; });
      })
      .then(function (kanvas) { susunPdf(kanvas, p).save(nama() + '.pdf'); })
      .catch(function (e) { alert('PDF belum bisa dibuat: ' + e.message + '.\nPastikan komputer terhubung ke internet, atau gunakan Cetak lalu pilih "Simpan sebagai PDF".'); })
      .then(function () { status(''); window.scrollTo({ top: y, behavior: 'instant' }); });
  }

  /* ---------- Word (.docx): dibangun langsung (OOXML) dari tampilan lembar ---------- */
  function tw(px) { return Math.round(px * 15); }                 // px -> twips
  function num(v) { return parseFloat(v) || 0; }
  function xesc(s) { return String(s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }).replace(/[\u0000-\u0008\u000b\u000c\u000e-\u001f]/g, ''); }
  function hex6(c) {
    var m = String(c).match(/[\d.]+/g);
    if (!m) return '000000';
    return m.slice(0, 3).map(function (n) { return ('0' + Math.round(n).toString(16)).slice(-2); }).join('').toUpperCase();
  }
  function terlihat(el) { var cs = getComputedStyle(el); return cs.display !== 'none' && cs.visibility !== 'hidden'; }
  function blokLike(cs) { return /^(block|grid|flex|table|list-item|flow-root|table-row|table-cell)$/.test(cs.display); }
  function garis(cs, s) {
    var w = num(cs['border' + s + 'Width']), st = cs['border' + s + 'Style'];
    if (w <= 0 || st === 'none' || st === 'hidden') return null;
    return { sz: Math.max(2, Math.round(w * 0.75 * 8)), warna: hex6(cs['border' + s + 'Color']) };
  }
  function tepi(nama, g) { return '<w:' + nama + ' w:val="single" w:sz="' + g.sz + '" w:space="0" w:color="' + g.warna + '"/>'; }
  var wctx;

  function rpr(cs, ul) {
    var f = xesc(cs.fontFamily.split(',')[0].replace(/["']/g, '').trim() || 'Times New Roman'), x = '<w:rFonts w:ascii="' + f + '" w:hAnsi="' + f + '" w:eastAsia="' + f + '" w:cs="' + f + '"/>';
    if (+cs.fontWeight >= 600) x += '<w:b/>';
    if (cs.fontStyle === 'italic') x += '<w:i/>';
    var c = hex6(cs.color); if (c !== '000000') x += '<w:color w:val="' + c + '"/>';
    var hp = Math.max(2, Math.round(num(cs.fontSize) * 1.5));
    x += '<w:sz w:val="' + hp + '"/><w:szCs w:val="' + hp + '"/>';
    if (ul) x += '<w:u w:val="single"/>';
    return '<w:rPr>' + x + '</w:rPr>';
  }
  function bergaris(cs) { return cs.textDecorationLine.indexOf('underline') >= 0; }
  function tinggiBaris(cs) {
    var lh = num(cs.lineHeight);
    return lh > 0 ? lh : num(cs.fontSize) * 1.2;
  }
  function ppr(cs, ind, o) {
    o = o || {};
    var x = '';
    var bt = o.garis === false ? null : garis(cs, 'Top'), br = o.garis === false ? null : garis(cs, 'Right'), bb = o.garis === false ? null : garis(cs, 'Bottom'), bl = o.garis === false ? null : garis(cs, 'Left');
    if (bt || br || bb || bl) {
      x += '<w:pBdr>' + (bt ? tepi('top', bt) : '') + (bl ? tepi('left', bl) : '') + (bb ? tepi('bottom', bb) : '') + (br ? tepi('right', br) : '') + (bt && bb ? tepi('between', bb) : '') + '</w:pBdr>';
    }
    var line = o.tinggi !== undefined ? o.tinggi : tinggiBaris(cs);
    x += '<w:spacing w:before="' + tw(o.before || 0) + '" w:after="' + tw(o.after || 0) + '" w:line="' + tw(line) + '" w:lineRule="' + (o.pasti ? 'exact' : 'atLeast') + '"/>';
    var kiri = (ind ? ind.left : 0), kanan = (ind ? ind.right : 0);
    if (kiri > 0.5 || kanan > 0.5) x += '<w:ind w:left="' + tw(kiri) + '" w:right="' + tw(kanan) + '"/>';
    var a = cs.textAlign === 'start' ? 'left' : (cs.textAlign === 'end' ? 'right' : cs.textAlign);
    if (a === 'center' || a === 'right') x += '<w:jc w:val="' + a + '"/>';
    else if (a === 'justify') x += '<w:jc w:val="both"/>';
    return '<w:pPr>' + x + '</w:pPr>';
  }
  function paraXml(runs, cs, ind, o) {
    // buang spasi di awal/akhir paragraf
    var i, r;
    for (i = 0; i < runs.length; i++) { if (runs[i].t !== undefined) { runs[i].t = runs[i].t.replace(/^ +/, ''); if (runs[i].t) break; } else break; }
    for (i = runs.length - 1; i >= 0; i--) { if (runs[i].t !== undefined) { runs[i].t = runs[i].t.replace(/ +$/, ''); if (runs[i].t) break; } else break; }
    var isi = '';
    runs.forEach(function (r) {
      if (r.t !== undefined) { if (r.t) isi += '<w:r>' + r.rpr + '<w:t xml:space="preserve">' + xesc(r.t) + '</w:t></w:r>'; }
      else isi += r.x;
    });
    return '<w:p>' + ppr(cs, ind, o) + isi + '</w:p>';
  }
  function kecilP() { return '<w:p><w:pPr><w:spacing w:before="0" w:after="0" w:line="20" w:lineRule="exact"/></w:pPr><w:r><w:rPr><w:sz w:val="2"/></w:rPr></w:r></w:p>'; }
  function jarakP(px) {
    if (px < 1.5) return '';
    return '<w:p><w:pPr><w:spacing w:before="0" w:after="0" w:line="' + tw(px) + '" w:lineRule="exact"/></w:pPr><w:r><w:rPr><w:sz w:val="2"/></w:rPr></w:r></w:p>';
  }
  function patahP() { return '<w:p><w:pPr><w:spacing w:before="0" w:after="0" w:line="20" w:lineRule="exact"/></w:pPr><w:r><w:br w:type="page"/></w:r></w:p>'; }
  function patah(cs) { return cs.breakBefore === 'page' || cs.pageBreakBefore === 'always'; }

  function gambarRun(el) {
    var r = el.getBoundingClientRect();
    if (r.width < 1 || r.height < 1) return '';
    var url = el.currentSrc || el.src, ext = /\.(png|jpe?g|gif)(\?|$)/i.exec(url), e = ext ? ext[1].toLowerCase().replace('jpeg', 'jpg') : 'png', n = wctx.gambar.length + 1;
    var g = { url: url, nama: 'gambar' + n + '.' + e, mime: e === 'jpg' ? 'image/jpeg' : 'image/' + e, rid: 'rIdG' + n };
    wctx.gambar.push(g);
    var cx = Math.round(r.width * 9525), cy = Math.round(r.height * 9525);
    return '<w:r><w:drawing><wp:inline distT="0" distB="0" distL="0" distR="0"><wp:extent cx="' + cx + '" cy="' + cy + '"/><wp:docPr id="' + n + '" name="Gambar ' + n + '"/>'
      + '<a:graphic xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"><a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture">'
      + '<pic:pic xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture"><pic:nvPicPr><pic:cNvPr id="' + n + '" name="' + g.nama + '"/><pic:cNvPicPr/></pic:nvPicPr>'
      + '<pic:blipFill><a:blip r:embed="' + g.rid + '"/><a:stretch><a:fillRect/></a:stretch></pic:blipFill>'
      + '<pic:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="' + cx + '" cy="' + cy + '"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></pic:spPr></pic:pic>'
      + '</a:graphicData></a:graphic></wp:inline></w:drawing></w:r>';
  }

  /* run inline (span, b, u, dll.) dikumpulkan ke daftar runs */
  function kumpulRuns(el, cs, ul, runs) {
    var u = ul || bergaris(cs);
    Array.prototype.forEach.call(el.childNodes, function (n) {
      if (n.nodeType === 3) { var t = n.nodeValue.replace(/[ \t\r\n\f]+/g, ' '); if (t) runs.push({ t: t, rpr: rpr(cs, u) }); return; }
      if (n.nodeType !== 1 || !terlihat(n)) return;
      var c = getComputedStyle(n);
      if (n.tagName === 'BR') runs.push({ x: '<w:r><w:br/></w:r>' });
      else if (n.tagName === 'IMG') runs.push({ x: gambarRun(n) });
      else kumpulRuns(n, c, u, runs);
    });
  }

  /* isi sebuah elemen: paragraf dan blok anak. o = properti paragraf (before/after) untuk elemen daun. */
  function isiX(el, cs, ind, ul, o) {
    var u = ul || bergaris(cs), out = '', runs = [];
    function flush() {
      if (!runs.length) return;
      var ada = runs.some(function (r) { return r.t === undefined || /\S/.test(r.t); });
      if (ada) out += paraXml(runs, cs, ind, o);
      runs = [];
    }
    Array.prototype.forEach.call(el.childNodes, function (n) {
      if (n.nodeType === 3) { var t = n.nodeValue.replace(/[ \t\r\n\f]+/g, ' '); if (t) runs.push({ t: t, rpr: rpr(cs, u) }); return; }
      if (n.nodeType !== 1 || !terlihat(n)) return;
      var c = getComputedStyle(n);
      if (n.tagName === 'IMG') { runs.push({ x: gambarRun(n) }); return; }
      if (n.tagName === 'BR') { runs.push({ x: '<w:r><w:br/></w:r>' }); return; }
      if (blokLike(c) || n.tagName === 'TABLE') { flush(); out += blokX(n, c, ind, u); return; }
      kumpulRuns(n, c, u, runs);
    });
    flush();
    return out;
  }

  function adaBlokAnak(el) {
    return Array.prototype.some.call(el.children, function (n) { return terlihat(n) && (blokLike(getComputedStyle(n)) || n.tagName === 'TABLE'); });
  }

  function blokX(el, cs, ind, ul, maks) {
    if (!terlihat(el)) return '';
    if (el.tagName === 'TABLE') return tabelX(el, cs, ind, maks);
    if (cs.display === 'grid') return kisiX(el, cs, ind, maks);
    var xml = patah(cs) ? patahP() : '', r = el.getBoundingClientRect();
    var mt = num(cs.marginTop), mb = num(cs.marginBottom);
    var ind2 = { left: ind.left + num(cs.marginLeft) + num(cs.paddingLeft), right: ind.right + num(cs.marginRight) + num(cs.paddingRight) };
    var kosong = !el.textContent.trim() && !el.querySelector('img,table');
    if (kosong) {
      var punyaGaris = garis(cs, 'Top') || garis(cs, 'Bottom') || garis(cs, 'Left') || garis(cs, 'Right');
      if (r.height < 3 && !punyaGaris) return xml;
      return xml + '<w:p>' + ppr(cs, ind2, { before: mt, after: mb, tinggi: Math.max(1, r.height), pasti: true }) + '<w:r><w:rPr><w:sz w:val="2"/></w:rPr></w:r></w:p>';
    }
    if (adaBlokAnak(el)) {
      return xml + jarakP(mt) + isiX(el, cs, ind2, ul, { before: 0, after: 0 }) + jarakP(mb);
    }
    return xml + isiX(el, cs, ind2, ul, { before: mt, after: mb });
  }

  /* kolom dari geometri sel */
  function batasKolom(sel, kiri) {
    var xs = [];
    sel.forEach(function (td) { var r = td.getBoundingClientRect(); xs.push(Math.round(r.left - kiri), Math.round(r.right - kiri)); });
    xs.sort(function (a, b) { return a - b; });
    var h = []; xs.forEach(function (x) { if (!h.length || x - h[h.length - 1] > 2) h.push(x); });
    return h;
  }
  function posisi(batas, td, kiri) {
    var r = td.getBoundingClientRect(), a = Math.round(r.left - kiri), z = Math.round(r.right - kiri), mulai = -1, n = 0;
    for (var i = 1; i < batas.length; i++) if (batas[i] > a + 2 && batas[i] <= z + 2) { if (mulai < 0) mulai = i - 1; n++; }
    return { mulai: Math.max(0, mulai), span: Math.max(1, n) };
  }
  /* Ruang tambahan agar teks tidak terpotong ke baris baru akibat selisih metrik font antara browser dan Word. */
  function tersedia(el, cs) {
    var p = el.parentElement; if (!p) return 0;
    var pc = getComputedStyle(p);
    return p.clientWidth - num(pc.paddingLeft) - num(pc.paddingRight) - num(cs.marginLeft) - num(cs.marginRight);
  }
  function sesuaikan(gw, tambah, ruang) {
    var jml = tambah.reduce(function (a, b) { return a + b; }, 0), akhir = gw.length - 1;
    for (var i = 0; i < gw.length; i++) gw[i] += tambah[i] || 0;
    var lebih = Math.max(0, jml - Math.max(0, ruang));
    if (lebih > 0) gw[akhir] = Math.max(30, gw[akhir] - lebih);
    else if (ruang - jml > 2) gw[akhir] += Math.min(10, ruang - jml);
  }
  function selPr(lebar, span, cs, kontainer, va, tambahan) {
    var x = '<w:tcW w:w="' + tw(lebar) + '" w:type="dxa"/>' + (span > 1 ? '<w:gridSpan w:val="' + span + '"/>' : '');
    var t = garis(cs, 'Top') || (kontainer && garis(kontainer, 'Top')), b = garis(cs, 'Bottom') || (kontainer && garis(kontainer, 'Bottom')), l = garis(cs, 'Left'), rr = garis(cs, 'Right');
    if (t || b || l || rr) x += '<w:tcBorders>' + (t ? tepi('top', t) : '') + (l ? tepi('left', l) : '') + (b ? tepi('bottom', b) : '') + (rr ? tepi('right', rr) : '') + '</w:tcBorders>';
    var bg = cs.backgroundColor;
    if (bg && !/rgba\(0, 0, 0, 0\)|transparent/.test(bg)) x += '<w:shd w:val="clear" w:color="auto" w:fill="' + hex6(bg) + '"/>';
    var pt_ = tw(num(cs.paddingTop)), pl = tw(num(cs.paddingLeft) + ((tambahan && tambahan.kiri) || 0)), pb = tw(num(cs.paddingBottom) + ((tambahan && tambahan.bawah) || 0)), pr = tw(num(cs.paddingRight) + ((tambahan && tambahan.kanan) || 0));
    x += '<w:tcMar><w:top w:w="' + pt_ + '" w:type="dxa"/><w:left w:w="' + pl + '" w:type="dxa"/><w:bottom w:w="' + pb + '" w:type="dxa"/><w:right w:w="' + pr + '" w:type="dxa"/></w:tcMar>';
    x += '<w:vAlign w:val="' + (va || 'top') + '"/>';
    return '<w:tcPr>' + x + '</w:tcPr>';
  }
  function tabelPr(lebar, ind, gridCols, tinggi) {
    return '<w:tbl><w:tblPr><w:tblW w:w="' + tw(lebar) + '" w:type="dxa"/>' + (ind > 0.5 ? '<w:tblInd w:w="' + tw(ind) + '" w:type="dxa"/>' : '')
      + (tinggi || '') + '<w:tblLayout w:type="fixed"/><w:tblCellMar><w:top w:w="0" w:type="dxa"/><w:left w:w="0" w:type="dxa"/><w:bottom w:w="0" w:type="dxa"/><w:right w:w="0" w:type="dxa"/></w:tblCellMar></w:tblPr>'
      + '<w:tblGrid>' + gridCols + '</w:tblGrid>';
  }
  function isiSel(xml) {
    if (!xml) return '<w:p/>';
    return /<\/w:tbl>$/.test(xml) ? xml + kecilP() : xml;
  }

  function tabelX(el, cs, ind, maks) {
    var r = el.getBoundingClientRect(), sel = [];
    Array.prototype.forEach.call(el.rows, function (tr) { Array.prototype.forEach.call(tr.cells, function (td) { if (terlihat(td)) sel.push(td); }); });
    if (!sel.length) return '';
    var batas = batasKolom(sel, r.left), gw = [], tambah = [], i;
    for (i = 1; i < batas.length; i++) { gw.push(batas[i] - batas[i - 1]); tambah.push(0); }
    sel.forEach(function (td) {
      var ps = posisi(batas, td, r.left);
      if (ps.span === 1 && /nowrap/.test(getComputedStyle(td).whiteSpace)) tambah[ps.mulai] = 4;
    });
    sesuaikan(gw, tambah, (maks !== undefined ? maks : tersedia(el, cs)) - r.width);
    var grid = '', lebar = 0;
    gw.forEach(function (w) { grid += '<w:gridCol w:w="' + tw(w) + '"/>'; lebar += w; });
    var bt = garis(cs, 'Top'), pr = bt ? '<w:tblBorders>' + tepi('top', bt) + '</w:tblBorders>' : '';
    var xml = jarakP(num(cs.marginTop)) + tabelPr(lebar, ind.left + num(cs.marginLeft), grid, pr);
    Array.prototype.forEach.call(el.rows, function (tr) {
      xml += '<w:tr><w:trPr><w:cantSplit/><w:trHeight w:val="' + tw(tr.getBoundingClientRect().height) + '" w:hRule="atLeast"/></w:trPr>';
      Array.prototype.forEach.call(tr.cells, function (td) {
        if (!terlihat(td)) return;
        var c = getComputedStyle(td), ps = posisi(batas, td, r.left), w = 0, va = c.verticalAlign === 'middle' ? 'center' : (c.verticalAlign === 'bottom' ? 'bottom' : 'top');
        for (var j = ps.mulai; j < ps.mulai + ps.span && j < gw.length; j++) w += gw[j];
        xml += '<w:tc>' + selPr(w, ps.span, c, null, va) + isiSel(isiX(td, c, { left: 0, right: 0 }, false, { before: 0, after: 0, garis: false })) + '</w:tc>';
      });
      xml += '</w:tr>';
    });
    return xml + '</w:tbl>' + (num(cs.marginBottom) >= 1.5 ? jarakP(num(cs.marginBottom)) : kecilP());
  }

  function kisiX(el, cs, ind, maks) {
    var kolom = cs.gridTemplateColumns.split(/\s+/).map(parseFloat).filter(function (n) { return !isNaN(n); }),
        anak = Array.prototype.filter.call(el.children, terlihat);
    if (!kolom.length || !anak.length) return '';
    var n = kolom.length, gk = num(cs.columnGap), gb = num(cs.rowGap), baris = Math.ceil(anak.length / n), q;
    // lebar kolom terlihat + ruang tambahan (label sempit tidak terpotong)
    var idx = [], gw = [], tambah = [];
    for (q = 0; q < n; q++) if (kolom[q] >= 1) { idx.push(q); gw.push(kolom[q] + (q < n - 1 ? gk : 0)); tambah.push(0); }
    for (q = 0; q < idx.length - 1; q++) tambah[q] = 4;
    var lebar0 = gw.reduce(function (a, b) { return a + b; }, 0);
    sesuaikan(gw, tambah, (maks !== undefined ? maks : tersedia(el, cs)) - lebar0);
    var lebar = gw.reduce(function (a, b) { return a + b; }, 0), grid = gw.map(function (w) { return '<w:gridCol w:w="' + tw(w) + '"/>'; }).join('');
    var va = cs.alignItems === 'center' ? 'center' : (cs.alignItems === 'end' || cs.alignItems === 'flex-end' ? 'bottom' : 'top');
    var tunggal = baris === 1 ? Math.max(0, el.getBoundingClientRect().height - num(cs.paddingTop) - num(cs.paddingBottom) - num(cs.borderTopWidth) - num(cs.borderBottomWidth)) : 0;
    var xml = (patah(cs) ? patahP() : '') + jarakP(num(cs.marginTop)) + tabelPr(lebar, ind.left + num(cs.marginLeft) + num(cs.paddingLeft), grid, '');
    for (var b = 0; b < baris; b++) {
      xml += '<w:tr><w:trPr><w:cantSplit/>' + (tunggal > 2 ? '<w:trHeight w:val="' + tw(tunggal) + '" w:hRule="atLeast"/>' : '') + '</w:trPr>';
      for (var k = 0; k < n; k++) {
        var a = anak[b * n + k], pos = idx.indexOf(k);
        if (pos < 0 || !a) continue;
        var c = getComputedStyle(a), w = gw[pos], dalam = w - num(c.paddingLeft) - (k < n - 1 ? gk : 0), isi;
        if (a.tagName === 'TABLE' || c.display === 'grid') isi = blokX(a, c, { left: 0, right: 0 }, false, dalam);
        else if (a.tagName === 'IMG') isi = '<w:p>' + ppr(c, { left: 0, right: 0 }, { tinggi: 1, garis: false }) + gambarRun(a) + '</w:p>';
        else isi = isiX(a, c, { left: 0, right: 0 }, false, { before: 0, after: 0, garis: false });
        xml += '<w:tc>' + selPr(w, 1, c, cs, va, { kanan: (k < n - 1 ? gk : 0), bawah: (b < baris - 1 ? gb : 0) }) + isiSel(isi) + '</w:tc>';
      }
      xml += '</w:tr>';
    }
    return xml + '</w:tbl>' + (num(cs.marginBottom) >= 1.5 ? jarakP(num(cs.marginBottom)) : kecilP());
  }

  /* ---------- paket .docx (ZIP tanpa kompresi) ---------- */
  var CRC = (function () { var t = [], c, n, k; for (n = 0; n < 256; n++) { c = n; for (k = 0; k < 8; k++) c = c & 1 ? 0xEDB88320 ^ (c >>> 1) : c >>> 1; t[n] = c >>> 0; } return t; })();
  function crc32(b) { var c = 0xFFFFFFFF; for (var i = 0; i < b.length; i++) c = CRC[(c ^ b[i]) & 0xFF] ^ (c >>> 8); return (c ^ 0xFFFFFFFF) >>> 0; }
  function zip(berkas) {
    var enc = new TextEncoder(), bagian = [], pusat = [], offset = 0, d = new Date();
    var wkt = (d.getHours() << 11) | (d.getMinutes() << 5) | (d.getSeconds() >> 1), tgl = ((d.getFullYear() - 1980) << 9) | ((d.getMonth() + 1) << 5) | d.getDate();
    berkas.forEach(function (f) {
      var nm = enc.encode(f.nama), crc = crc32(f.data), lh = new DataView(new ArrayBuffer(30));
      lh.setUint32(0, 0x04034b50, true); lh.setUint16(4, 20, true); lh.setUint16(6, 0x0800, true); lh.setUint16(8, 0, true);
      lh.setUint16(10, wkt, true); lh.setUint16(12, tgl, true); lh.setUint32(14, crc, true); lh.setUint32(18, f.data.length, true); lh.setUint32(22, f.data.length, true);
      lh.setUint16(26, nm.length, true); lh.setUint16(28, 0, true);
      bagian.push(new Uint8Array(lh.buffer), nm, f.data);
      var ch = new DataView(new ArrayBuffer(46));
      ch.setUint32(0, 0x02014b50, true); ch.setUint16(4, 20, true); ch.setUint16(6, 20, true); ch.setUint16(8, 0x0800, true); ch.setUint16(10, 0, true);
      ch.setUint16(12, wkt, true); ch.setUint16(14, tgl, true); ch.setUint32(16, crc, true); ch.setUint32(20, f.data.length, true); ch.setUint32(24, f.data.length, true);
      ch.setUint16(28, nm.length, true); ch.setUint32(42, offset, true);
      pusat.push(new Uint8Array(ch.buffer), nm);
      offset += 30 + nm.length + f.data.length;
    });
    var ukuranPusat = pusat.reduce(function (a, b) { return a + b.length; }, 0), ek = new DataView(new ArrayBuffer(22));
    ek.setUint32(0, 0x06054b50, true); ek.setUint16(8, berkas.length, true); ek.setUint16(10, berkas.length, true); ek.setUint32(12, ukuranPusat, true); ek.setUint32(16, offset, true);
    return new Blob(bagian.concat(pusat, [new Uint8Array(ek.buffer)]), { type: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' });
  }

  function dokumenDocx() {
    wctx = { gambar: [] };
    var inner = $('inner'), ics = getComputedStyle(inner), p = kertas(), tmm = 56.6929;
    var badan = blokX(inner, ics, { left: 0, right: 0 }, false);
    if (!badan || /<\/w:tbl>$/.test(badan)) badan += kecilP();
    var sect = '<w:sectPr><w:pgSz w:w="' + Math.round(p.W * tmm) + '" w:h="' + Math.round(p.H * tmm) + '"' + (p.W > p.H ? ' w:orient="landscape"' : '') + '/>'
      + '<w:pgMar w:top="' + Math.round(p.mt * tmm) + '" w:right="' + Math.round(p.mr * tmm) + '" w:bottom="' + Math.round(p.mb * tmm) + '" w:left="' + Math.round(p.ml * tmm) + '" w:header="0" w:footer="0" w:gutter="0"/></w:sectPr>';
    var ns = 'xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing"';
    var f = xesc(ics.fontFamily.split(',')[0].replace(/["']/g, '').trim() || 'Times New Roman');
    var doc = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:document ' + ns + '><w:body>' + badan + sect + '</w:body></w:document>';
    var styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:docDefaults><w:rPrDefault><w:rPr><w:rFonts w:ascii="' + f + '" w:hAnsi="' + f + '" w:eastAsia="' + f + '" w:cs="' + f + '"/><w:sz w:val="' + Math.round(num(ics.fontSize) * 1.5) + '"/><w:szCs w:val="' + Math.round(num(ics.fontSize) * 1.5) + '"/><w:lang w:val="id-ID"/></w:rPr></w:rPrDefault><w:pPrDefault><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/></w:pPr></w:pPrDefault></w:docDefaults><w:style w:type="paragraph" w:default="1" w:styleId="Normal"><w:name w:val="Normal"/><w:qFormat/></w:style></w:styles>';
    var rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rIdS" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
      + wctx.gambar.map(function (g) { return '<Relationship Id="' + g.rid + '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/' + g.nama + '"/>'; }).join('') + '</Relationships>';
    var tipe = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Default Extension="png" ContentType="image/png"/><Default Extension="jpg" ContentType="image/jpeg"/><Default Extension="gif" ContentType="image/gif"/><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/><Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/></Types>';
    var rootRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/></Relationships>';
    var enc = new TextEncoder();
    return { gambar: wctx.gambar, berkas: [
      { nama: '[Content_Types].xml', data: enc.encode(tipe) }, { nama: '_rels/.rels', data: enc.encode(rootRels) },
      { nama: 'word/document.xml', data: enc.encode(doc) }, { nama: 'word/styles.xml', data: enc.encode(styles) },
      { nama: 'word/_rels/document.xml.rels', data: enc.encode(rels) }] };
  }
  function ambilGambar(g) {
    return fetch(g.url).then(function (r) { return r.arrayBuffer(); }).then(function (b) { g.data = new Uint8Array(b); }).catch(function () { g.data = null; });
  }
  function buatDocx() {
    var d = dokumenDocx();
    return Promise.all(d.gambar.map(ambilGambar)).then(function () {
      d.gambar.forEach(function (g) { if (g.data) d.berkas.push({ nama: 'word/media/' + g.nama, data: g.data }); });
      // gambar yang gagal diunduh: hapus rujukannya agar berkas tetap valid
      var bad = d.gambar.filter(function (g) { return !g.data; });
      if (bad.length) {
        var dok = d.berkas[2], rel = d.berkas[4], t = new TextDecoder().decode(dok.data), rl = new TextDecoder().decode(rel.data);
        bad.forEach(function (g) {
          t = t.replace(new RegExp('<w:r><w:drawing>(?:(?!</w:drawing>).)*?r:embed="' + g.rid + '".*?</w:drawing></w:r>', 's'), '');
          rl = rl.replace(new RegExp('<Relationship Id="' + g.rid + '"[^>]*/>'), '');
        });
        dok.data = new TextEncoder().encode(t); rel.data = new TextEncoder().encode(rl);
      }
      return zip(d.berkas);
    });
  }
  function unduhWord() {
    status('Menyiapkan dokumen Word...');
    buatDocx().then(function (blob) { simpanBlob(blob, nama() + '.docx'); })
      .catch(function (e) { alert('Dokumen Word belum bisa dibuat: ' + e.message); })
      .then(function () { status(''); });
  }

  $('unduhPdf').addEventListener('click', unduhPdf);
  $('unduhWord').addEventListener('click', unduhWord);
})();
</script>
<?= $this->endSection() ?>
