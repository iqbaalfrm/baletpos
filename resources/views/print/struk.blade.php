<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Struk - {{ $transaction->invoice_code }}</title>
    <style>
        /* Reset CSS biar rapi pas diprint */
        body {
            font-family: 'Courier New', Courier, monospace; /* Font ala mesin kasir */
            font-size: 12px; /* Ukuran font standar struk */
            margin: 0;
            padding: 10px;
            color: #000;
        }

        /* Container utama (atur lebar sesuai printer, misal 80mm atau full A5) */
        .container {
            width: 100%;
            max-width: 800px; /* Sesuaikan kalau mau lebar kayak di foto */
            margin: 0 auto;
        }

        /* Header Toko */
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }
        .store-name {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .store-address {
            font-size: 11px;
        }

        /* Info Transaksi (Kiri Kanan) */
        .meta-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        .meta-group table {
            width: 100%;
        }
        .meta-group td {
            padding: 1px 5px;
            vertical-align: top;
        }

        /* Tabel Barang */
        .item-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .item-table th {
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            text-align: left;
            padding: 5px;
        }
        .item-table td {
            padding: 5px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        /* Totalan */
        .totals {
            width: 100%;
            display: flex;
            justify-content: flex-end;
        }
        .totals-table {
            width: 40%; /* Lebar area total */
        }
        .totals-table td {
            padding: 2px;
            text-align: right;
        }
        .grand-total {
            font-weight: bold;
            font-size: 14px;
            border-top: 1px dashed #000;
            padding-top: 5px;
        }

        /* Footer */
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 11px;
            border-top: 1px dashed #000;
            padding-top: 10px;
        }

        /* HIDE elemen browser pas ngeprint */
        @media print {
            @page { margin: 0; size: auto; }
            body { margin: 10px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()"> <div class="container">
        <div class="header">
            <div class="store-name">BALET COMPUTER</div> 
            <div class="store-address">Jalan Prof. Moh. Yamin No 57 Kudaile Slawi Kab. Tegal</div>
        </div>

        <div class="meta-info">
            <div class="meta-group">
                <table>
                    <tr><td>No Nota</td><td>: {{ $transaction->invoice_code }}</td></tr>
                    <tr><td>Kasir</td><td>: {{ $transaction->user->name ?? 'Admin' }}</td></tr>
                </table>
            </div>
            <div class="meta-group">
                <table>
                    <tr><td>Tanggal</td><td>: {{ date('d F Y', strtotime($transaction->created_at)) }}</td></tr>
                    <tr><td>Pelanggan</td><td>: {{ $transaction->customer_name ?? 'Umum' }}</td></tr>
                </table>
            </div>
        </div>

        <table class="item-table">
            <thead>
                <tr>
                    <th style="width: 5%">No</th>
                    <th style="width: 15%">Kode</th>
                    <th style="width: 35%">Nama Barang</th>
                    <th class="text-right" style="width: 15%">Harga</th>
                    <th class="text-center" style="width: 5%">Qty</th>
                    <th class="text-right" style="width: 10%">Diskon</th>
                    <th class="text-right" style="width: 15%">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transaction->details as $index => $detail)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $detail->product->code ?? '-' }}</td> <td>{{ $detail->product->name }}</td>
                    <td class="text-right">{{ number_format($detail->selling_price_at_date, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $detail->quantity }}</td>
                    <td class="text-right">0</td> <td class="text-right">{{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <table class="totals-table">
                <tr>
                    <td>Total Harga</td>
                    <td>{{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Diskon</td>
                    <td>0</td>
                </tr>
                <tr class="grand-total">
                    <td>Total Bayar</td>
                    <td>{{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Metode Bayar</td>
                    <td>{{ ucfirst($transaction->payment_method) }}</td>
                </tr>
                <tr>
                    <td>Tunai</td>
                    <td>{{ number_format($transaction->payment_amount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Kembali</td>
                    <td>{{ number_format($transaction->change_amount, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <div class="footer">
            <p>Barang yang sudah dibeli tidak dapat dikembalikan / ditukar.</p>
            <p>*** TERIMA KASIH ATAS KUNJUNGAN ANDA ***</p>
        </div>
    </div>

</body>
</html>