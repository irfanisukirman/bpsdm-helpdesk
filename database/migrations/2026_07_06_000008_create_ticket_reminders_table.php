<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ticket_reminders — pencatatan pengingat/eskalasi agar tidak terkirim ganda
// (PRD Bagian 6.1: "Setiap pengiriman pengingat/eskalasi tercatat agar tidak terkirim ganda")
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->unsignedTinyInteger('stage'); // 1, 3, atau 5 (hari kerja ambang)
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->unique(['ticket_id', 'stage']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_reminders');
    }
};
