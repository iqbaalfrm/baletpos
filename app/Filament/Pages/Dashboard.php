<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Filament\Widgets\SalesByCategoryChart;
use App\Filament\Widgets\TopProductsChart;
use App\Filament\Widgets\RevenueChart;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?int $navigationSort = -2;

    protected function getHeaderWidgets(): array
    {
        return [
            StatsOverviewWidget::class,
        ];
    }

    public function getWidgets(): array
    {
        return [
            SalesByCategoryChart::class,
            TopProductsChart::class,
            RevenueChart::class,
        ];
    }
}