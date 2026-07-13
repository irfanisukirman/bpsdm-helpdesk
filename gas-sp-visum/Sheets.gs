/**
 * Sheets.gs — Lapisan basis data (Google Spreadsheet).
 *
 * Skema mengikuti PRD §5:
 *   Pegawai     : id, nama_lengkap, pangkat_golongan, nip, jabatan, tingkat_biaya, aktif
 *   Paket_Dasar : id, nama_paket, dasar_1, dasar_2, dasar_3, aktif
 *   Referensi   : key, value           (pasangan kunci–nilai)
 *   Riwayat     : id_dokumen, waktu, pembuat, nomor_sp, maksud, tujuan, tanggal,
 *                 daftar_pegawai, tautan_pdf
 *
 * ID Spreadsheet & folder arsip disimpan di Script Properties agar tidak
 * ada nilai yang tertanam di kode (lihat PRD §5 catatan Referensi).
 */

var PROP = PropertiesService.getScriptProperties();
var KEY_SS = 'SPREADSHEET_ID';
var KEY_ARCHIVE = 'ARCHIVE_ROOT_ID';

/** Membuka Spreadsheet basis data; melempar galat bila setup() belum dijalankan. */
function db_() {
  var id = PROP.getProperty(KEY_SS);
  if (!id) throw new Error('Spreadsheet belum disiapkan. Jalankan fungsi setup() satu kali terlebih dahulu.');
  return SpreadsheetApp.openById(id);
}

/** Mengubah satu sheet menjadi array objek, memakai baris pertama sebagai kunci kolom. */
function sheetRows_(name) {
  var sh = db_().getSheetByName(name);
  if (!sh) throw new Error('Sheet "' + name + '" tidak ditemukan.');
  var values = sh.getDataRange().getValues();
  if (values.length < 2) return [];
  var head = values[0].map(function (h) { return String(h).trim(); });
  return values.slice(1).filter(function (r) {
    return r.join('').trim() !== '';
  }).map(function (r) {
    var o = {};
    head.forEach(function (h, i) { o[h] = r[i]; });
    return o;
  });
}

function isTrue_(v) {
  var s = String(v).trim().toLowerCase();
  return s === 'true' || s === 'ya' || s === 'y' || s === '1' || s === 'aktif' || s === 'x';
}

/* ---------------------------------------------------------------- Pegawai */

function getPegawaiAktif() {
  return sheetRows_('Pegawai')
    .filter(function (p) { return isTrue_(p.aktif); })
    .map(function (p) {
      return {
        id: String(p.id),
        nama: String(p.nama_lengkap).trim(),
        pangkat: String(p.pangkat_golongan).trim(),
        nip: String(p.nip).trim(),
        jabatan: String(p.jabatan).trim(),
        tingkat: String(p.tingkat_biaya || '').trim()
      };
    });
}

function getPegawaiByIds_(ids) {
  var all = {};
  getPegawaiAktif().forEach(function (p) { all[p.id] = p; });
  return (ids || []).map(function (id) { return all[String(id)]; })
                    .filter(function (p) { return !!p; });
}

/* ------------------------------------------------------------ Paket_Dasar */

function getPaketAktif() {
  return sheetRows_('Paket_Dasar')
    .filter(function (p) { return isTrue_(p.aktif); })
    .map(function (p) {
      return {
        id: String(p.id),
        nama: String(p.nama_paket).trim(),
        d1: String(p.dasar_1).trim(),
        d2: String(p.dasar_2).trim(),
        d3: String(p.dasar_3).trim()
      };
    });
}

function getPaketById_(id) {
  var found = getPaketAktif().filter(function (p) { return p.id === String(id); })[0];
  return found || getPaketAktif()[0];
}

/* -------------------------------------------------------------- Referensi */

function getReferensi() {
  var out = {};
  sheetRows_('Referensi').forEach(function (r) {
    if (r.key) out[String(r.key).trim()] = String(r.value).trim();
  });
  return out;
}

/* ---------------------------------------------------------------- Riwayat */

function appendRiwayat_(row) {
  var sh = db_().getSheetByName('Riwayat');
  sh.appendRow([
    row.id_dokumen, new Date(), row.pembuat, row.nomor_sp, row.maksud,
    row.tujuan, row.tanggal, row.daftar_pegawai, row.tautan_pdf
  ]);
}

/* ----------------------------------------------------------- Folder arsip */

/** Folder arsip per tahun di dalam folder akar; dibuat bila belum ada. */
function getArchiveFolder_(year) {
  var rootId = PROP.getProperty(KEY_ARCHIVE);
  var root = rootId ? DriveApp.getFolderById(rootId) : DriveApp.getRootFolder();
  var name = String(year);
  var it = root.getFoldersByName(name);
  return it.hasNext() ? it.next() : root.createFolder(name);
}

/* ============================================================== SETUP =====
 * Jalankan setup() SATU KALI dari editor Apps Script. Fungsi ini:
 *   1. Membuat Spreadsheet basis data + folder arsip di Drive.
 *   2. Mengisi keempat sheet dengan header dan data contoh.
 *   3. Menyimpan ID-nya ke Script Properties.
 * Aman dijalankan ulang: bila ID sudah ada, hanya melengkapi sheet yang hilang.
 * ========================================================================= */
