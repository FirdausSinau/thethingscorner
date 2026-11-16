<?php

namespace App\Console\Commands;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;

class ExpirePayments extends Command
{
    protected $signature = 'payments:expire';

    protected $description = 'Tandai pembayaran pending atau gagal yang sudah melewati batas waktu';

    public function handle(PaymentService $payments): int
    {
        $expired = 0;

        Payment::query()
            ->whereIn('status', [PaymentStatus::Pending->value, PaymentStatus::Failed->value])
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->chunkById(100, function ($items) use ($payments, &$expired): void {
                foreach ($items as $payment) {
                    try {
                        $payments->expire($payment);
                        $expired++;
                    } catch (ValidationException) {
                        // Pembayaran berubah status saat command berjalan.
                    }
                }
            });

        $this->info("{$expired} pembayaran kedaluwarsa.");

        return self::SUCCESS;
    }
}
