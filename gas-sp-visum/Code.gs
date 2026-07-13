/**
 * Code.gs — Titik masuk aplikasi web (HTML Service) dan API klien.
 *
 * Alur (PRD §4): satu formulir → pratinjau langsung → dua berkas PDF
 * (SP dan SPD/Visum) tersimpan di Drive.
 */

/** Menyajikan formulir. */
function doGet() {
  return HtmlService.createTemplateFromFile('index')
    .evaluate()
    .setTitle('Generator SP & SPD/Visum — BPSDM Jabar')
    .addMetaTag('viewport', 'width=device-width, initial-scale=1')
    .setXFrameOptionsMode(HtmlService.XFrameOptionsMode.ALLOWALL);
}

/** Menyisipkan partial HTML (dipakai untuk memuat CSS/JS). */
function include(name) {
  return HtmlService.createHtmlOutputFromFile(name).getContent();
}

/** Data awal untuk formulir: pegawai, paket, dan nilai referensi. */
function getBootstrap() {
  return {
    pegawai: getPegawaiAktif(),
    paket: getPaketAktif(),
    ref: getReferensi(),
    hariIni: Utilities.formatDate(new Date(), 'Asia/Jakarta', 'yyyy-MM-dd')
  };
}

/**
 * Pratinjau langsung: mengembalikan HTML satu dokumen sesuai tab.
 * @param {string} view  'sp' | 'spd'
 * @param {Object} payload  isian formulir
 */
function buildPreview(view, payload) {
  return view === 'sp' ? buildSPHtml(payload) : buildSPDHtml(payload);
}

/**
 * Menghasilkan kedua PDF, menyimpannya di arsip Drive, dan mencatat Riwayat.
 * @return {{spUrl, spdUrl, folderUrl, kode}}
 */
function generateDocuments(payload) {
  var d = resolveData_(payload);
  if (!d.pegawai.length) throw new Error('Pilih minimal satu pegawai sebelum membuat dokumen.');

  var tgl = d.tgl_surat || Utilities.formatDate(new Date(), 'Asia/Jakarta', 'yyyy-MM-dd');
  var folder = getArchiveFolder_(tgl.slice(0, 4));
  var kode = d.nomor_sp ? d.nomor_sp.replace(/[\/\\:*?"<>|]+/g, '-') : 'TANPA-NOMOR';

  var spFile = htmlToPdf_(buildSPHtml(payload), 'SP_' + kode + '_' + tgl, folder);
  var spdFile = htmlToPdf_(buildSPDHtml(payload), 'SPD_' + kode + '_' + tgl, folder);

  appendRiwayat_({
    id_dokumen: Utilities.getUuid(),
    pembuat: Session.getActiveUser().getEmail() || '(tidak diketahui)',
    nomor_sp: d.nomor_sp,
    maksud: d.maksud,
    tujuan: d.kota,
    tanggal: tgl,
    daftar_pegawai: d.pegawai.map(function (p) { return p.nama; }).join('; '),
    tautan_pdf: spFile.getUrl() + ' | ' + spdFile.getUrl()
  });

  return {
    spUrl: spFile.getUrl(),
    spdUrl: spdFile.getUrl(),
    folderUrl: folder.getUrl(),
    kode: kode,
    jumlahPegawai: d.pegawai.length
  };
}
