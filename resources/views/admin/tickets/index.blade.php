@extends('layouts.admin')
@section('title', 'Daftar Tiket')
@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 fw-bold mb-0">Daftar Tiket</h1>
        <div class="btn-group">
            <a href="{{ route('admin.tickets.export', ['format' => 'csv', ...$filters]) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-file-earmark-spreadsheet me-1"></i> Excel (CSV)</a>
            <a href="{{ route('admin.tickets.export', ['format' => 'pdf', ...$filters]) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-file-earmark-pdf me-1"></i> PDF</a>
        </div>
    </div>

    <div class="card p-3 mb-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label small text-secondary mb-1">Pencarian</label>
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control form-control-sm" placeholder="Nomor tiket, judul, atau nama pelapor">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-secondary mb-1">Kategori</label>
                <select name="category_id" class="form-select form-select-sm">
                    <option value="">Semua kategori</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(($filters['category_id'] ?? '') == $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-secondary mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach ($statuses as $st)
                        <option value="{{ $st->value }}" @selected(($filters['status'] ?? '') === $st->value)>{{ $st->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-sm btn-accent flex-grow-1"><i class="bi bi-funnel"></i> Filter</button>
                <a href="{{ route('admin.tickets.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Nomor</th><th>Judul</th><th>Pelapor</th><th>Kategori</th><th>Prioritas</th><th>Status</th><th>Tanggal</th></tr>
                </thead>
                <tbody>
                    @forelse ($tickets as $ticket)
                        <tr>
                            <td><a href="{{ route('admin.tickets.show', $ticket) }}" class="text-decoration-none fw-medium">{{ $ticket->ticket_number }}</a></td>
                            <td>{{ \Illuminate\Support\Str::limit($ticket->title, 38) }}</td>
                            <td class="small">{{ $ticket->reporter_name }}</td>
                            <td class="small">{{ $ticket->category->name }}</td>
                            <td><span class="badge {{ $ticket->priority->badgeClass() }}">{{ $ticket->priority->label() }}</span></td>
                            <td><span class="badge {{ $ticket->status->badgeClass() }}">{{ $ticket->status->label() }}</span></td>
                            <td class="small text-secondary">{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-secondary py-4">Tidak ada tiket yang cocok.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $tickets->links() }}</div>
@endsection
