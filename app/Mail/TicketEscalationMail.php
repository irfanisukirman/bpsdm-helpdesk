<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

/**
 * Pengingat & eskalasi berjenjang (PRD Bagian 6.1).
 * $stage: 1 (H+1 pengingat), 3 (H+3 eskalasi), 5 (H+5 eskalasi batas waktu).
 *
 * @param  Collection<int, Ticket>  $tickets
 */
class TicketEscalationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $recipient,
        public int $stage,
        public Collection $tickets,
    ) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->stage) {
            1 => 'Pengingat: Tiket Belum Diproses (H+1)',
            3 => 'Eskalasi: Tiket Belum Ditangani 3 Hari Kerja (H+3)',
            5 => 'Eskalasi: Tiket Melewati Batas Waktu Penanganan (H+5)',
            default => 'Pengingat Penanganan Tiket',
        };

        return new Envelope(to: [$this->recipient], subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.ticket-escalation');
    }
}
