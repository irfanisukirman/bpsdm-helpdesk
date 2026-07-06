@extends('layouts.public')
@section('title', 'Lacak Tiket')
@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-6 col-xl-5">
            <div class="text-center mb-3">
                <h1 class="h4 fw-bold mb-1">Lacak Status Tiket</h1>
                <p class="text-secondary">Masukkan nomor tiket dan surel yang Anda gunakan saat mengajukan.</p>
            </div>
            <div class="card p-4">
                <form method="POST" action="{{ route('track.show') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Nomor Tiket <span class="text-danger">*</span></label>
                        <input type="text" name="ticket_number" value="{{ old('ticket_number') }}" placeholder="HD-YYYYMMDD-XXXX"
                               class="form-control @error('ticket_number') is-invalid @enderror" required>
                        @error('ticket_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat Surel <span class="text-danger">*</span></label>
                        <input type="email" name="reporter_email" value="{{ old('reporter_email') }}"
                               class="form-control @error('reporter_email') is-invalid @enderror" required>
                        @error('reporter_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <button type="submit" class="btn btn-accent w-100"><i class="bi bi-search me-1"></i> Lacak</button>
                </form>
            </div>
        </div>
    </div>
@endsection
