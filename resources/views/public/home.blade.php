@extends('layouts.public')
@section('title', 'Beranda')
@section('content')
    <div class="hd-hero p-4 p-lg-5 mb-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <span class="badge text-bg-light text-accent mb-2">Layanan Bantuan Resmi</span>
                <h1 class="fw-bold mb-3">Sampaikan Kendala &amp; Permintaan Bantuan Layanan BPSDM</h1>
                <p class="text-secondary mb-4">
                    Ajukan pengaduan, konsultasi, atau permintaan bantuan atas layanan BPSDM Provinsi Jawa Barat.
                    Setiap laporan tercatat dengan nomor tiket dan dapat Anda pantau tanpa perlu membuat akun.
                </p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('tickets.create') }}" class="btn btn-accent btn-lg"><i class="bi bi-plus-circle me-1"></i> Ajukan Tiket</a>
                    <a href="{{ route('track.form') }}" class="btn btn-outline-accent btn-lg"><i class="bi bi-search me-1"></i> Lacak Tiket</a>
                </div>
            </div>
            <div class="col-lg-5 text-center d-none d-lg-block">
                <img src="{{ asset('images/logo-bpsdm.png') }}" alt="Logo BPSDM Provinsi Jawa Barat" class="img-fluid" style="max-height:180px;"
                     onerror="this.style.display='none';document.getElementById('heroFallback').style.display='inline-block';">
                <i id="heroFallback" class="bi bi-headset text-accent" style="display:none;font-size:10rem;opacity:.85;"></i>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        @php $steps = [
            ['bi-pencil-square', 'Ajukan', 'Isi formulir kendala Anda beserta lampiran bila diperlukan.', 'hd-grad-green', 'hd-bar-green'],
            ['bi-ticket-perforated', 'Dapatkan Nomor', 'Sistem memberi nomor tiket unik dan mengirimkannya ke surel Anda.', 'hd-grad-blue', 'hd-bar-blue'],
            ['bi-gear', 'Ditangani', 'Tiket otomatis diarahkan ke bidang yang berwenang.', 'hd-grad-yellow', 'hd-bar-yellow'],
            ['bi-shield-check', 'Selesai', 'Pantau status hingga penyelesaian melalui halaman pelacakan.', 'hd-grad-green', 'hd-bar-green'],
        ]; @endphp
        @foreach ($steps as [$icon, $title, $desc, $grad, $bar])
            <div class="col-md-6 col-lg-3">
                <div class="card hd-step h-100 p-4 text-center {{ $bar }}">
                    <span class="hd-step-num">{{ sprintf('%02d', $loop->iteration) }}</span>
                    <span class="hd-step-badge {{ $grad }}"><i class="bi {{ $icon }}"></i></span>
                    <h6 class="fw-semibold mb-1">{{ $title }}</h6>
                    <p class="text-secondary small mb-0">{{ $desc }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card p-4">
        <div class="row align-items-center g-3">
            <div class="col-md-8">
                <h5 class="fw-semibold mb-1">Pengguna LMS BPSDM?</h5>
                <p class="text-secondary mb-0 small">Anda dapat mengakses Helpdesk ini langsung dari menu LMS. Cukup sertakan NIP (opsional) pada formulir untuk mempermudah verifikasi.</p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="{{ config('helpdesk.lms_url') }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-secondary"><i class="bi bi-box-arrow-up-right me-1"></i> Buka LMS</a>
            </div>
        </div>
    </div>
@endsection
