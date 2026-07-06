<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * Perhitungan hari kerja (mengecualikan Sabtu, Minggu, dan hari libur nasional).
 * Dipakai untuk pengingat/eskalasi (Bagian 6) dan batas buka kembali (Bagian 5.10).
 */
class WorkingDaysService
{
    /** @var array<string, bool> */
    protected array $holidays;

    public function __construct(?array $holidays = null)
    {
        $list = $holidays ?? config('helpdesk.national_holidays', []);
        $this->holidays = [];
        foreach ($list as $date) {
            $date = trim((string) $date);
            if ($date !== '') {
                $this->holidays[$date] = true;
            }
        }
    }

    public function isWorkingDay(CarbonInterface $date): bool
    {
        if ($date->isWeekend()) {
            return false;
        }

        return ! isset($this->holidays[$date->format('Y-m-d')]);
    }

    /**
     * Jumlah hari kerja penuh yang telah berlalu sejak $from hingga $to.
     * Menghitung hari kerja di antara keduanya (tidak termasuk hari $from itu sendiri).
     */
    public function elapsedWorkingDays(CarbonInterface $from, ?CarbonInterface $to = null): int
    {
        $to = $to ? CarbonImmutable::instance($to) : CarbonImmutable::now();
        $cursor = CarbonImmutable::instance($from)->startOfDay();
        $end = $to->startOfDay();

        if ($end <= $cursor) {
            return 0;
        }

        $count = 0;
        $cursor = $cursor->addDay();
        while ($cursor <= $end) {
            if ($this->isWorkingDay($cursor)) {
                $count++;
            }
            $cursor = $cursor->addDay();
        }

        return $count;
    }

    /**
     * Tambah sejumlah hari kerja pada sebuah tanggal.
     */
    public function addWorkingDays(CarbonInterface $from, int $days): CarbonImmutable
    {
        $cursor = CarbonImmutable::instance($from);
        $added = 0;
        while ($added < $days) {
            $cursor = $cursor->addDay();
            if ($this->isWorkingDay($cursor)) {
                $added++;
            }
        }

        return $cursor;
    }
}
