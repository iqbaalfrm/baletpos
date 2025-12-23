<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use App\Http\Controllers\PrintController;

// Route buat cetak struk
Route::get('/print/struk/{id}', [PrintController::class, 'printStruk'])->name('print.struk');