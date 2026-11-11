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
            self::MenungguKonfirmasi => 'Menunggu konfirmasi admin',
            self::Dikemas => 'Dikemas',
            self::Dikirim => 'Dikirim',
            self::Selesai => 'Selesai',
            self::Dibatalkan => 'Dibatalkan',
            self::Kedaluwarsa => 'Kedaluwarsa',
        };
    }
}
