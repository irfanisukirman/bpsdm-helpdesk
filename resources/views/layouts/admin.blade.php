<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Helpdesk BPSDM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="{{ asset('css/helpdesk.css') }}" rel="stylesheet">
</head>
<body class="hd-admin">
@php
    $user = auth()->user();
    $nav = fn ($pattern) => request()->routeIs($pattern) ? 'active' : '';
@endphp
<div class="d-lg-flex">
    {{-- Sidebar --}}
    <aside class="hd-sidebar p-3 flex-shrink-0">
        <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center gap-2 text-white text-decoration-none mb-4 px-2">
            <span class="hd-logo" style="background:rgba(255,255,255,.14);"><i class="bi bi-headset"></i></span>
            <span class="lh-1"><strong>Helpdesk</strong><br><small class="hd-brand-sub">BPSDM Jabar</small></span>
        </a>

        <nav class="nav flex-column gap-1">
            <div class="hd-side-label px-2 mb-1">Menu</div>
            <a class="nav-link px-3 py-2 {{ $nav('admin.dashboard') }}" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
            <a class="nav-link px-3 py-2 {{ $nav('admin.tickets.*') }}" href="{{ route('admin.tickets.index') }}"><i class="bi bi-ticket-detailed me-2"></i>Tiket</a>

            @if ($user->isSuperAdmin())
                <div class="hd-side-label px-2 mb-1 mt-3">Pengelolaan</div>
                <a class="nav-link px-3 py-2 {{ $nav('admin.categories.*') }}" href="{{ route('admin.categories.index') }}"><i class="bi bi-diagram-3 me-2"></i>Kategori</a>
                <a class="nav-link px-3 py-2 {{ $nav('admin.subcategories.*') }}" href="{{ route('admin.subcategories.index') }}"><i class="bi bi-list-nested me-2"></i>Subkategori</a>
                <a class="nav-link px-3 py-2 {{ $nav('admin.users.*') }}" href="{{ route('admin.users.index') }}"><i class="bi bi-people me-2"></i>Akun Admin</a>
            @endif
        </nav>
    </aside>

    {{-- Konten --}}
    <div class="flex-grow-1 min-vw-0">
        <header class="hd-topbar d-flex justify-content-between align-items-center px-3 px-lg-4 py-2">
            <a class="btn btn-sm btn-outline-secondary d-lg-none" data-bs-toggle="offcanvas" href="#" role="button"><i class="bi bi-list"></i></a>
            <div class="ms-auto d-flex align-items-center gap-3">
                <a href="{{ route('home') }}" target="_blank" class="text-decoration-none small text-secondary"><i class="bi bi-box-arrow-up-right"></i> Situs Publik</a>
                <div class="dropdown">
                    <button class="btn btn-sm btn-light border dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i> {{ $user->name }}
                        <span class="badge text-bg-secondary ms-1">{{ $user->role->label() }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><span class="dropdown-item-text small text-secondary">{{ $user->email }}</span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-1"></i> Keluar</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <main class="p-3 p-lg-4">
            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-1"></i>{{ session('status') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    <button class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
@stack('scripts')
</body>
</html>
