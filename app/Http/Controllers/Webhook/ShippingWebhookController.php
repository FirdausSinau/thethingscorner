<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\IntegrationLog;
use App\Models\Shipment;
use App\Services\Shipping\ShippingProvider;
use App\Services\ShippingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class ShippingWebhookController extends Controller
{
    public function handle(
        Request $request,
        string $provider,
        ShippingProvider $providerAdapter,
        ShippingService $shipping,
    ): JsonResponse {
        if ($provider !== $providerAdapter->name()) {
            return response()->json(['message' => 'Provider tidak didukung.'], 404);
        }

        if ($provider === 'fake' && ! config('shipping.fake.enabled', false)) {
            return response()->json(['message' => 'Fake shipping provider dinonaktifkan.'], 404);
        }

        try {
            $event = $providerAdapter->parseTrackingCallback($request->all());

            $shipment = Shipment::query()
                ->where('tracking_number', $event->trackingNumber)
                ->first();

            if (! $shipment) {
                return response()->json(['message' => 'Nomor resi tidak dikenal.'], 404);
            }

            $shipping->updateTracking($shipment, $event);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => collect($e->errors())->flatten()->first(),
            ], 422);
        } catch (InvalidArgumentException $e) {
            IntegrationLog::create([
                'provider' => $provider,
                'type' => 'shipping',
                'event' => 'callback.invalid',
                'external_id' => $request->input('tracking_number'),
                'status' => IntegrationLog::STATUS_FAILED,
                'payload' => ['keys' => array_keys($request->all())],
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'status' => 'ok',
            'provider' => $provider,
            'tracking_number' => $shipment->tracking_number,
            'shipment_status' => $shipment->fresh()->status->value,
            'order_status' => $shipment->order?->fresh()->status->value,
        ]);
    }
}
