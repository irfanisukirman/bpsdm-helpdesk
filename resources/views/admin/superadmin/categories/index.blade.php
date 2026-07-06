@extends('layouts.admin')
@section('title', 'Kategori')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 fw-bold mb-0">Kategori Layanan</h1>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-accent"><i class="bi bi-plus-lg"></i> Tambah Kategori</a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Nama</th><th>Distribusi</th><th>Notify Email</th><th class="text-center">Subkategori</th><th class="text-center">Tiket</th><th class="text-center">Aktif</th><th></th></tr>
                </thead>
                <tbody>
                    @foreach ($categories as $cat)
                        <tr>
                            <td class="fw-medium">{{ $cat->name }}</td>
                            <td class="small">
                                {{ $cat->routing_role === 'super_admin' ? 'Super Admin' : 'Admin Bidang' }}
                                @if ($cat->routing_bidang) <span class="text-secondary">({{ $cat->routing_bidang }})</span> @endif
                            </td>
                            <td class="small">{{ $cat->notify_email }}</td>
                            <td class="text-center">{{ $cat->subcategories_count }}</td>
                            <td class="text-center">{{ $cat->tickets_count }}</td>
                            <td class="text-center">
                                @if ($cat->is_active)
                                    <span class="badge bg-success-subtle text-success-emphasis">Aktif</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.categories.edit', $cat) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('admin.categories.destroy', $cat) }}" class="d-inline" onsubmit="return confirm('Hapus kategori ini?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
