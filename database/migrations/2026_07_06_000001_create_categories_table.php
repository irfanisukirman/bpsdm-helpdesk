<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// categories — Kategori Layanan (Bidang), PRD Bagian 4.1
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // Peran tujuan distribusi
            $table->enum('routing_role', ['admin_bidang', 'super_admin'])->default('admin_bidang');
            // Kode bidang tujuan (mis. `sertifikasi`); null bila ke super admin
            $table->string('routing_bidang')->nullable();
            // Alamat notifikasi tiket baru (tahap awal: satu alamat pengelola)
            $table->string('notify_email');
            $table->boolean('is_active')->default(true);
            // disiapkan integrasi — referensi kategori padanan di LMS
            $table->string('lms_category_ref')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
