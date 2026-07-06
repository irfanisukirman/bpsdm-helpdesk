@extends('layouts.admin')
@section('title', $category->exists ? 'Ubah Kategori' : 'Tambah Kategori')
@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h1 class="h4 fw-bold mb-3">{{ $category->exists ? 'Ubah' : 'Tambah' }} Kategori</h1>
            <div class="card p-4">
                <form method="POST" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}">
                    @csrf
                    @if ($category->exists) @method('PUT') @endif
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $category->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Distribusi ke <span class="text-danger">*</span></label>
                            <select name="routing_role" id="routing_role" class="form-select">
                                <option value="admin_bidang" @selected(old('routing_role', $category->routing_role) === 'admin_bidang')>Admin Bidang</option>
                                <option value="super_admin" @selected(old('routing_role', $category->routing_role) === 'super_admin')>Super Admin</option>
                            </select>
                        </div>
                        <div class="col-md-6" id="bidangWrap">
                            <label class="form-label">Kode Bidang</label>
                            <input type="text" name="routing_bidang" value="{{ old('routing_bidang', $category->routing_bidang) }}" class="form-control @error('routing_bidang') is-invalid @enderror" placeholder="mis. sertifikasi">
                            <div class="form-text">Diisi bila distribusi ke Admin Bidang; kosongkan bila ke Super Admin.</div>
                            @error('routing_bidang') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Alamat Notifikasi <span class="text-danger">*</span></label>
                            <input type="email" name="notify_email" value="{{ old('notify_email', $category->notify_email) }}" class="form-control @error('notify_email') is-invalid @enderror" required>
                            <div class="form-text">Tujuan notifikasi tiket baru untuk kategori ini.</div>
                            @error('notify_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Referensi Kategori LMS <span class="text-secondary">(opsional)</span></label>
                            <input type="text" name="lms_category_ref" value="{{ old('lms_category_ref', $category->lms_category_ref) }}" class="form-control">
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $category->is_active ?? true))>
                                <label class="form-check-label" for="is_active">Kategori aktif</label>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">Batal</a>
                        <button class="btn btn-accent"><i class="bi bi-save me-1"></i> Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
<script>
    const roleEl = document.getElementById('routing_role');
    const wrap = document.getElementById('bidangWrap');
    function toggle() { wrap.style.opacity = roleEl.value === 'super_admin' ? .5 : 1; }
    roleEl.addEventListener('change', toggle); toggle();
</script>
@endpush
