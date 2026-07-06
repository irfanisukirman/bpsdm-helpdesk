<?php

namespace App\Enums;

enum UserRole: string
{
    case AdminBidang = 'admin_bidang';
    case SuperAdmin = 'super_admin';
    case Pimpinan = 'pimpinan';

    public function label(): string
    {
        return match ($this) {
            self::AdminBidang => 'Admin Bidang',
            self::SuperAdmin => 'Super Admin',
            self::Pimpinan => 'Pimpinan',
        };
    }
}
