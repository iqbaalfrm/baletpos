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
    Schema::create('operational_costs', function (Blueprint $table) {
        $table->id();
        $table->date('date'); // Tanggal pengeluaran
        $table->string('category'); // Kategori (Listrik, Gaji, ATK, dll)
        $table->string('description')->nullable(); // Keterangan detail
        $table->decimal('amount', 15, 2); // Jumlah uang keluar
        $table->foreignId('user_id')->constrained(); // Siapa yang input
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operational_costs');
    }
};
