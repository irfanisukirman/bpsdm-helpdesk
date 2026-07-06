<?php

namespace App\Http\Controllers\Public;

use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Public\TrackTicketRequest;
use App\Models\Ticket;
use App\Services\TicketService;
use App\Services\WorkingDaysService;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function __construct(
        protected TicketService $tickets,
        protected WorkingDaysService $workingDays,
    ) {}

    public function form()
    {
        return view('public.track.form');
    }

    /** Pelacakan tiket tanpa login (PRD Bagian 5.9). */
    public function show(TrackTicketRequest $request)
    {
        $ticket = $this->findVerified(
            $request->input('ticket_number'),
            $request->input('reporter_email'),
        );

        if (! $ticket) {
            return back()
                ->withInput($request->only('ticket_number'))
                ->withErrors(['ticket_number' => 'Nomor tiket dan surel tidak cocok. Periksa kembali data Anda.']);
        }

        $ticket->load(['category', 'subcategory', 'attachments', 'activities']);

        return view('public.track.show', [
            'ticket' => $ticket,
            'canReopen' => $this->canReopen($ticket),
            'reopenWindow' => config('helpdesk.reopen_window_working_days'),
            // Surel diverifikasi disematkan agar buka kembali tidak perlu ketik ulang.
            'verifiedEmail' => $ticket->reporter_email,
        ]);
    }

    /** Buka kembali tiket oleh pelapor (PRD Bagian 5.10). */
    public function reopen(Request $request)
    {
        $validated = $request->validate([
            'ticket_number' => ['required', 'string', 'max:30'],
            'reporter_email' => ['required', 'email', 'max:150'],
        ]);

        $ticket = $this->findVerified($validated['ticket_number'], $validated['reporter_email']);

        if (! $ticket) {
            return back()->withErrors(['ticket_number' => 'Verifikasi tiket gagal.']);
        }

        if (! $this->canReopen($ticket)) {
            return back()->withErrors([
                'reopen' => 'Tiket tidak dapat dibuka kembali (belum selesai atau telah melewati batas waktu).',
            ]);
        }

        $this->tickets->reopen($ticket);

        return back()->with('status', 'Tiket berhasil dibuka kembali. Admin akan meninjau kembali laporan Anda.');
    }

    protected function findVerified(string $number, string $email): ?Ticket
    {
        return Ticket::where('ticket_number', $number)
            ->whereRaw('LOWER(reporter_email) = ?', [mb_strtolower(trim($email))])
            ->first();
    }

    protected function canReopen(Ticket $ticket): bool
    {
        if ($ticket->status !== TicketStatus::Selesai || ! $ticket->resolved_at) {
            return false;
        }

        $elapsed = $this->workingDays->elapsedWorkingDays($ticket->resolved_at);

        return $elapsed <= (int) config('helpdesk.reopen_window_working_days');
    }
}
