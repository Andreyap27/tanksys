<?php

namespace App\Http\Controllers;

use App\Models\Stock;

class StockController extends Controller
{
    public function index()
    {
        $balance  = Stock::currentBalance();
        $totalIn  = Stock::sum('qty_in');
        $totalOut = Stock::sum('qty_out');
        return view('stock.index', compact('balance', 'totalIn', 'totalOut'));
    }

    public function data()
    {
        $running = 0;
        $stocks  = Stock::orderBy('date')->orderBy('created_at')->get()
            ->map(function ($s) use (&$running) {
                $running += (float) $s->qty_in - (float) $s->qty_out;
                return [
                    'date'    => $s->date->translatedFormat('d M Y'),
                    'party'   => $s->party,
                    'type'    => $s->type === 'purchase' ? 'Pembelian' : 'Penjualan',
                    'qty_in'  => $s->qty_in > 0 ? number_format($s->qty_in, 2, ',', '.') : null,
                    'qty_out' => $s->qty_out > 0 ? number_format($s->qty_out, 2, ',', '.') : null,
                    'balance' => number_format($running, 2, ',', '.'),
                ];
            });

        return response()->json(['data' => $stocks]);
    }
}
