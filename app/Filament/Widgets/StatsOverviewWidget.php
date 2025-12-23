<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        // Omset Hari Ini
        $todayRevenue = Transaction::whereDate('created_at', today())
            ->where('status', 'completed')
            ->sum('total_amount');

        // Transaksi Hari Ini
        $todayTransactions = Transaction::whereDate('created_at', today())
            ->where('status', 'completed')
            ->count();

        // Stok Menipis (produk dengan stok < 5)
        $lowStockCount = Product::where('stock', '<', 5)
            ->where('is_active', true)
            ->count();

        return [
            Stat::make('Omset Hari Ini', 'Rp ' . number_format($todayRevenue, 0, ',', '.'))
                ->description('Total penjualan hari ini')
                ->descriptionIcon('heroicon-m-arrow-trending-up'),

            Stat::make('Transaksi Hari Ini', $todayTransactions)
                ->description('Jumlah penjualan hari ini')
                ->descriptionIcon('heroicon-m-shopping-cart'),

            Stat::make('Stok Menipis', $lowStockCount)
                ->description('Produk yang hampir habis')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
        ];
    }
}