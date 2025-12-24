<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'is_active',
        'image',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationship with transactions through pivot table
    public function transactions()
    {
        return $this->belongsToMany(Transaction::class, 'transaction_payments')
                    ->withPivot('amount_paid')
                    ->withTimestamps();
    }
}