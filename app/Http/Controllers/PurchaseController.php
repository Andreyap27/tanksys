<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index()
    {
        return view('purchase.index');
    }

    public function data()
    {
        $purchases = Purchase::with('creator')->latest()->get()->map(fn($p) => [
            'id'            => $p->id,
            'date'          => $p->date->translatedFormat('d M Y'),
            'date_raw'      => $p->date->format('Y-m-d'),
            'vendor'        => $p->vendor,
            'description'   => $p->description ?? '',
            'quantity'      => number_format($p->quantity, 2, ',', '.'),
            'quantity_raw'  => $p->quantity,
            'price'         => number_format($p->price, 0, ',', '.'),
            'price_raw'     => $p->price,
            'amount'        => number_format($p->amount, 0, ',', '.'),
            'noted'         => $p->noted ?? '',
        ]);
        return response()->json(['data' => $purchases]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'date'        => 'required|date',
            'vendor'      => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'quantity'    => 'required|numeric|min:0.01',
            'price'       => 'required|numeric|min:0',
            'noted'       => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            $amount   = $request->quantity * $request->price;
            $purchase = Purchase::create([
                'date'        => $request->date,
                'vendor'      => $request->vendor,
                'description' => $request->description,
                'quantity'    => $request->quantity,
                'price'       => $request->price,
                'amount'      => $amount,
                'noted'       => $request->noted,
                'created_by'  => auth()->id(),
            ]);

            $balance = Stock::currentBalance() + $request->quantity;
            Stock::create([
                'date'           => $request->date,
                'type'           => 'purchase',
                'reference_id'   => $purchase->id,
                'reference_type' => Purchase::class,
                'party'          => $request->vendor,
                'qty_in'         => $request->quantity,
                'qty_out'        => 0,
                'balance'        => $balance,
            ]);
        });

        return response()->json(['message' => 'Purchase berhasil disimpan.']);
    }

    public function update(Request $request, Purchase $purchase)
    {
        $request->validate([
            'date'        => 'required|date',
            'vendor'      => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'quantity'    => 'required|numeric|min:0.01',
            'price'       => 'required|numeric|min:0',
            'noted'       => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $purchase) {
            $amount = $request->quantity * $request->price;
            $diff   = $request->quantity - $purchase->quantity;

            $purchase->update([
                'date'        => $request->date,
                'vendor'      => $request->vendor,
                'description' => $request->description,
                'quantity'    => $request->quantity,
                'price'       => $request->price,
                'amount'      => $amount,
                'noted'       => $request->noted,
            ]);

            $stock = $purchase->stock;
            if ($stock) {
                $stock->update([
                    'date'    => $request->date,
                    'party'   => $request->vendor,
                    'qty_in'  => $request->quantity,
                    'balance' => $stock->balance + $diff,
                ]);
            }
        });

        return response()->json(['message' => 'Purchase berhasil diupdate.']);
    }

    public function destroy(Purchase $purchase)
    {
        DB::transaction(function () use ($purchase) {
            $purchase->stock?->delete();
            $purchase->delete();
        });

        return response()->json(['message' => 'Purchase berhasil dihapus.']);
    }
}
