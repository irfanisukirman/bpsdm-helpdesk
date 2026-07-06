@extends('layouts.admin')
@section('title', 'Tiket ' . $ticket->ticket_number)
@section('content')
    @php use App\Enums\TicketStatus; @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
        <div>
            <a href="{{ route('admin.tickets.index') }}" class="btn btn-sm btn-link text-decoration-none px-0 mb-1"><i class="bi bi-arrow-left"></i> Kembali</a>
            <h1 class="h4 fw-bold mb-1">{{ $ticket->ticket_number }}</h1>
            <div class="d-flex flex-wrap gap-2">
                <span class="badge {{ $ticket->status->badgeClass() }}">{{ $ticket->status->label() }}</span>
                <span class="badge {{ $ticket->priority->badgeClass() }}">Prioritas: {{ $ticket->priority->label() }}</span>
                @if ($ticket->reopened_count > 0)
                    <span class="badge bg-warning-subtle text-warning-emphasis">Dibuka kembali {{ $ticket->reopened_count }}×</span>
                @endif
            </div>
        </div>

        @if ($canHandle && $ticket->status !== TicketStatus::Selesai)
            <div class="d-flex gap-2">
                @if ($ticket->status !== TicketStatus::Diproses)
                    <form method="POST" action="{{ route('admin.tickets.process', $ticket) }}">
                        @csrf
                        <button class="btn btn-primary"><i class="bi bi-play-fill"></i> Mulai Proses</button>
                    </form>
                @endif
                <button class="btn btn-accent" data-bs-toggle="collapse" data-bs-target="#resolveForm"><i class="bi bi-check2-circle"></i> Selesaikan</button>
            </div>
        @endif
    </div>

    <div class="row g-3">
        {{-- Kolom kiri --}}
        <div class="col-lg-8">
            {{-- Form penyelesaian --}}
            @if ($canHandle && $ticket->status !== TicketStatus::Selesai)
                <div class="collapse mb-3 {{ $errors->hasAny(['analysis','follow_up','resolution']) ? 'show' : '' }}" id="resolveForm">
                    <div class="card border-success-subtle">
                        <div class="card-header bg-success-subtle text-success-emphasis fw-semibold">Selesaikan Tiket</div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.tickets.resolve', $ticket) }}">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Analisis Permasalahan <span class="text-danger">*</span></label>
                                    <textarea name="analysis" rows="3" class="form-control @error('analysis') is-invalid @enderror" required>{{ old('analysis', $ticket->analysis) }}</textarea>
                                    @error('analysis') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Tindak Lanjut <span class="text-danger">*</span></label>
                                    <textarea name="follow_up" rows="3" class="form-control @error('follow_up') is-invalid @enderror" required>{{ old('follow_up', $ticket->follow_up) }}</textarea>
                                    @error('follow_up') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Penyelesaian <span class="text-danger">*</span></label>
                                    <textarea name="resolution" rows="3" class="form-control @error('resolution') is-invalid @enderror" required>{{ old('resolution', $ticket->resolution) }}</textarea>
                                    @error('resolution') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <button class="btn btn-accent"><i class="bi bi-check2-circle me-1"></i> Simpan &amp; Selesaikan Tiket</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            <div class="card mb-3">
                <div class="card-header bg-white fw-semibold">Rincian Tiket</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-6"><div class="text-secondary small">Pelapor</div><div>{{ $ticket->reporter_name }}</div></div>
                        <div class="col-sm-6"><div class="text-secondary small">NIP</div><div>{{ $ticket->reporter_nip ?: '—' }}</div></div>
                        <div class="col-sm-6"><div class="text-secondary small">Surel</div><div>{{ $ticket->reporter_email }}</div></div>
                        <div class="col-sm-6"><div class="text-secondary small">WhatsApp</div><div>{{ $ticket->reporter_whatsapp ?: '—' }}</div></div>
                        <div class="col-sm-6"><div class="text-secondary small">Kategori</div><div>{{ $ticket->category->name }}</div></div>
                        <div class="col-sm-6"><div class="text-secondary small">Subkategori</div><div>{{ $ticket->subcategory->name ?? '—' }}</div></div>
                        <div class="col-sm-6"><div class="text-secondary small">Bidang Penanganan</div><div>{{ $ticket->assigned_bidang ? \Illuminate\Support\Str::headline($ticket->assigned_bidang) : 'Layanan Umum (Super Admin)' }}</div></div>
                        <div class="col-sm-6"><div class="text-secondary small">Ditangani Oleh</div><div>{{ $ticket->handler->name ?? '—' }}</div></div>
                        <div class="col-12"><hr class="my-1"></div>
                        <div class="col-12"><div class="text-secondary small">Judul</div><div class="fw-semibold">{{ $ticket->title }}</div></div>
                        <div class="col-12"><div class="text-secondary small">Uraian</div><div style="white-space:pre-line">{{ $ticket->description }}</div></div>
                    </div>

                    @if ($ticket->attachments->isNotEmpty())
                        <hr>
                        <div class="text-secondary small mb-2">Lampiran</div>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($ticket->attachments as $att)
                                <a href="{{ route('admin.tickets.attachments.download', [$ticket, $att->id]) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-paperclip"></i> {{ \Illuminate\Support\Str::limit($att->original_name, 24) }} <span class="text-secondary">({{ $att->humanSize() }})</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            @if ($ticket->status === TicketStatus::Selesai)
                <div class="card mb-3 border-success-subtle">
                    <div class="card-header bg-success-subtle text-success-emphasis fw-semibold"><i class="bi bi-clipboard2-check me-1"></i> Hasil Penanganan</div>
                    <div class="card-body">
                        <div class="mb-3"><div class="text-secondary small">Analisis Permasalahan</div><div style="white-space:pre-line">{{ $ticket->analysis }}</div></div>
                        <div class="mb-3"><div class="text-secondary small">Tindak Lanjut</div><div style="white-space:pre-line">{{ $ticket->follow_up }}</div></div>
                        <div><div class="text-secondary small">Penyelesaian</div><div style="white-space:pre-line">{{ $ticket->resolution }}</div></div>
                        <hr>
                        <div class="small text-secondary">Diselesaikan pada {{ $ticket->resolved_at?->translatedFormat('d F Y, H:i') }} WIB</div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Kolom kanan --}}
        <div class="col-lg-4">
            @if ($canRedistribute)
                <div class="card mb-3">
                    <div class="card-header bg-white fw-semibold">Redistribusi</div>
                    <div class="card-body">
                        <p class="small text-secondary">Pindahkan tiket ke kategori/bidang yang tepat bila salah distribusi.</p>
                        <form method="POST" action="{{ route('admin.tickets.redistribute', $ticket) }}">
                            @csrf
                            <div class="input-group input-group-sm">
                                <select name="category_id" class="form-select">
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}" @selected($cat->id === $ticket->category_id)>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-outline-accent"><i class="bi bi-arrow-left-right"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            @if ($canHandle)
                <div class="card mb-3">
                    <div class="card-header bg-white fw-semibold">Catatan Internal</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.tickets.notes', $ticket) }}" class="mb-3">
                            @csrf
                            <textarea name="note" rows="2" class="form-control form-control-sm mb-2" placeholder="Tambahkan catatan (hanya untuk admin)…" required></textarea>
                            <button class="btn btn-sm btn-outline-accent w-100"><i class="bi bi-plus"></i> Tambah Catatan</button>
                        </form>
                        @forelse ($ticket->notes as $note)
                            <div class="border-start border-3 ps-2 mb-2 small">
                                <div style="white-space:pre-line">{{ $note->note }}</div>
                                <div class="text-secondary" style="font-size:.75rem">{{ $note->user->name ?? 'Sistem' }} · {{ $note->created_at->translatedFormat('d M Y, H:i') }}</div>
                            </div>
                        @empty
                            <p class="text-secondary small mb-0">Belum ada catatan.</p>
                        @endforelse
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-header bg-white fw-semibold">Riwayat Aktivitas</div>
                <div class="card-body">
                    <ul class="hd-timeline mb-0">
                        @foreach ($ticket->activities as $activity)
                            <li>
                                <span class="hd-dot"></span>
                                <div class="fw-medium small">{{ $activity->label() }}</div>
                                <div class="text-secondary" style="font-size:.75rem">
                                    {{ $activity->created_at->translatedFormat('d M Y, H:i') }}
                                    @if ($activity->user) · {{ $activity->user->name }} @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
