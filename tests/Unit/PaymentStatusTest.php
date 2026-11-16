<?php

namespace Tests\Unit;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use PHPUnit\Framework\TestCase;

class PaymentStatusTest extends TestCase
{
    public function test_payment_status_has_indonesian_labels(): void
    {
        $this->assertSame('Menunggu pembayaran', PaymentStatus::Pending->label());
        $this->assertSame('Lunas', PaymentStatus::Paid->label());
        $this->assertSame('Dana dikembalikan', PaymentStatus::Refunded->label());
    }

    public function test_pending_can_become_paid_expired_or_failed(): void
    {
        $this->assertTrue(PaymentStatus::Pending->canTransitionTo(PaymentStatus::Paid));
        $this->assertTrue(PaymentStatus::Pending->canTransitionTo(PaymentStatus::Expired));
        $this->assertTrue(PaymentStatus::Pending->canTransitionTo(PaymentStatus::Failed));
        $this->assertFalse(PaymentStatus::Pending->canTransitionTo(PaymentStatus::Refunded));
    }

    public function test_only_paid_payment_can_be_refunded(): void
    {
        $this->assertTrue(PaymentStatus::Paid->canTransitionTo(PaymentStatus::Refunded));
        $this->assertFalse(PaymentStatus::Failed->canTransitionTo(PaymentStatus::Refunded));
        $this->assertTrue(PaymentStatus::Expired->isFinal());
        $this->assertTrue(PaymentStatus::Refunded->isFinal());
    }

    public function test_payment_status_maps_to_order_status(): void
    {
        $this->assertSame(OrderStatus::MenungguPembayaran, PaymentStatus::Pending->orderStatus());
        $this->assertSame(OrderStatus::Dibayar, PaymentStatus::Paid->orderStatus());
        $this->assertSame(OrderStatus::Kedaluwarsa, PaymentStatus::Expired->orderStatus());
        $this->assertNull(PaymentStatus::Failed->orderStatus());
        $this->assertSame(OrderStatus::Dibatalkan, PaymentStatus::Refunded->orderStatus());
    }

    public function test_failed_payment_can_still_be_paid_later(): void
    {
        $this->assertTrue(PaymentStatus::Failed->canTransitionTo(PaymentStatus::Paid));
        $this->assertFalse(PaymentStatus::Expired->canTransitionTo(PaymentStatus::Paid));
    }
}
