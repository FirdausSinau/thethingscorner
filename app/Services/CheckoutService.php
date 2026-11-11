<?php

namespace App\Services;

use App\Enums\OrderChannel;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\ReservationStatus;
use App\Models\Address;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product\ProductVariant;
use App\Models\StockReservation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function placeOrder(
        User $user,
        Address $address,
        PaymentMethod $paymentMethod,
        int $shippingCost = 0
    ): Order {
        abort_unless($address->user_id === $user->id, 404);

        if ($shippingCost < 0) {
            throw ValidationException::withMessages([
                'shipping_cost' => 'Ongkir tidak boleh negatif.',
            ]);
        }

        return DB::transaction(function () use ($user, $address, $paymentMethod, $shippingCost): Order {
            $cartItems = CartItem::query()
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->get();

            if ($cartItems->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => 'Keranjang masih kosong.',
                ]);
            }

            $variants = ProductVariant::query()
                ->whereIn('id', $cartItems->pluck('variant_id')->unique())
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $variants->load('color.product');

            $subtotal = 0;
            $lines = [];

            foreach ($cartItems as $item) {
                $variant = $variants->get($item->variant_id);

                if (! $variant) {
                    throw ValidationException::withMessages([
                        'cart' => 'Ada varian yang sudah tidak tersedia.',
                    ]);
                }

                if (! $variant->color?->product?->is_active) {
                    throw ValidationException::withMessages([
                        'cart' => "Produk {$variant->color?->product?->name} sudah tidak dijual.",
                    ]);
                }

                if ($item->qty > $variant->stock_available) {
                    throw ValidationException::withMessages([
                        'cart' => "Stok {$variant->color->product->name} ukuran {$variant->size} tidak cukup.",
                    ]);
                }

                $unitPrice = $variant->effective_price;
                $lineSubtotal = $unitPrice * $item->qty;
                $subtotal += $lineSubtotal;

                $lines[] = [
                    'variant' => $variant,
                    'qty' => $item->qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $lineSubtotal,
                ];
            }

            $order = Order::create([
                'order_code' => $this->makeOrderCode(),
                'user_id' => $user->id,
                'channel' => OrderChannel::Web,
                'status' => OrderStatus::MenungguPembayaran,
                'payment_method' => $paymentMethod,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'total' => $subtotal + $shippingCost,
                'recipient' => $address->recipient,
                'phone' => $address->phone,
                'address' => $this->addressSnapshot($address),
                'city' => $address->city,
                'postal_code' => $address->postal_code,
            ]);

            $expiresAt = now()->addMinutes($paymentMethod->reservationMinutes());

            foreach ($lines as $line) {
                /** @var ProductVariant $variant */
                $variant = $line['variant'];
                $product = $variant->color->product;

                OrderItem::create([
                    'order_id' => $order->id,
                    'variant_id' => $variant->id,
                    'product_name' => $product->name,
                    'sku' => $variant->sku,
                    'color' => $variant->color->name,
                    'size' => $variant->size,
                    'price' => $line['unit_price'],
                    'qty' => $line['qty'],
                    'subtotal' => $line['subtotal'],
                ]);

                StockReservation::create([
                    'order_id' => $order->id,
                    'variant_id' => $variant->id,
                    'qty' => $line['qty'],
                    'expires_at' => $expiresAt,
                    'status' => ReservationStatus::Active,
                ]);

                $variant->forceFill([
                    'stock_reserved' => $variant->stock_reserved + $line['qty'],
                ])->save();
            }

            CartItem::query()->where('user_id', $user->id)->delete();

            return $order->load(['items', 'reservations']);
        });
    }

    private function makeOrderCode(): string
    {
        do {
            $code = 'TTC-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (Order::query()->where('order_code', $code)->exists());

        return $code;
    }

    private function addressSnapshot(Address $address): string
    {
        return trim(implode(', ', array_filter([
            $address->detail,
            $address->district,
            $address->city,
            $address->province,
            $address->postal_code,
        ])));
    }
}
