<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Sale;
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
        $extras  = $this->extraByWarna(null);
        return view('stock.index', compact('kapals', 'mobils', 'byWarna', 'extras'));
    }

    private function warnaBalances(?string $kapalId): array
    {
        $result = [];
        foreach (['biru', 'kuning'] as $w) {
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

    private function extraByWarna(?string $kapalId): array
    {
        $result = [];
        foreach (['biru', 'kuning'] as $w) {
            $qp = Purchase::whereIn('status', ['approved', 'paid'])->where('warna', $w);
            $qs = Sale::whereIn('status', ['approved', 'paid'])->where('warna', $w);
            if ($kapalId) {
                $qp->where('kapal_id', $kapalId);
                $qs->where('kapal_id', $kapalId);
            }
            $result[$w] = [
                'qty'         => (float) (clone $qp)->sum('quantity'),
                'extra'       => (float) (clone $qp)->sum('extra'),
                'short_purchase' => (float) (clone $qp)->sum('short'),
                'short_sale'     => (float) (clone $qs)->sum('short'),
            ];
        }
        return $result;
    }

    public function data()
    {
        $kapalId = request('kapal_id');

        // Tanggal sama: masuk (purchase/transfer_in) lebih dulu, baru keluar
        $query = Stock::orderBy('date', 'asc')
            ->orderByRaw("FIELD(type, 'purchase', 'transfer_in', 'sale', 'transfer_out', 'usage')")
            ->orderBy('created_at', 'asc');
        if ($kapalId) $query->where('kapal_id', $kapalId);

        $running = 0;
        $stocks  = $query->get()
            ->map(function ($s) use (&$running) {
                $running += (float) $s->qty_in - (float) $s->qty_out;
                return [
                    'date'     => $this->resolveDate($s)->translatedFormat('d M Y H:i'),
                    'date_raw' => $s->date->format('Y-m-d'),
                    'party'    => $s->party,
                    'warna'    => $s->warna ?? '',
                    'type'     => match($s->type) {
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
            })
            ->reverse()  // balik urutan: baris terbaru di atas dengan saldo terkini
            ->values();

        return response()->json(['data' => $stocks]);
    }

    public function summary()
    {
        $kapalId = request('kapal_id') ?: null;
        $byWarna = $this->warnaBalances($kapalId);
        $extras  = $this->extraByWarna($kapalId);
        return response()->json(compact('byWarna', 'extras'));
    }
}
