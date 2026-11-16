<?php

namespace App\Services\Payment;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use InvalidArgumentException;

/**
 * Gateway lokal untuk demo dan test. Tidak memanggil jaringan.
 *
 * External ID deterministik supaya request ganda untuk order yang sama
 * menghasilkan referensi yang sama.
 */
class FakePaymentGateway implements PaymentGateway
{
    public function name(): string
    {
        return 'fake';
    }

    public function create(Order $order, PaymentMethod $method): PaymentInstruction
    {
        $externalId = 'FAKE-'.$order->order_code;

        return new PaymentInstruction(
            provider: $this->name(),
            externalId: $externalId,
            method: $method,
            amount: (int) $order->total,
            expiresAt: now()->addSeconds($method->reservationSeconds()),
            code: $externalId,
            instructions: $this->instructions($method, $externalId),
            metadata: [
                'order_code' => $order->order_code,
                'fake' => true,
            ],
        );
    }

    public function parseCallback(array $payload): PaymentEvent
    {
        $externalId = trim((string) ($payload['external_id'] ?? ''));

        if ($externalId === '') {
            throw new InvalidArgumentException('external_id wajib diisi.');
        }

        $statusValue = strtoupper(trim((string) ($payload['status'] ?? '')));
        $status = PaymentStatus::tryFrom($statusValue);

        if (! $status) {
            throw new InvalidArgumentException("Status pembayaran tidak dikenal: {$statusValue}.");
        }

        $amount = filter_var($payload['amount'] ?? null, FILTER_VALIDATE_INT);

        if ($amount === false || $amount < 1) {
            throw new InvalidArgumentException('amount harus bilangan bulat lebih dari 0.');
        }

        return new PaymentEvent(
            provider: $this->name(),
            externalId: $externalId,
            status: $status,
            amount: $amount,
            raw: $payload,
        );
    }

    private function instructions(PaymentMethod $method, string $externalId): string
    {
        return match ($method) {
            PaymentMethod::Qris => "Pindai QR simulasi {$externalId}. Pembayaran dianggap lunas lewat tombol simulasi.",
            PaymentMethod::Va => "Gunakan nomor VA simulasi {$externalId}. Pembayaran dianggap lunas lewat tombol simulasi.",
            PaymentMethod::Ewallet => "Buka e-wallet simulasi dan bayar dengan kode {$externalId}.",
            PaymentMethod::ManualTransfer => 'Transfer ke rekening toko, lalu unggah bukti transfer. Admin akan memverifikasi.',
        };
    }
}
