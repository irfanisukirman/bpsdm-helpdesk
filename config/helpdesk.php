<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Notifikasi
    |--------------------------------------------------------------------------
    | Tahap awal: seluruh notifikasi diarahkan ke satu alamat pengelola
    | (PRD Bagian 2.2). Alamat per-kategori disimpan di kolom
    | categories.notify_email agar mudah diubah menjadi alamat grup per
    | bidang tanpa mengubah kode.
    */
    'manager_email' => env('HELPDESK_MANAGER_EMAIL', 'pengelola.helpdesk@bpsdm.jabarprov.go.id'),

    /*
    |--------------------------------------------------------------------------
    | Penerima eskalasi berjenjang (PRD Bagian 6.1)
    |--------------------------------------------------------------------------
    | Dapat dikonfigurasi. Pada tahap awal boleh diarahkan ke alamat
    | pengelola yang sama.
    */
    'escalation' => [
        // Hari ke-1: pengingat ke admin/pengelola
        'day_1' => [
            'recipients' => [env('HELPDESK_MANAGER_EMAIL', 'pengelola.helpdesk@bpsdm.jabarprov.go.id')],
        ],
        // Hari ke-3: admin/pengelola + koordinator
        'day_3' => [
            'recipients' => array_values(array_unique([
                env('HELPDESK_MANAGER_EMAIL', 'pengelola.helpdesk@bpsdm.jabarprov.go.id'),
                env('HELPDESK_COORDINATOR_EMAIL', env('HELPDESK_MANAGER_EMAIL')),
            ])),
        ],
        // Hari ke-5: kepala bidang + super admin
        'day_5' => [
            'recipients' => array_values(array_unique([
                env('HELPDESK_KABID_EMAIL', env('HELPDESK_MANAGER_EMAIL')),
                env('HELPDESK_SUPERADMIN_EMAIL', env('HELPDESK_MANAGER_EMAIL')),
            ])),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Buka kembali tiket (PRD Bagian 5.10)
    |--------------------------------------------------------------------------
    | Batas waktu (hari kerja) setelah status `selesai` di mana pelapor masih
    | boleh mengajukan buka kembali.
    */
    'reopen_window_working_days' => (int) env('HELPDESK_REOPEN_WINDOW_DAYS', 3),

    /*
    |--------------------------------------------------------------------------
    | Target SLA per prioritas — dalam hari kerja (PRD Bagian 6.2)
    |--------------------------------------------------------------------------
    | Informatif pada MVP; dipakai untuk metrik kepatuhan pada dashboard.
    */
    'sla' => [
        'rendah' => ['response' => 3, 'resolution' => 7],
        'sedang' => ['response' => 2, 'resolution' => 5],
        'tinggi' => ['response' => 1, 'resolution' => 3],
    ],

    /*
    |--------------------------------------------------------------------------
    | Lampiran (PRD Bagian 8)
    |--------------------------------------------------------------------------
    */
    'attachments' => [
        'max_size_kb' => (int) env('HELPDESK_ATTACHMENT_MAX_KB', 5120), // 5 MB
        'allowed_mimes' => ['jpg', 'jpeg', 'png', 'pdf'],
        'max_files' => (int) env('HELPDESK_ATTACHMENT_MAX_FILES', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Hari libur nasional (untuk perhitungan hari kerja)
    |--------------------------------------------------------------------------
    | Format Y-m-d. Sabtu & Minggu otomatis dikecualikan. Daftar ini dapat
    | dipindah ke basis data / disinkronkan dengan kalender resmi kemudian.
    */
    'national_holidays' => array_filter(explode(',', (string) env('HELPDESK_NATIONAL_HOLIDAYS', ''))),

    /*
    |--------------------------------------------------------------------------
    | Integrasi LMS (Model B1 — sekadar tautan pengarah, PRD Bagian 12)
    |--------------------------------------------------------------------------
    */
    'lms_url' => env('LMS_URL', 'https://jabarcorputalent.jabarprov.go.id/'),

    /*
    |--------------------------------------------------------------------------
    | Live Chat (PRD Bagian 13.2 — Chat Support)
    |--------------------------------------------------------------------------
    | Widget chat pihak ketiga (mis. Tawk.to). Nonaktif sampai ID diisi di
    | .env. Hanya ditampilkan pada area publik, bukan dashboard admin.
    */
    'livechat' => [
        'enabled' => (bool) env('LIVECHAT_ENABLED', false),
        'provider' => env('LIVECHAT_PROVIDER', 'tawkto'),
        'tawkto' => [
            'property_id' => env('TAWKTO_PROPERTY_ID'),
            'widget_id' => env('TAWKTO_WIDGET_ID', 'default'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Identitas instansi (header/footer)
    |--------------------------------------------------------------------------
    */
    'instansi' => [
        'nama' => 'BPSDM Provinsi Jawa Barat',
        'nama_panjang' => 'Badan Pengembangan Sumber Daya Manusia Provinsi Jawa Barat',
        'alamat' => 'Jl. Kolonel Masturi No.11, Cipageran, Cimahi Utara, Kota Cimahi, Jawa Barat',
        'kontak_email' => env('HELPDESK_MANAGER_EMAIL', 'pengelola.helpdesk@bpsdm.jabarprov.go.id'),
    ],
];
