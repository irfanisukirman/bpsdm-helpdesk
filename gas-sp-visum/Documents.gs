/**
 * Documents.gs — Perakitan HTML dokumen + ekspor PDF.
 *
 * Satu berkas HTML dirakit untuk tiap dokumen, lalu dikonversi menjadi PDF
 * melalui Google Docs (PRD §7: "Template Google Docs → ekspor PDF").
 * HTML yang sama dipakai untuk pratinjau langsung di formulir, sehingga
 * tampilan di layar identik dengan berkas cetak.
 *
 * Amanat penting (PRD §3.1 & §8): blok tanda tangan HANYA berisi teks;
 * ruang TTE dikosongkan agar konsep tidak tampak sah. TTE dibubuhkan Sidebar.
 */

var BULAN = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
             'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

function fmtTgl_(iso) {
  if (!iso) return '…………………';
  var d = new Date(iso + 'T00:00:00');
  return d.getDate() + ' ' + BULAN[d.getMonth()] + ' ' + d.getFullYear();
}

/** Terbilang bahasa Indonesia (cukup untuk lama hari). */
function terbilang_(n) {
  n = Math.floor(Math.abs(n));
  var s = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh',
           'delapan', 'sembilan', 'sepuluh', 'sebelas'];
  if (n < 12) return s[n];
  if (n < 20) return terbilang_(n - 10) + ' belas';
  if (n < 100) return terbilang_(Math.floor(n / 10)) + ' puluh' + (n % 10 ? ' ' + terbilang_(n % 10) : '');
  if (n < 200) return 'seratus' + (n % 100 ? ' ' + terbilang_(n % 100) : '');
  if (n < 1000) return terbilang_(Math.floor(n / 100)) + ' ratus' + (n % 100 ? ' ' + terbilang_(n % 100) : '');
  if (n < 2000) return 'seribu' + (n % 1000 ? ' ' + terbilang_(n % 1000) : '');
  if (n < 1000000) return terbilang_(Math.floor(n / 1000)) + ' ribu' + (n % 1000 ? ' ' + terbilang_(n % 1000) : '');
  return String(n);
}

function lamaHari_(a, b) {
  if (!a) return 1;
  if (!b) b = a;
  var d1 = new Date(a + 'T00:00:00'), d2 = new Date(b + 'T00:00:00');
  return Math.max(1, Math.round((d2 - d1) / 86400000) + 1);
}

function esc_(s) {
  return String(s == null ? '' : s).replace(/[&<>]/g, function (c) {
    return { '&': '&amp;', '<': '&lt;', '>': '&gt;' }[c];
  });
}

/** "BADAN PENGEMBANGAN SUMBER DAYA MANUSIA" → "Badan Pengembangan Sumber Daya Manusia". */
function titleCase_(s) {
  return String(s || '').toLowerCase().replace(/(^|\s)\S/g, function (m) { return m.toUpperCase(); });
}

/**
 * Menormalkan payload dari formulir menjadi objek data lengkap: menggabungkan
 * isian pengguna dengan catatan pegawai/paket/referensi dari Spreadsheet.
 */
function resolveData_(payload) {
  var ref = getReferensi();
  var val = function (k, fb) {
    var v = payload && payload[k] != null ? String(payload[k]).trim() : '';
    return v || (fb != null ? fb : '');
  };
  var lama = lamaHari_(payload.tgl_berangkat, payload.tgl_kembali);
  return {
    nomor_sp: val('nomor_sp'),
    tgl_surat: payload.tgl_surat || '',
    paket: getPaketById_(payload.paket),
    pengundang: val('pengundang'),
    und_nomor: val('und_nomor'),
    und_tgl: payload.und_tgl || '',
    und_hal: val('und_hal'),
    maksud: val('maksud'),
    tempat: val('tempat'),
    alamat: val('alamat'),
    kota: val('kota'),
    angkutan: val('angkutan'),
    tgl_berangkat: payload.tgl_berangkat || '',
    tgl_kembali: payload.tgl_kembali || payload.tgl_berangkat || '',
    lama: lama,
    lamaTxt: lama + ' (' + terbilang_(lama) + ') hari',
    pegawai: getPegawaiByIds_(payload.pegawai || []),
    pj: {
      nama: val('pj_nama'), jabatan: val('pj_jabatan'),
      pangkat: val('pj_pangkat'), nip: val('pj_nip')
    },
    ref: {
      kedudukan: val('kedudukan', ref.kedudukan || 'Kota Cimahi'),
      ttd_jabatan: val('ttd_jabatan', ref.ttd_jabatan || 'Sekretaris'),
      ttd_nama: val('ttd_nama', ref.ttd_nama || ''),
      ttd_pangkat: val('ttd_pangkat', ref.ttd_pangkat || ''),
      ttd_nip: val('ttd_nip', ref.ttd_nip || ''),
      kpa_nama: val('kpa_nama', ref.kpa_nama || ''),
      kpa_nip: val('kpa_nip', ref.kpa_nip || ''),
      nama_instansi: ref.nama_instansi || 'BADAN PENGEMBANGAN SUMBER DAYA MANUSIA',
      nama_pemda: ref.nama_pemda || 'PEMERINTAH DAERAH PROVINSI JAWA BARAT',
      alamat_instansi: ref.alamat_instansi || '',
      laman_instansi: ref.laman_instansi || ''
    }
  };
}

