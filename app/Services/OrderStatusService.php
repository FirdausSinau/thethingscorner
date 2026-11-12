<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\ReservationStatus;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\Product\ProductVariant;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderStatusService
{
    /**
     * Ubah status order dan sesuaikan stok dalam satu transaksi.
     *
     * Urutan lock: order, reservasi, lalu varian. Urutan ini sama dengan
     * ReservationService supaya tidak terjadi deadlock.
     */
    public function change(
        Order $order,
        OrderStatus $to,
        ?User $actor = null,
        ?string $note = null,
        bool $bySystem = false
    ): Order {
        return DB::transaction(function () use ($order, $to, $actor, $note, $bySystem): Order {
            $order = Order::query()
                ->whereKey($order->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $from = $order->status;

            if ($from === $to) {
                return $order;
            }

            if ($to === OrderStatus::Kedaluwarsa && ! $bySystem) {
                $this->fail('Status Kedaluwarsa hanya bisa diatur otomatis oleh sistem.');
            }

            if (! $from->canTransitionTo($to)) {
                $this->fail($this->transitionMessage($order, $from, $to));
            }

            $this->syncStock($order, $from, $to);

            $order->status = $to;

            if ($from->isAwaitingPayment() && ! $to->isCancelled() && ! $order->paid_at) {
                $order->paid_at = now();
            }

            if ($to === OrderStatus::Selesai && ! $order->completed_at) {
                $order->completed_at = now();
            }

            $order->save();

            OrderStatusLog::create([
                'order_id' => $order->id,
                'from_status' => $from->value,
                'to_status' => $to->value,
                'actor_id' => $actor?->id,
                'note' => $note,
            ]);

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

        if ($from->isAwaitingPayment()) {
            $reservations = $order->reservations()->active()->lockForUpdate()->get();

            if ($reservations->isEmpty() && ! $cancelling) {
                $this->fail("Reservasi stok {$order->order_code} sudah tidak aktif (kedaluwarsa), jadi pembayaran tidak bisa dikonfirmasi.");
            }

            $variants = $this->lockVariants($reservations->pluck('variant_id'));

            foreach ($reservations as $reservation) {
                $variant = $variants->get($reservation->variant_id);

                if (! $variant) {
                    $this->fail("Varian untuk reservasi {$order->order_code} tidak ditemukan.");
                }

                if ($variant->stock_reserved < $reservation->qty) {
                    $this->fail("Data reservasi {$variant->sku} tidak konsisten. Periksa stok fisik dan stok yang ditahan.");
                }

                if (! $cancelling && $variant->stock < $reservation->qty) {
                    $this->fail("Stok fisik {$variant->sku} ({$variant->stock}) kurang dari pesanan ({$reservation->qty}). Perbarui stok dulu.");
                }

                $variant->decrement('stock_reserved', $reservation->qty);

                if (! $cancelling) {
                    $variant->decrement('stock', $reservation->qty);
                }

                $reservation->update([
                    'status' => match (true) {
                        ! $cancelling => ReservationStatus::Consumed,
                        $to === OrderStatus::Kedaluwarsa => ReservationStatus::Expired,
                        default => ReservationStatus::Released,
                    },
                ]);
            }

            return;
        }

        if ($cancelling) {
            $items = $order->items;
            $variants = $this->lockVariants($items->pluck('variant_id'));

            foreach ($items as $item) {
                $variants->get($item->variant_id)?->increment('stock', $item->qty);
            }
        }
    }

    private function lockVariants(Collection $ids): Collection
    {
        return ProductVariant::query()
            ->whereIn('id', $ids->unique())
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
