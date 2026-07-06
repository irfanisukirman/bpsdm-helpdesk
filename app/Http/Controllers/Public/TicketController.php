<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\StoreTicketRequest;
use App\Models\Category;
use App\Services\TicketService;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function __construct(protected TicketService $tickets) {}

    /** Formulir pengajuan tiket publik (PRD Bagian 5.1). */
    public function create()
    {
        $categories = Category::where('is_active', true)
            ->with('activeSubcategories')
            ->orderBy('name')
            ->get();

        return view('public.tickets.create', compact('categories'));
    }

    public function store(StoreTicketRequest $request)
    {
        $ticket = $this->tickets->create(
            $request->ticketData(),
            $request->file('attachments', []),
        );

        return redirect()
            ->route('tickets.success')
            ->with('ticket_number', $ticket->ticket_number);
    }

    public function success(Request $request)
    {
        $ticketNumber = session('ticket_number');
        if (! $ticketNumber) {
            return redirect()->route('tickets.create');
        }

        return view('public.tickets.success', compact('ticketNumber'));
    }
}
