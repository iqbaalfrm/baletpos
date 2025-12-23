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
    Schema::create('purchases', function (Blueprint $table) {
        $table->id();
        $table->foreignId('supplier_id')->constrained(); // Beli dari siapa
        $table->foreignId('user_id')->constrained(); // Siapa yg input (Admin)
        $table->string('purchase_date'); // Tanggal beli
        $table->decimal('total_amount', 15, 2); // Total duit keluar
        $table->string('status')->default('completed'); // completed
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
