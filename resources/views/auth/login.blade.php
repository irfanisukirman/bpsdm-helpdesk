@extends('layouts.public')
@section('title', 'Masuk Admin')
@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-5 col-xl-4">
            <div class="text-center mb-3">
                <span class="hd-logo mx-auto mb-2" style="background:var(--hd-accent);"><i class="bi bi-person-lock"></i></span>
                <h1 class="h4 fw-bold mb-1">Masuk Area Internal</h1>
                <p class="text-secondary small">Khusus admin bidang, super admin, dan pimpinan.</p>
            </div>
            <div class="card p-4">
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Alamat Surel</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autofocus>
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kata Sandi</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label small" for="remember">Ingat saya</label>
                    </div>
                    <button type="submit" class="btn btn-accent w-100"><i class="bi bi-box-arrow-in-right me-1"></i> Masuk</button>
                </form>
            </div>
            <div class="text-center mt-3">
                <a href="{{ route('home') }}" class="btn btn-link text-decoration-none small"><i class="bi bi-arrow-left"></i> Kembali ke Beranda</a>
            </div>
        </div>
    </div>
@endsection
