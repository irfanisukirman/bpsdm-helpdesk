<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Notifikasi tiket dibuka kembali kepada admin bidang (PRD Bagian 5.10). */
class TicketReopenedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Ticket $ticket, public string $recipient) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            to: [$this->recipient],
            subject: "[{$this->ticket->ticket_number}] Tiket Dibuka Kembali oleh Pelapor",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.ticket-reopened');
    }
}
