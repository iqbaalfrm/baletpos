<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class PrintController extends Controller
{
    public function printStruk($id)
    {
        // Ambil data transaksi beserta detail produknya
        $transaction = Transaction::with(['details.product', 'user'])->findOrFail($id);

        // Lempar ke tampilan struk
        return view('print.struk', compact('transaction'));
    }
}