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

    public function reservationMinutes(): int
    {
        return match ($this) {
            self::Qris, self::Va, self::Ewallet => 30,
            self::ManualTransfer => 60,
        };
    }
}
