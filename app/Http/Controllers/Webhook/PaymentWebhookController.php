<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\IntegrationLog;
use App\Services\Payment\PaymentGateway;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class PaymentWebhookController extends Controller
{
    public function handle(
        Request $request,
        string $provider,
        PaymentGateway $gateway,
        PaymentService $payments,
    ): JsonResponse {
        if ($provider !== $gateway->name()) {
            return response()->json(['message' => 'Provider tidak didukung.'], 404);
        }

        if ($provider === 'fake' && ! config('payment.fake.enabled', false)) {
            return response()->json(['message' => 'Fake gateway dinonaktifkan.'], 404);
        }

        try {
            $event = $gateway->parseCallback($request->all());
            $payment = $payments->handleCallback($event);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => collect($e->errors())->flatten()->first(),
            ], 422);
        } catch (InvalidArgumentException $e) {
            IntegrationLog::create([
                'provider' => $provider,
                'type' => 'payment',
                'event' => 'callback.invalid',
                'external_id' => $request->input('external_id'),
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
            'external_id' => $payment->external_id,
            'payment_status' => $payment->status->value,
            'order_status' => $payment->order?->status->value,
        ]);
    }
}
