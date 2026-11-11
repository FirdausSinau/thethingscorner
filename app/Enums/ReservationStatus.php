<?php

namespace App\Enums;

enum ReservationStatus: string
{
    case Active = 'active';
    case Released = 'released';
    case Consumed = 'consumed';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Aktif',
            self::Released => 'Dilepas',
            self::Consumed => 'Dipakai',
            self::Expired => 'Kedaluwarsa',
        };
    }
}
