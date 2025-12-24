<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'total_paid' => 'decimal:2',
        'change_amount' => 'decimal:2',
    ];

    // Relasi ke User (Kasir)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke User yang melakukan void (jika ada)
    public function voidByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'void_by');
    }

    // Relasi ke Detail Barang
    public function details(): HasMany
    {
        return $this->hasMany(TransactionDetail::class);
    }

    // Relasi ke Payment Methods melalui pivot table
    public function paymentMethods()
    {
        return $this->belongsToMany(PaymentMethod::class, 'transaction_payments')
                    ->withPivot('amount_paid')
                    ->withTimestamps();
    }

    // Accessor untuk mengecek apakah transaksi void
    public function getIsVoidAttribute(): bool
    {
        return $this->status === 'void';
    }
}