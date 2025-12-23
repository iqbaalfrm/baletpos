<?php

namespace App\Filament\Widgets;

use App\Models\OperationalCost;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StatsOverview extends BaseWidget
{
    // Auto reload tiap 15 detik biar berasa realtime
    protected static ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        // 1. Hitung OMSET (Cuma dari transaksi yang statusnya 'completed')
        $omset = Transaction::where('status', 'completed')->sum('total_amount');

        // 2. Hitung MODAL TERJUAL (HPP saat barang laku x Jumlah)
        // Pake whereHas biar cuma ngitung barang dari transaksi yang sukses
        $modalTerjual = TransactionDetail::whereHas('transaction', function ($query) {
            $query->where('status', 'completed');
        })->sum(DB::raw('cost_price_at_date * quantity'));

        // 3. Hitung TOTAL PENGELUARAN (Listrik, Gaji, dll)
        $biayaOperasional = OperationalCost::sum('amount');

        // 4. Hitung LABA BERSIH
        // Rumus: Uang Masuk - Modal Barang - Biaya Toko
        $labaBersih = $omset - $modalTerjual - $biayaOperasional;

        // 5. Hitung VALUASI ASET (Nilai barang yang numpuk di gudang)
        $totalAset = Product::where('is_active', true)->sum(DB::raw('stock * cost_price'));

        // Helper biar format Rupiahnya ganteng
        $formatRp = fn($num) => 'Rp ' . number_format($num, 0, ',', '.');

        return [
            Stat::make('Total Omset', $formatRp($omset))
                ->description('Pemasukan Kotor')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([7, 2, 10, 3, 15, 4, 17]),

            Stat::make('Laba Bersih', $formatRp($labaBersih))
                ->description('Omset - Modal - Biaya')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($labaBersih >= 0 ? 'success' : 'danger'), // Merah kalau rugi

            Stat::make('Valuasi Aset Gudang', $formatRp($totalAset))
                ->description('Total Modal Barang Ready')
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary'),
                
            Stat::make('Biaya Operasional', $formatRp($biayaOperasional))
                ->description('Pengeluaran Toko')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
        ];
    }
}