<?php

namespace Tests\Unit;

use App\Enums\ShipmentStatus;
use PHPUnit\Framework\TestCase;

class ShipmentStatusTest extends TestCase
{
    public function test_shipment_status_has_indonesian_labels(): void
    {
        $this->assertSame('Menunggu pickup', ShipmentStatus::Pending->label());
        $this->assertSame('Dalam perjalanan', ShipmentStatus::InTransit->label());
        $this->assertSame('Diterima', ShipmentStatus::Delivered->label());
    }

    public function test_pending_can_skip_pickup_for_webhook_events(): void
    {
        $this->assertTrue(ShipmentStatus::Pending->canTransitionTo(ShipmentStatus::Pickup));
        $this->assertTrue(ShipmentStatus::Pending->canTransitionTo(ShipmentStatus::InTransit));
        $this->assertTrue(ShipmentStatus::Pending->canTransitionTo(ShipmentStatus::Returned));
    }

    public function test_in_transit_can_be_delivered_or_returned(): void
    {
        $this->assertTrue(ShipmentStatus::InTransit->canTransitionTo(ShipmentStatus::Delivered));
        $this->assertTrue(ShipmentStatus::InTransit->canTransitionTo(ShipmentStatus::Returned));
        $this->assertFalse(ShipmentStatus::InTransit->canTransitionTo(ShipmentStatus::Pickup));
    }

    public function test_delivered_and_returned_are_final(): void
    {
        $this->assertTrue(ShipmentStatus::Delivered->isFinal());
        $this->assertTrue(ShipmentStatus::Returned->isFinal());
        $this->assertTrue(ShipmentStatus::Delivered->isDelivered());
        $this->assertTrue(ShipmentStatus::Returned->isReturned());
    }
}
