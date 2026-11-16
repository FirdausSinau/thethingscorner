<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Enums\ShipmentStatus;
use App\Models\Order;
use App\Services\OrderStatusService;
use Illuminate\Console\Command;

class AutoCompleteOrders extends Command
{
    protected $signature = 'orders:auto-complete';

    protected $description = 'Selesaikan pesanan DIKIRIM yang sudah 7 hari sejak dikirim';

    public function handle(OrderStatusService $service): int
    {
        $cutoff = now()->subDays(7);
        $completed = 0;

        Order::query()
            ->where('status', OrderStatus::Dikirim->value)
            ->with(['latestShipment', 'statusLogs'])
            ->chunkById(100, function ($orders) use ($service, $cutoff, &$completed): void {
                foreach ($orders as $order) {
                    $shipment = $order->latestShipment;

                    if ($shipment?->status === ShipmentStatus::Returned) {
                        continue;
                    }

                    $shippedAt = $shipment?->shipped_at
                        ?? $order->statusLogs
                            ->where('to_status', OrderStatus::Dikirim->value)
                            ->sortByDesc('created_at')
                            ->first()
                            ?->created_at;

                    if (! $shippedAt || $shippedAt->gt($cutoff)) {
                        continue;
                    }

                    $service->change(
                        $order,
                        OrderStatus::Selesai,
                        null,
                        'Auto-selesai setelah 7 hari',
                        true,
                    );

                    $completed++;
                }
            });

        $this->info("{$completed} pesanan diselesaikan otomatis.");

        return self::SUCCESS;
    }
}
