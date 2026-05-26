<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\StockTransfer;
use App\Models\Kapal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockTransferController extends Controller
{
    public function index()
    {
        return view('operasional.transfer.index');
    }

    public function data()
    {
        $transfers = StockTransfer::with(['fromKapal', 'toKapal', 'creator'])
            ->orderBy('date', 'desc')
            ->get()
            ->map(fn($t) => [
                'id'            => $t->id,
                'date'          => $t->date->translatedFormat('d M Y H:i'),
                'date_raw'      => $t->date->format('Y-m-d'),
                'from_kapal'    => $t->fromKapal?->name ?? '-',
                'to_kapal'      => $t->toKapal?->name ?? '-',
                'warna'         => $t->warna,
                'quantity'      => number_format($t->quantity, 0, ',', '.'),
                'quantity_raw'  => $t->quantity,
                'note'          => $t->note ?? '',
                'created_by'    => $t->creator?->name ?? '-',
            ]);

        return response()->json(['data' => $transfers]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'date'          => 'required|date',
            'from_kapal_id' => 'nullable|exists:kapals,id',
            'to_kapal_id'   => 'nullable|exists:kapals,id|different:from_kapal_id',
            'warna'         => 'required|in:biru,kuning',
            'quantity'      => 'required|numeric|min:0.01',
            'note'          => 'nullable|string',
        ], [
            'to_kapal_id.different' => 'Kapal tujuan tidak boleh sama dengan kapal asal.',
        ]);

        DB::transaction(function () use ($request) {
            $transfer = StockTransfer::create([
                'date'          => $request->date . ' ' . now()->format('H:i:s'),
                'from_kapal_id' => $request->from_kapal_id ?: null,
                'to_kapal_id'   => $request->to_kapal_id ?: null,
                'warna'         => $request->warna,
                'quantity'      => $request->quantity,
                'note'          => $request->note,
                'created_by'    => auth()->id(),
            ]);

            $fromKapal = $transfer->fromKapal?->name ?? 'Pusat';
            $toKapal   = $transfer->toKapal?->name   ?? 'Pusat';

            // Stock keluar dari kapal asal
            Stock::create([
                'kapal_id'       => $transfer->from_kapal_id,
                'date'           => $transfer->date,
                'type'           => 'transfer_out',
                'reference_id'   => $transfer->id,
                'reference_type' => StockTransfer::class,
                'party'          => 'Transfer ke ' . $toKapal,
                'warna'          => $transfer->warna,
                'qty_in'         => 0,
                'qty_out'        => $transfer->quantity,
            ]);

            // Stock masuk ke kapal tujuan
            Stock::create([
                'kapal_id'       => $transfer->to_kapal_id,
                'date'           => $transfer->date,
                'type'           => 'transfer_in',
                'reference_id'   => $transfer->id,
                'reference_type' => StockTransfer::class,
                'party'          => 'Transfer dari ' . $fromKapal,
                'warna'          => $transfer->warna,
                'qty_in'         => $transfer->quantity,
                'qty_out'        => 0,
            ]);
        });

        return response()->json(['message' => 'Transfer stok berhasil disimpan.']);
    }

    public function destroy(StockTransfer $stockTransfer)
    {
        if (!auth()->user()->canDelete()) {
            return response()->json(['message' => 'Tidak memiliki izin untuk menghapus.'], 403);
        }

        DB::transaction(function () use ($stockTransfer) {
            $stockTransfer->stocks()->each(fn($s) => $s->delete());
            $stockTransfer->delete();
        });

        return response()->json(['message' => 'Transfer stok berhasil dihapus.']);
    }
}
