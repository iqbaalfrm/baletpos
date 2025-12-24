<?php

namespace App\Filament\Widgets;

use App\Models\OperationalCost;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class ProfitLossReportWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        // Revenue from completed transactions in the current month
        $revenue = Transaction::where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');
        
        // Calculate HPP (Cost of Goods Sold) for the current month
        $hpp = TransactionDetail::whereHas('transaction', function ($query) {
                $query->where('status', 'completed')
                      ->whereMonth('created_at', now()->month)
                      ->whereYear('created_at', now()->year);
            })
            ->sum(DB::raw('cost_price_at_date * quantity'));
        
        // Operational costs for the current month
        $operationalCosts = OperationalCost::whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');
        
        // Gross profit
        $grossProfit = $revenue - $hpp;
        
        // Net profit
        $netProfit = $grossProfit - $operationalCosts;
        
        return [
            Stat::make('Pendapatan', 'Rp ' . number_format($revenue, 0, ',', '.'))
                ->description('Pendapatan bulan ini')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
                
            Stat::make('HPP', 'Rp ' . number_format($hpp, 0, ',', '.'))
                ->description('Harga Pokok Penjualan')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('danger'),
                
            Stat::make('Laba Bersih', 'Rp ' . number_format($netProfit, 0, ',', '.'))
                ->description('Pendapatan - HPP - Biaya Operasional')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($netProfit >= 0 ? 'success' : 'danger'),
        ];
    }
}