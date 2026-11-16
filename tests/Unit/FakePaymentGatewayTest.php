<?php

namespace Tests\Unit;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Services\Payment\FakePaymentGateway;
use Carbon\Carbon;
use InvalidArgumentException;
use Tests\TestCase;

class FakePaymentGatewayTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_create_returns_deterministic_instruction(): void
    {
        Carbon::setTestNow('2026-11-16 10:00:00');
        config(['payment.reservations.qris_seconds' => 30]);

        $order = new Order([
            'order_code' => 'TTC-001',
            'total' => 250000,
        ]);

        $instruction = (new FakePaymentGateway)->create($order, PaymentMethod::Qris);

        $this->assertSame('fake', $instruction->provider);
        $this->assertSame('FAKE-TTC-001', $instruction->externalId);
        $this->assertSame('FAKE-TTC-001', $instruction->code);
        $this->assertSame(PaymentMethod::Qris, $instruction->method);
        $this->assertSame(250000, $instruction->amount);
        $this->assertSame(now()->addSeconds(30)->toDateTimeString(), $instruction->expiresAt->toDateTimeString());
        $this->assertTrue($instruction->metadata['fake']);
    }

    public function test_create_uses_manual_transfer_reservation_window(): void
    {
        Carbon::setTestNow('2026-11-16 10:00:00');
        config(['payment.reservations.manual_transfer_seconds' => 3600]);

        $order = new Order([
            'order_code' => 'TTC-002',
            'total' => 150000,
        ]);

        $instruction = (new FakePaymentGateway)->create($order, PaymentMethod::ManualTransfer);

        $this->assertSame(now()->addHour()->toDateTimeString(), $instruction->expiresAt->toDateTimeString());
        $this->assertStringContainsString('unggah bukti', $instruction->instructions);
    }

    public function test_parse_callback_reads_paid_event(): void
    {
        $event = (new FakePaymentGateway)->parseCallback([
            'external_id' => 'FAKE-TTC-001',
            'status' => 'paid',
            'amount' => '250000',
        ]);

        $this->assertSame('fake', $event->provider);
        $this->assertSame('FAKE-TTC-001', $event->externalId);
        $this->assertSame(PaymentStatus::Paid, $event->status);
        $this->assertSame(250000, $event->amount);
    }

    public function test_parse_callback_rejects_unknown_status(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new FakePaymentGateway)->parseCallback([
            'external_id' => 'FAKE-TTC-001',
            'status' => 'settled',
            'amount' => 250000,
        ]);
    }

    public function test_parse_callback_rejects_invalid_amount(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new FakePaymentGateway)->parseCallback([
            'external_id' => 'FAKE-TTC-001',
            'status' => 'paid',
            'amount' => 0,
        ]);
    }

    public function test_parse_callback_requires_external_id(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new FakePaymentGateway)->parseCallback([
            'status' => 'paid',
            'amount' => 250000,
        ]);
    }
}
