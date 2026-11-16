<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'PENDING';
    case Paid = 'PAID';
    case Expired = 'EXPIRED';
    case Failed = 'FAILED';
    case Refunded = 'REFUNDED';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu pembayaran',
            self::Paid => 'Lunas',
            self::Expired => 'Kedaluwarsa',
            self::Failed => 'Gagal',
            self::Refunded => 'Dana dikembalikan',
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
            self::Pending => [self::Paid, self::Expired, self::Failed],
            self::Failed => [self::Paid, self::Expired],
            self::Paid => [self::Refunded],
            self::Expired, self::Refunded => [],
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

    public function isPaid(): bool
    {
        return $this === self::Paid;
    }

    /**
     * Status pesanan yang dipicu saat pembayaran mencapai status ini.
     * `FAILED` dan `REFUNDED` ditangani manual oleh PaymentService.
     */
    public function orderStatus(): ?OrderStatus
    {
        return match ($this) {
            self::Pending => OrderStatus::MenungguPembayaran,
            self::Paid => OrderStatus::Dibayar,
            self::Expired => OrderStatus::Kedaluwarsa,
            self::Failed => null,
            self::Refunded => OrderStatus::Dibatalkan,
        };
    }
}
