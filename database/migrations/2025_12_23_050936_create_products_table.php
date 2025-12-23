<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        // Relasi ke kategori
        $table->foreignId('category_id')->constrained()->cascadeOnDelete();
        
        $table->string('code')->unique(); // Barcode/Kode Barang
        $table->string('name');
        
        // Duit-duitan (Penting!)
        $table->decimal('cost_price', 15, 2)->default(0); // HPP / Harga Dasar
        $table->integer('margin_percentage')->default(0); // Setting Margin (%)
        $table->decimal('selling_price', 15, 2)->default(0); // Harga Jual
        
        // Inventory
        $table->integer('stock')->default(0);
        $table->integer('min_stock')->default(5); // Alert kalau stok tipis
        
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
