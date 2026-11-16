<?php

namespace App\Services\Shipping;

use App\Models\Order;

final readonly class ShipmentRequest
{
    public function __construct(
        public Order $order,
        public string $courier,
        public string $service,
        public int $weightGrams,
        public int $cost,
    ) {}
}
