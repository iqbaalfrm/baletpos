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
    Schema::create('sales_return_details', function (Blueprint $table) {
        $table->id();
        $table->foreignId('sales_return_id')->constrained()->cascadeOnDelete();
        $table->foreignId('product_id')->constrained();
        $table->integer('quantity'); // Jumlah yg diretur
        $table->decimal('refund_price', 15, 2); // Harga saat beli
        $table->decimal('subtotal', 15, 2);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_return_details');
    }
};
