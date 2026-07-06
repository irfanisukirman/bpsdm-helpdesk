# Sistem Helpdesk BPSDM Provinsi Jawa Barat

Aplikasi web *standalone* berbasis **Laravel 11** untuk menerima, mendistribusikan,
menangani, dan memantau pengaduan/konsultasi/permintaan bantuan atas layanan
BPSDM Provinsi Jawa Barat. Dibangun mengikuti PRD Sistem Helpdesk BPSDM v1.1.

## Fitur (MVP)

- **Form pengajuan publik** tanpa login, dengan *dependent dropdown* kategori→subkategori,
  validasi, lampiran (JPG/PNG/PDF ≤ 5 MB), persetujuan data pribadi, honeypot + *rate limiting* anti-spam.
- **Penomoran tiket** otomatis `HD-YYYYMMDD-XXXX`, aman dari *race condition* (penguncian baris + reset harian).
- **Distribusi otomatis** per kategori ke bidang yang berwenang.
- **Notifikasi surel** via antrean: konfirmasi pelapor, tiket baru ke admin/pengelola, penyelesaian, buka kembali.
- **Autentikasi & peran** internal: Admin Bidang, Super Admin, Pimpinan — dengan pemisahan data antar bidang di lapisan Policy.
- **Penanganan tiket**: mulai proses, catatan internal, analisis/tindak lanjut/penyelesaian.
- **Pelacakan tiket** tanpa login (verifikasi nomor + surel) dan **buka kembali** dalam batas hari kerja.
- **Pengingat & eskalasi** berjenjang H+1 / H+3 / H+5 (hari kerja) melalui perintah terjadwal.
- **Dashboard**: Admin Bidang (cakupan bidang), Super Admin (lintas bidang + pengelolaan), Pimpinan (statistik & grafik Chart.js).
- **Ekspor** daftar tiket ke Excel (CSV) dan PDF.
- Struktur data telah menyiapkan ruang **integrasi LMS** (Model B1 → B2): kolom NIP, `lms_user_id`, `lms_user_ref`, `lms_category_ref`.

## Tumpukan Teknologi

- Laravel 11, PHP 8.2+
- MySQL 8 / MariaDB 10.6+ (produksi) — SQLite untuk pengembangan lokal cepat
- Blade + Bootstrap 5 (server-side rendering), Chart.js untuk grafik
- Queue driver `database`, Task Scheduling via cron, Laravel Storage (disk lokal)
- PDF: `barryvdh/laravel-dompdf`

## Instalasi

```bash
composer install
cp .env.example .env
php artisan key:generate

# Basis data: sesuaikan .env (MySQL untuk produksi, atau SQLite untuk lokal)
touch database/database.sqlite   # bila memakai SQLite
php artisan migrate --seed

php artisan serve
```

Aset frontend memakai Bootstrap & Chart.js via CDN dan `public/css/helpdesk.css`,
sehingga **tidak memerlukan** langkah build (`npm`) untuk berjalan.

### Menjalankan antrean & penjadwalan

```bash
# Pengiriman surel (antrean database)
php artisan queue:work

# Pengingat & eskalasi harian — daftarkan scheduler ke cron server:
# * * * * * cd /path-ke-proyek && php artisan schedule:run >> /dev/null 2>&1
```

## Konfigurasi Helpdesk

Aturan yang dapat dikonfigurasi ada di `config/helpdesk.php` (dan variabel `.env`):

- `HELPDESK_MANAGER_EMAIL` — alamat pengelola tunggal untuk notifikasi tahap awal (PRD Bagian 2.2).
  Alamat notifikasi per-kategori disimpan pada kolom `categories.notify_email` sehingga dapat
  diubah menjadi alamat grup per bidang **tanpa mengubah kode**.
- Penerima eskalasi H+1/H+3/H+5, batas buka kembali (hari kerja), target SLA per prioritas,
  batas & jenis lampiran, hari libur nasional, tautan LMS.

## Akun Contoh (seeder)

Kata sandi seluruh akun contoh: `password`

| Peran | Surel |
|---|---|
| Super Admin | `superadmin@bpsdm.jabarprov.go.id` |
| Pimpinan | `pimpinan@bpsdm.jabarprov.go.id` |
| Admin Bidang | `admin.teknis_inti@…`, `admin.teknis_umum@…`, `admin.manajerial@…`, `admin.sertifikasi@…`, `admin.lms@…` |

> Ganti seluruh kata sandi contoh sebelum digunakan di lingkungan nyata.

## Pengujian

```bash
php artisan test
```

Mencakup alur pengajuan→distribusi, anti-spam, verifikasi pelacakan, otorisasi antar bidang,
alur penyelesaian, keunikan nomor tiket, render halaman, dan eskalasi pengingat.

## Struktur Utama

- `app/Services` — logika bisnis (TicketService, TicketNumberService, WorkingDaysService, ReminderService, DashboardService)
- `app/Http/Controllers` — Public, Auth, Admin, SuperAdmin
- `app/Policies/TicketPolicy` — pemisahan data antar bidang
- `app/Mail` — Mailable (semua `ShouldQueue`)
- `app/Console/Commands/SendTicketReminders` — pengingat & eskalasi
- `database/migrations`, `database/seeders`
- `resources/views` — publik, auth, admin (Bootstrap 5)
