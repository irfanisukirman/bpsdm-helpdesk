<?php

namespace App\Enums;

enum Priority: string
{
    case Rendah = 'rendah';
    case Sedang = 'sedang';
    case Tinggi = 'tinggi';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Rendah => 'bg-secondary-subtle text-secondary-emphasis',
            self::Sedang => 'bg-warning-subtle text-warning-emphasis',
            self::Tinggi => 'bg-danger-subtle text-danger-emphasis',
        };
    }

    public static function options(): array
    {
        return array_map(fn (self $p) => ['value' => $p->value, 'label' => $p->label()], self::cases());
    }
}
