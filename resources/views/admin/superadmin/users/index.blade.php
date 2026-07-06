@extends('layouts.admin')
@section('title', 'Akun Admin')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 fw-bold mb-0">Akun Admin</h1>
        <a href="{{ route('admin.users.create') }}" class="btn btn-accent"><i class="bi bi-person-plus"></i> Tambah Akun</a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Nama</th><th>Surel</th><th>Peran</th><th>Bidang</th><th class="text-center">Status</th><th></th></tr>
                </thead>
                <tbody>
                    @foreach ($users as $u)
                        <tr>
                            <td class="fw-medium">{{ $u->name }}</td>
                            <td class="small">{{ $u->email }}</td>
                            <td><span class="badge bg-body-secondary text-dark">{{ $u->role->label() }}</span></td>
                            <td class="small">{{ $u->bidang ? \Illuminate\Support\Str::headline($u->bidang) : '—' }}</td>
                            <td class="text-center">
                                @if ($u->is_active)
                                    <span class="badge bg-success-subtle text-success-emphasis">Aktif</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.users.edit', $u) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('admin.users.destroy', $u) }}" class="d-inline" onsubmit="return confirm('Hapus akun ini?');">
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
    <div class="mt-3">{{ $users->links() }}</div>
@endsection
