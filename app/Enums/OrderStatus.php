<?php

namespace App\Enums;

enum OrderStatus: string
{
    case MenungguPembayaran = 'MENUNGGU_PEMBAYARAN';
    case Dibayar = 'DIBAYAR';
    case MenungguKonfirmasi = 'MENUNGGU_KONFIRMASI';
    case Dikemas = 'DIKEMAS';
    case Dikirim = 'DIKIRIM';
    case Selesai = 'SELESAI';
    case Dibatalkan = 'DIBATALKAN';
    case Kedaluwarsa = 'KEDALUWARSA';

    public function label(): string
    {
        return match ($this) {
            self::MenungguPembayaran => 'Menunggu pembayaran',
            self::Dibayar => 'Dibayar',
            self::MenungguKonfirmasi => 'Dipending',
            self::Dikemas => 'Dikemas',
            self::Dikirim => 'Dikirim',
            self::Selesai => 'Selesai',
            self::Dibatalkan => 'Dibatalkan',
            self::Kedaluwarsa => 'Kedaluwarsa',
        };
    }

    public function isAwaitingPayment(): bool
    {
        return $this === self::MenungguPembayaran;
    }

    public function isCancelled(): bool
    {
        return in_array($this, [self::Dibatalkan, self::Kedaluwarsa], true);
    }

    /**
     * Status yang boleh dituju langsung dari status ini.
     *
     * @return array<int, self>
     */
    public function allowedNext(): array
    {
        return match ($this) {
            self::MenungguPembayaran => [self::Dibayar, self::Dibatalkan, self::Kedaluwarsa],
            self::Dibayar => [self::MenungguKonfirmasi],
            self::MenungguKonfirmasi => [self::Dikemas, self::Dibatalkan],
            self::Dikemas => [self::Dikirim],
            self::Dikirim => [self::Selesai],
            self::Selesai, self::Dibatalkan, self::Kedaluwarsa => [],
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

    /**
     * Pilihan untuk dropdown admin. Kedaluwarsa hanya boleh diatur sistem.
     *
     * @return array<int, self>
     */
    public function manualNext(): array
    {
        return array_values(array_filter(
            $this->allowedNext(),
            fn (self $status): bool => $status !== self::Kedaluwarsa
        ));
    }
}
