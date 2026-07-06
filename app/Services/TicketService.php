<?php

namespace App\Services;

use App\Enums\TicketStatus;
use App\Mail\NewTicketAdminMail;
use App\Mail\TicketReceivedMail;
use App\Mail\TicketReopenedMail;
use App\Mail\TicketResolvedMail;
use App\Models\Category;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\TicketNote;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Logika bisnis utama penanganan tiket (PRD Bagian 5).
 * Seluruh perubahan status dicatat pada ticket_activities untuk audit & SLA.
 */
class TicketService
{
    public function __construct(protected TicketNumberService $numbers) {}

    /**
     * Pengajuan tiket baru: buat nomor, simpan, distribusi otomatis, kirim notifikasi.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>  $files
     */
    public function create(array $data, array $files = []): Ticket
    {
        $category = Category::findOrFail($data['category_id']);

        $ticket = DB::transaction(function () use ($data, $category) {
            $ticket = new Ticket($data);
            $ticket->ticket_number = $this->numbers->generate();
            $ticket->status = TicketStatus::Diterima;
            $ticket->save();

            $this->logActivity($ticket, 'dibuat', null, TicketStatus::Diterima->value);

            return $ticket;
        });

        // Simpan lampiran di luar transaksi penomoran (I/O berkas).
        foreach ($files as $file) {
            $this->storeAttachment($ticket, $file);
        }

        // Notifikasi penerimaan kepada pelapor (antre).
        Mail::send(new TicketReceivedMail($ticket));

        // Distribusi otomatis (Bagian 5.4 & 5.5).
        $this->distribute($ticket, $category);

        return $ticket->refresh();
    }

    /** Distribusi otomatis berdasarkan pemetaan kategori (Bagian 5.4). */
    public function distribute(Ticket $ticket, ?Category $category = null): Ticket
    {
        $category ??= $ticket->category;
        $from = $ticket->status->value;

        $ticket->assigned_bidang = $category->routing_bidang; // null bila ke super admin
        $ticket->status = TicketStatus::Didistribusikan;
        $ticket->save();

        $this->logActivity($ticket, 'didistribusikan', $from, TicketStatus::Didistribusikan->value);

        // Notifikasi tiket baru ke alamat konfigurasi kategori (Bagian 5.5).
        Mail::send(new NewTicketAdminMail($ticket, $category->notify_email));

        return $ticket;
    }

    /** Admin membuka tiket → status diproses (Bagian 5.6). */
    public function startProcessing(Ticket $ticket, User $user): Ticket
    {
        if ($ticket->status === TicketStatus::Diproses) {
            return $ticket;
        }

        $from = $ticket->status->value;
        $ticket->status = TicketStatus::Diproses;
        $ticket->handled_by = $user->id;
        if (is_null($ticket->first_processed_at)) {
            $ticket->first_processed_at = now();
        }
        $ticket->save();

        $this->logActivity($ticket, 'mulai_diproses', $from, TicketStatus::Diproses->value, $user);

        return $ticket;
    }

    /**
     * Penyelesaian tiket (Bagian 5.7). Ketiga kolom penyelesaian wajib.
     *
     * @param  array{analysis: string, follow_up: string, resolution: string}  $data
     */
    public function resolve(Ticket $ticket, User $user, array $data): Ticket
    {
        $from = $ticket->status->value;

        $ticket->analysis = $data['analysis'];
        $ticket->follow_up = $data['follow_up'];
        $ticket->resolution = $data['resolution'];
        $ticket->status = TicketStatus::Selesai;
        $ticket->resolved_at = now();
        if (is_null($ticket->handled_by)) {
            $ticket->handled_by = $user->id;
        }
        $ticket->save();

        $this->logActivity($ticket, 'selesai', $from, TicketStatus::Selesai->value, $user);

        // Notifikasi penyelesaian kepada pelapor (Bagian 5.8).
        Mail::send(new TicketResolvedMail($ticket));

        return $ticket;
    }

    /** Buka kembali tiket oleh pelapor (Bagian 5.10). */
    public function reopen(Ticket $ticket): Ticket
    {
        $from = $ticket->status->value;

        $ticket->status = TicketStatus::Diproses;
        $ticket->reopened_count = $ticket->reopened_count + 1;
        $ticket->resolved_at = null;
        $ticket->save();

        $this->logActivity($ticket, 'dibuka_kembali', $from, TicketStatus::Diproses->value);

        // Beri tahu admin bidang / pengelola.
        Mail::send(new TicketReopenedMail($ticket, $ticket->category->notify_email));

        return $ticket;
    }

    /** Redistribusi tiket yang salah kategori oleh super admin (Bagian 7.2). */
    public function redistribute(Ticket $ticket, Category $newCategory, User $user): Ticket
    {
        $from = $ticket->status->value;

        $ticket->category_id = $newCategory->id;
        $ticket->assigned_bidang = $newCategory->routing_bidang;
        // Kembalikan ke antrean penanganan bidang baru bila belum selesai.
        if ($ticket->status !== TicketStatus::Selesai) {
            $ticket->status = TicketStatus::Didistribusikan;
            $ticket->handled_by = null;
        }
        $ticket->save();

        $this->logActivity($ticket, 'redistribusi', $from, $ticket->status->value, $user);

        Mail::send(new NewTicketAdminMail($ticket, $newCategory->notify_email));

        return $ticket;
    }

    public function addNote(Ticket $ticket, User $user, string $note): TicketNote
    {
        return $ticket->notes()->create([
            'user_id' => $user->id,
            'note' => $note,
        ]);
    }

    protected function storeAttachment(Ticket $ticket, UploadedFile $file): void
    {
        $safeName = Str::of($file->getClientOriginalName())
            ->replaceMatches('/[^A-Za-z0-9._-]/', '_')
            ->limit(120, '');
        $path = $file->store("attachments/{$ticket->id}", 'local');

        $ticket->attachments()->create([
            'path' => $path,
            'original_name' => (string) $safeName,
            'mime' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);
    }

    protected function logActivity(
        Ticket $ticket,
        string $action,
        ?string $from,
        ?string $to,
        ?User $user = null,
    ): TicketActivity {
        return $ticket->activities()->create([
            'actor_type' => $user ? 'user' : 'system',
            'user_id' => $user?->id,
            'action' => $action,
            'from_status' => $from,
            'to_status' => $to,
        ]);
    }
}
