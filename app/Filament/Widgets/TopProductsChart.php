<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TopProductsChart extends ChartWidget
{
    protected static ?string $heading = '5 Produk Terlaris';

    protected static ?string $maxHeight = '300px';

    protected static ?int $sort = 2; // Appears after SalesByCategoryChart

    protected function getData(): array
    {
        // Get top 5 most sold products based on transaction details quantity
        $topProducts = DB::table('transaction_details')
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->select(
                'products.name as product_name',
                DB::raw('SUM(transaction_details.quantity) as total_quantity')
            )
            ->groupBy('products.name')
            ->orderBy('total_quantity', 'desc')
            ->limit(5)
            ->get();

        $labels = $topProducts->pluck('product_name')->toArray();
        $data = $topProducts->pluck('total_quantity')->toArray();

        // Generate gradient colors for the bars
        $colors = [
            'rgba(59, 130, 246, 0.8)', // Blue
            'rgba(147, 51, 234, 0.8)', // Purple
            'rgba(245, 158, 11, 0.8)', // Amber
            'rgba(16, 185, 129, 0.8)', // Emerald
            'rgba(239, 68, 68, 0.8)', // Red
        ];

        $borderColors = [
            'rgb(59, 130, 246)', // Blue
            'rgb(147, 51, 234)', // Purple
            'rgb(245, 158, 11)', // Amber
            'rgb(16, 185, 129)', // Emerald
            'rgb(239, 68, 68)', // Red
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Terjual',
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderColor' => $borderColors,
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}