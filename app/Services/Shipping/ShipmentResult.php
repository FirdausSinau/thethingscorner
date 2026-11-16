<?php

namespace App\Services\Shipping;

use App\Enums\ShipmentStatus;

final readonly class ShipmentResult
{
    /**
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public string $provider,
        public string $reference,
        public string $trackingNumber,
        public ShipmentStatus $status,
        public array $raw = [],
    ) {}
}
