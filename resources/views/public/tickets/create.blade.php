@extends('layouts.public')
@section('title', 'Ajukan Tiket')
@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-9 col-xl-8">
            <div class="mb-3">
                <h1 class="h3 fw-bold mb-1">Formulir Pengajuan Tiket</h1>
                <p class="text-secondary">Kolom bertanda <span class="text-danger">*</span> wajib diisi.</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-1"></i> Mohon periksa kembali isian Anda.
                    <ul class="mb-0 mt-1 small">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card p-4">
                <form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data" novalidate>
                    @csrf

                    {{-- Honeypot anti-spam: harus tetap kosong (disembunyikan dari pengguna) --}}
                    <div class="d-none" aria-hidden="true">
                        <label>Website</label>
                        <input type="text" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    <h6 class="text-uppercase text-secondary small fw-bold mb-3">Data Pelapor</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="reporter_name" value="{{ old('reporter_name') }}" class="form-control @error('reporter_name') is-invalid @enderror" required>
                            @error('reporter_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">NIP <span class="text-secondary">(opsional)</span></label>
                            <input type="text" name="reporter_nip" value="{{ old('reporter_nip') }}" class="form-control @error('reporter_nip') is-invalid @enderror">
                            @error('reporter_nip') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Alamat Surel <span class="text-danger">*</span></label>
                            <input type="email" name="reporter_email" value="{{ old('reporter_email') }}" class="form-control @error('reporter_email') is-invalid @enderror" required>
                            <div class="form-text">Dipakai untuk konfirmasi dan pelacakan tiket.</div>
                            @error('reporter_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nomor WhatsApp <span class="text-secondary">(opsional)</span></label>
                            <input type="text" name="reporter_whatsapp" value="{{ old('reporter_whatsapp') }}" class="form-control @error('reporter_whatsapp') is-invalid @enderror">
                            @error('reporter_whatsapp') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="text-uppercase text-secondary small fw-bold mb-3">Rincian Kendala</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Kategori Layanan <span class="text-danger">*</span></label>
                            <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                <option value="">— Pilih kategori —</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Subkategori <span class="text-secondary">(opsional)</span></label>
                            <select name="subcategory_id" id="subcategory_id" class="form-select @error('subcategory_id') is-invalid @enderror">
                                <option value="">— Pilih kategori terlebih dahulu —</option>
                            </select>
                            @error('subcategory_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Prioritas</label>
                            <select name="priority" class="form-select">
                                @foreach (\App\Enums\Priority::options() as $opt)
                                    <option value="{{ $opt['value'] }}" @selected(old('priority', 'sedang') === $opt['value'])>{{ $opt['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Judul Kendala <span class="text-danger">*</span></label>
                            <input type="text" name="title" value="{{ old('title') }}" class="form-control @error('title') is-invalid @enderror" maxlength="200" required>
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Uraian Kendala <span class="text-danger">*</span></label>
                            <textarea name="description" rows="5" class="form-control @error('description') is-invalid @enderror" required>{{ old('description') }}</textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Lampiran <span class="text-secondary">(opsional)</span></label>
                            <input type="file" name="attachments[]" class="form-control @error('attachments.*') is-invalid @enderror" multiple accept=".jpg,.jpeg,.png,.pdf">
                            <div class="form-text">
                                Maksimal {{ config('helpdesk.attachments.max_files') }} berkas, tipe JPG/PNG/PDF,
                                ukuran per berkas ≤ {{ round(config('helpdesk.attachments.max_size_kb') / 1024) }} MB.
                            </div>
                            @error('attachments.*') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <hr class="my-4">
                    <div class="form-check mb-3 @error('consent') is-invalid @enderror">
                        <input class="form-check-input @error('consent') is-invalid @enderror" type="checkbox" name="consent" id="consent" value="1" @checked(old('consent')) required>
                        <label class="form-check-label small" for="consent">
                            Saya menyetujui bahwa data yang saya berikan (nama, NIP, surel, nomor WhatsApp) digunakan
                            untuk keperluan penanganan tiket sesuai kebijakan perlindungan data pribadi BPSDM. <span class="text-danger">*</span>
                        </label>
                        @error('consent') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('home') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-accent"><i class="bi bi-send me-1"></i> Kirim Tiket</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const SUBS = @json($categories->mapWithKeys(fn ($c) => [$c->id => $c->activeSubcategories->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])]));
    const OLD_SUB = @json(old('subcategory_id'));
    const catEl = document.getElementById('category_id');
    const subEl = document.getElementById('subcategory_id');

    function renderSubs() {
        const list = SUBS[catEl.value] || [];
        subEl.innerHTML = '';
        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = list.length ? '— Pilih subkategori (opsional) —' : '— Tidak ada subkategori —';
        subEl.appendChild(placeholder);
        list.forEach(s => {
            const o = document.createElement('option');
            o.value = s.id; o.textContent = s.name;
            if (String(OLD_SUB) === String(s.id)) o.selected = true;
            subEl.appendChild(o);
        });
    }
    catEl.addEventListener('change', renderSubs);
    if (catEl.value) renderSubs();
</script>
@endpush