function setup() {
  var ss;
  var existing = PROP.getProperty(KEY_SS);
  if (existing) {
    ss = SpreadsheetApp.openById(existing);
  } else {
    ss = SpreadsheetApp.create('BPSDM — Basis Data SP & SPD/Visum');
    PROP.setProperty(KEY_SS, ss.getId());
  }

  if (!PROP.getProperty(KEY_ARCHIVE)) {
    var folder = DriveApp.createFolder('Arsip SP & SPD BPSDM');
    PROP.setProperty(KEY_ARCHIVE, folder.getId());
  }

  seedSheet_(ss, 'Pegawai',
    ['id', 'nama_lengkap', 'pangkat_golongan', 'nip', 'jabatan', 'tingkat_biaya', 'aktif'],
    [
      ['1', 'DEDEN SUPRIATNA, S.Sos., M.Si.', 'Penata (III/c)', '19850312 201001 1 004', 'Analis Kebijakan Ahli Muda', '', 'TRUE'],
      ['2', 'RINA MARLIANA, S.T.', 'Penata Muda Tk. I (III/b)', '19900521 201503 2 002', 'Pranata Komputer Ahli Pertama', '', 'TRUE'],
      ['3', 'AGUS PERMANA', 'IX', '19921107 202221 1 001', 'Pengelola Kepegawaian (PPPK)', '', 'TRUE'],
      ['4', 'SITI NURHALIZA, S.A.P.', 'Penata Muda (III/a)', '19940218 201903 2 005', 'Analis SDM Aparatur', '', 'TRUE'],
      ['5', 'BAMBANG WIJAYA, S.Kom.', 'Penata Tk. I (III/d)', '19820930 200604 1 010', 'Sub Koordinator TIK', '', 'TRUE']
    ]);

  seedSheet_(ss, 'Paket_Dasar',
    ['id', 'nama_paket', 'dasar_1', 'dasar_2', 'dasar_3', 'aktif'],
    [
      ['apbd26', 'APBD Sekretariat T.A. 2026',
        'Peraturan Daerah Provinsi Jawa Barat Nomor 9 Tahun 2025 tentang Anggaran Pendapatan dan Belanja Daerah Tahun Anggaran 2026;',
        'Peraturan Gubernur Jawa Barat Nomor 62 Tahun 2025 tentang Penjabaran Anggaran Pendapatan dan Belanja Daerah Tahun Anggaran 2026;',
        'Dokumen Pelaksanaan Anggaran (DPA) Badan Pengembangan Sumber Daya Manusia Provinsi Jawa Barat Tahun Anggaran 2026;',
        'TRUE'],
      ['geser1', 'Pergeseran Anggaran I — Bidang Teknis',
        'Peraturan Daerah Provinsi Jawa Barat Nomor 9 Tahun 2025 tentang Anggaran Pendapatan dan Belanja Daerah Tahun Anggaran 2026;',
        'Peraturan Gubernur Jawa Barat Nomor 21 Tahun 2026 tentang Perubahan Penjabaran APBD Tahun Anggaran 2026;',
        'Dokumen Pelaksanaan Anggaran Perubahan (DPPA) BPSDM Provinsi Jawa Barat Tahun Anggaran 2026;',
        'TRUE']
    ]);

  seedSheet_(ss, 'Referensi',
    ['key', 'value'],
    [
      ['nama_instansi', 'BADAN PENGEMBANGAN SUMBER DAYA MANUSIA'],
      ['nama_pemda', 'PEMERINTAH DAERAH PROVINSI JAWA BARAT'],
      ['alamat_instansi', 'Jalan Kolonel Masturi Nomor 11, Cimahi 40514 · Telepon (022) 6631054'],
      ['laman_instansi', 'Laman bpsdm.jabarprov.go.id · Surel bpsdm@jabarprov.go.id'],
      ['kedudukan', 'Kota Cimahi'],
      ['ttd_jabatan', 'Sekretaris'],
      ['ttd_nama', 'Drs. H. ASEP TAUFIK ROHMAN, M.Si.'],
      ['ttd_pangkat', 'Pembina Utama Muda (IV/c)'],
      ['ttd_nip', '19700815 199603 1 003'],
      ['kpa_nama', 'YUDI KUNCORO, A.P., M.M.'],
      ['kpa_nip', '19740614 199311 1 001']
    ]);

  seedSheet_(ss, 'Riwayat',
    ['id_dokumen', 'waktu', 'pembuat', 'nomor_sp', 'maksud', 'tujuan', 'tanggal', 'daftar_pegawai', 'tautan_pdf'],
    []);

  // Rapikan: hapus sheet default kosong "Sheet1" bila ada.
  var s1 = ss.getSheetByName('Sheet1');
  if (s1 && ss.getSheets().length > 1) ss.deleteSheet(s1);

  Logger.log('Setup selesai.');
  Logger.log('Spreadsheet : ' + ss.getUrl());
  Logger.log('Folder arsip: ' + DriveApp.getFolderById(PROP.getProperty(KEY_ARCHIVE)).getUrl());
  return ss.getUrl();
}

/** Membuat sheet bila belum ada dan menuliskan header (+ data contoh bila sheet baru). */
function seedSheet_(ss, name, header, rows) {
  var sh = ss.getSheetByName(name);
  var fresh = false;
  if (!sh) { sh = ss.insertSheet(name); fresh = true; }
  if (sh.getLastRow() === 0) {
    sh.getRange(1, 1, 1, header.length).setValues([header]).setFontWeight('bold');
    sh.setFrozenRows(1);
    if (fresh && rows.length) {
      sh.getRange(2, 1, rows.length, header.length).setValues(rows);
    }
  }
  return sh;
}
