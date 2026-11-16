<?php

namespace App\Services\Shipping;

interface ShippingProvider
{
    public function name(): string;

    /**
     * @return array<int, ShippingRate>
     */
    public function rates(ShippingRateRequest $request): array;

    public function createShipment(ShipmentRequest $request): ShipmentResult;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function parseTrackingCallback(array $payload): TrackingEvent;
}
