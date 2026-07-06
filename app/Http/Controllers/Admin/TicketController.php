<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ResolveTicketRequest;
use App\Models\Category;
use App\Models\Ticket;
use App\Services\TicketService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TicketController extends Controller
{
    public function __construct(protected TicketService $tickets) {}

    /** Daftar tiket dengan pencarian & filter, dibatasi cakupan bidang. */
    public function index(Request $request)
    {
        $query = $this->scopedQuery($request)->with(['category', 'handler']);

        if ($search = trim((string) $request->input('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('reporter_name', 'like', "%{$search}%");
            });
        }

        if ($category = $request->input('category_id')) {
            $query->where('category_id', $category);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $tickets = $query->latest()->paginate(15)->withQueryString();

        return view('admin.tickets.index', [
            'tickets' => $tickets,
            'categories' => Category::orderBy('name')->get(),
            'statuses' => TicketStatus::cases(),
            'filters' => $request->only(['q', 'category_id', 'status']),
        ]);
    }

    public function show(Request $request, Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        $ticket->load(['category', 'subcategory', 'attachments', 'activities.user', 'notes.user', 'handler']);

        return view('admin.tickets.show', [
            'ticket' => $ticket,
            'canHandle' => $request->user()->can('handle', $ticket),
            'canRedistribute' => $request->user()->can('redistribute', $ticket),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    /** Mulai memproses tiket (PRD Bagian 5.6). */
    public function process(Request $request, Ticket $ticket)
    {
        $this->authorize('handle', $ticket);

        $this->tickets->startProcessing($ticket, $request->user());

        return back()->with('status', 'Tiket mulai diproses.');
    }

    /** Selesaikan tiket (PRD Bagian 5.7). */
    public function resolve(ResolveTicketRequest $request, Ticket $ticket)
    {
        $this->authorize('handle', $ticket);

        // Pastikan tiket sudah dalam proses agar first_processed_at terisi.
        if ($ticket->status !== TicketStatus::Diproses) {
            $this->tickets->startProcessing($ticket, $request->user());
        }

        $this->tickets->resolve($ticket, $request->user(), $request->only(['analysis', 'follow_up', 'resolution']));

        return redirect()
            ->route('admin.tickets.show', $ticket)
            ->with('status', 'Tiket telah diselesaikan.');
    }

    public function storeNote(Request $request, Ticket $ticket)
    {
        $this->authorize('handle', $ticket);

        $validated = $request->validate([
            'note' => ['required', 'string', 'max:5000'],
        ]);

        $this->tickets->addNote($ticket, $request->user(), $validated['note']);

        return back()->with('status', 'Catatan internal ditambahkan.');
    }

    /** Redistribusi tiket salah kategori (super admin, PRD Bagian 7.2). */
    public function redistribute(Request $request, Ticket $ticket)
    {
        $this->authorize('redistribute', $ticket);

        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
        ]);

        $category = Category::findOrFail($validated['category_id']);
        $this->tickets->redistribute($ticket, $category, $request->user());

        return back()->with('status', "Tiket diredistribusi ke kategori {$category->name}.");
    }

    public function downloadAttachment(Request $request, Ticket $ticket, int $attachment)
    {
        $this->authorize('view', $ticket);

        $file = $ticket->attachments()->findOrFail($attachment);

        return Storage::disk('local')->download($file->path, $file->original_name);
    }

    /** Ekspor daftar tiket ke Excel (CSV) atau PDF (PRD Bagian 7.1). */
    public function export(Request $request, string $format)
    {
        $tickets = $this->scopedQuery($request)->with('category')->latest()->get();

        return $format === 'pdf'
            ? $this->exportPdf($tickets)
            : $this->exportCsv($tickets);
    }

    /** Batasi cakupan tiket sesuai peran (pemisahan data antar bidang). */
    protected function scopedQuery(Request $request)
    {
        $user = $request->user();
        $query = Ticket::query();

        if ($user->isAdminBidang()) {
            $query->where('assigned_bidang', $user->bidang);
        }

        return $query;
    }

    protected function exportCsv($tickets): StreamedResponse
    {
        $filename = 'tiket-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($tickets) {
            $out = fopen('php://output', 'w');
            // BOM agar Excel mengenali UTF-8.
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Nomor Tiket', 'Tanggal', 'Pelapor', 'Kategori', 'Judul', 'Prioritas', 'Status', 'Bidang', 'Selesai Pada']);
            foreach ($tickets as $t) {
                fputcsv($out, [
                    $t->ticket_number,
                    $t->created_at->format('Y-m-d H:i'),
                    $t->reporter_name,
                    $t->category->name ?? '-',
                    $t->title,
                    $t->priority->label(),
                    $t->status->label(),
                    $t->assigned_bidang ?? '-',
                    optional($t->resolved_at)->format('Y-m-d H:i') ?? '-',
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    protected function exportPdf($tickets)
    {
        $pdf = Pdf::loadView('admin.tickets.export-pdf', [
            'tickets' => $tickets,
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('tiket-'.now()->format('Ymd-His').'.pdf');
    }
}
