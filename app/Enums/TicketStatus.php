<?php

namespace App\Enums;

enum TicketStatus: string
{
    case Diterima = 'diterima';
    case Didistribusikan = 'didistribusikan';
    case Diproses = 'diproses';
    case Selesai = 'selesai';

    public function label(): string
    {
        return match ($this) {
            self::Diterima => 'Diterima',
            self::Didistribusikan => 'Didistribusikan',
            self::Diproses => 'Diproses',
            self::Selesai => 'Selesai',
        };
    }

    /** Kelas warna badge Bootstrap (tema lembut, PRD Bagian 11). */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Diterima => 'bg-secondary-subtle text-secondary-emphasis',
            self::Didistribusikan => 'bg-info-subtle text-info-emphasis',
            self::Diproses => 'bg-primary-subtle text-primary-emphasis',
            self::Selesai => 'bg-success-subtle text-success-emphasis',
        };
    }

    public static function options(): array
    {
        return array_map(fn (self $s) => ['value' => $s->value, 'label' => $s->label()], self::cases());
    }
}
