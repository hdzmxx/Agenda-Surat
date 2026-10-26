<?= $this->extend('layout') ?>
<?= $this->section('head') ?>
<style id="attendance-page-rule">@page{size:210mm 297mm;margin:12mm}</style>
<style>
  :root{--attendance-width:210mm;--attendance-height:297mm}
  .attendance-toolbar{position:sticky;top:56px;z-index:5;display:flex;flex-wrap:wrap;align-items:center;gap:.75rem 1rem;background:var(--bs-body-bg);border:1px solid var(--bs-border-color);border-radius:8px;padding:10px 12px;margin-bottom:14px}
  .attendance-toolbar.is-editing{border-color:#0d6efd;box-shadow:inset 0 0 0 1px #0d6efd}
  .attendance-toolbar .bar{display:flex;flex-wrap:wrap;align-items:center;gap:.5rem}
  .attendance-toolbar .bar[hidden]{display:none}
  .attendance-toolbar .bar-aksi{margin-left:auto}
  .attendance-toolbar label{margin:0;white-space:nowrap}
  .attendance-wrap{overflow-x:auto;padding-bottom:14px}
  .attendance-sheet{width:var(--attendance-width);min-height:var(--attendance-height);margin:0 auto;padding:12mm;background:#fff;color:#000;box-shadow:0 1px 4px rgba(0,0,0,.3);font-family:"Times New Roman",serif}
  .attendance-kop{display:grid;grid-template-columns:24mm 1fr 24mm;align-items:center;border-bottom:3px double #000;padding-bottom:2mm;margin-bottom:2mm;text-align:center}
  .attendance-kop img{width:20mm;height:20mm;object-fit:contain;justify-self:start}
  .attendance-kop .kop-line{font-size:12pt;line-height:1.2}
  .attendance-kop .kop-name{font-size:15pt;font-weight:700;line-height:1.25}
  .attendance-kop .kop-address{font-size:9pt;line-height:1.25}
  .attendance-heading{text-align:center;font-size:13pt;font-weight:700;margin:1mm 0}
  .attendance-theme{text-align:center;font-size:11pt;font-weight:700;margin:0}
  .attendance-date{text-align:center;font-size:11pt;font-weight:700;margin:1mm 0 3mm}
  .attendance-table{width:100%;border-collapse:collapse;font-size:11pt}
  .attendance-table th,.attendance-table td{border:1px solid #000;padding:2px 4px;vertical-align:middle}
  .attendance-table th{text-align:center}
  .attendance-table td:nth-child(1),.attendance-table .signature-cell{text-align:center}
  .attendance-table tbody tr:nth-child(odd) .signature-cell{text-align:left}
  .attendance-table tbody tr:nth-child(even) .signature-cell{text-align:center}
  .attendance-table .nm{line-height:1.15}
  .attendance-table .idn{line-height:1.15;font-size:.9em;color:#000}
  .attendance-sheet .opt:empty{display:none}
  .attendance-table .edit-col{display:none;width:1%;white-space:nowrap;text-align:center}
  .attendance-signature{width:78mm;margin:9mm 10mm 0 auto;font-size:11pt;line-height:1.5}
  .attendance-signature .signature-space{height:23mm}
  .attendance-signature .signature-id{line-height:1.15}
  .attendance-signature .sig-nama{font-weight:700}
  /* Mode edit manual */
  .attendance-sheet.editing .attendance-table .edit-col{display:table-cell}
  .attendance-sheet.editing [data-f]{outline:1px dashed #6c8cd5;outline-offset:-1px;background:#f4f8ff;cursor:text}
  .attendance-sheet.editing [data-f]:focus{outline:2px solid #0d6efd;background:#fff}
  .attendance-sheet.editing .opt:empty{display:block;min-height:1.1em}
  .attendance-sheet.editing [data-f]:empty::before{content:attr(data-ph);color:#8a8a8a}
  @media(max-width:767.98px){.attendance-sheet{width:100%;min-height:0;padding:7mm}.attendance-kop{grid-template-columns:16mm 1fr 16mm}.attendance-kop img{width:14mm;height:14mm}.attendance-table{font-size:9pt}}
  @media print{
    .app-topbar,.app-sidebar,.app-backdrop,.attendance-toolbar,.alert{display:none!important}
    .app-main{margin:0!important;padding:0!important;min-height:0!important}
    .app-content{max-width:none!important}
    body{background:#fff!important}
    .attendance-wrap{overflow:visible;padding:0}
    .attendance-sheet{width:auto;min-height:0;margin:0;padding:0;box-shadow:none}
    .attendance-table thead{display:table-header-group}
    .attendance-table tr{break-inside:avoid;page-break-inside:avoid}
    .attendance-table .edit-col{display:none!important}
    .attendance-sheet [data-f]{outline:none!important;background:none!important}
    .attendance-sheet [data-f]:empty::before{content:none!important}
    .attendance-sheet .opt:empty{display:none!important}
  }
</style>
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<?php
helper('kop');
$kop = kop_settings();
$onlyGuru = ! $data['peserta_siswa'];
$hasStaff = $data['peserta_guru'] || $data['peserta_tamu'];
$jabatanHeader = $onlyGuru ? 'JABATAN' : ($hasStaff ? 'JABATAN / KELAS' : 'KELAS');
$tanggalKegiatan = mb_strtoupper(hari_id($data['tanggal']) . ', ' . tgl_id($data['tanggal']));

/** Teks judul dan tanda tangan: hasil edit manual bila ada, selain itu bawaan dari formulir. */
$teks = $teks ?? [];
$t = static fn (string $kunci, string $bawaan): string => array_key_exists($kunci, $teks) ? (string) $teks[$kunci] : $bawaan;

/** Teks identitas di bawah nama: "NIP. ...", "NISN. ...", atau teks bebas. */
$labelIdentitas = static function (array $o): string {
    $id = trim((string) ($o['identitas'] ?? ''));
    if ($id === '-') {
        $id = '';
    }
    $jenisId = (string) ($o['jenis_identitas'] ?? 'manual');
    if (! isset($o['tipe']) && $jenisId === 'manual' && str_starts_with((string) ($o['jabatan'] ?? ''), 'Siswa')) {
        $jenisId = 'nisn'; // data lama: identitas siswa belum diberi awalan
    }

    return match ($jenisId) {
        'nip'    => 'NIP. ' . ($id !== '' ? $id : '-'),
        'nisn'   => 'NISN. ' . ($id !== '' ? $id : '-'),
        'nipd'   => 'NIPD. ' . $id,
        'kosong' => '',
        default  => $id, // 'manual' dan 'teks' dicetak apa adanya
    };
};
$aksiBaris = '<button type="button" class="btn btn-sm btn-outline-secondary px-2 py-0 row-up" title="Naikkan">&#9650;</button> '
    . '<button type="button" class="btn btn-sm btn-outline-secondary px-2 py-0 row-down" title="Turunkan">&#9660;</button> '
    . '<button type="button" class="btn btn-sm btn-outline-danger px-2 py-0 row-del" title="Hapus baris">&#10005;</button>';
?>
<div class="attendance-toolbar" id="attendance-toolbar">
  <div class="bar">
    <label class="d-flex align-items-center gap-2 small text-muted" for="attendance-paper">Kertas
      <select class="form-select form-select-sm w-auto" id="attendance-paper">
        <option value="a4" selected>A4 (210 x 297 mm)</option>
        <option value="f4">F4 / Folio (215 x 330 mm)</option>
        <option value="letter">Letter (216 x 279 mm)</option>
        <option value="legal">Legal (216 x 356 mm)</option>
      </select>
    </label>
    <label class="d-flex align-items-center gap-2 small text-muted" for="attendance-orientation">Orientasi
      <select class="form-select form-select-sm w-auto" id="attendance-orientation">
        <option value="portrait" selected>Portrait</option>
        <option value="landscape">Landscape</option>
      </select>
    </label>
  </div>
  <div class="bar bar-aksi" id="bar-normal">
    <a class="btn btn-sm btn-outline-secondary btn-pill" href="<?= site_url('daftar-hadir') ?>">Kembali ke daftar</a>
    <a class="btn btn-sm btn-outline-secondary btn-pill" href="<?= site_url('daftar-hadir/' . $id . '/ubah') ?>">Ubah lewat formulir</a>
    <button class="btn btn-sm btn-outline-primary btn-pill" type="button" id="btn-edit">Edit manual</button>
    <button class="btn btn-sm btn-success btn-pill" type="button" onclick="window.print()">Cetak</button>
  </div>
  <div class="bar bar-aksi" id="bar-edit" hidden>
    <button class="btn btn-sm btn-outline-primary btn-pill" type="button" id="btn-add">Tambah baris</button>
    <button class="btn btn-sm btn-success btn-pill" type="button" id="btn-save">Simpan perubahan</button>
    <button class="btn btn-sm btn-outline-secondary btn-pill" type="button" id="btn-cancel">Batal</button>
  </div>
</div>
<form id="manual-form" method="post" action="<?= site_url('daftar-hadir/' . $id . '/manual') ?>" hidden>
  <?= csrf_field() ?>
  <input type="hidden" name="rows" id="manual-rows">
  <input type="hidden" name="teks" id="manual-teks">
</form>
<div class="attendance-wrap">
  <article class="attendance-sheet">
    <header class="attendance-kop">
      <img src="<?= base_url(kop_logo_path()) ?>" alt="Logo sekolah" onerror="this.style.visibility='hidden'">
      <div>
        <div class="kop-line"><?= esc($kop['pemerintah']) ?></div>
        <div class="kop-line"><?= esc($kop['dinas']) ?></div>
        <div class="kop-name"><?= esc(mb_strtoupper($kop['instansi'])) ?></div>
        <?php foreach ($kop['alamat'] as $baris): ?><div class="kop-address"><?= esc($baris) ?></div><?php endforeach ?>
      </div>
      <div></div>
    </header>
    <h1 class="attendance-heading" data-f="h_judul" data-ph="Judul daftar hadir"><?= esc($t('h_judul', 'DAFTAR HADIR KEGIATAN ' . mb_strtoupper($data['judul']))) ?></h1>
    <p class="attendance-theme opt" data-f="h_tema" data-ph="Tema atau keterangan (opsional)"><?= esc($t('h_tema', $data['tema'] !== '' ? '"' . $data['tema'] . '"' : '')) ?></p>
    <p class="attendance-date" data-f="h_tanggal" data-ph="Hari, tanggal"><?= esc($t('h_tanggal', $tanggalKegiatan)) ?></p>
    <table class="attendance-table">
      <thead><tr>
        <th style="width:5%">NO</th>
        <th style="width:48%">NAMA</th>
        <th style="width:22%"><?= esc($jabatanHeader) ?></th>
        <th style="width:25%">TANDA TANGAN</th>
        <th class="edit-col">AKSI</th>
      </tr></thead>
      <tbody>
        <?php foreach ($peserta as $index => $orang): ?>
          <tr data-tipe="<?= esc((string) ($orang['tipe'] ?? ''), 'attr') ?>">
            <td class="no"><?= $index + 1 ?></td>
            <td>
              <div class="nm" data-f="nama" data-ph="Nama"><?= esc($orang['nama']) ?></div>
              <div class="idn opt" data-f="identitas" data-ph="NIP / NISN (opsional)"><?= esc($labelIdentitas($orang)) ?></div>
            </td>
            <td data-f="jabatan" data-ph="Jabatan / kelas"><?= esc($orang['jabatan']) ?></td>
            <td class="signature-cell"><span class="no2"><?= $index + 1 ?></span></td>
            <td class="edit-col"><?= $aksiBaris ?></td>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table>
    <div class="attendance-signature">
      <div data-f="t_kota" data-ph="Kota, tanggal"><?= esc($t('t_kota', $data['kota'] . ', ' . tgl_id($data['tanggal']))) ?></div>
      <div data-f="t_jabatan" data-ph="Jabatan"><?= esc($t('t_jabatan', $data['jabatan'] . ',')) ?></div>
      <div class="signature-space"></div>
      <div class="signature-id">
        <div class="sig-nama" data-f="t_nama" data-ph="Nama"><?= esc($t('t_nama', $data['nama'])) ?></div>
        <div class="opt" data-f="t_nip" data-ph="NIP (opsional)"><?= esc($t('t_nip', $data['nip'] !== '' ? 'NIP. ' . $data['nip'] : '')) ?></div>
      </div>
    </div>
  </article>
</div>
<script>
  (function () {
    var KEY = 'daftarhadir-cetak',
        sizes = {a4: [210, 297], f4: [215, 330], letter: [215.9, 279.4], legal: [215.9, 355.6]},
        paper = document.getElementById('attendance-paper'),
        orientation = document.getElementById('attendance-orientation'),
        pageRule = document.getElementById('attendance-page-rule'),
        sheet = document.querySelector('.attendance-sheet'),
        tbody = sheet.querySelector('.attendance-table tbody');

    try {
      var saved = JSON.parse(localStorage.getItem(KEY) || '{}');
      if (sizes[saved.kertas]) paper.value = saved.kertas;
      if (saved.orientasi === 'portrait' || saved.orientasi === 'landscape') orientation.value = saved.orientasi;
    } catch (e) {}

    function applyPage() {
      var wh = sizes[paper.value] || sizes.a4,
          land = orientation.value === 'landscape',
          w = land ? wh[1] : wh[0],
          h = land ? wh[0] : wh[1];
      document.documentElement.style.setProperty('--attendance-width', w + 'mm');
      document.documentElement.style.setProperty('--attendance-height', h + 'mm');
      pageRule.textContent = '@page{size:' + w + 'mm ' + h + 'mm;margin:12mm}';
      sheet.dataset.orientation = orientation.value;
      try { localStorage.setItem(KEY, JSON.stringify({kertas: paper.value, orientasi: orientation.value})); } catch (e) {}
    }
    paper.addEventListener('change', applyPage);
    orientation.addEventListener('change', applyPage);
    applyPage();

    /* ---- Edit manual ---- */
    var barNormal = document.getElementById('bar-normal'),
        barEdit = document.getElementById('bar-edit'),
        aksiHtml = <?= json_encode($aksiBaris) ?>;
    function setEditable(root, on) {
      root.querySelectorAll('[data-f]').forEach(function (el) {
        if (on) el.setAttribute('contenteditable', 'true'); else el.removeAttribute('contenteditable');
      });
    }
    function renumber() {
      tbody.querySelectorAll('tr').forEach(function (tr, i) {
        tr.querySelector('.no').textContent = i + 1;
        tr.querySelector('.no2').textContent = i + 1;
      });
    }
    document.getElementById('btn-edit').addEventListener('click', function () {
      sheet.classList.add('editing');
      document.getElementById('attendance-toolbar').classList.add('is-editing');
      setEditable(sheet, true);
      barNormal.hidden = true;
      barEdit.hidden = false;
    });
    document.getElementById('btn-cancel').addEventListener('click', function () { location.reload(); });
    document.getElementById('btn-add').addEventListener('click', function () {
      var tr = document.createElement('tr');
      tr.dataset.tipe = 'tamu';
      tr.innerHTML = '<td class="no"></td><td><div class="nm" data-f="nama" data-ph="Nama"></div>'
        + '<div class="idn opt" data-f="identitas" data-ph="NIP / NISN (opsional)"></div></td>'
        + '<td data-f="jabatan" data-ph="Jabatan / kelas"></td>'
        + '<td class="signature-cell"><span class="no2"></span></td><td class="edit-col">' + aksiHtml + '</td>';
      tbody.appendChild(tr);
      setEditable(tr, true);
      renumber();
      tr.querySelector('[data-f="nama"]').focus();
    });
    tbody.addEventListener('click', function (e) {
      var tr = e.target.closest('tr');
      if (!tr) return;
      if (e.target.closest('.row-del')) {
        if (confirm('Hapus baris ini?')) { tr.remove(); renumber(); }
      } else if (e.target.closest('.row-up') && tr.previousElementSibling) {
        tbody.insertBefore(tr, tr.previousElementSibling); renumber();
      } else if (e.target.closest('.row-down') && tr.nextElementSibling) {
        tbody.insertBefore(tr.nextElementSibling, tr); renumber();
      }
    });
    sheet.addEventListener('paste', function (e) {
      if (!sheet.classList.contains('editing') || !e.target.closest('[data-f]')) return;
      e.preventDefault();
      var teks = (e.clipboardData || window.clipboardData).getData('text').replace(/\s*\n+\s*/g, ' ');
      document.execCommand('insertText', false, teks);
    });
    sheet.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' && e.target.closest('[data-f]')) e.preventDefault();
    });
    document.getElementById('btn-save').addEventListener('click', function () {
      var rows = [];
      tbody.querySelectorAll('tr').forEach(function (tr) {
        function nilai(f) {
          var el = tr.querySelector('[data-f="' + f + '"]');
          return el ? el.textContent.replace(/\s+/g, ' ').trim() : '';
        }
        rows.push({nama: nilai('nama'), identitas: nilai('identitas'), jabatan: nilai('jabatan'), tipe: tr.dataset.tipe || ''});
      });
      var teks = {};
      sheet.querySelectorAll('[data-f]').forEach(function (el) {
        if (!el.closest('tbody')) teks[el.dataset.f] = el.textContent.replace(/\s+/g, ' ').trim();
      });
      document.getElementById('manual-rows').value = JSON.stringify(rows);
      document.getElementById('manual-teks').value = JSON.stringify(teks);
      document.getElementById('manual-form').submit();
    });
  })();
</script>
<?= $this->endSection() ?>