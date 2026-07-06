@php
    $cards = [
        ['Total Tiket', $summary['total'], 'bi-ticket-detailed', 'text-accent bg-body-secondary'],
        ['Diterima', $summary['diterima'], 'bi-inbox', 'text-secondary-emphasis bg-secondary-subtle'],
        ['Didistribusikan', $summary['didistribusikan'], 'bi-diagram-3', 'text-info-emphasis bg-info-subtle'],
        ['Diproses', $summary['diproses'], 'bi-gear', 'text-primary-emphasis bg-primary-subtle'],
        ['Selesai', $summary['selesai'], 'bi-check2-circle', 'text-success-emphasis bg-success-subtle'],
    ];
@endphp
<div class="row g-3">
    @foreach ($cards as [$label, $value, $icon, $tone])
        <div class="col-6 col-lg">
            <div class="card hd-stat-card p-3 h-100">
                <div class="d-flex align-items-center gap-3">
                    <span class="hd-stat-icon {{ $tone }}"><i class="bi {{ $icon }}"></i></span>
                    <div>
                        <div class="hd-stat-value">{{ $value }}</div>
                        <div class="hd-stat-label">{{ $label }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
