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
        $date   = now()->format('Ymd');
        $prefix = "INV-{$date}-";

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
            'date'           => $sale->date->format('Y-m-d H:i:s'),
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
            $sales = Sale::with('customer')->orderBy('date', 'desc')->get()->map(fn($s) => [
                'id'             => $s->id,
                'date'           => $this->resolveDate($s)->translatedFormat('d M Y H:i'),
                'date_raw'       => $s->date->format('Y-m-d H:i:s'),
                'invoice_number' => $s->invoice_number,
                'do_number'      => $s->do_number ?? '',
                'customer_id'    => $s->customer_id,
                'customer_name'  => $s->customer->name,
                'description'    => $s->description ?? '-',
                'quantity'       => number_format($s->quantity, 0, ',', '.'),
                'quantity_raw'   => $s->quantity,
                'extra'          => number_format($s->extra, 0, ',', '.'),
                'extra_raw'      => $s->extra,
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
            'date'           => 'required|date',
            'invoice_number' => 'required|string|unique:sales,invoice_number,NULL,id,deleted_at,NULL',
            'do_number'      => 'nullable|string|max:5',
            'customer_id'    => 'required|exists:customers,id',
            'description'    => 'nullable|string|max:255',
            'warna'          => 'nullable|string',
            'quantity'       => 'required|numeric|min:0.01',
            'extra'          => 'nullable|numeric|min:0',
            'price'          => 'required|numeric|min:0',
            'noted'          => 'nullable|string',
        ]);

        $extra    = (float) ($request->extra ?? 0);
        $totalOut = (float) $request->quantity + $extra;
        $warna    = $request->warna ?: null;
        $currentBalance = Stock::currentBalance(null, $warna);
        if ($totalOut > $currentBalance) {
            $label = $warna ? " BBM " . ucfirst($warna) : '';
            return response()->json([
                'message' => 'Stok' . $label . ' tidak mencukupi. Stok saat ini: ' . number_format($currentBalance, 2, ',', '.') . ' L',
            ], 422);
        }

        DB::transaction(function () use ($request, $extra) {
            $sale = Sale::create([
                'date'           => $request->date . ' ' . now()->format('H:i:s'),
                'invoice_number' => $request->invoice_number,
                'do_number'      => $request->do_number ?: null,
                'customer_id'    => $request->customer_id,
                'description'    => $request->description,
                'warna'          => $request->warna ?: null,
                'quantity'       => $request->quantity,
                'extra'          => $extra,
                'short'          => 0,
                'price'          => $request->price,
                'amount'         => $request->quantity * $request->price,
                'noted'          => $request->noted,
                'created_by'     => auth()->id(),
                'status'         => 'approved',
                'approved_by'    => auth()->id(),
                'approved_at'    => now(),
            ]);

            Stock::create([
                'kapal_id'       => null,
                'date'           => $sale->date,
                'type'           => 'sale',
                'reference_id'   => $sale->id,
                'reference_type' => Sale::class,
                'party'          => $sale->customer->name,
                'warna'          => $sale->warna,
                'qty_in'         => 0,
                'qty_out'        => (float) $sale->quantity + (float) $sale->extra,
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
            'date'           => 'required|date',
            'invoice_number' => 'required|string|unique:sales,invoice_number,' . $sale->id . ',id,deleted_at,NULL',
            'do_number'      => 'nullable|string|max:5',
            'customer_id'    => 'required|exists:customers,id',
            'description'    => 'nullable|string|max:255',
            'warna'          => 'nullable|string',
            'quantity'       => 'required|numeric|min:0.01',
            'extra'          => 'nullable|numeric|min:0',
            'price'          => 'required|numeric|min:0',
            'noted'          => 'nullable|string',
        ]);

        $extra    = (float) ($request->extra ?? 0);
        $totalOut = (float) $request->quantity + $extra;
        $warna    = $request->warna ?: null;
        $oldQtyOut = in_array($sale->status, ['approved', 'paid']) && ($sale->warna ?: null) === $warna
            ? ((float) $sale->quantity + (float) $sale->extra)
            : 0;
        $effectiveBalance = Stock::currentBalance(null, $warna) + $oldQtyOut;
        if ($totalOut > $effectiveBalance) {
            $label = $warna ? " BBM " . ucfirst($warna) : '';
            return response()->json([
                'message' => 'Stok' . $label . ' tidak mencukupi. Stok saat ini: ' . number_format($effectiveBalance, 2, ',', '.') . ' L',
            ], 422);
        }

        DB::transaction(function () use ($request, $sale, $extra) {
            if (in_array($sale->status, ['approved', 'paid'])) {
                $sale->stock?->delete();
            }

            $sale->update([
                'date'           => $request->date . ' ' . now()->format('H:i:s'),
                'invoice_number' => $request->invoice_number,
                'do_number'      => $request->do_number ?: null,
                'customer_id'    => $request->customer_id,
                'description'    => $request->description,
                'warna'          => $request->warna ?: null,
                'quantity'       => $request->quantity,
                'extra'          => $extra,
                'short'          => 0,
                'price'          => $request->price,
                'amount'         => $request->quantity * $request->price,
                'noted'          => $request->noted,
                'status'         => 'approved',
                'approved_by'    => auth()->id(),
                'approved_at'    => now(),
            ]);

            Stock::create([
                'kapal_id'       => null,
                'date'           => $sale->date,
                'type'           => 'sale',
                'reference_id'   => $sale->id,
                'reference_type' => Sale::class,
                'party'          => $sale->customer->name,
                'warna'          => $sale->warna,
                'qty_in'         => 0,
                'qty_out'        => (float) $sale->quantity + (float) $sale->extra,
            ]);
        });

        return response()->json(['message' => 'Penjualan berhasil diupdate.']);
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

        $totalOut = (float) $sale->quantity + (float) $sale->extra;
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
                'date'        => $sale->date->toDateString() . ' ' . now()->format('H:i:s'),
                'status'      => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            Stock::create([
                'kapal_id'       => null,
                'date'           => $sale->date,
                'type'           => 'sale',
                'reference_id'   => $sale->id,
                'reference_type' => Sale::class,
                'party'          => $sale->customer->name,
                'warna'          => $sale->warna,
                'qty_in'         => 0,
                'qty_out'        => (float) $sale->quantity + (float) $sale->extra,
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
        $sales = Sale::onlyTrashed()->with(['creator', 'customer', 'deleter'])->orderBy('date', 'desc')->get()->map(fn($s) => [
            'id'              => $s->id,
            'date'            => $this->resolveDate($s)->translatedFormat('d M Y H:i'),
            'date_raw'        => $s->date->format('Y-m-d H:i:s'),
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
