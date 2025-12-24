<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class AssetReportWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        // Total asset value (HPP * stock)
        $totalAssetValue = Product::where('is_active', true)->sum(DB::raw('cost_price * stock'));

        // Total quantity of products
        $totalQuantity = Product::where('is_active', true)->sum('stock');

        // Average selling price
        $avgSellingPrice = Product::where('is_active', true)->avg('selling_price') ?? 0;

        return [
            Stat::make('Total Nilai Aset', 'Rp ' . number_format($totalAssetValue, 0, ',', '.'))
                ->description('Nilai total stok berdasarkan HPP')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Total Jumlah Barang', number_format($totalQuantity, 0, ',', '.'))
                ->description('Jumlah total stok semua produk')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('info'),

            Stat::make('Rata-rata Harga Jual', 'Rp ' . number_format($avgSellingPrice, 0, ',', '.'))
                ->description('Rata-rata harga jual produk')
                ->descriptionIcon('heroicon-m-currency-rupee')
                ->color('warning'),
        ];
    }
}