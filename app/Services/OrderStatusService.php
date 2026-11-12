<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\ReservationStatus;
use App\Models\Order;
use App\Models\Product\ProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderStatusService
{
    public function change(Order $order, OrderStatus $to, bool $bySystem = false): Order
    {
        return DB::transaction(function () use ($order, $to, $bySystem): Order {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $from  = $order->status;

            if ($from === $to) {
                return $order;
            }

            if ($to === OrderStatus::Kedaluwarsa && ! $bySystem) {
                $this->fail("Status Kedaluwarsa hanya bisa diatur otomatis oleh sistem.");
            }

            if (! $from->canTransitionTo($to)) {
                $this->fail($this->transitionMessage($order, $from, $to));
            }

            $this->syncStock($order, $from, $to);

            $order->status = $to;

            // Keluar dari "menunggu pembayaran" tanpa dibatalkan = pembayaran terkonfirmasi
            if ($from->isAwaitingPayment() && ! $to->isCancelled() && ! $order->paid_at) {
                $order->paid_at = now();
            }

            if ($to === OrderStatus::Selesai && ! $order->completed_at) {
                $order->completed_at = now();
            }

            $order->save();

            return $order;
        }, 3);
    }

    private function transitionMessage(Order $order, OrderStatus $from, OrderStatus $to): string
    {
        if ($from->isFinal()) {
            return "{$order->order_code} sudah {$from->label()} dan tidak bisa diubah lagi.";
        }

        $allowed = collect($from->manualNext())->map->label()->implode(', ');

        return "{$order->order_code}: {$from->label()} tidak bisa langsung menjadi {$to->label()}. Langkah yang diizinkan: {$allowed}.";
    }

    private function syncStock(Order $order, OrderStatus $from, OrderStatus $to): void
    {
        $cancelling = $to->isCancelled();

        // Dari menunggu pembayaran: reservasi dilepas (batal) atau dikonfirmasi (stok fisik berkurang)
        if ($from->isAwaitingPayment()) {
            $reservations = $order->reservations()->active()->lockForUpdate()->get();

            if ($reservations->isEmpty() && ! $cancelling) {
                $this->fail("Reservasi stok {$order->order_code} sudah tidak aktif (kedaluwarsa), jadi pembayaran tidak bisa dikonfirmasi.");
            }

            $variants = $this->lockVariants($reservations->pluck('variant_id'));

            foreach ($reservations as $reservation) {
                $variant = $variants[$reservation->variant_id];

                if (! $cancelling && $variant->stock < $reservation->qty) {
                    $this->fail("Stok fisik {$variant->sku} ({$variant->stock}) kurang dari pesanan ({$reservation->qty}). Perbarui stok dulu.");
                }

                $variant->decrement('stock_reserved', $reservation->qty);

                if (! $cancelling) {
                    $variant->decrement('stock', $reservation->qty);
                }

                $reservation->update([
                    'status' => match (true) {
                        ! $cancelling                    => ReservationStatus::Consumed,
                        $to === OrderStatus::Kedaluwarsa => ReservationStatus::Expired,
                        default                          => ReservationStatus::Released,
                    },
                ]);
            }

            return;
        }

        // Sudah dibayar lalu dibatalkan (belum dikirim): stok fisik dikembalikan
        if ($cancelling) {
            $items    = $order->items;
            $variants = $this->lockVariants($items->pluck('variant_id'));

            foreach ($items as $item) {
                $variants[$item->variant_id]->increment('stock', $item->qty);
            }
        }
    }

    private function lockVariants(Collection $ids): Collection
    {
        return ProductVariant::whereIn('id', $ids->unique())
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['status' => $message]);
    }
}