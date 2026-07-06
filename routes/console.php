<?php

use Illuminate\Support\Facades\Schedule;

// Pengingat & eskalasi harian pada hari kerja pagi (PRD Bagian 6.1).
Schedule::command('helpdesk:send-reminders')
    ->weekdays()
    ->dailyAt('07:30')
    ->timezone(config('app.timezone'));

// Bersihkan surel yang gagal di antrean (opsional housekeeping).
Schedule::command('queue:prune-failed --hours=168')->weekly();
