<?php

namespace App\Enums;

enum OrderChannel: string
{
    case Web = 'web';
    case Shopee = 'shopee';

    public function label(): string
    {
        return match ($this) {
            self::Web => 'Web',
            self::Shopee => 'Shopee',
        };
    }
}
