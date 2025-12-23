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
            <h3 class="text-gray-500 text-sm font-medium">HPP (COGS)</h3>
            <p class="text-2xl font-bold text-gray-800 dark:text-white mt-1">Rp {{ number_format($hpp_terjual, 0, ',', '.') }}</p>
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

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Laptop & Peripheral Sales -->
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow overflow-hidden border border-gray-200 dark:border-gray-700">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                <h3 class="font-bold text-lg">Laporan Penjualan Produk</h3>
                <p class="text-xs text-gray-500">Laptop, Peripheral, dll.</p>
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

        <!-- Technician Services Sales -->
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow overflow-hidden border border-gray-200 dark:border-gray-700">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                <h3 class="font-bold text-lg">Laporan Teknisi</h3>
                <p class="text-xs text-gray-500">Jasa Service & Teknisi</p>
            </div>
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 uppercase">
                    <tr>
                        <th class="px-4 py-3">Jasa Service</th>
                        <th class="px-4 py-3 text-center">Qty</th>
                        <th class="px-4 py-3 text-right">Total Omset</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($sales_by_technician as $service)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                        <td class="px-4 py-3 font-medium">{{ $service->service_name }}</td>
                        <td class="px-4 py-3 text-center">{{ $service->total_qty }}</td>
                        <td class="px-4 py-3 text-right font-bold">Rp {{ number_format($service->total_omset, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-4 py-6 text-center text-gray-500">Belum ada data jasa teknisi.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
        <!-- Asset Report -->
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow overflow-hidden border border-gray-200 dark:border-gray-700 h-fit">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-emerald-50 dark:bg-emerald-900/20">
                <h3 class="font-bold text-lg text-emerald-700 dark:text-emerald-400">Laporan Aset</h3>
                <p class="text-xs text-gray-500">Nilai total stok barang saat ini (HPP Terjual)</p>
            </div>
            <div class="p-6 text-center">
                <p class="text-gray-500 mb-2">Total Nilai Aset</p>
                <h2 class="text-3xl font-extrabold text-gray-800 dark:text-white">
                    Rp {{ number_format($total_aset, 0, ',', '.') }}
                </h2>
                <p class="text-xs text-gray-400 mt-4">*Dihitung dari (Stok x Harga Beli)</p>
            </div>
        </div>

        <!-- Operational Costs Report -->
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow overflow-hidden border border-gray-200 dark:border-gray-700">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                <h3 class="font-bold text-lg">Laporan Biaya Operasional</h3>
                <p class="text-xs text-gray-500">Pengeluaran bulanan toko</p>
            </div>
            <div class="p-6">
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Biaya Operasional:</span>
                        <span class="font-bold">Rp {{ number_format($biaya_operasional, 0, ',', '.') }}</span>
                    </div>
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600 font-medium">Laba Kotor:</span>
                            <span class="font-bold">Rp {{ number_format($laba_kotor, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between mt-1">
                            <span class="text-gray-600 font-medium">Laba Bersih:</span>
                            <span class="font-bold {{ $laba_bersih >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">Rp {{ number_format($laba_bersih, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Profit & Loss Report -->
    <div class="mt-6 bg-white dark:bg-gray-900 rounded-xl shadow overflow-hidden border border-gray-200 dark:border-gray-700">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-blue-50 dark:bg-blue-900/20">
            <h3 class="font-bold text-lg text-blue-700 dark:text-blue-400">Laporan Laba Rugi (P&L)</h3>
            <p class="text-xs text-gray-500">Perhitungan laba dan rugi sesuai formula</p>
        </div>
        <div class="p-6">
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">Total Omset (Omset):</span>
                    <span class="font-bold">Rp {{ number_format($omset, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">HPP Terjual (COGS):</span>
                    <span class="font-bold">Rp {{ number_format($hpp_terjual, 0, ',', '.') }}</span>
                </div>
                <div class="border-t border-gray-200 dark:border-gray-700 pt-2 flex justify-between">
                    <span class="text-gray-600 font-medium">Laba Kotor:</span>
                    <span class="font-bold">Rp {{ number_format($laba_kotor, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Biaya Operasional:</span>
                    <span class="font-bold">Rp {{ number_format($biaya_operasional, 0, ',', '.') }}</span>
                </div>
                <div class="border-t border-gray-200 dark:border-gray-700 pt-2 flex justify-between text-lg font-bold">
                    <span class="text-gray-800 dark:text-white">Laba Bersih:</span>
                    <span class="{{ $laba_bersih >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">Rp {{ number_format($laba_bersih, 0, ',', '.') }}</span>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-4">Formula: <em>Omset - COGS - Biaya Operasional = Laba Bersih</em></p>
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