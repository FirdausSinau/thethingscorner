<?php

namespace App\Services;

use App\Models\Product\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockService
{
    /**
     * Ubah stok fisik varian. Stok tidak boleh lebih kecil dari jumlah yang sedang ditahan
     * untuk pesanan pembeli, supaya pembayaran nanti tidak membuat stok negatif.
     */
    public function setPhysicalStock(ProductVariant $variant, int $stock): ProductVariant
    {
        if ($stock < 0) {
            throw ValidationException::withMessages([
                'stock' => 'Stok tidak boleh negatif.',
            ]);
        }

        return DB::transaction(function () use ($variant, $stock): ProductVariant {
            $locked = ProductVariant::query()
                ->whereKey($variant->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($stock < $locked->stock_reserved) {
                throw ValidationException::withMessages([
                    'stock' => "Stok tidak boleh kurang dari {$locked->stock_reserved} pasang yang sedang ditahan untuk pesanan pembeli.",
                ]);
            }

            $locked->update(['stock' => $stock]);

            return $locked;
        });
    }
}
