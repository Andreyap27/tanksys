<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\Kapal;
use App\Models\Mobil;

class StockController extends Controller
{
    public function index()
    {
        $kapals   = Kapal::orderBy('code')->get();
        $mobils   = Mobil::orderBy('name')->get();
        $balance  = Stock::currentBalance();
        $totalIn  = Stock::sum('qty_in');
        $totalOut = Stock::sum('qty_out');
        return view('stock.index', compact('balance', 'totalIn', 'totalOut', 'kapals', 'mobils'));
    }

    public function data()
    {
        $kapalId = request('kapal_id');
        $query   = Stock::orderBy('date')->orderBy('created_at');
        if ($kapalId) $query->where('kapal_id', $kapalId);
        $running = 0;
        $stocks  = $query->get()
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

    public function summary()
    {
        $kapalId  = request('kapal_id');
        $balance  = Stock::currentBalance($kapalId ?: null);
        $query    = Stock::query();
        if ($kapalId) $query->where('kapal_id', $kapalId);
        $totalIn  = (float) $query->sum('qty_in');
        $totalOut = (float) $query->sum('qty_out');
        return response()->json(compact('balance', 'totalIn', 'totalOut'));
    }
}
