<?php

namespace App\Filament\Pages;

use App\Models\OperationalCost;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Filament\Pages\Page;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Support\Facades\DB;

class ReportPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static ?string $navigationLabel = 'Laporan & Analisa';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $title = 'Laporan Keuangan & Stok';
    protected static ?string $slug = 'laporan';
    protected static ?int $navigationSort = 3; // Di bawah Transaksi
    protected static string $view = 'filament.pages.report-page';

    public static function canAccess(): bool
    {
        // Allow access to admin and finance users
        return auth()->user()->role === 'admin' || auth()->user()->role === 'finance';
    }

    // Variable Filter Tanggal
    public ?array $data = [];

    // Variable Data Laporan
    public $omset = 0;
    public $hpp_terjual = 0; // HPP (Cost of Goods Sold) - Poin 10
    public $biaya_operasional = 0; // Poin 9
    public $laba_kotor = 0;
    public $laba_bersih = 0; // Poin 10

    public $sales_by_category = []; // Poin 5, 6, 7
    public $sales_by_technician = [];
    public $total_aset = 0; // Poin 8

    public function mount(): void
    {
        // Default filter: Bulan Ini
        $this->form->fill([
            'start_date' => now()->startOfMonth()->format('Y-m-d'),
            'end_date' => now()->endOfMonth()->format('Y-m-d'),
        ]);

        $this->updateReport();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('start_date')->label('Dari Tanggal')->required(),
                DatePicker::make('end_date')->label('Sampai Tanggal')->required(),
            ])
            ->columns(2)
            ->statePath('data'); // Simpan data form ke variable $data
    }

    // Function Sakti: Hitung Semua Laporan
    public function updateReport()
    {
        $start = $this->data['start_date'] ?? now()->startOfMonth();
        $end = $this->data['end_date'] ?? now()->endOfMonth();

        // 1. Hitung LABA RUGI (Poin 10)
        // Ambil transaksi yang 'completed' aja
        $this->omset = Transaction::whereBetween('created_at', [$start, $end])
            ->where('status', 'completed')
            ->sum('total_amount');

        // Hitung HPP Terjual (Cost of Goods Sold)
        $this->hpp_terjual = TransactionDetail::whereHas('transaction', function ($q) use ($start, $end) {
                $q->whereBetween('created_at', [$start, $end])
                  ->where('status', 'completed');
            })
            ->sum(DB::raw('cost_price_at_date * quantity'));

        // Hitung Pengeluaran Toko (Listrik, Gaji, dll) - Poin 9
        $this->biaya_operasional = OperationalCost::whereBetween('date', [$start, $end])
            ->sum('amount');

        $this->laba_kotor = $this->omset - $this->hpp_terjual;
        $this->laba_bersih = $this->laba_kotor - $this->biaya_operasional;


        // 2. Hitung PENJUALAN PER KATEGORI (Poin 5, 6, 7)
        // Ini misahin Laptop, Peripheral, Service
        $this->sales_by_category = TransactionDetail::query()
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->whereBetween('transactions.created_at', [$start, $end])
            ->where('transactions.status', 'completed')
            ->select(
                'categories.name as category_name',
                DB::raw('sum(transaction_details.quantity) as total_qty'),
                DB::raw('sum(transaction_details.subtotal) as total_omset')
            )
            ->groupBy('categories.name')
            ->get();

        // 3. Hitung PENJUALAN PER TEKNISI (Poin 6 - Teknisi Report)
        // Assuming we have technician services in categories like 'Service'
        $this->sales_by_technician = TransactionDetail::query()
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->whereBetween('transactions.created_at', [$start, $end])
            ->where('transactions.status', 'completed')
            ->where('categories.name', 'like', '%Service%') // Assuming service categories contain 'Service'
            ->select(
                'products.name as service_name',
                DB::raw('sum(transaction_details.quantity) as total_qty'),
                DB::raw('sum(transaction_details.subtotal) as total_omset')
            )
            ->groupBy('products.name')
            ->get();

        // 4. Hitung VALUASI ASET (Poin 8)
        // Total duit yang mandeg di stok gudang saat ini
        $this->total_aset = Product::where('is_active', true)
            ->sum(DB::raw('stock * cost_price'));
    }

    // Function buat tombol "Filter"
    public function filter()
    {
        $this->updateReport();
        \Filament\Notifications\Notification::make()->title('Data Diperbarui')->success()->send();
    }
}