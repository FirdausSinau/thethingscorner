<?php

namespace App\Enums;

enum UserRole: string
{
    case Buyer = 'buyer';
    case Admin = 'admin';
    case AdminMaster = 'admin_master';

    public function label(): string
    {
        return match ($this) {
            self::Buyer => 'Pembeli',
            self::Admin => 'Admin Biasa',
            self::AdminMaster => 'Admin Master',
        };
    }

    public function isAdmin(): bool
    {
        return $this !== self::Buyer;
    }
}
