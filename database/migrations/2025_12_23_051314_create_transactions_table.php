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
    Schema::create('transactions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained(); // Siapa Kasirnya
        $table->string('invoice_code')->unique(); // INV/2023...
        $table->decimal('total_amount', 15, 2); // Total Belanja
        $table->decimal('payment_amount', 15, 2); // Uang Bayar
        $table->decimal('change_amount', 15, 2); // Kembalian
        $table->string('payment_method')->default('cash'); // Cash, Transfer, QRIS
        $table->string('status')->default('completed'); // completed, void
        $table->text('notes')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
