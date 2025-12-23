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
    Schema::create('transaction_details', function (Blueprint $table) {
        $table->id();
        $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
        $table->foreignId('product_id')->constrained();
        
        $table->integer('quantity');
        
        // KUNCI LABA RUGI: Simpan harga saat kejadian
        $table->decimal('cost_price_at_date', 15, 2); // HPP saat dijual
        $table->decimal('selling_price_at_date', 15, 2); // Harga Jual saat itu
        $table->decimal('subtotal', 15, 2); // qty * selling_price
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_details');
    }
};
