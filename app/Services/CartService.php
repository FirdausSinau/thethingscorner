<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Product\ProductVariant;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function add(User $user, ProductVariant $variant, int $qty = 1): CartItem
    {
        $this->ensureBuyer($user);
        $this->ensurePurchasable($variant);

        if ($qty < 1) {
            throw ValidationException::withMessages([
                'qty' => 'Jumlah minimal 1.',
            ]);
        }

        $item = CartItem::firstOrNew([
            'user_id' => $user->id,
            'variant_id' => $variant->id,
        ]);

        $newQty = ($item->exists ? $item->qty : 0) + $qty;

        $this->ensureStock($variant, $newQty);

        $item->qty = $newQty;
        $item->save();

        return $item->load('variant.color.product');
    }

    public function update(User $user, CartItem $item, int $qty): CartItem
    {
        $this->ensureOwned($user, $item);

        if ($qty < 1) {
            throw ValidationException::withMessages([
                'qty' => 'Jumlah minimal 1.',
            ]);
        }

        $variant = $item->variant()->with('color.product')->firstOrFail();

        $this->ensurePurchasable($variant);
        $this->ensureStock($variant, $qty);

        $item->update(['qty' => $qty]);

        return $item->load('variant.color.product');
    }

    public function remove(User $user, CartItem $item): void
    {
        $this->ensureOwned($user, $item);

        $item->delete();
    }

    public function clear(User $user): void
    {
        CartItem::query()->where('user_id', $user->id)->delete();
    }

    /**
     * @return Collection<int, CartItem>
     */
    public function items(User $user): Collection
    {
        return CartItem::query()
            ->with(['variant.color.product'])
            ->where('user_id', $user->id)
            ->latest('updated_at')
            ->get();
    }

    /**
     * @return array{items: Collection<int, CartItem>, subtotal: int, count: int}
     */
    public function summary(User $user): array
    {
        $items = $this->items($user);

        return [
            'items' => $items,
            'subtotal' => (int) $items->sum(fn (CartItem $item) => $item->line_total),
            'count' => (int) $items->sum('qty'),
        ];
    }

    private function ensurePurchasable(ProductVariant $variant): void
    {
        $variant->loadMissing('color.product');

        if (! $variant->color?->product?->is_active) {
            throw ValidationException::withMessages([
                'variant_id' => 'Produk ini sedang tidak dijual.',
            ]);
        }

        if ($variant->color->product->is_preorder) {
            throw ValidationException::withMessages([
                'variant_id' => 'Produk ini dipesan lewat pengajuan PO, bukan keranjang.',
            ]);
        }

        if ($variant->stock_available < 1) {
            throw ValidationException::withMessages([
                'variant_id' => 'Stok ukuran ini habis.',
            ]);
        }
    }

    private function ensureStock(ProductVariant $variant, int $qty): void
    {
        if ($qty > $variant->stock_available) {
            throw ValidationException::withMessages([
                'qty' => "Stok tersedia hanya {$variant->stock_available}.",
            ]);
        }
    }

    private function ensureBuyer(User $user): void
    {
        if (! $user->isBuyer()) {
            throw new AuthorizationException('Akun admin tidak bisa berbelanja.');
        }
    }

    private function ensureOwned(User $user, CartItem $item): void
    {
        abort_unless($item->user_id === $user->id, 404);
    }
}
