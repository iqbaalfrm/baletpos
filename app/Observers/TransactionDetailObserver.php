<?php

namespace App\Observers;

use App\Models\TransactionDetail;
use App\Models\Product;

class TransactionDetailObserver
{
    // Pas Item Terjual -> Potong Stok
    public function created(TransactionDetail $transactionDetail): void
    {
        $product = $transactionDetail->product;
        if ($product) {
            $product->decrement('stock', $transactionDetail->quantity);
        }
    }
}