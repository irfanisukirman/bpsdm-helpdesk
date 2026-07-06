<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ticket_activities — Riwayat Aktivitas (Audit), PRD Bagian 4.6
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->enum('actor_type', ['system', 'user'])->default('system');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action'); // dibuat, didistribusikan, mulai_diproses, selesai, dibuka_kembali, redistribusi
            $table->string('from_status')->nullable();
            $table->string('to_status')->nullable();
            $table->timestamps();

            $table->index(['ticket_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_activities');
    }
};
