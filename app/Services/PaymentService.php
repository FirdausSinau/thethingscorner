<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\IntegrationLog;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\Payment\PaymentEvent;
use App\Services\Payment\PaymentGateway;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function __construct(
        private PaymentGateway $gateway,
        private OrderStatusService $statusService,
    ) {}

    /**
     * Buat pembayaran pending untuk order. Request ganda mengembalikan
     * pembayaran pending yang sama supaya tidak ada dua instruksi aktif.
     */
    public function createForOrder(Order $order, PaymentMethod $method): Payment
    {
        return DB::transaction(function () use ($order, $method): Payment {
            $order = Order::query()
                ->whereKey($order->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($order->status !== OrderStatus::MenungguPembayaran) {
                $paid = $order->payments()
                    ->where('status', PaymentStatus::Paid->value)
                    ->latest()
                    ->first();

                if ($paid) {
                    return $paid;
                }

                $this->fail("Pesanan {$order->order_code} berstatus {$order->status->label()} dan tidak bisa dibayar.");
            }

            $pending = $order->payments()
                ->where('status', PaymentStatus::Pending->value)
                ->latest()
                ->first();

            if ($pending) {
                return $pending;
            }

            $payment = $method === PaymentMethod::ManualTransfer
                ? $this->createManualPayment($order, $method)
                : $this->createGatewayPayment($order, $method);

            $this->logIntegration(
                provider: $payment->provider,
                event: 'payment.created',
                externalId: $payment->external_id,
                status: IntegrationLog::STATUS_PROCESSED,
                payload: [
                    'order_code' => $order->order_code,
                    'method' => $payment->method->value,
                    'amount' => $payment->amount,
                    'expires_at' => $payment->expires_at?->toIso8601String(),
                ],
            );

            return $payment;
        }, 3);
    }

    /**
     * Tandai pembayaran lunas. Idempotent: pembayaran yang sudah PAID
     * tidak mengubah stok atau status order lagi.
     */
    public function markPaid(Payment $payment, ?User $actor = null): void
    {
        DB::transaction(function () use ($payment, $actor): void {
            $order = Order::query()
                ->whereKey($payment->order_id)
                ->lockForUpdate()
                ->firstOrFail();

            $locked = Payment::query()
                ->whereKey($payment->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status === PaymentStatus::Paid) {
                return;
            }

            if (! $locked->status->canTransitionTo(PaymentStatus::Paid)) {
                $this->fail("Pembayaran {$locked->external_id} berstatus {$locked->status->label()} dan tidak bisa dilunasi.");
            }

            if ((int) $order->total !== (int) $locked->amount) {
                $this->fail("Nominal pembayaran {$locked->amount} tidak sama dengan total pesanan {$order->total}.");
            }

            $otherPaid = Payment::query()
                ->where('order_id', $order->id)
                ->where('status', PaymentStatus::Paid->value)
                ->whereKeyNot($locked->getKey())
                ->exists();

            if ($otherPaid) {
                $this->fail("Pesanan {$order->order_code} sudah punya pembayaran lunas lain.");
            }

            if (! in_array($order->status, [OrderStatus::MenungguPembayaran, OrderStatus::Dibayar], true)) {
                $this->fail("Pesanan {$order->order_code} berstatus {$order->status->label()}, pembayaran tidak bisa dikonfirmasi.");
            }

            if ($order->status === OrderStatus::MenungguPembayaran) {
                $this->statusService->change($order, OrderStatus::Dibayar, $actor, 'Pembayaran diterima');
            }

            $locked->update([
                'status' => PaymentStatus::Paid,
                'paid_at' => $locked->paid_at ?? now(),
                'verified_by' => $actor?->id,
            ]);

            $this->logIntegration(
                provider: $locked->provider,
                event: 'payment.paid',
                externalId: $locked->external_id,
                status: IntegrationLog::STATUS_PROCESSED,
                payload: [
                    'order_code' => $order->order_code,
                    'amount' => $locked->amount,
                    'actor_id' => $actor?->id,
                ],
            );
        }, 3);
    }

    /**
     * Provider menolak pembayaran. Order tetap menunggu sampai expiry
     * supaya percobaan pembayaran lain masih bisa masuk.
     */
    public function markFailed(Payment $payment, ?string $reason = null): void
    {
        DB::transaction(function () use ($payment, $reason): void {
            $locked = Payment::query()
                ->whereKey($payment->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status === PaymentStatus::Failed) {
                return;
            }

            if (! $locked->status->canTransitionTo(PaymentStatus::Failed)) {
                $this->fail("Pembayaran {$locked->external_id} berstatus {$locked->status->label()} dan tidak bisa ditandai gagal.");
            }

            $locked->update(['status' => PaymentStatus::Failed]);

            $this->logIntegration(
                provider: $locked->provider,
                event: 'payment.failed',
                externalId: $locked->external_id,
                status: IntegrationLog::STATUS_PROCESSED,
                payload: ['order_id' => $locked->order_id],
                message: $reason,
            );
        }, 3);
    }

    /**
     * Pembayaran kedaluwarsa. Reservasi dilepas dan order menjadi
     * KEDALUWARSA, tanpa mengurangi stok fisik.
     */
    public function expire(Payment $payment): void
    {
        DB::transaction(function () use ($payment): void {
            $order = Order::query()
                ->whereKey($payment->order_id)
                ->lockForUpdate()
                ->firstOrFail();

            $locked = Payment::query()
                ->whereKey($payment->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status === PaymentStatus::Expired) {
                return;
            }

            if (! $locked->status->canTransitionTo(PaymentStatus::Expired)) {
                $this->fail("Pembayaran {$locked->external_id} berstatus {$locked->status->label()} dan tidak bisa kedaluwarsa.");
            }

            $locked->update(['status' => PaymentStatus::Expired]);

            if ($order->status === OrderStatus::MenungguPembayaran) {
                $this->statusService->change($order, OrderStatus::Kedaluwarsa, null, 'Pembayaran kedaluwarsa', true);
            }

            $this->logIntegration(
                provider: $locked->provider,
                event: 'payment.expired',
                externalId: $locked->external_id,
                status: IntegrationLog::STATUS_PROCESSED,
                payload: [
                    'order_code' => $order->order_code,
                    'order_status' => $order->fresh()->status->value,
                ],
            );
        }, 3);
    }

    /**
     * Admin menyetujui transfer manual. Bukti wajib ada.
     */
    public function verifyManual(Payment $payment, User $admin): void
    {
        $this->ensureAdmin($admin);

        if ($payment->provider !== 'manual' || $payment->method !== PaymentMethod::ManualTransfer) {
            $this->fail('Pembayaran ini bukan transfer manual.');
        }

        if (! $payment->proof_path) {
            $this->fail('Bukti transfer belum diunggah.');
        }

        $payment->refresh();

        $this->markPaid($payment, $admin);
    }

    /**
     * Admin menolak bukti transfer. Status pembayaran tetap PENDING
     * supaya pembeli bisa mengunggah bukti baru atau menunggu expiry.
     */
    public function rejectManual(Payment $payment, User $admin, string $note): void
    {
        $this->ensureAdmin($admin);

        $note = trim($note);

        if ($note === '') {
            $this->fail('Alasan penolakan wajib diisi.');
        }

        DB::transaction(function () use ($payment, $admin, $note): void {
            $locked = Payment::query()
                ->whereKey($payment->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->provider !== 'manual' || $locked->method !== PaymentMethod::ManualTransfer) {
                $this->fail('Pembayaran ini bukan transfer manual.');
            }

            if ($locked->status !== PaymentStatus::Pending) {
                $this->fail("Pembayaran {$locked->external_id} berstatus {$locked->status->label()} dan tidak bisa ditolak.");
            }

            $this->logIntegration(
                provider: $locked->provider,
                event: 'payment.manual_rejected',
                externalId: $locked->external_id,
                status: IntegrationLog::STATUS_PROCESSED,
                payload: [
                    'order_id' => $locked->order_id,
                    'actor_id' => $admin->id,
                ],
                message: $note,
            );
        }, 3);
    }

    /**
     * Proses callback provider yang sudah diparse. Nominal dan provider
     * harus cocok dengan pembayaran yang tersimpan.
     */
    public function handleCallback(PaymentEvent $event): Payment
    {
        $payment = Payment::query()
            ->where('external_id', $event->externalId)
            ->first();

        if (! $payment) {
            $this->logIntegration(
                provider: $event->provider,
                event: 'callback.failed',
                externalId: $event->externalId,
                status: IntegrationLog::STATUS_FAILED,
                payload: $event->raw,
                message: 'external_id tidak dikenal',
            );

            $this->fail("Pembayaran {$event->externalId} tidak ditemukan.");
        }

        if ($payment->provider !== $event->provider) {
            $this->logIntegration(
                provider: $event->provider,
                event: 'callback.failed',
                externalId: $event->externalId,
                status: IntegrationLog::STATUS_FAILED,
                payload: $event->raw,
                message: 'provider tidak cocok',
            );

            $this->fail("Provider callback tidak cocok untuk {$event->externalId}.");
        }

        if ($event->amount !== $payment->amount) {
            $this->logIntegration(
                provider: $payment->provider,
                event: 'callback.failed',
                externalId: $payment->external_id,
                status: IntegrationLog::STATUS_FAILED,
                payload: $event->raw,
                message: 'nominal tidak cocok',
            );

            $this->fail("Nominal callback {$event->amount} tidak cocok dengan tagihan {$payment->amount}.");
        }

        $this->logIntegration(
            provider: $payment->provider,
            event: 'callback.received',
            externalId: $payment->external_id,
            status: IntegrationLog::STATUS_RECEIVED,
            payload: $event->raw,
        );

        if ($payment->status === PaymentStatus::Paid && $event->status === PaymentStatus::Paid) {
            $this->logIntegration(
                provider: $payment->provider,
                event: 'callback.ignored',
                externalId: $payment->external_id,
                status: IntegrationLog::STATUS_IGNORED,
                payload: $event->raw,
                message: 'callback duplikat',
            );

            return $payment;
        }

        if ($event->status === PaymentStatus::Paid) {
            $this->markPaid($payment);
        } elseif ($event->status === PaymentStatus::Expired) {
            $this->expire($payment);
        } elseif ($event->status === PaymentStatus::Failed) {
            $this->markFailed($payment, 'Provider mengirim status gagal.');
        } else {
            $this->logIntegration(
                provider: $payment->provider,
                event: 'callback.ignored',
                externalId: $payment->external_id,
                status: IntegrationLog::STATUS_IGNORED,
                payload: $event->raw,
                message: "status {$event->status->value} tidak diproses",
            );
        }

        return $payment->refresh();
    }

    private function createManualPayment(Order $order, PaymentMethod $method): Payment
    {
        return Payment::create([
            'order_id' => $order->id,
            'provider' => 'manual',
            'method' => $method,
            'external_id' => $this->uniqueExternalId('MANUAL-'.$order->order_code),
            'amount' => (int) $order->total,
            'expires_at' => now()->addSeconds($method->reservationSeconds()),
        ]);
    }

    private function createGatewayPayment(Order $order, PaymentMethod $method): Payment
    {
        $instruction = $this->gateway->create($order, $method);

        if ($instruction->amount !== (int) $order->total) {
            $this->fail('Nominal dari gateway tidak sama dengan total pesanan.');
        }

        return Payment::create([
            'order_id' => $order->id,
            'provider' => $instruction->provider,
            'method' => $method,
            'external_id' => $this->uniqueExternalId($instruction->externalId),
            'amount' => $instruction->amount,
            'expires_at' => $instruction->expiresAt,
        ]);
    }

    private function uniqueExternalId(string $base): string
    {
        $candidate = $base;
        $attempt = 2;

        while (Payment::query()->where('external_id', $candidate)->exists()) {
            $candidate = $base.'-'.$attempt;
            $attempt++;
        }

        return $candidate;
    }

    private function ensureAdmin(User $admin): void
    {
        if (! $admin->isAdmin() || ! $admin->is_active) {
            throw new AuthorizationException('Hanya admin aktif yang bisa memverifikasi pembayaran.');
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function logIntegration(
        string $provider,
        string $event,
        ?string $externalId,
        string $status,
        array $payload = [],
        ?string $message = null,
    ): void {
        IntegrationLog::create([
            'provider' => $provider,
            'type' => 'payment',
            'event' => $event,
            'external_id' => $externalId,
            'status' => $status,
            'payload' => $this->sanitize($payload),
            'message' => $message,
        ]);
    }

    /**
     * Jangan simpan signature, token, atau secret di log.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function sanitize(array $payload): array
    {
        $blocked = ['signature', 'token', 'secret', 'server_key', 'callback_token', 'authorization'];

        foreach ($payload as $key => $value) {
            if (in_array(strtolower((string) $key), $blocked, true)) {
                $payload[$key] = '[redacted]';

                continue;
            }

            if (is_array($value)) {
                $payload[$key] = $this->sanitize($value);
            }
        }

        return $payload;
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['payment' => $message]);
    }
}
