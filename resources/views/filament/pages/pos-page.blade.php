<div class="grid grid-cols-1 md:grid-cols-3 h-screen bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 overflow-hidden font-sans">

    <div class="md:col-span-2 flex flex-col h-full border-r border-slate-200 dark:border-slate-800">

        <div class="p-4 bg-white dark:bg-slate-900 shadow-md z-10 space-y-3">

            <div class="flex gap-3 h-12">
                <a href="/admin" class="flex items-center justify-center w-12 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-400 rounded-xl border border-slate-200 dark:border-slate-700 transition">
                    <x-heroicon-o-arrow-left class="w-6 h-6"/>
                </a>

                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                        <x-heroicon-o-magnifying-glass class="w-5 h-5"/>
                    </span>
                    <input wire:model.live.debounce.300ms="search" type="text"
                        class="w-full h-full bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white border border-slate-200 dark:border-slate-700 rounded-xl pl-10 pr-4 focus:outline-none focus:ring-1 focus:ring-blue-500 placeholder-slate-500"
                        placeholder="Cari produk (Nama / SKU)...">
                </div>

                <div class="flex items-center gap-2">

                    <button @click="toggleTheme()" type="button"
                        class="flex items-center justify-center w-12 h-12 rounded-xl border transition
                        bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700
                        text-slate-500 dark:text-yellow-400 hover:bg-slate-100 dark:hover:bg-slate-700">
                        <x-heroicon-o-sun x-show="theme === 'dark'" class="w-6 h-6" />
                        <x-heroicon-o-moon x-show="theme === 'light'" class="w-6 h-6" />
                    </button>

                    <form action="{{ route('filament.admin.auth.logout') }}" method="post" class="h-full">
                        @csrf
                        <button type="submit"
                            class="flex items-center justify-center w-12 h-12 rounded-xl border transition
                            bg-red-50 dark:bg-red-900/30 border-red-200 dark:border-red-900/50
                            text-red-600 dark:text-red-500 hover:bg-red-100 dark:hover:bg-red-800"
                            title="Keluar / Logout">
                            <x-heroicon-o-power class="w-6 h-6"/>
                        </button>
                    </form>
                </div>
            </div>

            <div class="flex space-x-2 overflow-x-auto pb-1 scrollbar-hide">
                <button wire:click="$set('selectedCategory', null)"
                    class="px-4 py-2 rounded-lg text-sm font-bold whitespace-nowrap transition {{ is_null($selectedCategory) ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-900/50' : 'bg-slate-200 text-slate-800 hover:bg-slate-300 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-slate-700' }}">
                    Semua
                </button>
                @foreach($this->categories as $category)
                    <button wire:click="$set('selectedCategory', {{ $category->id }})"
                        class="px-4 py-2 rounded-lg text-sm font-bold whitespace-nowrap transition {{ $selectedCategory == $category->id ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-900/50' : 'bg-slate-200 text-slate-800 hover:bg-slate-300 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-slate-700' }}">
                        {{ $category->name }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-4 bg-slate-50 dark:bg-slate-950">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                @forelse($this->products as $product)
                    <div wire:click="addToCart({{ $product->id }})"
                         class="group bg-white dark:bg-slate-900 rounded-xl p-3 cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800 hover:border-emerald-500 transition relative overflow-hidden">

                        <div class="aspect-square bg-slate-100 dark:bg-slate-800 rounded-lg mb-2 flex items-center justify-center overflow-hidden relative">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}"
                                     alt="{{ $product->name }}"
                                     class="object-cover w-full h-full group-hover:scale-110 transition duration-300">
                            @else
                                <span class="text-3xl group-hover:scale-110 transition duration-300">📦</span>
                            @endif
                        </div>

                        <h3 class="font-bold text-slate-900 dark:text-slate-200 text-sm truncate">{{ $product->name }}</h3>
                        <div class="flex justify-between items-end mt-1">
                            <span class="text-emerald-600 dark:text-emerald-400 font-bold text-sm">
                                Rp{{ number_format($product->selling_price/1000, 0) }}k
                            </span>
                            <span class="text-[10px] text-slate-600 dark:text-slate-500 bg-slate-200 dark:bg-slate-800 px-1.5 py-0.5 rounded">
                                Stok: {{ $product->stock }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-10 text-slate-500 dark:text-slate-400">
                        <x-heroicon-o-face-frown class="w-12 h-12 mx-auto mb-2 opacity-50"/>
                        <p>Produk tidak ditemukan</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="mt-6 px-4 pb-4">
            {{ $this->products->links() }}
        </div>
    </div>

    <div class="flex flex-col h-full bg-white dark:bg-slate-900 border-l border-slate-200 dark:border-slate-800 shadow-2xl relative z-20">

        <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-white dark:bg-slate-900 shadow-sm">
            <h2 class="text-lg font-bold flex items-center gap-2 text-slate-900 dark:text-white">
                <x-heroicon-m-shopping-bag class="w-5 h-5 text-emerald-600 dark:text-emerald-500"/> Keranjang
            </h2>
            <button wire:click="resetCart" class="text-red-600 hover:text-red-500 dark:text-red-500 dark:hover:text-red-400 text-xs font-bold uppercase tracking-wider px-2 py-1 rounded hover:bg-red-500/10 transition">
                Reset
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-4 space-y-2">
            @if(empty($cart))
                <div class="h-full flex flex-col items-center justify-center text-slate-600 dark:text-slate-500 opacity-60">
                    <x-heroicon-o-shopping-cart class="w-20 h-20 mb-3 text-slate-400 dark:text-slate-600"/>
                    <p class="font-medium text-slate-500 dark:text-slate-400">Keranjang Kosong</p>
                    <p class="text-xs text-slate-600 dark:text-slate-500">Pilih produk di sebelah kiri</p>
                </div>
            @else
                @foreach($cart as $id => $item)
                    <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl flex items-start gap-4 border border-slate-200 dark:border-slate-700/50 animate-in slide-in-from-right duration-300 shadow-md">
                        <!-- Product Image -->
                        <div class="flex-shrink-0">
                            @if($item['image_url'])
                                <img src="{{ $item['image_url'] }}"
                                     alt="{{ $item['name'] }}"
                                     class="w-24 h-24 rounded-xl object-cover border border-slate-200 dark:border-slate-700">
                            @else
                                <div class="w-24 h-24 rounded-xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center border border-slate-200 dark:border-slate-700">
                                    <x-heroicon-o-shopping-bag class="w-10 h-10 text-slate-400 dark:text-slate-500"/>
                                </div>
                            @endif
                        </div>

                        <div class="flex-1 min-w-0">
                            <h4 class="font-bold text-slate-900 dark:text-slate-200 text-xl truncate">{{ $item['name'] }}</h4>
                            <div class="text-blue-600 dark:text-blue-400 text-lg font-semibold mt-1">
                                Rp {{ number_format($item['price'], 0, ',', '.') }}
                            </div>

                            <!-- Service Note Input -->
                            @if($item['category_name'] === 'Service')
                                <div class="mt-3">
                                    <label class="block text-sm text-slate-600 dark:text-slate-400 mb-1">Catatan (cth: Keluhan/Data)...</label>
                                    <textarea
                                        wire:model.live="cart.{{ $id }}.note"
                                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg p-3 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                        placeholder="Tambahkan catatan..."
                                        rows="2">
                                    </textarea>
                                </div>
                            @endif

                            <!-- Serial Number Input for Laptops -->
                            @if(str_contains(strtolower($item['category_name']), 'laptop') || str_contains(strtolower($item['name']), 'laptop'))
                                <div class="mt-3">
                                    <label class="block text-sm text-slate-600 dark:text-slate-400 mb-1">Serial Number (SN):</label>
                                    <input
                                        wire:model.live="cart.{{ $id }}.serial_number"
                                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                        placeholder="Masukkan serial number..."
                                        type="text">
                                </div>
                            @endif
                        </div>

                        <div class="flex items-center bg-slate-100 dark:bg-slate-900 rounded-lg p-2 border border-slate-300 dark:border-slate-700">
                            <button wire:click="updateQty({{ $id }}, 'minus')" class="p-2 text-slate-600 hover:text-red-500 dark:text-slate-400 dark:hover:text-red-400 transition"><x-heroicon-s-minus-small class="w-6 h-6"/></button>
                            <span class="w-12 text-center font-bold text-lg">{{ $item['qty'] }}</span>
                            <button wire:click="updateQty({{ $id }}, 'plus')" class="p-2 text-slate-600 hover:text-emerald-500 dark:text-slate-400 dark:hover:text-emerald-400 transition"><x-heroicon-s-plus-small class="w-6 h-6"/></button>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        <div class="p-5 bg-white dark:bg-slate-800 border-t border-slate-200 dark:border-slate-700 space-y-4">
            <div class="flex justify-between text-lg font-medium text-slate-800 dark:text-slate-300">
                <span>Total</span>
                <span class="text-emerald-600 dark:text-emerald-400 font-bold text-2xl">Rp {{ number_format($total_amount, 0, ',', '.') }}</span>
            </div>

            <button wire:click="openCheckoutModal"
                class="w-full py-4 rounded-xl font-bold text-lg transition transform active:scale-95 flex items-center justify-center gap-2
                {{ $total_amount > 0 ? 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-lg shadow-emerald-900/50' : 'bg-slate-200 dark:bg-slate-700 text-slate-500 dark:text-slate-400 cursor-not-allowed' }}">
                <x-heroicon-m-banknotes class="w-6 h-6"/>
                Checkout
            </button>
        </div>
    </div>

    @if($isShowCheckoutModal)
    <div class="fixed inset-0 z-50 bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4 animate-in fade-in duration-200">
        <div class="bg-white dark:bg-slate-900 w-full max-w-4xl rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden flex flex-col md:flex-row">

            <div class="p-6 md:w-1/2 space-y-6 border-b border-slate-200 md:border-b-0 md:border-r dark:border-slate-800">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <x-heroicon-o-credit-card class="w-6 h-6 text-emerald-600 dark:text-emerald-500"/> Pembayaran
                    </h3>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-slate-600 dark:text-slate-400">Split Payment</span>
                        <button
                            wire:click="toggleSplitPayment"
                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none {{ $is_split_payment ? 'bg-emerald-600' : 'bg-slate-300 dark:bg-slate-600' }}"
                        >
                            <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $is_split_payment ? 'translate-x-6' : 'translate-x-1' }}"></span>
                        </button>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="bg-blue-100/50 dark:bg-blue-600/20 border border-blue-200 dark:border-blue-600/30 rounded-xl p-4">
                        <p class="text-sm text-blue-800 dark:text-blue-200 mb-1">Total Tagihan</p>
                        <h3 class="text-3xl font-bold text-slate-900 dark:text-white">Rp {{ number_format($total_amount, 0, ',', '.') }}</h3>
                    </div>

                    <div class="bg-amber-100/50 dark:bg-amber-500/20 border border-amber-200 dark:border-amber-500/30 rounded-xl p-4">
                        <p class="text-sm text-amber-800 dark:text-amber-200 mb-1">Kembalian</p>
                        <h3 class="text-3xl font-bold text-amber-600 dark:text-amber-400">Rp {{ number_format($change_amount, 0, ',', '.') }}</h3>
                    </div>
                </div>

                <!-- Traditional Payment Method -->
                @if(!$is_split_payment)
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-500 uppercase mb-1">Nama Pelanggan</label>
                        <input wire:model="customer_name" type="text" class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg px-4 py-2.5 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder-slate-400 dark:placeholder-slate-500" placeholder="Contoh: Budi">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-500 uppercase mb-1">Metode Bayar</label>
                        <select wire:model="payment_method" class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg px-4 py-2.5 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach($this->paymentMethods as $method)
                                <option value="{{ $method->code }}">{{ $method->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif
            </div>

            <div class="p-6 md:w-1/2 bg-slate-50 dark:bg-slate-800/50 flex flex-col justify-between">
                <div class="space-y-6">
                    <!-- Traditional Payment Input -->
                    @if(!$is_split_payment)
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-400 mb-2">Nominal Diterima</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-500 dark:text-slate-400 font-bold text-lg">Rp</span>
                            <input wire:model.live="payment_amount" type="number"
                                class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-3xl font-bold rounded-xl pl-12 pr-4 py-4 focus:outline-none focus:ring-2 focus:ring-emerald-500 placeholder-slate-300 dark:placeholder-slate-600"
                                placeholder="0" wire:ref="paymentInput" wire:blur="updatedPaymentAmount">
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <button wire:click="setPaymentAmount({{ $total_amount }})"
                            class="col-span-3 bg-indigo-100 hover:bg-indigo-200 dark:bg-indigo-600 dark:hover:bg-indigo-500 text-indigo-800 dark:text-white font-bold py-3 rounded-lg border border-indigo-300 dark:border-indigo-400/30 transition shadow-lg">
                            Uang Pas (Rp {{ number_format($total_amount, 0, ',', '.') }})
                        </button>

                        <button wire:click="setPaymentAmount(10000)" class="bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-200 font-semibold py-2 rounded-lg border border-slate-300 dark:border-slate-600 transition">10.000</button>
                        <button wire:click="setPaymentAmount(20000)" class="bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-200 font-semibold py-2 rounded-lg border border-slate-300 dark:border-slate-600 transition">20.000</button>
                        <button wire:click="setPaymentAmount(50000)" class="bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-200 font-semibold py-2 rounded-lg border border-slate-300 dark:border-slate-600 transition">50.000</button>
                        <button wire:click="setPaymentAmount(100000)" class="bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-200 font-semibold py-2 rounded-lg border border-slate-300 dark:border-slate-600 transition">100.000</button>
                    </div>
                    @else
                    <!-- Split Payment Section -->
                    <div>
                        <div class="flex justify-between items-center mb-3">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-400">Metode Pembayaran</label>
                            <button wire:click="addPaymentMethod" class="text-xs bg-emerald-600 hover:bg-emerald-700 text-white px-2 py-1 rounded-lg">
                                + Tambah
                            </button>
                        </div>

                        @if(count($selected_payment_methods) > 0)
                        <div class="space-y-3 max-h-60 overflow-y-auto">
                            @foreach($selected_payment_methods as $index => $payment)
                            <div class="bg-white dark:bg-slate-900 rounded-lg p-3 border border-slate-300 dark:border-slate-700">
                                <div class="flex justify-between items-center mb-2">
                                    <select
                                        wire:model.live="selected_payment_methods.{{ $index }}.id"
                                        class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded px-2 py-1 text-sm"
                                    >
                                        @foreach($this->paymentMethods as $method)
                                            <option value="{{ $method->id }}">{{ $method->name }}</option>
                                        @endforeach
                                    </select>
                                    @if(count($selected_payment_methods) > 1)
                                    <button
                                        wire:click="removePaymentMethod({{ $index }})"
                                        class="ml-2 text-red-600 hover:text-red-800 dark:text-red-500 dark:hover:text-red-400"
                                    >
                                        <x-heroicon-o-trash class="w-5 h-5"/>
                                    </button>
                                    @endif
                                </div>

                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-2 text-slate-500 dark:text-slate-400 font-bold">Rp</span>
                                    <input
                                        wire:model.live="selected_payment_methods.{{ $index }}.amount"
                                        type="number"
                                        class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded pl-8 pr-2 py-2 text-slate-900 dark:text-white"
                                        placeholder="0"
                                        wire:blur="calculateSplitPaymentTotal"
                                    >
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <p class="text-slate-500 dark:text-slate-400 text-sm">Belum ada metode pembayaran</p>
                        @endif

                        <div class="mt-3 p-3 bg-slate-200 dark:bg-slate-700 rounded-lg">
                            <div class="flex justify-between text-sm">
                                <span>Total Pembayaran:</span>
                                <span class="font-bold">Rp {{ number_format($split_payment_total, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-4 mt-8">
                    <button wire:click="closeCheckoutModal"
                        class="py-3.5 rounded-xl font-bold text-slate-700 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                        Batal
                    </button>
                    <button wire:click="processPayment"
                        wire:loading.attr="disabled"
                        class="py-3.5 rounded-xl font-bold text-white shadow-lg shadow-emerald-900/30 transition transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed
                        {{ (!$is_split_payment && round($payment_amount, 2) >= round($total_amount, 2)) || ($is_split_payment && round($split_payment_total, 2) >= round($total_amount, 2)) ? 'bg-emerald-600 hover:bg-emerald-500' : 'bg-slate-200 dark:bg-slate-700 text-slate-500 dark:text-slate-400' }}"
                        @if((!$is_split_payment && round($payment_amount, 2) < round($total_amount, 2)) || ($is_split_payment && round($split_payment_total, 2) < round($total_amount, 2))) disabled @endif>
                        <span wire:loading.remove wire:target="processPayment">Bayar Sekarang</span>
                        <span wire:loading wire:target="processPayment">Memproses...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($isShowSuccessModal)
    <div class="fixed inset-0 z-50 bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4 animate-in fade-in duration-200">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-md">
            <!-- Header Section -->
            <div class="bg-emerald-500 p-6 rounded-t-2xl">
                <div class="flex items-center justify-center">
                    <div class="w-16 h-16 rounded-full bg-emerald-600 flex items-center justify-center">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-xl font-bold text-white text-center mt-4">Transaksi Berhasil!</h3>
            </div>

            <!-- Body Section -->
            <div class="p-6">
                <div class="bg-slate-100 dark:bg-slate-800 rounded-xl p-4 mb-6">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-slate-600 dark:text-slate-300">Total Tagihan:</span>
                        <span class="font-bold">Rp {{ number_format(round($total_amount, 2), 0, ',', '.') }}</span>
                    </div>

                    @if($is_split_payment)
                        <!-- Split Payment Details -->
                        <div class="mt-3 space-y-2">
                            <p class="text-slate-600 dark:text-slate-300 font-semibold">Detail Pembayaran:</p>
                            @foreach($selected_payment_methods as $payment)
                                @php
                                    $method = $this->paymentMethods->firstWhere('id', $payment['id']);
                                @endphp
                                @if($method && (float)$payment['amount'] > 0)
                                <div class="flex justify-between items-center">
                                    <span class="text-slate-600 dark:text-slate-300">Bayar {{ $method->name }}:</span>
                                    <span class="font-bold">Rp {{ number_format(round((float)$payment['amount'], 2), 0, ',', '.') }}</span>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-slate-600 dark:text-slate-300">Uang Diterima:</span>
                            <span class="font-bold">Rp {{ number_format(round($payment_amount, 2), 0, ',', '.') }}</span>
                        </div>
                    @endif

                    <div class="flex justify-between items-center mt-4 pt-4 border-t border-slate-300 dark:border-slate-700">
                        <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400">Kembalian:</span>
                        <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format(round($change_amount, 2), 0, ',', '.') }}</span>
                    </div>
                </div>

                <p class="text-slate-600 dark:text-slate-400 text-center mb-4">Nomor Nota: {{ $lastTransactionId }}</p>

                <!-- Action Buttons (Grid Layout) -->
                <div class="grid grid-cols-2 gap-3">
                    <button wire:click="printInvoice" class="bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-800 dark:text-white font-bold py-3 px-4 rounded-lg transition">
                        Cetak Struk
                    </button>
                    <button wire:click="doneTransaction" class="bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-800 text-white font-bold py-3 px-4 rounded-lg transition">
                        Transaksi Baru
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif


    <script>
    document.addEventListener('livewire:initialized', () => {
        @this.on('open-print-window', (event) => {
            // Buka URL struk di window baru (popup print)
            window.open(event.url, '_blank', 'width=800,height=600');
        });

        @this.on('payment-modal-opened', () => {
            // Focus the payment amount input when modal opens
            const paymentInput = document.querySelector('input[wire\\:model="payment_amount"]');
            if (paymentInput) {
                setTimeout(() => {
                    paymentInput.focus();
                    paymentInput.select(); // Select all text for easy editing
                }, 100);
            }
        });
    });
</script>

</div>