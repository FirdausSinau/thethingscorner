<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\ReservationStatus;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\Product\ProductVariant;
use App\Models\StockReservation;
use Illuminate\Support\Facades\DB;

class ReservationService
{
    /**
     * Lepas reservasi yang sudah lewat batas waktu.
     *
     * Urutan lock: order, reservasi, lalu varian. Urutan ini sama dengan
     * OrderStatusService supaya tidak terjadi deadlock.
     */
    public function releaseIfExpired(StockReservation $reservation): bool
    {
        return DB::transaction(function () use ($reservation): bool {
            $order = Order::query()
                ->whereKey($reservation->order_id)
                ->lockForUpdate()
                ->first();

            $current = StockReservation::query()
                ->whereKey($reservation->getKey())
                ->lockForUpdate()
                ->first();

            if (! $current
                || $current->status !== ReservationStatus::Active
                || $current->expires_at->isFuture()) {
                return false;
            }

            $variant = ProductVariant::query()
                ->whereKey($current->variant_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($variant->stock_reserved < $current->qty) {
                throw new \RuntimeException("Stok yang ditahan untuk {$variant->sku} lebih kecil dari reservasi.");
            }

            $variant->forceFill([
                'stock_reserved' => $variant->stock_reserved - $current->qty,
            ])->save();

            $current->update(['status' => ReservationStatus::Expired]);

            if ($order?->status === OrderStatus::MenungguPembayaran
                && ! StockReservation::query()
                    ->where('order_id', $order->id)
                    ->where('status', ReservationStatus::Active->value)
                    ->exists()) {
                $order->update(['status' => OrderStatus::Kedaluwarsa]);

                OrderStatusLog::create([
                    'order_id' => $order->id,
                    'from_status' => OrderStatus::MenungguPembayaran->value,
                    'to_status' => OrderStatus::Kedaluwarsa->value,
                    'note' => 'Reservasi kedaluwarsa',
                ]);
            }

            return true;
        });
    }
}
