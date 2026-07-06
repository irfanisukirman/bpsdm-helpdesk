<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Notifikasi penyelesaian kepada pelapor (PRD Bagian 5.8). */
class TicketResolvedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Ticket $ticket) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            to: [$this->ticket->reporter_email],
            subject: "[{$this->ticket->ticket_number}] Tiket Anda Telah Selesai",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.ticket-resolved');
    }
}
