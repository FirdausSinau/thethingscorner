<?php

namespace App\Console\Commands;

use App\Models\StockReservation;
use App\Services\ReservationService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('reservations:release-expired')]
#[Description('Release stock reservations that have passed their expiry time.')]
class ReleaseExpiredReservations extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(ReservationService $reservations): int
    {
        $released = 0;

        StockReservation::query()
            ->active()
            ->where('expires_at', '<=', now())
            ->orderBy('id')
            ->chunkById(100, function ($items) use ($reservations, &$released): void {
                foreach ($items as $reservation) {
                    if ($reservations->releaseIfExpired($reservation)) {
                        $released++;
                    }
                }
            });

        $this->info("Released {$released} expired reservation(s).");

        return self::SUCCESS;
    }
}
