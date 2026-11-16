<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\ShipmentStatus;
use App\Models\IntegrationLog;
use App\Models\Order;
use App\Models\Shipment;
use App\Services\Shipping\ShipmentRequest;
use App\Services\Shipping\ShippingProvider;
use App\Services\Shipping\ShippingRate;
use App\Services\Shipping\ShippingRateRequest;
use App\Services\Shipping\TrackingEvent;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ShippingService
{
    public function __construct(
        private ShippingProvider $provider,
        private OrderStatusService $statusService,
    ) {}

    /**
     * Tarif ongkir dengan fallback flat. Checkout tidak boleh gagal
     * hanya karena provider sedang bermasalah.
     *
     * @return array<int, ShippingRate>
     */
    public function rates(int $weightGrams, string $city, string $postalCode, ?string $courier = null): array
    {
        if ($weightGrams < 1) {
            $this->fail('Berat pengiriman harus lebih dari 0 gram.');
        }

        try {
            $rates = $this->provider->rates(
                new ShippingRateRequest($city, $postalCode, $weightGrams, $courier)
            );
        } catch (Exception $e) {
            $this->logIntegration(
                event: 'shipping.rate_fallback',
                externalId: null,
                status: IntegrationLog::STATUS_FAILED,
                payload: [
                    'weight_grams' => $weightGrams,
                    'courier' => $courier,
                ],
                message: $e->getMessage(),
            );

            return [$this->flatRate()];
        }

        if ($rates === []) {
            $this->logIntegration(
                event: 'shipping.rate_fallback',
                externalId: null,
                status: IntegrationLog::STATUS_FAILED,
                payload: [
                    'weight_grams' => $weightGrams,
                    'courier' => $courier,
                ],
                message: 'Provider tidak mengembalikan tarif.',
            );

            return [$this->flatRate()];
        }

        return $rates;
    }

    /**
     * @return array<int, ShippingRate>
     */
    public function rateFor(Order $order, ?string $courier = null): array
    {
        return $this->rates(
            weightGrams: $this->weightFor($order),
            city: $order->city,
            postalCode: $order->postal_code,
            courier: $courier,
        );
    }

    /**
     * Berat total dari snapshot order. Produk tanpa berat memakai
     * default 1000 gram per item.
     */
    public function weightFor(Order $order): int
    {
        $order->loadMissing('items.variant.color.product');

        $total = 0;

        foreach ($order->items as $item) {
            $productWeight = (int) ($item->variant?->color?->product?->weight_grams ?? 0);
            $unitWeight = $productWeight > 0 ? $productWeight : 1000;
            $total += $unitWeight * $item->qty;
        }

        return max(1, $total);
    }

    /**
     * Buat shipment untuk order DIKEMAS. Request ganda mengembalikan
     * shipment aktif yang sama.
     */
    public function createForOrder(
        Order $order,
        string $courier,
        string $service,
        int $weightGrams,
        int $cost,
        ?string $trackingNumber = null,
        ?string $providerReference = null,
    ): Shipment {
        $courier = trim($courier);
        $service = trim($service);

        if ($courier === '' || $service === '') {
            $this->fail('Kurir dan layanan wajib diisi.');
        }

        if ($weightGrams < 1) {
            $this->fail('Berat pengiriman harus lebih dari 0 gram.');
        }

        if ($cost < 0) {
            $this->fail('Biaya pengiriman tidak boleh negatif.');
        }

        $this->assertCanShip($order);

        $existing = $this->activeShipmentFor($order);

        if ($existing) {
            return $this->attachManualTracking($existing, $trackingNumber, $providerReference);
        }

        $provider = $trackingNumber ? 'manual' : $this->provider->name();
        $reference = $providerReference;
        $status = ShipmentStatus::Pending;

        if (! $trackingNumber) {
            $result = $this->provider->createShipment(
                new ShipmentRequest($order, $courier, $service, $weightGrams, $cost)
            );

            $provider = $result->provider;
            $reference = $result->reference;
            $trackingNumber = $result->trackingNumber;
            $status = $result->status;
        }

        return DB::transaction(function () use (
            $order,
            $courier,
            $service,
            $weightGrams,
            $cost,
            $trackingNumber,
            $provider,
            $reference,
            $status,
        ): Shipment {
            $lockedOrder = Order::query()
                ->whereKey($order->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->status !== OrderStatus::Dikemas) {
                $this->fail("Pesanan {$lockedOrder->order_code} berstatus {$lockedOrder->status->label()}; shipment hanya bisa dibuat saat DIKEMAS.");
            }

            $existing = $this->activeShipmentFor($lockedOrder);

            if ($existing) {
                return $existing;
            }

            $shipment = Shipment::create([
                'order_id' => $lockedOrder->id,
                'provider' => $provider,
                'courier' => $courier,
                'service' => $service,
                'provider_reference' => $reference,
                'tracking_number' => $trackingNumber,
                'cost' => $cost,
                'weight_grams' => $weightGrams,
                'status' => $status,
            ]);

            $this->logIntegration(
                event: 'shipping.created',
                externalId: $shipment->tracking_number,
                status: IntegrationLog::STATUS_PROCESSED,
                provider: $shipment->provider,
                payload: [
                    'order_code' => $lockedOrder->order_code,
                    'provider' => $shipment->provider,
                    'courier' => $shipment->courier,
                    'service' => $shipment->service,
                    'cost' => $shipment->cost,
                    'weight_grams' => $shipment->weight_grams,
                ],
            );

            return $shipment;
        }, 3);
    }

    /**
     * Terapkan event tracking. Status order mengikuti shipment:
     * DIKEMAS menjadi DIKIRIM, DELIVERED menyelesaikan order.
     */
    public function updateTracking(Shipment $shipment, TrackingEvent $event): void
    {
        DB::transaction(function () use ($shipment, $event): void {
            $order = Order::query()
                ->whereKey($shipment->order_id)
                ->lockForUpdate()
                ->firstOrFail();

            $locked = Shipment::query()
                ->whereKey($shipment->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->tracking_number !== null && $locked->tracking_number !== $event->trackingNumber) {
                $this->fail("Nomor resi {$event->trackingNumber} tidak cocok dengan shipment {$locked->tracking_number}.");
            }

            if ($locked->status === $event->status) {
                $this->logIntegration(
                    event: 'shipping.tracking_ignored',
                    externalId: $locked->tracking_number,
                    status: IntegrationLog::STATUS_IGNORED,
                    provider: $locked->provider,
                    payload: [
                        'status' => $event->status->value,
                        'occurred_at' => $event->occurredAt->format(DATE_ATOM),
                    ],
                    message: 'status tracking duplikat',
                );

                return;
            }

            if (! $locked->status->canTransitionTo($event->status)) {
                $this->fail("Status pengiriman {$locked->status->label()} tidak bisa menjadi {$event->status->label()}.");
            }

            $attributes = [
                'tracking_number' => $locked->tracking_number ?? $event->trackingNumber,
                'status' => $event->status,
            ];

            if (in_array($event->status, [ShipmentStatus::Pickup, ShipmentStatus::InTransit], true)
                && ! $locked->shipped_at) {
                $attributes['shipped_at'] = $event->occurredAt;
            }

            if ($event->status === ShipmentStatus::Delivered) {
                $attributes['delivered_at'] = $event->occurredAt;

                if (! $locked->shipped_at) {
                    $attributes['shipped_at'] = $event->occurredAt;
                }
            }

            $locked->update($attributes);

            if (in_array($event->status, [ShipmentStatus::Pickup, ShipmentStatus::InTransit, ShipmentStatus::Delivered], true)) {
                if ($order->status === OrderStatus::Dikemas) {
                    $this->statusService->change($order, OrderStatus::Dikirim, null, 'Paket diserahkan ke kurir', true);
                    $order->refresh();
                }

                if ($order->status === OrderStatus::Dikirim && $event->status === ShipmentStatus::Delivered) {
                    $this->statusService->change($order, OrderStatus::Selesai, null, 'Paket diterima', true);
                }
            }

            $this->logIntegration(
                event: 'shipping.tracking_updated',
                externalId: $locked->tracking_number,
                status: IntegrationLog::STATUS_PROCESSED,
                provider: $locked->provider,
                payload: [
                    'order_code' => $order->order_code,
                    'status' => $event->status->value,
                    'description' => $event->description,
                    'occurred_at' => $event->occurredAt->format(DATE_ATOM),
                    'order_status' => $order->fresh()->status->value,
                ],
            );
        }, 3);
    }

    private function activeShipmentFor(Order $order): ?Shipment
    {
        return $order->shipments()
            ->active()
            ->latest('id')
            ->first();
    }

    private function attachManualTracking(
        Shipment $shipment,
        ?string $trackingNumber,
        ?string $providerReference,
    ): Shipment {
        if ($trackingNumber && ! $shipment->tracking_number) {
            $shipment->update([
                'provider' => 'manual',
                'provider_reference' => $providerReference,
                'tracking_number' => $trackingNumber,
            ]);
        }

        return $shipment->fresh();
    }

    private function assertCanShip(Order $order): void
    {
        if ($order->status !== OrderStatus::Dikemas) {
            $this->fail("Pesanan {$order->order_code} berstatus {$order->status->label()}; shipment hanya bisa dibuat saat DIKEMAS.");
        }
    }

    private function flatRate(): ShippingRate
    {
        $config = (array) config('shipping.flat', []);

        return new ShippingRate(
            courier: (string) ($config['courier'] ?? 'Flat'),
            service: (string) ($config['service'] ?? 'REG'),
            description: (string) ($config['description'] ?? 'Tarif flat'),
            cost: (int) ($config['cost'] ?? 15000),
            estimatedDays: (string) ($config['etd'] ?? '2-4 hari'),
            isFallback: true,
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function logIntegration(
        string $event,
        ?string $externalId,
        string $status,
        array $payload = [],
        ?string $message = null,
        ?string $provider = null,
    ): void {
        IntegrationLog::create([
            'provider' => $provider ?? $this->provider->name(),
            'type' => 'shipping',
            'event' => $event,
            'external_id' => $externalId,
            'status' => $status,
            'payload' => $payload,
            'message' => $message,
        ]);
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['shipping' => $message]);
    }
}