/* --------------------------------------------------------------- Gaya doc */

function docCss_() {
  return '<style>'
    + '@page{size:A4;margin:0}'
    + 'body{font-family:"Times New Roman",Georgia,serif;color:#141414;font-size:12pt;line-height:1.4;margin:0}'
    + '.page{padding:22mm 20mm;position:relative;page-break-after:always}'
    + '.page:last-child{page-break-after:auto}'
    + '.wm{position:absolute;top:42%;left:0;right:0;text-align:center;font-family:Arial,sans-serif;'
    + 'font-size:42pt;font-weight:bold;color:rgba(20,60,40,.06);transform:rotate(-24deg);pointer-events:none}'
    + '.kop{display:table;width:100%;border-bottom:3px double #141414;padding-bottom:6px}'
    + '.kop .logo{display:table-cell;width:74px;vertical-align:middle;font-size:8pt;color:#999;text-align:center;'
    + 'font-family:Arial,sans-serif;border:1px solid #bbb;height:82px}'
    + '.kop .txt{display:table-cell;text-align:center;vertical-align:middle;padding-left:10px}'
    + '.kop .l1{font-size:13pt;font-weight:bold}.kop .l2{font-size:16pt;font-weight:bold}'
    + '.kop .l3{font-size:9pt;margin-top:2px}'
    + 'h2.title{text-align:center;font-size:13pt;font-weight:bold;letter-spacing:1px;text-decoration:underline;margin:14px 0 2px}'
    + '.num{text-align:center;font-size:11pt;margin-bottom:8px}.empty{color:#b00;font-style:italic}'
    + '.lead{margin:6px 0}'
    + 'ol.paren{margin:4px 0 4px 0;padding-left:20px}ol.paren li{margin-bottom:4px}'
    + 'ol.untuk{margin:4px 0;padding-left:20px}ol.untuk li{margin-bottom:6px}'
    + 'table{border-collapse:collapse;width:100%}'
    + 'table.kepada td,table.kepada th{border:1px solid #333;padding:4px 6px;font-size:11pt;vertical-align:top}'
    + 'table.kepada th{background:#eee;text-align:center;font-size:10pt}'
    + 'table.kepada td.c{text-align:center}'
    + '.mem{text-align:center;font-weight:bold;letter-spacing:2px;margin:8px 0}'
    + '.sign{width:44%;margin-left:56%;text-align:center;margin-top:18px}'
    + '.tte{height:60px;font-size:9pt;color:#c00;font-family:Arial,sans-serif;padding-top:6px}'
    + '.sign .nm{font-weight:bold;text-decoration:underline}.sign .sb{font-size:11pt}'
    + 'table.spd td{border:1px solid #333;padding:4px 6px;font-size:11pt;vertical-align:top}'
    + 'table.spd .no{width:24px;text-align:center}table.spd .lbl{width:34%}'
    + '.spdhdr{display:table;width:100%;margin:10px 0 4px}'
    + '.spdhdr .lft{display:table-cell;font-size:10pt;vertical-align:top}'
    + '.spdhdr .rgt{display:table-cell;text-align:right;vertical-align:top}'
    + '.spdhdr table.box td{border:1px solid #333;padding:2px 6px;font-size:9pt}'
    + 'table.visum td{border:1px solid #333;padding:6px 8px;font-size:10.5pt;vertical-align:top}'
    + 'table.visum .rn{width:26px;text-align:center;font-weight:bold;background:#f4f4f4}'
    + '.perhatian{font-size:9.5pt;font-style:italic}'
    + '.fill{color:#b00;font-style:italic}'
    + '</style>';
}

function wrapPages_(pagesHtml) {
  return '<!doctype html><html><head><meta charset="utf-8">' + docCss_() + '</head><body>' + pagesHtml + '</body></html>';
}

