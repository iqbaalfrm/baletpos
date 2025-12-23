<x-filament-panels::page>
    
    <div class="bg-white dark:bg-gray-900 p-4 rounded-xl shadow border border-gray-200 dark:border-gray-700">
        <form wire:submit="filter" class="flex flex-col md:flex-row gap-4 items-end">
            {{ $this->form }}
            
            <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-2 px-6 rounded-lg transition">
                Tampilkan Data
            </button>
            
            <button type="button" onclick="window.print()" class="bg-gray-700 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded-lg transition flex items-center gap-2">
                <x-heroicon-o-printer class="w-5 h-5"/> Cetak Laporan
            </button>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow border-l-4 border-blue-500">
            <h3 class="text-gray-500 text-sm font-medium">Total Omset (Penjualan)</h3>
            <p class="text-2xl font-bold text-gray-800 dark:text-white mt-1">Rp {{ number_format($omset, 0, ',', '.') }}</p>
        </div>

        <div class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow border-l-4 border-amber-500">
            <h3 class="text-gray-500 text-sm font-medium">HPP (Modal Barang Terjual)</h3>
            <p class="text-2xl font-bold text-gray-800 dark:text-white mt-1">Rp {{ number_format($modal_terjual, 0, ',', '.') }}</p>
        </div>

        <div class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow border-l-4 border-red-500">
            <h3 class="text-gray-500 text-sm font-medium">Biaya Operasional</h3>
            <p class="text-2xl font-bold text-gray-800 dark:text-white mt-1">Rp {{ number_format($biaya_operasional, 0, ',', '.') }}</p>
        </div>

        <div class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow border-l-4 {{ $laba_bersih >= 0 ? 'border-emerald-500' : 'border-rose-600' }}">
            <h3 class="text-gray-500 text-sm font-medium">Laba Bersih</h3>
            <p class="text-2xl font-bold mt-1 {{ $laba_bersih >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                Rp {{ number_format($laba_bersih, 0, ',', '.') }}
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-2 bg-white dark:bg-gray-900 rounded-xl shadow overflow-hidden border border-gray-200 dark:border-gray-700">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                <h3 class="font-bold text-lg">Rincian Penjualan per Kategori</h3>
                <p class="text-xs text-gray-500">Laptop, Peripheral, Service, dll.</p>
            </div>
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 uppercase">
                    <tr>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3 text-center">Qty Terjual</th>
                        <th class="px-4 py-3 text-right">Total Omset</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($sales_by_category as $cat)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                        <td class="px-4 py-3 font-medium">{{ $cat->category_name }}</td>
                        <td class="px-4 py-3 text-center">{{ $cat->total_qty }}</td>
                        <td class="px-4 py-3 text-right font-bold">Rp {{ number_format($cat->total_omset, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-4 py-6 text-center text-gray-500">Belum ada data penjualan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-xl shadow overflow-hidden border border-gray-200 dark:border-gray-700 h-fit">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-emerald-50 dark:bg-emerald-900/20">
                <h3 class="font-bold text-lg text-emerald-700 dark:text-emerald-400">Aset Gudang</h3>
                <p class="text-xs text-gray-500">Nilai uang yang ada di stok barang.</p>
            </div>
            <div class="p-6 text-center">
                <p class="text-gray-500 mb-2">Total Nilai Aset</p>
                <h2 class="text-3xl font-extrabold text-gray-800 dark:text-white">
                    Rp {{ number_format($total_aset, 0, ',', '.') }}
                </h2>
                <p class="text-xs text-gray-400 mt-4">*Dihitung dari (Stok x Harga Beli)</p>
            </div>
        </div>

    </div>

    <style>
        @media print {
            body { background: white; color: black; }
            .fi-sidebar, .fi-topbar, button { display: none !important; } /* Sembunyikan menu admin & tombol */
            .bg-white, .dark\:bg-gray-900 { background: white !important; border: 1px solid #ddd !important; color: black !important; }
            .shadow { box-shadow: none !important; }
            .text-white { color: black !important; }
        }
    </style>
</x-filament-panels::page>