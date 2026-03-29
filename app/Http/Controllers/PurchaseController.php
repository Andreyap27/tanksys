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
        return view('purchase.index', [
            'canApprove' => auth()->user()->canApprove(),
            'canManage'  => auth()->user()->canManage(),
            'canDelete'  => auth()->user()->canDelete(),
        ]);
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
            'status'        => $p->status,
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

        Purchase::create([
            'date'        => $request->date,
            'vendor'      => $request->vendor,
            'description' => $request->description,
            'quantity'    => $request->quantity,
            'price'       => $request->price,
            'amount'      => $request->quantity * $request->price,
            'noted'       => $request->noted,
            'created_by'  => auth()->id(),
            'status'      => 'pending',
        ]);

        $message = 'Purchase berhasil disimpan dan menunggu persetujuan SPV.';

        return response()->json(['message' => $message]);
    }

    public function update(Request $request, Purchase $purchase)
    {
        if (!auth()->user()->canManage()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk mengedit.'], 403);
        }

        $request->validate([
            'date'        => 'required|date',
            'vendor'      => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'quantity'    => 'required|numeric|min:0.01',
            'price'       => 'required|numeric|min:0',
            'noted'       => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $purchase) {
            if ($purchase->status === 'approved' && $purchase->stock) {
                $purchase->stock->delete();
            }

            $purchase->update([
                'date'        => $request->date,
                'vendor'      => $request->vendor,
                'description' => $request->description,
                'quantity'    => $request->quantity,
                'price'       => $request->price,
                'amount'      => $request->quantity * $request->price,
                'noted'       => $request->noted,
                'status'      => 'pending',
                'approved_by' => null,
                'approved_at' => null,
            ]);
        });

        return response()->json(['message' => 'Purchase berhasil diupdate dan menunggu persetujuan ulang.']);
    }

    public function destroy(Purchase $purchase)
    {
        if (!auth()->user()->canDelete()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menghapus data.'], 403);
        }

        DB::transaction(function () use ($purchase) {
            if ($purchase->status === 'approved') {
                $purchase->stock?->delete();
            }
            $purchase->delete();
        });

        return response()->json(['message' => 'Purchase berhasil dihapus.']);
    }

    public function approve(Purchase $purchase)
    {
        if (!auth()->user()->canApprove()) {
            return response()->json(['message' => 'Tidak memiliki akses untuk menyetujui.'], 403);
        }

        if ($purchase->status !== 'pending') {
            return response()->json(['message' => 'Purchase ini sudah diproses sebelumnya.'], 422);
        }

        DB::transaction(function () use ($purchase) {
            $purchase->update([
                'status'      => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            Stock::create([
                'date'           => $purchase->date,
                'type'           => 'purchase',
                'reference_id'   => $purchase->id,
                'reference_type' => Purchase::class,
                'party'          => $purchase->vendor,
                'qty_in'         => $purchase->quantity,
                'qty_out'        => 0,
            ]);
        });

        return response()->json(['message' => 'Purchase berhasil disetujui.']);
    }

    public function reject(Purchase $purchase)
    {
        if (!auth()->user()->canApprove()) {
            return response()->json(['message' => 'Tidak memiliki akses.'], 403);
        }

        if ($purchase->status !== 'pending') {
            return response()->json(['message' => 'Purchase ini sudah diproses sebelumnya.'], 422);
        }

        $purchase->update(['status' => 'rejected']);

        return response()->json(['message' => 'Purchase berhasil ditolak.']);
    }
}
