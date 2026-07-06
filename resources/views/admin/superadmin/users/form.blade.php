@extends('layouts.admin')
@section('title', $user->exists ? 'Ubah Akun' : 'Tambah Akun')
@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h1 class="h4 fw-bold mb-3">{{ $user->exists ? 'Ubah' : 'Tambah' }} Akun Admin</h1>
            <div class="card p-4">
                <form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}">
                    @csrf
                    @if ($user->exists) @method('PUT') @endif
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Surel <span class="text-danger">*</span></label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Peran <span class="text-danger">*</span></label>
                            <select name="role" id="role" class="form-select">
                                @foreach (['admin_bidang' => 'Admin Bidang', 'super_admin' => 'Super Admin', 'pimpinan' => 'Pimpinan'] as $val => $lbl)
                                    <option value="{{ $val }}" @selected(old('role', $user->role->value ?? 'admin_bidang') === $val)>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6" id="bidangWrap">
                            <label class="form-label">Bidang</label>
                            <select name="bidang" class="form-select @error('bidang') is-invalid @enderror">
                                <option value="">— Pilih bidang —</option>
                                @foreach ($bidangOptions as $code => $name)
                                    <option value="{{ $code }}" @selected(old('bidang', $user->bidang) === $code)>{{ $name }} ({{ $code }})</option>
                                @endforeach
                            </select>
                            <div class="form-text">Wajib untuk peran Admin Bidang.</div>
                            @error('bidang') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kata Sandi {{ $user->exists ? '(kosongkan bila tidak diubah)' : '*' }}</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" {{ $user->exists ? '' : 'required' }}>
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Konfirmasi Kata Sandi</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $user->is_active ?? true))>
                                <label class="form-check-label" for="is_active">Akun aktif</label>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Batal</a>
                        <button class="btn btn-accent"><i class="bi bi-save me-1"></i> Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
<script>
    const roleEl = document.getElementById('role');
    const wrap = document.getElementById('bidangWrap');
    function toggle() { wrap.style.display = roleEl.value === 'admin_bidang' ? '' : 'none'; }
    roleEl.addEventListener('change', toggle); toggle();
</script>
@endpush
