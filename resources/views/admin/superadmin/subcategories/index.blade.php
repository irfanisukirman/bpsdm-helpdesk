@extends('layouts.admin')
@section('title', 'Subkategori')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 fw-bold mb-0">Subkategori</h1>
        <button class="btn btn-accent" data-bs-toggle="collapse" data-bs-target="#addSub"><i class="bi bi-plus-lg"></i> Tambah Subkategori</button>
    </div>

    <div class="collapse mb-3" id="addSub">
        <div class="card card-body">
            <form method="POST" action="{{ route('admin.subcategories.store') }}" class="row g-2 align-items-end">
                @csrf
                <div class="col-md-4">
                    <label class="form-label small">Kategori</label>
                    <select name="category_id" class="form-select form-select-sm" required>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label small">Nama Subkategori</label>
                    <input type="text" name="name" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-sm btn-accent w-100">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3">
        @foreach ($categories as $cat)
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header bg-white fw-semibold">{{ $cat->name }}</div>
                    <ul class="list-group list-group-flush">
                        @forelse ($cat->subcategories as $sub)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>
                                    {{ $sub->name }}
                                    @unless ($sub->is_active) <span class="badge bg-secondary-subtle text-secondary-emphasis ms-1">Nonaktif</span> @endunless
                                </span>
                                <span class="d-flex gap-1">
                                    <form method="POST" action="{{ route('admin.subcategories.update', $sub) }}">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="name" value="{{ $sub->name }}">
                                        <input type="hidden" name="is_active" value="{{ $sub->is_active ? 0 : 1 }}">
                                        <button class="btn btn-sm btn-outline-secondary" title="Aktif/Nonaktif"><i class="bi bi-toggle-{{ $sub->is_active ? 'on' : 'off' }}"></i></button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.subcategories.destroy', $sub) }}" onsubmit="return confirm('Hapus subkategori ini?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </span>
                            </li>
                        @empty
                            <li class="list-group-item text-secondary small">Belum ada subkategori.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        @endforeach
    </div>
@endsection
