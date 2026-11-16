<?php

namespace App\Services\Shipping;

final readonly class ShippingRate
{
    public function __construct(
        public string $courier,
        public string $service,
        public string $description,
        public int $cost,
        public string $estimatedDays,
        public bool $isFallback = false,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'courier' => $this->courier,
            'service' => $this->service,
            'description' => $this->description,
            'cost' => $this->cost,
            'estimated_days' => $this->estimatedDays,
            'is_fallback' => $this->isFallback,
        ];
    }
}
