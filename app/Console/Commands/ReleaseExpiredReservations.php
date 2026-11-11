<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Enums\ReservationStatus;
use App\Models\Product\ProductVariant;
use App\Models\StockReservation;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('reservations:release-expired')]
#[Description('Release stock reservations that have passed their expiry time.')]
class ReleaseExpiredReservations extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $released = 0;

        StockReservation::query()
            ->active()
            ->where('expires_at', '<=', now())
            ->orderBy('id')
            ->chunkById(100, function ($reservations) use (&$released): void {
                foreach ($reservations as $reservation) {
                    DB::transaction(function () use ($reservation, &$released): void {
                        $variant = ProductVariant::query()
                            ->whereKey($reservation->variant_id)
                            ->lockForUpdate()
                            ->first();

                        if ($variant) {
                            $variant->forceFill([
                                'stock_reserved' => max(0, $variant->stock_reserved - $reservation->qty),
                            ])->save();
                        }

                        $reservation->update(['status' => ReservationStatus::Expired]);

                        $order = $reservation->order()->lockForUpdate()->first();

                        if ($order && $order->status === OrderStatus::MenungguPembayaran) {
                            $activeReservations = StockReservation::query()
                                ->where('order_id', $order->id)
                                ->where('status', ReservationStatus::Active->value)
                                ->count();

                            if ($activeReservations === 0) {
                                $order->update(['status' => OrderStatus::Kedaluwarsa]);
                            }
                        }

                        $released++;
                    });
                }
            });

        $this->info("Released {$released} expired reservation(s).");

        return self::SUCCESS;
    }
}
