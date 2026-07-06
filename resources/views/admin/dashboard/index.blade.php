@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 fw-bold mb-0">Dashboard</h1>
            <p class="text-secondary mb-0 small">
                @if (auth()->user()->isSuperAdmin())
                    Ringkasan seluruh bidang.
                @else
                    Bidang: <strong>{{ \Illuminate\Support\Str::headline(auth()->user()->bidang) }}</strong>
                @endif
            </p>
        </div>
        <div class="text-secondary small text-end">
            <div>Hari ini: <strong>{{ $summary['hari_ini'] }}</strong> tiket</div>
            <div>Bulan ini: <strong>{{ $summary['bulan_ini'] }}</strong> tiket</div>
        </div>
    </div>

    @include('admin.partials.stat-cards')

    @if ($perBidang)
        <div class="card mt-4">
            <div class="card-header bg-white fw-semibold">Rekap per Bidang</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Bidang</th><th class="text-end">Total</th><th class="text-end">Dalam Proses</th><th class="text-end">Selesai</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($perBidang as $row)
                            <tr>
                                <td>{{ $row['kategori'] }}</td>
                                <td class="text-end">{{ $row['total'] }}</td>
                                <td class="text-end">{{ $row['proses'] }}</td>
                                <td class="text-end">{{ $row['selesai'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="card mt-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Tiket Terbaru</span>
            <a href="{{ route('admin.tickets.index') }}" class="btn btn-sm btn-outline-accent">Lihat Semua</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Nomor</th><th>Judul</th><th>Kategori</th><th>Status</th><th>Tanggal</th></tr>
                </thead>
                <tbody>
                    @forelse ($recent as $ticket)
                        <tr>
                            <td><a href="{{ route('admin.tickets.show', $ticket) }}" class="text-decoration-none fw-medium">{{ $ticket->ticket_number }}</a></td>
                            <td>{{ \Illuminate\Support\Str::limit($ticket->title, 40) }}</td>
                            <td class="small">{{ $ticket->category->name }}</td>
                            <td><span class="badge {{ $ticket->status->badgeClass() }}">{{ $ticket->status->label() }}</span></td>
                            <td class="small text-secondary">{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-secondary py-4">Belum ada tiket.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
