<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin
        User::updateOrCreate(
            ['email' => 'superadmin@bpsdm.jabarprov.go.id'],
            [
                'name' => 'Super Admin BPSDM',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'bidang' => null,
                'is_active' => true,
            ],
        );

        // Pimpinan
        User::updateOrCreate(
            ['email' => 'pimpinan@bpsdm.jabarprov.go.id'],
            [
                'name' => 'Pimpinan BPSDM',
                'password' => Hash::make('password'),
                'role' => 'pimpinan',
                'bidang' => null,
                'is_active' => true,
            ],
        );

        // Admin Bidang (kode bidang selaras dengan CategorySeeder)
        $bidang = [
            'teknis_inti' => 'Admin Teknis Inti',
            'teknis_umum' => 'Admin Teknis Umum',
            'manajerial' => 'Admin Manajerial',
            'sertifikasi' => 'Admin Sertifikasi Kompetensi',
            'lms' => 'Admin LMS',
        ];

        foreach ($bidang as $code => $name) {
            User::updateOrCreate(
                ['email' => "admin.{$code}@bpsdm.jabarprov.go.id"],
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'role' => 'admin_bidang',
                    'bidang' => $code,
                    'is_active' => true,
                ],
            );
        }
    }
}