function watermark_() {
  return '<div class="wm">DRAF — BELUM DITANDATANGANI</div>';
}

/* -------------------------------------------------------- Halaman: SP */

function kop_(d) {
  return '<div class="kop">'
    + '<div class="logo">LOGO<br>JABAR</div>'
    + '<div class="txt">'
    + '<div class="l1">' + esc_(d.ref.nama_pemda) + '</div>'
    + '<div class="l2">' + esc_(d.ref.nama_instansi) + '</div>'
    + '<div class="l3">' + esc_(d.ref.alamat_instansi) + '<br>' + esc_(d.ref.laman_instansi) + '</div>'
    + '</div></div>';
}

function butir1_(d) {
  var tgl = d.tgl_berangkat
    ? (d.tgl_kembali && d.tgl_kembali !== d.tgl_berangkat
        ? fmtTgl_(d.tgl_berangkat) + ' s.d. ' + fmtTgl_(d.tgl_kembali)
        : fmtTgl_(d.tgl_berangkat))
    : '…………';
  return 'Melaksanakan perjalanan dinas dalam rangka <b>' + esc_(d.maksud || '…………') + '</b> selama <b>'
    + esc_(d.lamaTxt) + '</b> pada tanggal <b>' + esc_(tgl) + '</b> di ' + esc_(d.tempat || '…………')
    + (d.alamat ? ', ' + esc_(d.alamat) : '') + ', ' + esc_(d.kota || '…………') + '.';
}

function pageSP_(d) {
  var rows = d.pegawai.length
    ? d.pegawai.map(function (p, i) {
        return '<tr><td class="c">' + (i + 1) + '</td><td>' + esc_(p.nama) + '</td><td class="c">'
          + esc_(p.pangkat) + '</td><td class="c">' + esc_(p.nip) + '</td><td>' + esc_(p.jabatan) + '</td></tr>';
      }).join('')
    : '<tr><td colspan="5" style="text-align:center" class="fill">Belum ada pegawai dipilih</td></tr>';

  var dasar4 = 'Surat ' + esc_(d.pengundang || '…………') + ' Nomor ' + esc_(d.und_nomor || '…………')
    + ' tanggal ' + fmtTgl_(d.und_tgl) + ' hal ' + esc_(d.und_hal || '…………') + '.';

  var nomor = d.nomor_sp ? esc_(d.nomor_sp) : '<span class="empty">………/……/BPSDM</span>';

  return '<div class="page">' + watermark_()
    + kop_(d)
    + '<h2 class="title">SURAT PERINTAH</h2>'
    + '<div class="num">Nomor : ' + nomor + '</div>'
    + '<div class="lead">Dasar :</div>'
    + '<ol class="paren"><li>' + esc_(d.paket.d1) + '</li><li>' + esc_(d.paket.d2) + '</li><li>'
    + esc_(d.paket.d3) + '</li><li>' + dasar4 + '</li></ol>'
    + '<div class="lead">Kepala ' + esc_(titleCase_(d.ref.nama_instansi))
    + ' Provinsi Jawa Barat,</div>'
    + '<div class="mem">MEMERINTAHKAN :</div>'
    + '<div class="lead">Kepada :</div>'
    + '<table class="kepada"><tr><th style="width:24px">No</th><th>Nama</th><th style="width:22%">Pangkat/Gol.</th>'
    + '<th style="width:22%">NIP</th><th>Jabatan</th></tr>' + rows + '</table>'
    + '<div class="lead" style="margin-top:8px">Untuk :</div>'
    + '<ol class="untuk"><li>' + butir1_(d) + '</li><li>Melaporkan hasil pelaksanaan tugas kepada pimpinan.</li></ol>'
    + signBlock_('a.n. Kepala ' + esc_(titleCase_(d.ref.nama_instansi)) + ' Provinsi Jawa Barat<br>'
        + esc_(d.ref.ttd_jabatan) + ',',
        d.ref.ttd_nama, d.ref.ttd_pangkat, d.ref.ttd_nip,
        'Ditetapkan di ' + esc_(d.ref.kedudukan), 'Pada tanggal ' + fmtTgl_(d.tgl_surat))
    + '</div>';
}

