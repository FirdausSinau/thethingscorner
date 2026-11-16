<?php

namespace App\Services\Shipping;

final readonly class ShippingRateRequest
{
    public function __construct(
        public string $destinationCity,
        public string $destinationPostalCode,
        public int $weightGrams,
        public ?string $courier = null,
    ) {}
}
