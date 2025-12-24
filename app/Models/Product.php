<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'margin_percentage' => 'decimal:2',
        'stock' => 'integer',
    ];

    // Tambahin ini biar Produk tau dia masuk kategori apa
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // Accessor untuk menghitung margin berdasarkan harga pokok dan harga jual
    public function getMarginPercentageAttribute(): float
    {
        if ($this->cost_price > 0) {
            return round((($this->selling_price - $this->cost_price) / $this->cost_price) * 100, 2);
        }
        return 0;
    }

    // Accessor untuk menghitung harga jual berdasarkan harga pokok dan margin
    public function getSellingPriceFromMarginAttribute(): float
    {
        if ($this->cost_price > 0 && $this->margin_percentage > 0) {
            return round($this->cost_price * (1 + ($this->margin_percentage / 100)), 2);
        }
        return $this->cost_price;
    }

    // Method untuk menghitung harga jual dari harga pokok dan margin
    public static function calculateSellingPriceFromCostMargin(float $costPrice, float $marginPercentage): float
    {
        return round($costPrice * (1 + ($marginPercentage / 100)), 2);
    }

    // Method untuk menghitung margin dari harga pokok dan harga jual
    public static function calculateMarginFromCostSelling(float $costPrice, float $sellingPrice): float
    {
        if ($costPrice > 0) {
            return round((($sellingPrice - $costPrice) / $costPrice) * 100, 2);
        }
        return 0;
    }

    // Method untuk menghitung total nilai stok (HPP * jumlah stok)
    public function getStockValueAttribute(): float
    {
        return $this->cost_price * $this->stock;
    }

    // Method untuk menghitung total nilai stok berdasarkan harga jual
    public function getStockValueSellingAttribute(): float
    {
        return $this->selling_price * $this->stock;
    }
}