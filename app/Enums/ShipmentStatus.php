<?php

namespace App\Enums;

enum ShipmentStatus: string
{
    case Pending = 'PENDING';
    case Pickup = 'PICKUP';
    case InTransit = 'IN_TRANSIT';
    case Delivered = 'DELIVERED';
    case Returned = 'RETURNED';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu pickup',
            self::Pickup => 'Dijemput kurir',
            self::InTransit => 'Dalam perjalanan',
            self::Delivered => 'Diterima',
            self::Returned => 'Dikembalikan',
        };
    }

    /**
     * Status yang boleh dituju langsung dari status ini.
     *
     * @return array<int, self>
     */
    public function allowedNext(): array
    {
        return match ($this) {
            self::Pending => [self::Pickup, self::InTransit, self::Returned],
            self::Pickup => [self::InTransit, self::Returned],
            self::InTransit => [self::Delivered, self::Returned],
            self::Delivered, self::Returned => [],
        };
    }

    public function canTransitionTo(self $to): bool
    {
        return in_array($to, $this->allowedNext(), true);
    }

    public function isFinal(): bool
    {
        return $this->allowedNext() === [];
    }

    public function isDelivered(): bool
    {
        return $this === self::Delivered;
    }

    public function isReturned(): bool
    {
        return $this === self::Returned;
    }
}
