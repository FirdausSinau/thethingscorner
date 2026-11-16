<?php

namespace App\Services;

use App\Models\Shipment;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PdfDocument;
use Illuminate\Validation\ValidationException;
use Picqer\Barcode\BarcodeGeneratorPNG;

class ShipmentLabelService
{
    /**
     * @return array<string, mixed>
     */
    public function data(Shipment $shipment): array
    {
        $shipment->loadMissing(['order.items']);

        $order = $shipment->order;

        if (! $order) {
            $this->fail('Shipment tidak memiliki order.');
        }

        if (! $shipment->tracking_number) {
            $this->fail('Nomor resi belum tersedia. Isi resi sebelum mencetak label.');
        }

        $barcode = null;

        try {
            $generator = new BarcodeGeneratorPNG;
            $barcode = base64_encode($generator->getBarcode(
                $shipment->tracking_number,
                $generator::TYPE_CODE_128,
                2,
                50,
            ));
        } catch (\Throwable $e) {
            report($e);
        }

        return [
            'shipment' => $shipment,
            'order' => $order,
            'barcode' => $barcode,
            'storeName' => (string) config('app.name', 'Thethingscorner'),
            'origin' => (array) config('shipping.origin', []),
            'itemCount' => (int) $order->items->sum('qty'),
            'weightGrams' => (int) $shipment->weight_grams,
        ];
    }

    public function pdf(Shipment $shipment): PdfDocument
    {
        return Pdf::loadView('pdf.shipment-label', $this->data($shipment))
            ->setPaper([0, 0, 283.46, 425.20], 'portrait');
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['shipping' => $message]);
    }
}
