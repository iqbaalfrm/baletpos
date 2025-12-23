<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create 3 specific users with roles
        $admin = User::create([
            'name' => 'Admin Utama',
            'email' => 'admin@pos.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $kasir = User::create([
            'name' => 'Kasir Toko',
            'email' => 'kasir@pos.com',
            'password' => Hash::make('password'),
            'role' => 'cashier',
        ]);

        $finance = User::create([
            'name' => 'Staf Keuangan',
            'email' => 'finance@pos.com',
            'password' => Hash::make('password'),
            'role' => 'finance',
        ]);

        // Create categories
        $laptopCategory = Category::create(['name' => 'Laptop', 'slug' => 'laptop', 'type' => 'product']);
        $peripheralCategory = Category::create(['name' => 'Peripherals', 'slug' => 'peripherals', 'type' => 'product']);
        $serviceCategory = Category::create(['name' => 'Service', 'slug' => 'service', 'type' => 'service']);
        $accessoriesCategory = Category::create(['name' => 'Aksesoris Komputer', 'slug' => 'aksesoris-komputer', 'type' => 'product']);
        $componentCategory = Category::create(['name' => 'Komponen PC', 'slug' => 'komponen-pc', 'type' => 'product']);

        // Create 10 suppliers
        $suppliers = [];
        for ($i = 1; $i <= 10; $i++) {
            $suppliers[] = Supplier::create([
                'code' => 'SUP' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'name' => 'Supplier ' . $i,
                'phone' => '0812' . str_pad($i, 8, '0', STR_PAD_LEFT),
                'address' => 'Alamat Supplier ' . $i . ', Jakarta',
            ]);
        }

        // Create 50 products with realistic data
        $products = [];
        for ($i = 1; $i <= 50; $i++) {
            $category = match (true) {
                $i <= 15 => $laptopCategory->id,
                $i <= 25 => $peripheralCategory->id,
                $i <= 35 => $serviceCategory->id,
                $i <= 45 => $accessoriesCategory->id,
                default => $componentCategory->id,
            };

            $supplier = $suppliers[array_rand($suppliers)]->id;

            $productName = match (true) {
                $i <= 15 => 'Laptop ' . $i,
                $i <= 25 => 'Mouse Wireless ' . $i,
                $i <= 35 => 'Service Software ' . $i,
                $i <= 45 => 'Kabel USB ' . $i,
                default => 'RAM 8GB ' . $i,
            };

            $costPrice = match (true) {
                $i <= 15 => rand(3000000, 15000000), // Laptop
                $i <= 25 => rand(50000, 300000), // Mouse
                $i <= 35 => rand(100000, 500000), // Service
                $i <= 45 => rand(25000, 100000), // Accessories
                default => rand(300000, 800000), // Components
            };

            $margin = rand(10, 30); // 10-30% margin
            $sellingPrice = $costPrice * (1 + ($margin / 100));

            $products[] = Product::create([
                'category_id' => $category,
                'supplier_id' => $supplier,
                'code' => 'PRD' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'name' => $productName,
                'cost_price' => $costPrice,
                'margin_percentage' => $margin,
                'selling_price' => $sellingPrice,
                'stock' => rand(1, 50),
                'min_stock' => 5,
                'is_active' => true,
            ]);
        }

        // Create 20 dummy transactions from last month
        for ($i = 1; $i <= 20; $i++) {
            $transaction = Transaction::create([
                'user_id' => $admin->id,
                'invoice_code' => 'INV/' . now()->subDays(rand(1, 30))->format('Ymd') . '/' . Str::upper(Str::random(4)),
                'total_amount' => 0,
                'payment_amount' => 0,
                'change_amount' => 0,
                'status' => 'completed',
                'payment_method' => ['cash', 'qris', 'transfer'][array_rand(['cash', 'qris', 'transfer'])],
                'created_at' => now()->subDays(rand(1, 30)),
            ]);

            $transactionTotal = 0;
            $detailCount = rand(1, 5); // Each transaction has 1-5 items

            for ($j = 0; $j < $detailCount; $j++) {
                $randomProduct = $products[array_rand($products)];
                $qty = rand(1, 3);
                $subtotal = $randomProduct->selling_price * $qty;

                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $randomProduct->id,
                    'quantity' => $qty,
                    'cost_price_at_date' => $randomProduct->cost_price,
                    'selling_price_at_date' => $randomProduct->selling_price,
                    'subtotal' => $subtotal,
                ]);

                $transactionTotal += $subtotal;

                // Decrease stock
                $randomProduct->decrement('stock', $qty);
            }

            // Update transaction total
            $transaction->update([
                'total_amount' => $transactionTotal,
                'payment_amount' => $transactionTotal + rand(0, 100000), // Add some change
                'change_amount' => rand(0, 100000),
            ]);
        }
    }
}