<?php

namespace App\Services;

use App\Models\TicketSequence;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Penghasil nomor tiket unik berformat HD-YYYYMMDD-XXXX.
 * Urutan XXXX di-reset harian dan aman dari race condition melalui penguncian
 * baris di dalam transaksi (PRD Bagian 4.8 & 5.2).
 */
class TicketNumberService
{
    public function generate(?Carbon $date = null): string
    {
        $date = $date ?: Carbon::now();
        $seqDate = $date->format('Y-m-d');

        $number = DB::transaction(function () use ($seqDate) {
            // Kunci baris urutan hari ini (atau buat bila belum ada).
            $sequence = TicketSequence::query()
                ->lockForUpdate()
                ->find($seqDate);

            if (! $sequence) {
                // Sisipkan baris awal; bila balapan, ambil ulang dengan kunci.
                TicketSequence::query()->insertOrIgnore([
                    'seq_date' => $seqDate,
                    'last_number' => 0,
                ]);
                $sequence = TicketSequence::query()->lockForUpdate()->find($seqDate);
            }

            $next = $sequence->last_number + 1;
            $sequence->last_number = $next;
            $sequence->save();

            return $next;
        });

        return sprintf('HD-%s-%04d', $date->format('Ymd'), $number);
    }
}
