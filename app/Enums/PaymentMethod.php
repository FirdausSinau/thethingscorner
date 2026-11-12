<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Qris = 'qris';
    case Va = 'va';
    case Ewallet = 'ewallet';
    case ManualTransfer = 'manual_transfer';

    public function label(): string
    {
        return match ($this) {
            self::Qris => 'QRIS',
            self::Va => 'Virtual Account',
            self::Ewallet => 'E-wallet',
            self::ManualTransfer => 'Transfer manual',
        };
    }

    public function reservationSeconds(): int
    {
        return match ($this) {
            self::Qris => (int) config('payment.reservations.qris_seconds', 1800),
            self::Va => (int) config('payment.reservations.va_seconds', 1800),
            self::Ewallet => (int) config('payment.reservations.ewallet_seconds', 1800),
            self::ManualTransfer => (int) config('payment.reservations.manual_transfer_seconds', 3600),
        };
    }

    public function reservationLabel(): string
    {
        $seconds = $this->reservationSeconds();

        if ($seconds < 60) {
            return "{$seconds} detik";
        }

        return intdiv($seconds, 60).' menit';
    }
}
