<?php

namespace App\Console\Commands;

use App\Services\ReminderService;
use Illuminate\Console\Command;

class SendTicketReminders extends Command
{
    protected $signature = 'helpdesk:send-reminders';

    protected $description = 'Kirim pengingat & eskalasi tiket yang belum diproses (H+1/H+3/H+5)';

    public function handle(ReminderService $service): int
    {
        $summary = $service->run();

        foreach ($summary as $stage => $count) {
            $this->line("Tahap H+{$stage}: {$count} tiket diproses untuk pengingat/eskalasi.");
        }

        $this->info('Selesai menjalankan pengingat & eskalasi.');

        return self::SUCCESS;
    }
}
