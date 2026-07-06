<?php

namespace App\Services;

use App\Enums\TicketStatus;
use App\Models\Category;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Perhitungan statistik untuk dashboard (PRD Bagian 7).
 * Menerima cakupan bidang opsional untuk membatasi data admin bidang.
 */
class DashboardService
{
    /** @param  string|null  $bidang  null = seluruh bidang (super admin/pimpinan). */
    public function summary(?string $bidang = null): array
    {
        $base = fn (): Builder => $this->scoped($bidang);

        return [
            'total' => $base()->count(),
            'diterima' => $base()->where('status', TicketStatus::Diterima->value)->count(),
            'didistribusikan' => $base()->where('status', TicketStatus::Didistribusikan->value)->count(),
            'diproses' => $base()->where('status', TicketStatus::Diproses->value)->count(),
            'selesai' => $base()->where('status', TicketStatus::Selesai->value)->count(),
            'hari_ini' => $base()->whereDate('created_at', Carbon::today())->count(),
            'bulan_ini' => $base()->whereBetween('created_at', [
                Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth(),
            ])->count(),
        ];
    }

    /** Statistik jumlah tiket per bidang (untuk super admin & pimpinan). */
    public function perBidang(): array
    {
        return Category::orderBy('name')->get()->map(function (Category $category) {
            $query = Ticket::where('category_id', $category->id);

            return [
                'kategori' => $category->name,
                'total' => (clone $query)->count(),
                'selesai' => (clone $query)->where('status', TicketStatus::Selesai->value)->count(),
                'proses' => (clone $query)->whereIn('status', [
                    TicketStatus::Diterima->value,
                    TicketStatus::Didistribusikan->value,
                    TicketStatus::Diproses->value,
                ])->count(),
            ];
        })->all();
    }

    /** Data grafik untuk dashboard pimpinan. */
    public function charts(): array
    {
        return [
            'per_month' => $this->ticketsPerMonth(),
            'per_category' => $this->ticketsPerCategory(),
            'avg_resolution_hours' => $this->averageResolutionHours(),
            'completion_rate' => $this->completionRate(),
        ];
    }

    protected function scoped(?string $bidang): Builder
    {
        $query = Ticket::query();

        return $bidang ? $query->where('assigned_bidang', $bidang) : $query;
    }

    /** Jumlah tiket per bulan untuk 12 bulan terakhir. */
    protected function ticketsPerMonth(): array
    {
        $labels = [];
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $labels[] = $month->translatedFormat('M Y');
            $data[] = Ticket::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        }

        return ['labels' => $labels, 'data' => $data];
    }

    protected function ticketsPerCategory(): array
    {
        $categories = Category::orderBy('name')->get();

        return [
            'labels' => $categories->pluck('name')->all(),
            'data' => $categories->map(fn (Category $c) => Ticket::where('category_id', $c->id)->count())->all(),
        ];
    }

    /** Rata-rata waktu penyelesaian (jam) dari created_at ke resolved_at. */
    protected function averageResolutionHours(): float
    {
        $resolved = Ticket::whereNotNull('resolved_at')->get(['created_at', 'resolved_at']);
        if ($resolved->isEmpty()) {
            return 0.0;
        }

        $totalHours = $resolved->sum(
            fn (Ticket $t) => $t->created_at->diffInMinutes($t->resolved_at) / 60
        );

        return round($totalHours / $resolved->count(), 1);
    }

    /** Persentase tiket selesai terhadap total. */
    protected function completionRate(): float
    {
        $total = Ticket::count();
        if ($total === 0) {
            return 0.0;
        }

        $done = Ticket::where('status', TicketStatus::Selesai->value)->count();

        return round(($done / $total) * 100, 1);
    }
}
