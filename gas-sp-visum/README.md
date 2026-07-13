# Generator SP &amp; SPD/Visum — BPSDM Provinsi Jawa Barat

Aplikasi Google Apps Script sesuai **PRD v0.2**. Satu formulir menghasilkan dua
berkas PDF: **Surat Perintah** (1 berkas) dan **SPD + Visum** (1 berkas berisi 2
halaman per pegawai). Bukan sistem persuratan — tidak menerbitkan nomor surat,
tidak ada alur persetujuan, dan **tidak membubuhkan kotak TTE** (itu kewenangan
Sidebar). Lihat PRD §1 dan §3.1.

## Isi proyek

| Berkas | Peran |
|---|---|
| `appsscript.json` | Manifest: zona waktu, scope, layanan lanjutan Drive |
| `Code.gs` | Titik masuk web app (`doGet`) &amp; API klien (`getBootstrap`, `buildPreview`, `generateDocuments`) |
| `Sheets.gs` | Lapisan basis data Spreadsheet + fungsi `setup()` |
| `Documents.gs` | Perakitan HTML dokumen, *terbilang*, konversi HTML → PDF |
| `index.html` | Formulir (Bootstrap 5) dengan pratinjau langsung |
| `stylesheet.html` / `javascript.html` | Partial CSS &amp; JS klien |

## Basis data (Spreadsheet)

Dibuat otomatis oleh `setup()`. Skema sesuai PRD §5:

- **Pegawai** — `id, nama_lengkap, pangkat_golongan, nip, jabatan, tingkat_biaya, aktif`
  (`pangkat_golongan` teks bebas: "Penata (III/c)" untuk ASN, "IX" untuk PPPK).
- **Paket_Dasar** — `id, nama_paket, dasar_1, dasar_2, dasar_3, aktif`
  (disunting admin tiap terbit Pergub pergeseran; kolom `aktif` untuk menonaktifkan).
- **Referensi** — `key, value` (penanda tangan SP, KPA, tempat kedudukan, kop instansi).
- **Riwayat** — dicatat tiap pembuatan dokumen (rekap penugasan akhir tahun).

## Cara memasang

### A. Manual (paling cepat)

1. Buka <https://script.google.com> dengan **akun Google Workspace instansi**
   (bukan akun perorangan — lihat PRD §8) → **New project**.
2. Salin isi tiap berkas `.gs` dan `.html` ke file dengan nama yang sama.
   Untuk `appsscript.json`: Project Settings → centang *"Show appsscript.json"*,
   lalu tempel isinya.
3. Aktifkan **Advanced Drive Service**: menu Services (+) → *Drive API* → Add.
   (Dipakai untuk mengonversi HTML → Google Docs → PDF.)
4. Jalankan fungsi **`setup()`** satu kali (menu Run). Setujui izin saat diminta.
   Lihat Execution log untuk tautan Spreadsheet &amp; folder arsip.
5. **Deploy → New deployment → Web app**. `Execute as: Me`,
   `Who has access: sesuai kebijakan instansi`. Buka URL yang dihasilkan.

### B. Dengan clasp (untuk pengembangan)

```bash
npm i -g @clasp/cli          # atau: npm i -g @google/clasp
clasp login
clasp create --type webapp --title "SP-SPD BPSDM" --rootDir ./gas-sp-visum
# salin scriptId ke .clasp.json (lihat .clasp.json.example)
clasp push
```

Lalu jalankan `setup()` sekali dan buat deployment web app dari editor
(`clasp deploy` juga bisa).

## Catatan implementasi

- **Konversi PDF** memakai jalur HTML → Google Docs → PDF (PRD §7). Fidelitas
  cetak wajib diuji pada rilis awal (PRD §9 Tahap 4); tata letak SPD dua kolom
  adalah bagian paling sensitif.
- **Kop &amp; logo**: saat ini kotak "LOGO JABAR" berupa placeholder. Sisipkan
  lambang resmi pada `kop_()` di `Documents.gs` bila sudah tersedia.
- **Data contoh** yang di-*seed* `setup()` (nama pegawai/pejabat) adalah contoh
  untuk uji tata letak — perbarui melalui Spreadsheet dengan data resmi.
- **Belum terpasang**: penomoran surat, persetujuan elektronik, TTE, rincian
  biaya — memang di luar lingkup (PRD §1).

## Hal yang masih perlu dipastikan (PRD §10)

Beberapa keputusan memengaruhi template dan perlu konfirmasi Tata Usaha:
maksud SPD (dari uraian SP atau lebih ringkas), pengisian Tingkat Biaya,
jumlah paket dasar per bidang, akun Google Workspace instansi, dan apakah
alat angkutan memiliki pilihan baku.
