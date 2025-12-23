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
    Schema::create('sales_returns', function (Blueprint $table) {
        $table->id();
        $table->foreignId('transaction_id')->constrained(); // Nota mana yg diretur
        $table->foreignId('user_id')->constrained(); // Siapa admin yg proses
        $table->date('date');
        $table->string('reason')->nullable(); // Alasan retur (Rusak/Salah Beli)
        $table->decimal('total_refund', 15, 2); // Uang yang dibalikin
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_returns');
    }
};
