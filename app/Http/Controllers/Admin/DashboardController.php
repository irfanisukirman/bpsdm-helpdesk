<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(protected DashboardService $dashboard) {}

    public function index(Request $request)
    {
        $user = $request->user();

        // Pimpinan: dashboard statistik & grafik (hanya baca).
        if ($user->isPimpinan()) {
            return view('admin.dashboard.pimpinan', [
                'summary' => $this->dashboard->summary(),
                'perBidang' => $this->dashboard->perBidang(),
                'charts' => $this->dashboard->charts(),
            ]);
        }

        // Admin bidang: cakupan terbatas pada bidangnya.
        $bidang = $user->isSuperAdmin() ? null : $user->bidang;

        $recent = Ticket::with('category')
            ->when($bidang, fn ($q) => $q->where('assigned_bidang', $bidang))
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.dashboard.index', [
            'summary' => $this->dashboard->summary($bidang),
            'perBidang' => $user->isSuperAdmin() ? $this->dashboard->perBidang() : null,
            'recent' => $recent,
        ]);
    }
}
