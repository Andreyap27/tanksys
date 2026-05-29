<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\StockUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockUsageController extends Controller
{
    public function index()
    {
        return view('operasional.usage.index');
    }

    public function data()
    {
        $usages = StockUsage::with(['kapal', 'creator'])
            ->orderBy('date', 'desc')
            ->get()
            ->map(fn($u) => [
                'id'           => $u->id,
                'date'         => $this->resolveDate($u)->translatedFormat('d M Y H:i'),
                'date_raw'     => $u->date->format('Y-m-d H:i:s'),
                'kapal'        => $u->kapal?->name ?? '-',
                'warna'        => $u->warna,
                'quantity'     => number_format($u->quantity, 0, ',', '.'),
                'quantity_raw' => $u->quantity,
                'keperluan'    => $u->keperluan,
                'created_by'   => $u->creator?->name ?? '-',
            ]);

        return response()->json(['data' => $usages]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'date'      => 'required|date',
            'kapal_id'  => 'nullable|exists:kapals,id',
            'warna'     => 'required|in:biru,kuning',
            'quantity'  => 'required|numeric|min:0.01',
            'keperluan' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($request) {
            $usage = StockUsage::create([
                'date'       => $request->date . ' ' . now()->format('H:i:s'),
                'kapal_id'   => $request->kapal_id ?: null,
                'warna'      => $request->warna,
                'quantity'   => $request->quantity,
                'keperluan'  => $request->keperluan,
                'created_by' => auth()->id(),
            ]);

            Stock::create([
                'kapal_id'       => $usage->kapal_id,
                'date'           => $usage->date,
                'type'           => 'usage',
                'reference_id'   => $usage->id,
                'reference_type' => StockUsage::class,
                'party'          => $usage->keperluan,
                'warna'          => $usage->warna,
                'qty_in'         => 0,
                'qty_out'        => $usage->quantity,
            ]);
        });

        return response()->json(['message' => 'Pemakaian stok berhasil disimpan.']);
    }

    public function destroy(StockUsage $stockUsage)
    {
        if (!auth()->user()->canDelete()) {
            return response()->json(['message' => 'Tidak memiliki izin untuk menghapus.'], 403);
        }

        DB::transaction(function () use ($stockUsage) {
            $stockUsage->stock?->delete();
            $stockUsage->delete();
        });

        return response()->json(['message' => 'Pemakaian stok berhasil dihapus.']);
    }
}
