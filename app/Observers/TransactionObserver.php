<?php

namespace App\Observers;

use App\Models\Transaction;

class TransactionObserver
{
    // Pas Status Berubah jadi VOID -> Balikin Stok
    public function updated(Transaction $transaction): void
    {
        // Cek apakah status berubah jadi 'void'
        if ($transaction->isDirty('status') && $transaction->status === 'void') {
            foreach ($transaction->details as $detail) {
                $detail->product->increment('stock', $detail->quantity);
            }
        }
    }
}