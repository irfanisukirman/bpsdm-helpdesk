<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// tickets — Tiket, PRD Bagian 4.3
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique(); // Format HD-YYYYMMDD-XXXX
            $table->string('reporter_name');
            $table->string('reporter_nip')->nullable(); // opsional, berguna untuk integrasi LMS
            $table->string('reporter_email');           // dipakai untuk verifikasi pelacakan
            $table->string('reporter_whatsapp')->nullable();
            $table->foreignId('category_id')->constrained('categories');
            $table->foreignId('subcategory_id')->nullable()->constrained('subcategories')->nullOnDelete();
            $table->string('title');
            $table->text('description');
            $table->enum('priority', ['rendah', 'sedang', 'tinggi'])->default('sedang');
            $table->enum('status', ['diterima', 'didistribusikan', 'diproses', 'selesai'])->default('diterima');
            $table->string('assigned_bidang')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('analysis')->nullable();   // Analisis Permasalahan
            $table->text('follow_up')->nullable();  // Tindak Lanjut
            $table->text('resolution')->nullable(); // Penyelesaian
            $table->unsignedInteger('reopened_count')->default(0);
            $table->timestamp('first_processed_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->unsignedBigInteger('lms_user_id')->nullable(); // disiapkan integrasi
            $table->timestamps();

            $table->index('status');
            $table->index('assigned_bidang');
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
