<?php

namespace App\Services;

use App\Enums\TicketStatus;
use App\Mail\TicketEscalationMail;
use App\Models\Ticket;
use App\Models\TicketReminder;
use Illuminate\Support\Facades\Mail;

/**
 * Pengingat & eskalasi berjenjang harian (PRD Bagian 6.1).
 * Sasaran: tiket yang belum berpindah ke status `diproses`.
 * Ambang dihitung dalam hari kerja sejak tanggal pengajuan.
 */
class ReminderService
{
    /** Ambang eskalasi (hari kerja) => kunci konfigurasi penerima. */
    protected array $stages = [
        1 => 'helpdesk.escalation.day_1.recipients',
        3 => 'helpdesk.escalation.day_3.recipients',
        5 => 'helpdesk.escalation.day_5.recipients',
    ];

    public function __construct(protected WorkingDaysService $workingDays) {}

    /**
     * Jalankan seluruh tahap. Mengembalikan ringkasan jumlah surel per tahap.
     *
     * @return array<int, int>
     */
    public function run(): array
    {
        $summary = [];

        // Tiket yang belum diproses.
        $pending = Ticket::query()
            ->with('reminders')
            ->whereIn('status', [TicketStatus::Diterima->value, TicketStatus::Didistribusikan->value])
            ->get();

        foreach ($this->stages as $stage => $configKey) {
            $recipients = array_filter((array) config($configKey, []));
            if (empty($recipients)) {
                $summary[$stage] = 0;
                continue;
            }

            // Tiket yang telah melewati ambang & belum pernah dikirimi tahap ini.
            $eligible = $pending->filter(function (Ticket $ticket) use ($stage) {
                $elapsed = $this->workingDays->elapsedWorkingDays($ticket->created_at);

                return $elapsed >= $stage
                    && ! $ticket->reminders->contains('stage', $stage);
            })->values();

            if ($eligible->isEmpty()) {
                $summary[$stage] = 0;
                continue;
            }

            foreach ($recipients as $recipient) {
                Mail::send(new TicketEscalationMail($recipient, $stage, $eligible));
            }

            // Catat agar tidak terkirim ganda (Bagian 6.1).
            foreach ($eligible as $ticket) {
                TicketReminder::firstOrCreate(
                    ['ticket_id' => $ticket->id, 'stage' => $stage],
                    ['sent_at' => now()],
                );
            }

            $summary[$stage] = $eligible->count();
        }

        return $summary;
    }
}
