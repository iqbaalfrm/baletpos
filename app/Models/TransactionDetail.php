<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'product_id',
        'quantity',
        'cost_price_at_date',
        'selling_price_at_date',
        'subtotal',
        'note',
        'serial_number',
    ];

    // Relasi ke Header Transaksi (PENTING BIAR GAK ERROR!)
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    // Relasi ke Produk
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}