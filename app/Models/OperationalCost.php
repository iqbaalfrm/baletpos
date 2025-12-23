<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperationalCost extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Relasi: Pengeluaran ini diinput oleh siapa?
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}