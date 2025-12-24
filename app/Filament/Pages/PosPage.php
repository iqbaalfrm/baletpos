<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\WithPagination;

class PosPage extends Page
{
    use WithPagination;

    // Layout khusus (Full Screen)
    protected static string $layout = 'components.layouts.pos';

    // View
    protected static string $view = 'filament.pages.pos-page';

    // Navigasi Admin
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?string $navigationLabel = 'POS Kasir';
    protected static ?string $title = 'Point of Sales';
    protected static ?string $slug = 'pos';

    public static function canAccess(): bool
    {
        // Only Admin and Cashier can access POS, Finance cannot
        $user = auth()->user();
        return $user && ($user->role === 'admin' || $user->role === 'cashier');
    }

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

    // === SPLIT PAYMENT VARIABLES ===
    public $payment_methods = [];
    public $selected_payment_methods = [];
    public $split_payment_total = 0;
    public $is_split_payment = false;

    // 1. AMBIL DATA PRODUK (SEARCH & FILTER)
    public function getProductsProperty()
    {
        return Product::query()
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%')
                                             ->orWhere('code', 'like', '%' . $this->search . '%'))
            ->when($this->selectedCategory, fn($q) => $q->where('category_id', $this->selectedCategory))
            ->paginate(8);
    }

    public function getPaymentMethodsProperty()
    {
        return PaymentMethod::where('is_active', true)->get();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSelectedCategory()
    {
        $this->resetPage();
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
                'image_url' => $product->image ? asset('storage/' . $product->image) : null,
                'category_name' => $product->category->name ?? 'General',
                'note' => '',
                'serial_number' => '',
            ];
        }
        $this->calculateTotal();
    }

    public function updateCartNote($productId, $note)
    {
        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['note'] = $note;
        }
    }

    public function updateCartSerialNumber($productId, $serialNumber)
    {
        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['serial_number'] = $serialNumber;
        }
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

    // Ensure payment amount is set properly for traditional payment
    public function updatedPaymentMethod()
    {
        // Recalculate if needed when payment method changes
    }

    // Reset Cart Method
    public function resetCart()
    {
        $this->cart = [];
        $this->total_amount = 0;
        $this->payment_amount = 0;
        $this->change_amount = 0;
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
        $this->is_split_payment = false;
        $this->selected_payment_methods = [];
        $this->split_payment_total = 0;
        $this->isShowCheckoutModal = true;

        // Focus the payment input after the modal opens
        $this->dispatch('payment-modal-opened');
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

    // SPLIT PAYMENT LOGIC
    public function toggleSplitPayment()
    {
        $this->is_split_payment = !$this->is_split_payment;
        if (!$this->is_split_payment) {
            // Reset split payment data when switching off
            $this->selected_payment_methods = [];
            $this->split_payment_total = 0;
        } else {
            // Add the first payment method with zero amount
            $first_method = $this->paymentMethods->first();
            if ($first_method) {
                $this->addPaymentMethod($first_method->id);
            }
        }
    }

    public function addPaymentMethod($paymentMethodId = null)
    {
        if ($paymentMethodId) {
            $paymentMethod = $this->paymentMethods->firstWhere('id', $paymentMethodId);
        } else {
            $paymentMethod = $this->paymentMethods->first();
        }

        if ($paymentMethod) {
            $this->selected_payment_methods[] = [
                'id' => $paymentMethod->id,
                'name' => $paymentMethod->name,
                'code' => $paymentMethod->code,
                'amount' => 0,
            ];
            $this->calculateSplitPaymentTotal();
        }
    }

    public function removePaymentMethod($index)
    {
        if (isset($this->selected_payment_methods[$index])) {
            unset($this->selected_payment_methods[$index]);
            $this->selected_payment_methods = array_values($this->selected_payment_methods); // Re-index array
            $this->calculateSplitPaymentTotal();
        }
    }

    public function updatePaymentAmount($index, $amount)
    {
        if (isset($this->selected_payment_methods[$index])) {
            $this->selected_payment_methods[$index]['amount'] = (int) $amount;
            $this->calculateSplitPaymentTotal();
        }
    }

    // Livewire lifecycle method to recalculate when properties change
    public function updatedSelectedPaymentMethods()
    {
        $this->calculateSplitPaymentTotal();
    }

    // Handle updates to individual payment amounts
    public function updated($property, $value)
    {
        if (str_starts_with($property, 'selected_payment_methods.')) {
            $this->calculateSplitPaymentTotal();
        }
    }

    public function calculateSplitPaymentTotal()
    {
        $this->split_payment_total = collect($this->selected_payment_methods)->sum(function($payment) {
            return (float) $payment['amount'];
        });
        $this->split_payment_total = round($this->split_payment_total, 2);
        $this->change_amount = max(0, $this->split_payment_total - $this->total_amount);
    }

    // 4. PROSES PEMBAYARAN & SIMPAN KE DB
    public function processPayment()
    {
        if ($this->is_split_payment) {
            // Validate split payment with proper floating point comparison
            if (round($this->split_payment_total, 2) < round($this->total_amount, 2)) {
                Notification::make()->title('Total pembayaran kurang!')->danger()->send();
                return;
            }

            // Remove any payment methods with zero amount
            $this->selected_payment_methods = array_filter($this->selected_payment_methods, function($payment) {
                return (float) $payment['amount'] > 0;
            });

            // Re-index the array after filtering
            $this->selected_payment_methods = array_values($this->selected_payment_methods);

            if (empty($this->selected_payment_methods)) {
                Notification::make()->title('Tidak ada metode pembayaran yang valid!')->danger()->send();
                return;
            }
        } else {
            // Traditional payment validation with proper floating point comparison
            if (round($this->payment_amount, 2) < round($this->total_amount, 2)) {
                Notification::make()->title('Uang pembayaran kurang!')->danger()->send();
                return;
            }
        }

        DB::transaction(function () {
            // Header Transaksi
            $trx = Transaction::create([
                'invoice_code' => $this->generateInvoiceNumber(),
                'user_id' => auth()->id(),
                'total_amount' => $this->total_amount,
                'payment_amount' => $this->is_split_payment ? $this->split_payment_total : $this->payment_amount,
                'total_paid' => $this->is_split_payment ? $this->split_payment_total : $this->payment_amount,
                'change_amount' => $this->is_split_payment ? $this->change_amount : ($this->payment_amount - $this->total_amount),
                'status' => 'completed',
                'payment_method' => $this->is_split_payment ? 'split_payment' : $this->payment_method,
            ]);

            // Detail Transaksi & Potong Stok
            foreach ($this->cart as $item) {
                $product = Product::find($item['id']);

                $detailData = [
                    'transaction_id' => $trx->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['qty'],
                    'cost_price_at_date' => $product->cost_price,
                    'selling_price_at_date' => $item['price'],
                    'subtotal' => $item['price'] * $item['qty'],
                ];

                // Add note if it exists and category is Service
                if ($item['category_name'] === 'Service' && !empty($item['note'])) {
                    $detailData['note'] = $item['note'];
                }

                // Add serial number if it exists
                if (!empty($item['serial_number'])) {
                    $detailData['serial_number'] = $item['serial_number'];
                }

                TransactionDetail::create($detailData);
            }

            // If using split payment, save payment method details to pivot table
            if ($this->is_split_payment) {
                foreach ($this->selected_payment_methods as $payment) {
                    $trx->paymentMethods()->attach($payment['id'], [
                        'amount_paid' => (float) $payment['amount']
                    ]);
                }
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
        $this->is_split_payment = false;
        $this->selected_payment_methods = [];
        $this->split_payment_total = 0;
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

    private function generateInvoiceNumber(): string
    {
        $date = now()->format('Ymd');
        $random = Str::upper(Str::random(4));
        return "INV/{$date}/{$random}";
    }
}