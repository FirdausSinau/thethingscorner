<?php

namespace App\Services\Shipping;

use App\Enums\ShipmentStatus;
use DateTimeInterface;

final readonly class TrackingEvent
{
    /**
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public string $trackingNumber,
        public ShipmentStatus $status,
        public string $description,
        public DateTimeInterface $occurredAt,
        public array $raw = [],
    ) {}
}
