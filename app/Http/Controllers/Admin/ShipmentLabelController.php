<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Services\ShipmentLabelService;

class ShipmentLabelController extends Controller
{
    public function show(Shipment $shipment, ShipmentLabelService $labels)
    {
        $pdf = $labels->pdf($shipment);

        return $pdf->download("resi-{$shipment->tracking_number}.pdf");
    }
}
