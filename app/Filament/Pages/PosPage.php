<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PosPage extends Page
{
    // Layout khusus (Full Screen)
    protected static string $layout = 'components.layouts.pos';
    
    // View
    protected static string $view = 'filament.pages.pos-page';

    // Navigasi Admin
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $title = 'Point of Sales';
    protected static ?string $slug = 'pos';

    // === VARIABLE DATA UTAMA ===
    public $search = '';
    public $selectedCategory = null;
    public $cart = []; 
    public $total_amount = 0;
    public $payment_amount = 0;
    public $change_amount = 0;

    // === VARIABLE MODAL CHECKOUT & SUKSES ===
    public $isShowCheckoutModal = false;
    public $isShowSuccessModal = false;
    public $lastTransactionId = null;
    
    public $customer_name = 'Umum';
    public $payment_method = 'cash';

    // 1. AMBIL DATA PRODUK (SEARCH & FILTER)
    public function getProductsProperty()
    {
        return Product::query()
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%')
                                             ->orWhere('code', 'like', '%' . $this->search . '%'))
            ->when($this->selectedCategory, fn($q) => $q->where('category_id', $this->selectedCategory))
            ->get();
    }

    public function getCategoriesProperty()
    {
        return Category::all();
    }

    // 2. LOGIC KERANJANG (CART)
    public function addToCart($productId)
    {
        $product = Product::find($productId);
        if (!$product) return;

        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['qty']++;
        } else {
            $this->cart[$productId] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->selling_price,
                'qty' => 1,
            ];
        }
        $this->calculateTotal();
    }

    public function updateQty($productId, $type)
    {
        if (!isset($this->cart[$productId])) return;

        if ($type === 'plus') {
            $this->cart[$productId]['qty']++;
        } elseif ($type === 'minus') {
            if ($this->cart[$productId]['qty'] > 1) {
                $this->cart[$productId]['qty']--;
            } else {
                unset($this->cart[$productId]);
            }
        }
        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        $this->total_amount = collect($this->cart)->sum(fn($item) => $item['price'] * $item['qty']);
        $this->updatedPaymentAmount(); // Update kembalian realtime
    }

    public function updatedPaymentAmount()
    {
        $pay = (int) $this->payment_amount;
        // Kembalian gak boleh minus
        $this->change_amount = ($pay >= $this->total_amount) ? $pay - $this->total_amount : 0;
    }

    // 3. LOGIC MODAL CHECKOUT
    public function openCheckoutModal()
    {
        if (empty($this->cart)) {
            Notification::make()->title('Keranjang kosong!')->warning()->send();
            return;
        }
        // Reset input bayar saat buka modal, kecuali mau default 'Uang Pas' bisa di set disini
        $this->payment_amount = 0; 
        $this->change_amount = 0;
        $this->customer_name = 'Umum';
        $this->isShowCheckoutModal = true;
    }

    public function closeCheckoutModal()
    {
        $this->isShowCheckoutModal = false;
    }

    // Logic Tombol Uang Cepat (Termasuk Uang Pas)
    public function setPaymentAmount($amount)
    {
        $this->payment_amount = $amount;
        $this->updatedPaymentAmount();
    }

    // 4. PROSES PEMBAYARAN & SIMPAN KE DB
    public function processPayment()
    {
        if ($this->payment_amount < $this->total_amount) {
            Notification::make()->title('Uang pembayaran kurang!')->danger()->send();
            return;
        }

        DB::transaction(function () {
            // Header Transaksi
            $trx = Transaction::create([
                'invoice_code' => 'INV/' . date('Ymd') . '/' . Str::upper(Str::random(4)),
                'user_id' => auth()->id(),
                'total_amount' => $this->total_amount,
                'payment_amount' => $this->payment_amount,
                'change_amount' => $this->change_amount,
                'status' => 'completed',
                'payment_method' => $this->payment_method,
                // 'customer_name' => $this->customer_name, 
            ]);

            // Detail Transaksi & Potong Stok
            foreach ($this->cart as $item) {
                $product = Product::find($item['id']);
                TransactionDetail::create([
                    'transaction_id' => $trx->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['qty'],
                    'cost_price_at_date' => $product->cost_price,
                    'selling_price_at_date' => $item['price'],
                    'subtotal' => $item['price'] * $item['qty'],
                ]);
            }

            // Simpan ID buat keperluan cetak struk
            $this->lastTransactionId = $trx->id;
        });

        // Flow: Tutup Checkout -> Buka Sukses
        $this->isShowCheckoutModal = false;
        $this->isShowSuccessModal = true;
        
        Notification::make()->title('Transaksi Berhasil!')->success()->send();
    }

    // 5. SELESAI TRANSAKSI (RESET)
    public function doneTransaction()
    {
        $this->cart = [];
        $this->payment_amount = 0;
        $this->total_amount = 0;
        $this->change_amount = 0;
        $this->customer_name = 'Umum';
        $this->isShowSuccessModal = false;
        $this->lastTransactionId = null;
    }
public function printInvoice()
    {
        // Redirect ke route cetak struk di tab baru
        // Kita pake JS dispatch biar bisa open new tab (karena redirect PHP biasa gak bisa open tab)
        
        $url = route('print.struk', ['id' => $this->lastTransactionId]);
        
        $this->dispatch('open-print-window', url: $url);
        
        // Tutup modal dan reset
        $this->doneTransaction();
    }
}