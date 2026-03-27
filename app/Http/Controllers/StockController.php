<?php

namespace App\Http\Controllers;

use App\Models\Stock;

class StockController extends Controller
{
    public function index()
    {
        $balance = Stock::currentBalance();
        return view('dashboard.stock.index', compact('balance'));
    }

    public function data()
    {
        $stocks = Stock::latest('id')->get()->map(fn($s) => [
            'date'    => $s->date->format('d/m/Y'),
            'party'   => $s->party,
            'type'    => $s->type === 'purchase' ? 'Pembelian' : 'Penjualan',
            'qty_in'  => $s->qty_in > 0 ? number_format($s->qty_in, 2) : '-',
            'qty_out' => $s->qty_out > 0 ? number_format($s->qty_out, 2) : '-',
            'balance' => number_format($s->balance, 2),
        ]);
        return response()->json(['data' => $stocks]);
    }
}
