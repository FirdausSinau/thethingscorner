<?php

namespace App\Services\Shipping;

use App\Enums\ShipmentStatus;
use Carbon\CarbonImmutable;
use InvalidArgumentException;

/**
 * Provider lokal untuk demo dan test. Tarif berasal dari config dan
 * deterministik supaya hasil test tidak berubah.
 */
class FakeShippingProvider implements ShippingProvider
{
    public function name(): string
    {
        return 'fake';
    }

    public function rates(ShippingRateRequest $request): array
    {
        if ($request->weightGrams < 1) {
            throw new InvalidArgumentException('Berat pengiriman harus lebih dari 0 gram.');
        }

        $billableKg = max(1, (int) ceil($request->weightGrams / 1000));
        $couriers = (array) config('shipping.fake.couriers', []);
        $rates = [];

        foreach ($couriers as $courier) {
            $courierCode = (string) ($courier['courier'] ?? '');

            if ($courierCode === '') {
                continue;
            }

            if ($request->courier !== null
                && strcasecmp($request->courier, $courierCode) !== 0) {
                continue;
            }

            $baseCost = (int) ($courier['base_cost'] ?? 0);
            $perKg = (int) ($courier['per_kg'] ?? 0);

            $rates[] = new ShippingRate(
                courier: $courierCode,
                service: (string) ($courier['service'] ?? 'REG'),
                description: (string) ($courier['description'] ?? 'Reguler'),
                cost: $baseCost + $perKg * ($billableKg - 1),
                estimatedDays: (string) ($courier['etd'] ?? '2-3 hari'),
            );
        }

        if ($rates === []) {
            throw new InvalidArgumentException('Tidak ada layanan pengiriman untuk kurir yang dipilih.');
        }

        return $rates;
    }

    public function createShipment(ShipmentRequest $request): ShipmentResult
    {
        if ($request->weightGrams < 1) {
            throw new InvalidArgumentException('Berat pengiriman harus lebih dari 0 gram.');
        }

        if ($request->cost < 0) {
            throw new InvalidArgumentException('Biaya pengiriman tidak boleh negatif.');
        }

        $courier = strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', $request->courier));

        if ($courier === '') {
            throw new InvalidArgumentException('Kurir wajib diisi.');
        }

        $reference = 'FAKE-SHIP-'.$request->order->order_code;
        $trackingNumber = "FAKE-{$courier}-{$request->order->order_code}";

        return new ShipmentResult(
            provider: $this->name(),
            reference: $reference,
            trackingNumber: $trackingNumber,
            status: ShipmentStatus::Pending,
            raw: [
                'order_code' => $request->order->order_code,
                'courier' => $request->courier,
                'service' => $request->service,
            ],
        );
    }

    public function parseTrackingCallback(array $payload): TrackingEvent
    {
        $trackingNumber = trim((string) ($payload['tracking_number'] ?? ''));

        if ($trackingNumber === '') {
            throw new InvalidArgumentException('tracking_number wajib diisi.');
        }

        $statusValue = strtoupper(trim((string) ($payload['status'] ?? '')));
        $status = ShipmentStatus::tryFrom($statusValue);

        if (! $status) {
            throw new InvalidArgumentException("Status pengiriman tidak dikenal: {$statusValue}.");
        }

        $occurredAt = isset($payload['occurred_at'])
            ? CarbonImmutable::parse((string) $payload['occurred_at'])
            : CarbonImmutable::now();

        return new TrackingEvent(
            trackingNumber: $trackingNumber,
            status: $status,
            description: (string) ($payload['description'] ?? $status->label()),
            occurredAt: $occurredAt,
            raw: $payload,
        );
    }
}