function signBlock_(roleHtml, nama, pangkat, nip, place1, place2) {
  return '<div class="sign">'
    + '<div>' + place1 + '</div><div>' + place2 + '</div>'
    + '<div style="margin-top:6px">' + roleHtml + '</div>'
    + '<div class="tte">ruang tanda tangan elektronik (Sidebar)</div>'
    + '<div class="nm">' + esc_(nama || '…………') + '</div>'
    + (pangkat ? '<div class="sb">' + esc_(pangkat) + '</div>' : '')
    + '<div class="sb">NIP. ' + esc_(nip || '…………') + '</div>'
    + '</div>';
}

/* ------------------------------------------------ Halaman: SPD Lembar 1 */

function pageSPD_(d, p) {
  return '<div class="page">' + watermark_()
    + '<div class="spdhdr"><div class="lft">'
    + 'Lampiran : Surat Perintah<br>'
    + 'Nomor&nbsp;&nbsp;&nbsp;: ' + (d.nomor_sp ? esc_(d.nomor_sp) : '<span class="fill">………</span>') + '<br>'
    + 'Tanggal : ' + fmtTgl_(d.tgl_surat) + '</div>'
    + '<div class="rgt"><table class="box">'
    + '<tr><td>Lembar Ke</td><td class="fill">………</td></tr>'
    + '<tr><td>Kode No.</td><td class="fill">………</td></tr>'
    + '<tr><td>Nomor</td><td class="fill">………</td></tr></table></div></div>'
    + '<h2 class="title" style="font-size:12pt">SURAT PERJALANAN DINAS (SPD)</h2>'
    + '<table class="spd">'
    + '<tr><td class="no">1</td><td class="lbl">Pejabat berwenang yang memberi perintah</td><td>Kuasa Pengguna Anggaran</td></tr>'
    + '<tr><td class="no">2</td><td class="lbl">Nama/NIP Pegawai yang melaksanakan perjalanan dinas</td><td>'
      + esc_(p.nama) + '<br>NIP. ' + esc_(p.nip) + '</td></tr>'
    + '<tr><td class="no">3</td><td class="lbl">a. Pangkat dan Golongan<br>b. Jabatan/Instansi<br>c. Tingkat Biaya Perjalanan Dinas</td>'
      + '<td>a. ' + esc_(p.pangkat) + '<br>b. ' + esc_(p.jabatan) + ' / ' + esc_(d.ref.nama_instansi)
      + ' Provinsi Jawa Barat<br>c. ' + (p.tingkat ? esc_(p.tingkat) : '—') + '</td></tr>'
    + '<tr><td class="no">4</td><td class="lbl">Maksud Perjalanan Dinas</td><td>' + esc_(d.maksud || '…………') + '</td></tr>'
    + '<tr><td class="no">5</td><td class="lbl">Alat angkutan yang dipergunakan</td><td>' + esc_(d.angkutan || '…………') + '</td></tr>'
    + '<tr><td class="no">6</td><td class="lbl">a. Tempat berangkat<br>b. Tempat tujuan</td><td>a. '
      + esc_(d.ref.kedudukan) + '<br>b. ' + esc_(d.kota || '…………') + '</td></tr>'
    + '<tr><td class="no">7</td><td class="lbl">a. Lamanya perjalanan dinas<br>b. Tanggal berangkat<br>c. Tanggal harus kembali</td>'
      + '<td>a. ' + esc_(d.lamaTxt) + '<br>b. ' + fmtTgl_(d.tgl_berangkat) + '<br>c. ' + fmtTgl_(d.tgl_kembali) + '</td></tr>'
    + '<tr><td class="no">8</td><td class="lbl">Pengikut</td><td style="height:28px"></td></tr>'
    + '<tr><td class="no">9</td><td class="lbl">Pembebanan Anggaran</td><td>' + esc_(d.ref.nama_instansi) + ' Provinsi Jawa Barat</td></tr>'
    + '<tr><td class="no">10</td><td class="lbl">Keterangan lain-lain</td><td style="height:26px"></td></tr>'
    + '</table>'
    + signBlock_('Kuasa Pengguna Anggaran,', d.ref.kpa_nama, '', d.ref.kpa_nip,
        'Dikeluarkan di ' + esc_(d.ref.kedudukan), 'Pada tanggal ' + fmtTgl_(d.tgl_surat))
    + '</div>';
}

/* ------------------------------------------------ Halaman: Visum Lembar 2 */

