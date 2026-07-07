@extends('layouts.admin')
@section('title', 'Dashboard Pimpinan')
@section('content')
    <div class="mb-3">
        <h1 class="h4 fw-bold mb-0">Dashboard Pimpinan</h1>
        <p class="text-secondary mb-0 small">Statistik & kinerja layanan Helpdesk (hanya baca).</p>
    </div>

    @include('admin.partials.stat-cards')

    <div class="row g-3 mt-1">
        <div class="col-lg-3 col-6">
            <div class="card p-3 h-100 text-center">
                <div class="hd-stat-label">Rata-rata Waktu Penyelesaian</div>
                <div class="hd-stat-value text-accent mt-1">{{ $charts['avg_resolution_hours'] }}</div>
                <div class="small text-secondary">jam</div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="card p-3 h-100 text-center">
                <div class="hd-stat-label">Persentase Penyelesaian</div>
                <div class="hd-stat-value text-success mt-1">{{ $charts['completion_rate'] }}%</div>
                <div class="small text-secondary">dari total tiket</div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card p-3 h-100">
                <div class="fw-semibold mb-2 small text-secondary">Tiket per Kategori</div>
                <canvas id="chartCategory" height="120"></canvas>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-lg-8">
            <div class="card p-3 h-100">
                <div class="fw-semibold mb-2 small text-secondary">Tiket per Bulan (12 bulan terakhir)</div>
                <canvas id="chartMonth" height="90"></canvas>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header bg-white fw-semibold">Rekap per Bidang</div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light"><tr><th>Bidang</th><th class="text-end">Total</th><th class="text-end">Selesai</th></tr></thead>
                        <tbody>
                            @foreach ($perBidang as $row)
                                <tr><td class="small">{{ $row['kategori'] }}</td><td class="text-end">{{ $row['total'] }}</td><td class="text-end">{{ $row['selesai'] }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js" crossorigin="anonymous"></script>
<script>
    const accent = '#008a41';
    // Palet selaras identitas BPSDM Jabar (hijau, cyan, kuning + variannya)
    const palette = ['#008a41','#159fd6','#f7b21a','#4bbf7b','#66c6ea','#f9cd63'];

    new Chart(document.getElementById('chartMonth'), {
        type: 'line',
        data: {
            labels: @json($charts['per_month']['labels']),
            datasets: [{
                label: 'Jumlah Tiket',
                data: @json($charts['per_month']['data']),
                borderColor: accent, backgroundColor: 'rgba(11,90,52,.12)',
                fill: true, tension: .3, pointRadius: 3,
            }]
        },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
    });

    new Chart(document.getElementById('chartCategory'), {
        type: 'bar',
        data: {
            labels: @json($charts['per_category']['labels']),
            datasets: [{ label: 'Tiket', data: @json($charts['per_category']['data']), backgroundColor: palette }]
        },
        options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { precision: 0 } } } }
    });
</script>
@endpush
