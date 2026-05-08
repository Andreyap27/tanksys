<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\Kapal;
use App\Models\Mobil;

class StockController extends Controller
{
    public function index()
    {
        $kapals  = Kapal::orderBy('code')->get();
        $mobils  = Mobil::orderBy('name')->get();
        $byWarna = $this->warnaBalances(null);
        return view('stock.index', compact('kapals', 'mobils', 'byWarna'));
    }

    private function warnaBalances(?string $kapalId): array
    {
        $result = [];
        foreach (['merah', 'biru', 'kuning'] as $w) {
            $q = Stock::where('warna', $w);
            if ($kapalId) $q->where('kapal_id', $kapalId);
            $in  = (float) (clone $q)->sum('qty_in');
            $out = (float) (clone $q)->sum('qty_out');
            $result[$w] = [
                'in'      => $in,
                'out'     => $out,
                'balance' => $in - $out,
            ];
        }
        return $result;
    }

    public function data()
    {
        $kapalId = request('kapal_id');
        $query   = Stock::orderBy('date', 'desc')->orderBy('created_at', 'desc');
        if ($kapalId) $query->where('kapal_id', $kapalId);
        $running = 0;
        $stocks  = $query->get()
            ->map(function ($s) use (&$running) {
                $running += (float) $s->qty_in - (float) $s->qty_out;
                return [
                    'date'    => $s->date->translatedFormat('d M Y'),
                    'party'   => $s->party,
                    'type'    => match($s->type) {
                        'purchase'     => 'Pembelian',
                        'sale'         => 'Penjualan',
                        'transfer_in'  => 'Transfer Masuk',
                        'transfer_out' => 'Transfer Keluar',
                        'usage'        => 'Pemakaian',
                        default        => ucfirst($s->type),
                    },
                    'qty_in'  => $s->qty_in > 0 ? number_format($s->qty_in, 2, ',', '.') : null,
                    'qty_out' => $s->qty_out > 0 ? number_format($s->qty_out, 2, ',', '.') : null,
                    'balance' => number_format($running, 2, ',', '.'),
                ];
            });

        return response()->json(['data' => $stocks]);
    }

    public function summary()
    {
        $kapalId = request('kapal_id') ?: null;
        $byWarna = $this->warnaBalances($kapalId);
        return response()->json(compact('byWarna'));
    }
}
