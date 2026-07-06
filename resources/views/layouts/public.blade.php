<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Helpdesk') — {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="{{ asset('css/helpdesk.css') }}" rel="stylesheet">
</head>
<body class="hd-public d-flex flex-column min-vh-100 bg-body-tertiary">
    <nav class="navbar navbar-expand-lg hd-navbar shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}">
                <span class="hd-logo"><i class="bi bi-headset"></i></span>
                <span class="d-flex flex-column lh-1">
                    <strong>Helpdesk BPSDM</strong>
                    <small class="hd-brand-sub">Provinsi Jawa Barat</small>
                </span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="nav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                    <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('tickets.create') }}">Ajukan Tiket</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('track.form') }}">Lacak Tiket</a></li>
                    <li class="nav-item ms-lg-2"><a class="btn btn-sm btn-outline-light" href="{{ route('login') }}"><i class="bi bi-person-lock"></i> Masuk Admin</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="flex-grow-1 py-4 py-lg-5">
        <div class="container">
            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-1"></i> {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @yield('content')
        </div>
    </main>

    <footer class="hd-footer mt-auto py-4">
        <div class="container">
            <div class="row gy-3 align-items-start">
                <div class="col-md-7">
                    <strong>{{ config('helpdesk.instansi.nama_panjang') }}</strong>
                    <p class="mb-0 small opacity-75">{{ config('helpdesk.instansi.alamat') }}</p>
                </div>
                <div class="col-md-5 text-md-end small">
                    <div><i class="bi bi-envelope me-1"></i> {{ config('helpdesk.instansi.kontak_email') }}</div>
                    <div class="opacity-75 mt-1">&copy; {{ date('Y') }} BPSDM Provinsi Jawa Barat</div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    @stack('scripts')
</body>
</html>
