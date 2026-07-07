# Logo resmi BPSDM Provinsi Jawa Barat

Taruh berkas logo resmi di folder ini dengan nama **persis**:

```
public/images/logo-bpsdm.png
```

Setelah berkas ada, logo otomatis muncul di:
- Halaman beranda (hero) — `resources/views/public/home.blade.php`
- Halaman login admin — `resources/views/auth/login.blade.php`

Jika berkas belum ada, tampilan otomatis memakai ikon cadangan (tidak rusak).

Saran:
- Gunakan PNG latar transparan, tinggi ≥ 160px agar tajam.
- Untuk header navbar berlatar hijau, disarankan menyiapkan pula versi logo
  putih/monokrom (mis. `logo-bpsdm-white.png`) bila ingin dipasang di navbar.
