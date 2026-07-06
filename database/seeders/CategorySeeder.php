<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $notify = config('helpdesk.manager_email');

        // Kategori sesuai probis (PRD Bagian 4.1). notify_email tahap awal: satu alamat pengelola.
        $categories = [
            [
                'name' => 'Teknis Inti',
                'routing_role' => 'admin_bidang',
                'routing_bidang' => 'teknis_inti',
                'subcategories' => ['Kendala aplikasi pelatihan teknis', 'Materi pelatihan teknis', 'Jadwal pelatihan'],
            ],
            [
                'name' => 'Teknis Umum',
                'routing_role' => 'admin_bidang',
                'routing_bidang' => 'teknis_umum',
                'subcategories' => ['Kendala perangkat', 'Akses jaringan', 'Aplikasi umum'],
            ],
            [
                'name' => 'Manajerial',
                'routing_role' => 'admin_bidang',
                'routing_bidang' => 'manajerial',
                'subcategories' => ['Kepesertaan', 'Administrasi pelatihan', 'Surat & dokumen'],
            ],
            [
                'name' => 'Sertifikasi Kompetensi',
                'routing_role' => 'admin_bidang',
                'routing_bidang' => 'sertifikasi',
                'subcategories' => ['Kendala sertifikat', 'Jadwal uji kompetensi', 'Data asesor'],
            ],
            [
                'name' => 'LMS / E-Learning',
                'routing_role' => 'admin_bidang',
                'routing_bidang' => 'lms',
                'subcategories' => ['Reset password', 'Akses kelas daring', 'Kendala materi e-learning', 'Kendala pengumpulan tugas'],
            ],
            [
                'name' => 'Layanan Umum',
                'routing_role' => 'super_admin',
                'routing_bidang' => null,
                'subcategories' => ['Informasi umum', 'Pengaduan layanan', 'Lainnya'],
            ],
        ];

        foreach ($categories as $data) {
            $subs = $data['subcategories'];
            unset($data['subcategories']);

            $category = Category::updateOrCreate(
                ['name' => $data['name']],
                array_merge($data, [
                    'notify_email' => $notify,
                    'is_active' => true,
                ]),
            );

            foreach ($subs as $sub) {
                $category->subcategories()->updateOrCreate(['name' => $sub], ['is_active' => true]);
            }
        }
    }
}
