<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Notifikasi penerimaan kepada pelapor (PRD Bagian 5.3). */
class TicketReceivedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Ticket $ticket) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            to: [$this->ticket->reporter_email],
            subject: "[{$this->ticket->ticket_number}] Tiket Anda Telah Diterima",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.ticket-received');
    }
}
