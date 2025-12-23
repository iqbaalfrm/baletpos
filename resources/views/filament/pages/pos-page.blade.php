<div class="grid grid-cols-1 md:grid-cols-3 h-screen bg-gray-950 text-gray-100 overflow-hidden font-sans">
    
    <div class="md:col-span-2 flex flex-col h-full border-r border-gray-800">
        
        <div class="p-4 bg-gray-900 shadow-md z-10 space-y-3">
            
            <div class="flex gap-3 h-12">
                <a href="/admin" class="flex items-center justify-center w-12 bg-gray-800 hover:bg-gray-700 text-gray-400 hover:text-white rounded-xl border border-gray-700 transition" title="Kembali ke Dashboard">
                    <x-heroicon-o-arrow-left class="w-6 h-6"/>
                </a>

                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                        <x-heroicon-o-magnifying-glass class="w-5 h-5"/>
                    </span>
                    <input wire:model.live.debounce.300ms="search" type="text" 
                        class="w-full h-full bg-gray-800 text-white border border-gray-700 rounded-xl pl-10 pr-4 focus:outline-none focus:ring-1 focus:ring-emerald-500 placeholder-gray-500" 
                        placeholder="Cari produk (Nama / SKU)...">
                </div>

                <form action="{{ route('filament.admin.auth.logout') }}" method="post" class="h-full">
                    @csrf
                    <button type="submit" class="h-full px-4 bg-red-900/30 hover:bg-red-600 text-red-500 hover:text-white border border-red-900/50 rounded-xl transition flex items-center gap-2" title="Keluar Aplikasi">
                        <x-heroicon-o-power class="w-6 h-6"/>
                        <span class="hidden lg:inline font-bold text-sm">Keluar</span>
                    </button>
                </form>
            </div>

            <div class="flex space-x-2 overflow-x-auto pb-1 scrollbar-hide">
                <button wire:click="$set('selectedCategory', null)" 
                    class="px-4 py-2 rounded-lg text-sm font-bold whitespace-nowrap transition {{ is_null($selectedCategory) ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-900/50' : 'bg-gray-800 text-gray-400 hover:bg-gray-700' }}">
                    Semua
                </button>
                @foreach($this->categories as $category)
                    <button wire:click="$set('selectedCategory', {{ $category->id }})" 
                        class="px-4 py-2 rounded-lg text-sm font-bold whitespace-nowrap transition {{ $selectedCategory == $category->id ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-900/50' : 'bg-gray-800 text-gray-400 hover:bg-gray-700' }}">
                        {{ $category->name }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-4 bg-gray-950">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                @forelse($this->products as $product)
                    <div wire:click="addToCart({{ $product->id }})" 
                         class="group bg-gray-900 rounded-xl p-3 cursor-pointer hover:bg-gray-800 border border-gray-800 hover:border-emerald-500 transition relative overflow-hidden">
                        
                        <div class="aspect-square bg-gray-800 rounded-lg mb-2 flex items-center justify-center overflow-hidden relative">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" 
                                     alt="{{ $product->name }}" 
                                     class="object-cover w-full h-full group-hover:scale-110 transition duration-300">
                            @else
                                <span class="text-3xl group-hover:scale-110 transition duration-300">📦</span>
                            @endif
                        </div>
                        
                        <h3 class="font-bold text-gray-200 text-sm truncate">{{ $product->name }}</h3>
                        <div class="flex justify-between items-end mt-1">
                            <span class="text-emerald-400 font-bold text-sm">
                                Rp{{ number_format($product->selling_price/1000, 0) }}k
                            </span>
                            <span class="text-[10px] text-gray-500 bg-gray-800 px-1.5 py-0.5 rounded">
                                Stok: {{ $product->stock }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-10 text-gray-500">
                        <x-heroicon-o-face-frown class="w-12 h-12 mx-auto mb-2 opacity-50"/>
                        <p>Produk tidak ditemukan</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="flex flex-col h-full bg-gray-900 border-l border-gray-800 shadow-2xl relative z-20">
        
        <div class="p-4 border-b border-gray-800 flex justify-between items-center bg-gray-900 shadow-sm">
            <h2 class="text-lg font-bold flex items-center gap-2 text-white">
                <x-heroicon-m-shopping-bag class="w-5 h-5 text-emerald-500"/> Keranjang
            </h2>
            <button wire:click="$set('cart', [])" class="text-red-500 hover:text-red-400 text-xs font-bold uppercase tracking-wider px-2 py-1 rounded hover:bg-red-500/10 transition">
                Reset
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-4 space-y-2">
            @if(empty($cart))
                <div class="h-full flex flex-col items-center justify-center text-gray-600 opacity-60">
                    <x-heroicon-o-shopping-cart class="w-20 h-20 mb-3 text-gray-700"/>
                    <p class="font-medium text-gray-500">Keranjang Kosong</p>
                    <p class="text-xs text-gray-600">Pilih produk di sebelah kiri</p>
                </div>
            @else
                @foreach($cart as $id => $item)
                    <div class="bg-gray-800 p-3 rounded-xl flex items-center gap-3 border border-gray-700/50 animate-in slide-in-from-right duration-300">
                        <div class="flex-1 min-w-0">
                            <h4 class="font-bold text-gray-200 text-sm truncate">{{ $item['name'] }}</h4>
                            <div class="text-emerald-400 text-xs font-medium">
                                Rp {{ number_format($item['price'], 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="flex items-center bg-gray-900 rounded-lg p-1 border border-gray-700">
                            <button wire:click="updateQty({{ $id }}, 'minus')" class="p-1 text-gray-400 hover:text-red-400 transition"><x-heroicon-s-minus-small class="w-4 h-4"/></button>
                            <span class="w-8 text-center font-bold text-sm">{{ $item['qty'] }}</span>
                            <button wire:click="updateQty({{ $id }}, 'plus')" class="p-1 text-gray-400 hover:text-emerald-400 transition"><x-heroicon-s-plus-small class="w-4 h-4"/></button>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        <div class="p-5 bg-gray-800 border-t border-gray-700 space-y-4">
            <div class="flex justify-between text-lg font-medium text-gray-300">
                <span>Total</span>
                <span class="text-emerald-400 font-bold text-2xl">Rp {{ number_format($total_amount, 0, ',', '.') }}</span>
            </div>

            <button wire:click="openCheckoutModal" 
                class="w-full py-4 rounded-xl font-bold text-lg transition transform active:scale-95 flex items-center justify-center gap-2
                {{ $total_amount > 0 ? 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-lg shadow-emerald-900/50' : 'bg-gray-700 text-gray-500 cursor-not-allowed' }}">
                <x-heroicon-m-banknotes class="w-6 h-6"/>
                Checkout
            </button>
        </div>
    </div>

    @if($isShowCheckoutModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 backdrop-blur-sm transition-opacity p-4 animate-in fade-in duration-200">
        <div class="bg-gray-900 w-full max-w-4xl rounded-2xl shadow-2xl border border-gray-800 overflow-hidden flex flex-col md:flex-row">
            
            <div class="p-6 md:w-1/2 space-y-6 border-b md:border-b-0 md:border-r border-gray-800">
                <h3 class="text-xl font-bold text-white flex items-center gap-2">
                    <x-heroicon-o-credit-card class="w-6 h-6 text-emerald-500"/> Pembayaran
                </h3>

                <div class="space-y-3">
                    <div class="bg-blue-600/20 border border-blue-600/30 rounded-xl p-4">
                        <p class="text-sm text-blue-200 mb-1">Total Tagihan</p>
                        <h3 class="text-3xl font-bold text-white">Rp {{ number_format($total_amount, 0, ',', '.') }}</h3>
                    </div>
                    
                    <div class="bg-amber-500/20 border border-amber-500/30 rounded-xl p-4">
                        <p class="text-sm text-amber-200 mb-1">Kembalian</p>
                        <h3 class="text-3xl font-bold text-amber-400">Rp {{ number_format($change_amount, 0, ',', '.') }}</h3>
                    </div>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Pelanggan</label>
                        <input wire:model="customer_name" type="text" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder-gray-600" placeholder="Contoh: Budi">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Metode Bayar</label>
                        <select wire:model="payment_method" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="cash">Tunai (Cash)</option>
                            <option value="qris">QRIS</option>
                            <option value="transfer">Transfer Bank</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="p-6 md:w-1/2 bg-gray-800/50 flex flex-col justify-between">
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Nominal Diterima</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400 font-bold text-lg">Rp</span>
                            <input wire:model.live="payment_amount" type="number" 
                                class="w-full bg-gray-900 border border-gray-700 text-white text-3xl font-bold rounded-xl pl-12 pr-4 py-4 focus:outline-none focus:ring-2 focus:ring-emerald-500 placeholder-gray-700" 
                                placeholder="0" autofocus>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <button wire:click="setPaymentAmount({{ $total_amount }})" 
                            class="col-span-3 bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 rounded-lg border border-indigo-400/30 transition shadow-lg">
                            Uang Pas (Rp {{ number_format($total_amount, 0, ',', '.') }})
                        </button>

                        <button wire:click="setPaymentAmount(10000)" class="bg-gray-700 hover:bg-gray-600 text-gray-200 font-semibold py-2 rounded-lg border border-gray-600 transition">10.000</button>
                        <button wire:click="setPaymentAmount(20000)" class="bg-gray-700 hover:bg-gray-600 text-gray-200 font-semibold py-2 rounded-lg border border-gray-600 transition">20.000</button>
                        <button wire:click="setPaymentAmount(50000)" class="bg-gray-700 hover:bg-gray-600 text-gray-200 font-semibold py-2 rounded-lg border border-gray-600 transition">50.000</button>
                        <button wire:click="setPaymentAmount(100000)" class="bg-gray-700 hover:bg-gray-600 text-gray-200 font-semibold py-2 rounded-lg border border-gray-600 transition">100.000</button>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-8">
                    <button wire:click="closeCheckoutModal" 
                        class="py-3.5 rounded-xl font-bold text-gray-300 hover:text-white hover:bg-gray-700 transition">
                        Batal
                    </button>
                    <button wire:click="processPayment" 
                        class="py-3.5 rounded-xl font-bold text-white shadow-lg shadow-emerald-900/30 transition transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed
                        {{ $payment_amount >= $total_amount ? 'bg-emerald-600 hover:bg-emerald-500' : 'bg-gray-700' }}"
                        @if($payment_amount < $total_amount) disabled @endif>
                        Bayar Sekarang
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($isShowSuccessModal)
    <div class="fixed inset-0 z-[60] flex items-center justify-center bg-black/90 backdrop-blur-sm p-4 animate-in zoom-in duration-200">
        <div class="bg-gray-900 w-full max-w-sm rounded-3xl shadow-2xl border border-gray-800 overflow-hidden text-center p-8 relative">
            
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-32 h-32 bg-emerald-500/20 blur-3xl rounded-full pointer-events-none"></div>

            <div class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 mb-6 shadow-xl shadow-emerald-900/50">
                <x-heroicon-s-check class="h-12 w-12 text-white" />
            </div>

            <h2 class="text-3xl font-bold text-white mb-2">Berhasil!</h2>
            <p class="text-gray-400 mb-8">Transaksi sukses tersimpan.</p>

            <div class="bg-gray-800/50 rounded-2xl p-4 space-y-3 mb-8 border border-gray-800">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-400">Kembalian</span>
                    <span class="text-emerald-400 font-bold text-lg">Rp {{ number_format($change_amount, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="space-y-3">
                <button wire:click="printInvoice" 
                    class="w-full flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-3.5 rounded-xl transition shadow-lg shadow-emerald-900/20">
                    <x-heroicon-m-printer class="w-5 h-5"/>
                    Cetak Struk
                </button>
                
                <button wire:click="doneTransaction" 
                    class="w-full bg-gray-800 hover:bg-gray-700 text-gray-300 font-bold py-3.5 rounded-xl transition">
                    Transaksi Baru
                </button>
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
    });
</script>

</div>