function pageVisum_(d, p) {
  var kota = esc_(d.kota || '…………'), ked = esc_(d.ref.kedudukan);
  var tB = fmtTgl_(d.tgl_berangkat), tK = fmtTgl_(d.tgl_kembali);
  var kpaMini = 'Kuasa Pengguna Anggaran,<div class="tte" style="height:44px"></div>'
    + '<span class="nm">' + esc_(d.ref.kpa_nama || '…………') + '</span><br><span class="sb">NIP. ' + esc_(d.ref.kpa_nip || '…') + '</span>';

  var pengesah = d.pj.nama
    ? esc_(d.pj.jabatan || 'Pejabat instansi tujuan') + ',<div class="tte" style="height:44px;color:#141414"></div>'
        + '<span class="nm">' + esc_(d.pj.nama) + '</span>'
        + (d.pj.pangkat ? '<br><span class="sb">' + esc_(d.pj.pangkat) + '</span>' : '')
        + (d.pj.nip ? '<br><span class="sb">NIP. ' + esc_(d.pj.nip) + '</span>' : '')
    : '<span class="fill">( diisi &amp; dicap oleh pejabat instansi tujuan )</span>'
        + '<div class="tte" style="height:44px;color:#141414"></div>…………………………';

  return '<div class="page">' + watermark_()
    + '<h2 class="title" style="font-size:12pt">LEMBAR II — SPD</h2>'
    + '<table class="visum">'
    + '<tr><td class="rn">I</td><td>Berangkat dari <b>' + ked + '</b> ke <b>' + kota + '</b> pada tanggal <b>' + tB + '</b>.'
      + '<div style="text-align:right;width:60%;margin-left:40%;margin-top:6px">' + kpaMini + '</div></td></tr>'
    + '<tr><td class="rn">II</td><td><table style="width:100%;border:none"><tr>'
      + '<td style="border:none;width:50%;vertical-align:top">Tiba di <b>' + kota + '</b> pada tanggal <b>' + tB + '</b>.<br>'
      + 'Berangkat dari <b>' + kota + '</b> ke <b>' + ked + '</b> pada tanggal <b>' + tK + '</b>.</td>'
      + '<td style="border:none;width:50%;text-align:center;vertical-align:top">Mengetahui/mengesahkan,<br>' + pengesah + '</td>'
      + '</tr></table></td></tr>'
    + '<tr><td class="rn">III</td><td style="height:24px;color:#999">—</td></tr>'
    + '<tr><td class="rn">IV</td><td style="height:24px;color:#999">—</td></tr>'
    + '<tr><td class="rn">V</td><td>Tiba kembali di <b>' + ked + '</b> pada tanggal <b>' + tK + '</b>.<br>'
      + 'Telah diperiksa dengan keterangan bahwa perjalanan tersebut atas perintahnya dan semata-mata untuk '
      + 'kepentingan jabatan dalam waktu yang sesingkat-singkatnya.'
      + '<div style="text-align:right;width:60%;margin-left:40%;margin-top:6px">' + kpaMini + '</div></td></tr>'
    + '<tr><td class="rn">VI</td><td class="perhatian"><b>PERHATIAN :</b> Pejabat yang berwenang menerbitkan SPD, '
      + 'pegawai yang melakukan perjalanan dinas, para pejabat yang mengesahkan tanggal berangkat/tiba, serta '
      + 'bendahara bertanggung jawab berdasarkan peraturan-peraturan Keuangan Negara apabila negara menderita '
      + 'rugi akibat kesalahan, kelalaian, dan kealpaannya.</td></tr>'
    + '</table></div>';
}

/* -------------------------------------------- Perakitan dokumen lengkap */

function buildSPHtml(payload) {
  return wrapPages_(pageSP_(resolveData_(payload)));
}

/** SPD + Visum untuk seluruh pegawai, digabung (PRD §6). */
function buildSPDHtml(payload) {
  var d = resolveData_(payload);
  var pages = d.pegawai.map(function (p) {
    return pageSPD_(d, p) + pageVisum_(d, p);
  }).join('');
  return wrapPages_(pages || '<div class="page">Belum ada pegawai dipilih.</div>');
}

/* ------------------------------------------------------ HTML → PDF (Drive) */

/**
 * Mengonversi HTML menjadi PDF melalui Google Docs, lalu menyimpannya di
 * folder arsip tahun berjalan. Google Doc sementara langsung dibuang.
 */
function htmlToPdf_(html, filename, folder) {
  var blob = Utilities.newBlob(html, MimeType.HTML, filename + '.html');
  var tmp = Drive.Files.insert(
    { title: filename + ' (sementara)', mimeType: MimeType.GOOGLE_DOCS },
    blob, { convert: true });
  var docFile = DriveApp.getFileById(tmp.id);
  var pdf = docFile.getAs(MimeType.PDF).setName(filename + '.pdf');
  var saved = folder.createFile(pdf);
  docFile.setTrashed(true);
  return saved;
}
