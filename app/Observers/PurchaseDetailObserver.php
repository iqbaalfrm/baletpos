<?php

namespace App\Observers;

use App\Models\PurchaseDetail;

class PurchaseDetailObserver
{
    public function created(PurchaseDetail $purchaseDetail): void
    {
        $product = $purchaseDetail->product;
        if ($product) {
            // 1. Tambah Stok
            $product->stock += $purchaseDetail->quantity;
            
            // 2. Update HPP Master Barang (Pake harga beli terbaru)
            // Kalau lo mau pake metode Average, rumusnya beda lagi. Ini metode Last Price.
            $product->cost_price = $purchaseDetail->unit_cost;
            
            $product->save();
        }
    }
}