<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ticket_notes — Catatan Internal, PRD Bagian 4.5
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('note'); // Hanya tampil bagi admin, tidak bagi pelapor
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_notes');
    }
};
