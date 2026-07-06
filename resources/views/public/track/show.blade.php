@extends('layouts.public')
@section('title', 'Detail Tiket')
@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-9 col-xl-8">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <div class="text-secondary small">Nomor Tiket</div>
                    <h1 class="h4 fw-bold text-accent mb-0">{{ $ticket->ticket_number }}</h1>
                </div>
                <span class="badge fs-6 {{ $ticket->status->badgeClass() }}">{{ $ticket->status->label() }}</span>
            </div>

            @error('reopen') <div class="alert alert-danger">{{ $message }}</div> @enderror

            <div class="card p-4 mb-3">
                <div class="row g-3">
                    <div class="col-sm-6"><div class="text-secondary small">Tanggal Pengajuan</div><div>{{ $ticket->created_at->translatedFormat('d F Y, H:i') }} WIB</div></div>
                    <div class="col-sm-6"><div class="text-secondary small">Nama Pelapor</div><div>{{ $ticket->reporter_name }}</div></div>
                    <div class="col-sm-6"><div class="text-secondary small">Kategori</div><div>{{ $ticket->category->name }}@if ($ticket->subcategory) <span class="text-secondary">— {{ $ticket->subcategory->name }}</span>@endif</div></div>
                    <div class="col-sm-6"><div class="text-secondary small">Bidang Penanganan</div><div>{{ $ticket->assigned_bidang ? \Illuminate\Support\Str::headline($ticket->assigned_bidang) : 'Layanan Umum' }}</div></div>
                    <div class="col-12"><div class="text-secondary small">Judul Kendala</div><div class="fw-semibold">{{ $ticket->title }}</div></div>
                    <div class="col-12"><div class="text-secondary small">Uraian</div><div style="white-space:pre-line">{{ $ticket->description }}</div></div>
                </div>
            </div>

            @if ($ticket->attachments->isNotEmpty())
                <div class="card p-4 mb-3">
                    <h6 class="fw-semibold mb-2">Lampiran</h6>
                    <ul class="list-unstyled mb-0 small">
                        @foreach ($ticket->attachments as $att)
                            <li class="d-flex align-items-center gap-2 py-1">
                                <i class="bi bi-paperclip text-secondary"></i> {{ $att->original_name }}
                                <span class="text-secondary">({{ $att->humanSize() }})</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($ticket->isResolved() || $ticket->analysis || $ticket->follow_up || $ticket->resolution)
                <div class="card p-4 mb-3 border-success-subtle">
                    <h6 class="fw-semibold text-success mb-3"><i class="bi bi-clipboard2-check me-1"></i> Hasil Penanganan</h6>
                    <div class="mb-3"><div class="text-secondary small">Analisis Permasalahan</div><div style="white-space:pre-line">{{ $ticket->analysis ?: '—' }}</div></div>
                    <div class="mb-3"><div class="text-secondary small">Tindak Lanjut</div><div style="white-space:pre-line">{{ $ticket->follow_up ?: '—' }}</div></div>
                    <div><div class="text-secondary small">Penyelesaian</div><div style="white-space:pre-line">{{ $ticket->resolution ?: '—' }}</div></div>
                </div>
            @endif

            @if ($ticket->activities->isNotEmpty())
                <div class="card p-4 mb-3">
                    <h6 class="fw-semibold mb-3">Riwayat</h6>
                    <ul class="hd-timeline">
                        @foreach ($ticket->activities as $activity)
                            <li>
                                <span class="hd-dot"></span>
                                <div class="fw-medium">{{ $activity->label() }}</div>
                                <div class="text-secondary small">{{ $activity->created_at->translatedFormat('d F Y, H:i') }} WIB</div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($canReopen)
                <div class="card p-4 border-warning-subtle">
                    <h6 class="fw-semibold mb-1">Belum Puas dengan Penyelesaian?</h6>
                    <p class="text-secondary small mb-3">Anda dapat mengajukan buka kembali tiket ini dalam batas {{ $reopenWindow }} hari kerja setelah tiket dinyatakan selesai.</p>
                    <form method="POST" action="{{ route('track.reopen') }}" onsubmit="return confirm('Ajukan buka kembali tiket ini?');">
                        @csrf
                        <input type="hidden" name="ticket_number" value="{{ $ticket->ticket_number }}">
                        <input type="hidden" name="reporter_email" value="{{ $verifiedEmail }}">
                        <button type="submit" class="btn btn-outline-accent"><i class="bi bi-arrow-counterclockwise me-1"></i> Buka Kembali Tiket</button>
                    </form>
                </div>
            @endif

            <div class="mt-3">
                <a href="{{ route('track.form') }}" class="btn btn-link text-decoration-none"><i class="bi bi-arrow-left"></i> Lacak tiket lain</a>
            </div>
        </div>
    </div>
@endsection
