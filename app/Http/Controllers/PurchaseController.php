<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Stock;
use App\Models\Notification;
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
        $kapalId   = request('kapal_id');
        $query     = Purchase::with('creator')->orderBy('date', 'desc');
        if ($kapalId) $query->where('kapal_id', $kapalId);
        $purchases = $query->get()->map(fn($p) => [
            'id'            => $p->id,
            'kapal_id'      => $p->kapal_id,
            'date'          => $p->date->translatedFormat('d M Y'),
            'date_raw'      => $p->date->format('Y-m-d'),
            'vendor'        => $p->vendor,
            'description'   => $p->description ?? '',
            'quantity'      => number_format($p->quantity, 2, ',', '.'),
            'quantity_raw'  => $p->quantity,
            'price'         => number_format($p->price, 0, ',', '.'),
            'price_raw'     => $p->price,
            'amount'        => number_format($p->amount, 0, ',', '.'),
            'amount_raw'    => $p->amount,
            'noted'         => $p->noted ?? '',
            'status'        => $p->status,
        ]);
        return response()->json(['data' => $purchases]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kapal_id'    => 'nullable|exists:kapals,id',
            'date'        => 'required|date',
            'vendor'      => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'quantity'    => 'required|numeric|min:0.01',
            'price'       => 'required|numeric|min:0',
            'noted'       => 'nullable|string',
        ]);

        Purchase::create([
            'kapal_id'    => $request->kapal_id ?: null,
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

        Notification::sendToApprovers('approval', 'Purchase Baru',
            auth()->user()->name . ' menambahkan purchase dari ' . $request->vendor . ' menunggu persetujuan.',
            route('purchase.index'));

        $message = 'Purchase berhasil disimpan dan menunggu persetujuan SPV.';

        return response()->json(['message' => $message]);
    }

    public function update(Request $request, Purchase $purchase)
    {
        if (!auth()->user()->canManage()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk mengedit.'], 403);
        }

        $request->validate([
            'kapal_id'    => 'nullable|exists:kapals,id',
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
                'kapal_id'    => $request->kapal_id ?: null,
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
                'kapal_id'       => $purchase->kapal_id,
                'date'           => $purchase->date,
                'type'           => 'purchase',
                'reference_id'   => $purchase->id,
                'reference_type' => Purchase::class,
                'party'          => $purchase->vendor,
                'qty_in'         => $purchase->quantity,
                'qty_out'        => 0,
            ]);
        });

        // Notify creator
        Notification::send([$purchase->created_by], 'info', 'Purchase Disetujui',
            'Purchase dari ' . $purchase->vendor . ' telah disetujui.',
            route('purchase.index'));

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

        Notification::send([$purchase->created_by], 'info', 'Purchase Ditolak',
            'Purchase dari ' . $purchase->vendor . ' telah ditolak.',
            route('purchase.index'));

        return response()->json(['message' => 'Purchase berhasil ditolak.']);
    }

    public function trash()
    {
        return view('purchase.trash', [
            'canRestore' => auth()->user()->canManage(),
            'canDelete'  => auth()->user()->canDelete(),
        ]);
    }

    public function trashData()
    {
        $kapalId   = request('kapal_id');
        $query     = Purchase::onlyTrashed()->with(['creator', 'deleter'])->orderBy('date', 'desc');
        if ($kapalId) $query->where('kapal_id', $kapalId);
        $purchases = $query->get()->map(fn($p) => [
            'id'            => $p->id,
            'kapal_id'      => $p->kapal_id,
            'date'          => $p->date->translatedFormat('d M Y'),
            'date_raw'      => $p->date->format('Y-m-d'),
            'vendor'        => $p->vendor,
            'description'   => $p->description ?? '',
            'quantity'      => number_format($p->quantity, 2, ',', '.'),
            'quantity_raw'  => $p->quantity,
            'price'         => number_format($p->price, 0, ',', '.'),
            'price_raw'     => $p->price,
            'amount'        => number_format($p->amount, 0, ',', '.'),
            'amount_raw'    => $p->amount,
            'noted'         => $p->noted ?? '',
            'status'        => $p->status,
            'deleted_at'    => $p->deleted_at->translatedFormat('d M Y H:i'),
            'deleted_by'    => $p->deleter?->name ?? 'N/A',
        ]);
        return response()->json(['data' => $purchases]);
    }

    public function restore($id)
    {
        if (!auth()->user()->canManage()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk restore data.'], 403);
        }

        $purchase = Purchase::onlyTrashed()->find($id);
        if (!$purchase) {
            return response()->json(['message' => 'Purchase tidak ditemukan.'], 404);
        }

        $purchase->restore();

        return response()->json(['message' => 'Purchase berhasil di-restore.']);
    }

    public function forceDelete($id)
    {
        if (!auth()->user()->canDelete()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menghapus data.'], 403);
        }

        $purchase = Purchase::onlyTrashed()->find($id);
        if (!$purchase) {
            return response()->json(['message' => 'Purchase tidak ditemukan.'], 404);
        }

        $purchase->forceDelete();

        return response()->json(['message' => 'Purchase berhasil dihapus permanen.']);
    }
}
