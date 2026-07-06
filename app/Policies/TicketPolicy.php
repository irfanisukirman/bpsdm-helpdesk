<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

/**
 * Pemisahan data antar bidang ditegakkan di lapisan otorisasi (PRD Bagian 8),
 * bukan hanya di tampilan.
 */
class TicketPolicy
{
    public function viewAny(User $user): bool
    {
        // Semua peran internal dapat melihat daftar (dengan cakupan berbeda di query).
        return true;
    }

    public function view(User $user, Ticket $ticket): bool
    {
        if ($user->isSuperAdmin() || $user->isPimpinan()) {
            return true;
        }

        // Admin bidang hanya melihat tiket pada bidangnya.
        return $user->isAdminBidang()
            && $ticket->assigned_bidang !== null
            && $ticket->assigned_bidang === $user->bidang;
    }

    /** Memproses/menyelesaikan/menambah catatan — hanya admin bidang terkait & super admin. */
    public function handle(User $user, Ticket $ticket): bool
    {
        if ($user->isPimpinan()) {
            return false; // hanya baca
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isAdminBidang()
            && $ticket->assigned_bidang !== null
            && $ticket->assigned_bidang === $user->bidang;
    }

    /** Redistribusi tiket salah kategori — hanya super admin. */
    public function redistribute(User $user, Ticket $ticket): bool
    {
        return $user->isSuperAdmin();
    }
}
