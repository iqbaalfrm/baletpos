<?php

namespace App\Filament\Widgets;

use App\Models\TransactionDetail;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class SalesByCategoryChart extends ChartWidget
{
    protected static ?string $heading = 'Penjualan per Kategori (Laptop vs Peripheral vs Service)';
    
    // Urutan widget di dashboard (biar di bawah kotak angka)
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        // Query sakti: Join Transaksi -> Produk -> Kategori
        $data = TransactionDetail::query()
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'completed') // Cuma ambil yg lunas
            ->select('categories.name', DB::raw('sum(transaction_details.subtotal) as total_omset'))
            ->groupBy('categories.name')
            ->pluck('total_omset', 'name'); // Hasilnya: ['Laptop' => 5000000, 'Mouse' => 100000]

        return [
            'datasets' => [
                [
                    'label' => 'Total Omset (Rp)',
                    'data' => $data->values()->toArray(),
                    'backgroundColor' => [
                        '#3b82f6', // Biru
                        '#ef4444', // Merah
                        '#22c55e', // Hijau
                        '#eab308', // Kuning
                    ],
                ],
            ],
            'labels' => $data->keys()->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar'; // Bisa ganti 'doughnut' atau 'pie' kalau mau bunder
    }
}