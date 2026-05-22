<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Customer;
use App\Models\Stock;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index()
    {
        return view('sales.index');
    }

    public function nextInvoice()
    {
        $kapalId = request('kapal_id');
        $date    = now()->format('Ymd');

        if ($kapalId) {
            $kapal  = \App\Models\Kapal::find($kapalId);
            $code   = $kapal ? $kapal->code : 'K000';
            $prefix = "INV-{$date}-{$code}-";
        } else {
            $prefix = "INV-{$date}-";
        }

        $latest = Sale::withTrashed()
            ->where('invoice_number', 'like', $prefix . '%')
            ->orderByRaw('LENGTH(invoice_number) DESC, invoice_number DESC')
            ->value('invoice_number');
        $num = $latest ? (int) substr($latest, strlen($prefix)) : 0;
        return response()->json(['invoice_number' => $prefix . str_pad($num + 1, 3, '0', STR_PAD_LEFT)]);
    }

    public function show(Sale $sale)
    {
        return response()->json([
            'id'             => $sale->id,
            'date'           => $sale->date->format('Y-m-d'),
            'invoice_number' => $sale->invoice_number,
            'customer_id'    => $sale->customer_id,
            'customer_name'  => $sale->customer->name,
            'description'    => $sale->description ?? '',
            'quantity'       => $sale->quantity,
            'price'          => $sale->price,
            'noted'          => $sale->noted ?? '',
        ]);
    }

    public function data()
    {
        try {
            $kapalId = request('kapal_id');
            $query   = Sale::with('customer')->orderBy('date', 'desc');
            if ($kapalId) $query->where('kapal_id', $kapalId);
            $sales = $query->get()->map(fn($s) => [
                'id'             => $s->id,
                'kapal_id'       => $s->kapal_id,
                'date'           => $s->date->translatedFormat('d M Y'),
                'date_raw'       => $s->date->format('Y-m-d'),
                'invoice_number' => $s->invoice_number,
                'customer_id'    => $s->customer_id,
                'customer_name'  => $s->customer->name,
                'description'    => $s->description ?? '-',
                'warna'          => $s->warna ?? '',
                'quantity'       => number_format($s->quantity, 0, ',', '.'),
                'quantity_raw'   => $s->quantity,
                'extra'          => number_format($s->extra, 0, ',', '.'),
                'extra_raw'      => $s->extra,
                'short'          => number_format($s->short, 0, ',', '.'),
                'short_raw'      => $s->short,
                'price'          => number_format($s->price, 0, ',', '.'),
                'price_raw'      => $s->price,
                'amount'         => number_format($s->amount, 0, ',', '.'),
                'amount_raw'     => $s->amount,
                'noted'          => $s->noted ?? '-',
                'status'         => $s->status,
            ]);
            return response()->json(['data' => $sales]);
        } catch (\Exception $e) {
            return response()->json(['data' => [], 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'kapal_id'       => 'nullable|exists:kapals,id',
            'date'           => 'required|date',
            'invoice_number' => 'required|string|unique:sales,invoice_number,NULL,id,deleted_at,NULL',
            'customer_id'    => 'required|exists:customers,id',
            'description'    => 'nullable|string|max:255',
            'warna'          => 'required|in:biru,kuning',
            'quantity'       => 'required|numeric|min:0.01',
            'extra'          => 'nullable|numeric|min:0',
            'short'          => 'nullable|numeric|min:0',
            'price'          => 'required|numeric|min:0',
            'noted'          => 'nullable|string',
        ]);

        $extra    = (float) ($request->extra ?? 0);
        $short    = (float) ($request->short ?? 0);
        $totalOut = (float) $request->quantity + $extra - $short;
        $warna    = $request->warna ?: null;
        $currentBalance = Stock::currentBalance(null, $warna);
        if ($totalOut > $currentBalance) {
            $label = $warna ? " BBM " . ucfirst($warna) : '';
            return response()->json([
                'message' => 'Stok' . $label . ' tidak mencukupi. Stok saat ini: ' . number_format($currentBalance, 2, ',', '.') . ' L',
            ], 422);
        }

        DB::transaction(function () use ($request, $extra, $short) {
            $sale = Sale::create([
                'kapal_id'       => $request->kapal_id ?: null,
                'date'           => $request->date,
                'invoice_number' => $request->invoice_number,
                'customer_id'    => $request->customer_id,
                'description'    => $request->description,
                'warna'          => $request->warna ?: null,
                'quantity'       => $request->quantity,
                'extra'          => $extra,
                'short'          => $short,
                'price'          => $request->price,
                'amount'         => $request->quantity * $request->price,
                'noted'          => $request->noted,
                'created_by'     => auth()->id(),
                'status'         => 'approved',
                'approved_by'    => auth()->id(),
                'approved_at'    => now(),
            ]);

            Stock::create([
                'kapal_id'       => $sale->kapal_id,
                'date'           => $sale->date,
                'type'           => 'sale',
                'reference_id'   => $sale->id,
                'reference_type' => Sale::class,
                'party'          => $sale->customer->name,
                'warna'          => $sale->warna,
                'qty_in'         => 0,
                'qty_out'        => (float) $sale->quantity + (float) $sale->extra - (float) $sale->short,
            ]);
        });

        return response()->json(['message' => 'Penjualan berhasil disimpan.']);
    }

    public function update(Request $request, Sale $sale)
    {
        if (!auth()->user()->canManage()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk mengedit.'], 403);
        }

        $request->validate([
            'kapal_id'       => 'nullable|exists:kapals,id',
            'date'           => 'required|date',
            'invoice_number' => 'required|string|unique:sales,invoice_number,' . $sale->id . ',id,deleted_at,NULL',
            'customer_id'    => 'required|exists:customers,id',
            'description'    => 'nullable|string|max:255',
            'warna'          => 'required|in:biru,kuning',
            'quantity'       => 'required|numeric|min:0.01',
            'extra'          => 'nullable|numeric|min:0',
            'short'          => 'nullable|numeric|min:0',
            'price'          => 'required|numeric|min:0',
            'noted'          => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $sale) {
            if (in_array($sale->status, ['approved', 'paid'])) {
                $sale->stock?->delete();
            }

            $sale->update([
                'kapal_id'       => $request->kapal_id ?: null,
                'date'           => $request->date,
                'invoice_number' => $request->invoice_number,
                'customer_id'    => $request->customer_id,
                'description'    => $request->description,
                'warna'          => $request->warna ?: null,
                'quantity'       => $request->quantity,
                'extra'          => (float) ($request->extra ?? 0),
                'short'          => (float) ($request->short ?? 0),
                'price'          => $request->price,
                'amount'         => $request->quantity * $request->price,
                'noted'          => $request->noted,
                'status'         => 'pending',
                'approved_by'    => null,
                'approved_at'    => null,
            ]);
        });

        Notification::sendToApprovers('approval', 'Penjualan Diupdate',
            auth()->user()->name . ' mengubah penjualan ' . $request->invoice_number . ' dan menunggu persetujuan.',
            route('sales.index'));

        return response()->json(['message' => 'Penjualan berhasil diupdate dan menunggu persetujuan ulang.']);
    }

    public function destroy(Sale $sale)
    {
        if (!auth()->user()->canDelete()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menghapus data.'], 403);
        }

        DB::transaction(function () use ($sale) {
            if (in_array($sale->status, ['approved', 'paid'])) {
                $sale->stock?->delete();
            }
            $sale->delete();
        });

        return response()->json(['message' => 'Penjualan berhasil dihapus.']);
    }

    public function approve(Sale $sale)
    {
        if (!auth()->user()->canApprove()) {
            return response()->json(['message' => 'Tidak memiliki akses untuk menyetujui.'], 403);
        }

        if ($sale->status !== 'pending') {
            return response()->json(['message' => 'Penjualan ini sudah diproses sebelumnya.'], 422);
        }

        $totalOut = (float) $sale->quantity + (float) $sale->extra - (float) $sale->short;
        $warna    = $sale->warna ?: null;
        $currentBalance = Stock::currentBalance(null, $warna);
        if ($totalOut > $currentBalance) {
            $label = $warna ? " BBM " . ucfirst($warna) : '';
            return response()->json([
                'message' => 'Stok' . $label . ' tidak mencukupi untuk menyetujui penjualan ini. Stok saat ini: ' . number_format($currentBalance, 2, ',', '.') . ' L',
            ], 422);
        }

        DB::transaction(function () use ($sale) {
            $sale->update([
                'status'      => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            Stock::create([
                'kapal_id'       => $sale->kapal_id,
                'date'           => $sale->date,
                'type'           => 'sale',
                'reference_id'   => $sale->id,
                'reference_type' => Sale::class,
                'party'          => $sale->customer->name,
                'warna'          => $sale->warna,
                'qty_in'         => 0,
                'qty_out'        => (float) $sale->quantity + (float) $sale->extra - (float) $sale->short,
            ]);
        });

        Notification::send([$sale->created_by], 'info', 'Penjualan Disetujui',
            'Penjualan ' . $sale->invoice_number . ' telah disetujui.',
            route('sales.index'));

        return response()->json(['message' => 'Penjualan berhasil disetujui.']);
    }

    public function reject(Sale $sale)
    {
        if (!auth()->user()->canApprove()) {
            return response()->json(['message' => 'Tidak memiliki akses.'], 403);
        }

        if ($sale->status !== 'pending') {
            return response()->json(['message' => 'Penjualan ini sudah diproses sebelumnya.'], 422);
        }

        $sale->update(['status' => 'rejected']);

        Notification::send([$sale->created_by], 'info', 'Penjualan Ditolak',
            'Penjualan ' . $sale->invoice_number . ' telah ditolak.',
            route('sales.index'));

        return response()->json(['message' => 'Penjualan berhasil ditolak.']);
    }

    public function paid(Sale $sale)
    {
        if (!auth()->user()->canApprove()) {
            return response()->json(['message' => 'Tidak memiliki akses.'], 403);
        }

        if ($sale->status !== 'approved') {
            return response()->json(['message' => 'Hanya penjualan berstatus approved yang dapat ditandai paid.'], 422);
        }

        $sale->update(['status' => 'paid']);

        return response()->json(['message' => 'Penjualan berhasil ditandai sebagai paid.']);
    }

    public function invoice(Sale $sale)
    {
        $sale->load('customer', 'creator');
        return view('sales.invoice', compact('sale'));
    }

    public function trash()
    {
        return view('sales.trash');
    }

    public function trashData()
    {
        $kapalId = request('kapal_id');
        $query   = Sale::onlyTrashed()->with(['creator', 'customer', 'deleter'])->orderBy('date', 'desc');
        if ($kapalId) $query->where('kapal_id', $kapalId);
        $sales   = $query->get()->map(fn($s) => [
            'id'              => $s->id,
            'kapal_id'        => $s->kapal_id,
            'date'            => $s->date->translatedFormat('d M Y'),
            'date_raw'        => $s->date->format('Y-m-d'),
            'invoice_number'  => $s->invoice_number,
            'customer_name'   => $s->customer->name ?? '-',
            'description'     => $s->description ?? '',
            'quantity'        => number_format($s->quantity, 0, ',', '.'),
            'quantity_raw'    => $s->quantity,
            'price'           => number_format($s->price, 0, ',', '.'),
            'price_raw'       => $s->price,
            'amount'          => number_format($s->amount, 0, ',', '.'),
            'amount_raw'      => $s->amount,
            'noted'           => $s->noted ?? '',
            'status'          => $s->status,
            'deleted_at'      => $s->deleted_at->translatedFormat('d M Y H:i'),
            'deleted_by'      => $s->deleter?->name ?? 'N/A',
        ]);
        return response()->json(['data' => $sales]);
    }

    public function restore($id)
    {
        if (!auth()->user()->canRestore()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk restore data.'], 403);
        }

        $sale = Sale::onlyTrashed()->find($id);
        if (!$sale) {
            return response()->json(['message' => 'Penjualan tidak ditemukan.'], 404);
        }

        $sale->restore();

        return response()->json(['message' => 'Penjualan berhasil di-restore.']);
    }

    public function forceDelete($id)
    {
        if (!auth()->user()->canDelete()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menghapus data.'], 403);
        }

        $sale = Sale::onlyTrashed()->find($id);
        if (!$sale) {
            return response()->json(['message' => 'Penjualan tidak ditemukan.'], 404);
        }

        $sale->forceDelete();

        return response()->json(['message' => 'Penjualan berhasil dihapus permanen.']);
    }
}
