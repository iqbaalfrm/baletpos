<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class RevenueChart extends ChartWidget
{
    protected static ?string $heading = 'Tren Omset Harian';

    protected static ?string $maxHeight = '300px';

    protected static ?int $sort = 99; // High sort value to place it at the bottom

    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        // Get revenue data for the current month
        $currentMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $revenueData = Transaction::whereBetween('created_at', [$currentMonth, $endOfMonth])
            ->where('status', 'completed')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Format the data for the chart
        $labels = $revenueData->pluck('date')->map(function ($date) {
            return \Carbon\Carbon::parse($date)->format('d M');
        })->toArray();

        $data = $revenueData->pluck('total')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Omset Harian',
                    'data' => $data,
                    'borderColor' => '#3b82f6', // Blue color
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)', // Light blue background
                    'tension' => 0.4, // Smooth line
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}