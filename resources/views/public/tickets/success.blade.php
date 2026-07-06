@extends('layouts.public')
@section('title', 'Tiket Terkirim')
@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-7 col-xl-6 text-center">
            <div class="card p-4 p-lg-5">
                <div class="mb-3">
                    <span class="hd-stat-icon bg-success-subtle text-success mx-auto" style="width:64px;height:64px;font-size:2rem;">
                        <i class="bi bi-check-lg"></i>
                    </span>
                </div>
                <h1 class="h4 fw-bold">Tiket Anda Berhasil Dikirim</h1>
                <p class="text-secondary">Simpan nomor tiket berikut untuk melacak status penanganan.</p>

                <div class="bg-body-secondary rounded-3 py-3 my-3">
                    <div class="small text-secondary">Nomor Tiket</div>
                    <div class="h3 fw-bold text-accent mb-0" style="letter-spacing:.03em;">{{ $ticketNumber }}</div>
                </div>

                <p class="small text-secondary">
                    Salinan nomor tiket juga telah kami kirim ke alamat surel Anda. Gunakan nomor ini beserta surel
                    Anda pada halaman pelacakan.
                </p>

                <div class="d-flex justify-content-center gap-2 mt-2">
                    <a href="{{ route('track.form') }}" class="btn btn-accent"><i class="bi bi-search me-1"></i> Lacak Tiket</a>
                    <a href="{{ route('home') }}" class="btn btn-outline-secondary">Beranda</a>
                </div>
            </div>
        </div>
    </div>
@endsection
