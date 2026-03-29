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
        return view('sales.index', [
            'canApprove' => auth()->user()->canApprove(),
            'canManage'  => auth()->user()->canManage(),
            'canDelete'  => auth()->user()->canDelete(),
        ]);
    }

    public function nextInvoice()
    {
        $date   = now()->format('Ymd');
        $prefix = "INV-{$date}-";
        $latest = Sale::where('invoice_number', 'like', $prefix . '%')
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
        $sales = Sale::with('customer')->latest()->get()->map(fn($s) => [
            'id'             => $s->id,
            'date'           => $s->date->translatedFormat('d M Y'),
            'date_raw'       => $s->date->format('Y-m-d'),
            'invoice_number' => $s->invoice_number,
            'customer_id'    => $s->customer_id,
            'customer_name'  => $s->customer->name,
            'description'    => $s->description ?? '-',
            'quantity'       => number_format($s->quantity, 2),
            'quantity_raw'   => $s->quantity,
            'price'          => number_format($s->price, 0, ',', '.'),
            'price_raw'      => $s->price,
            'amount'         => number_format($s->amount, 0, ',', '.'),
            'noted'          => $s->noted ?? '-',
            'status'         => $s->status,
        ]);
        return response()->json(['data' => $sales]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'date'           => 'required|date',
            'invoice_number' => 'required|string|unique:sales',
            'customer_id'    => 'required|exists:customers,id',
            'description'    => 'nullable|string|max:255',
            'quantity'       => 'required|numeric|min:0.01',
            'price'          => 'required|numeric|min:0',
            'noted'          => 'nullable|string',
        ]);

        Sale::create([
            'date'           => $request->date,
            'invoice_number' => $request->invoice_number,
            'customer_id'    => $request->customer_id,
            'description'    => $request->description,
            'quantity'       => $request->quantity,
            'price'          => $request->price,
            'amount'         => $request->quantity * $request->price,
            'noted'          => $request->noted,
            'created_by'     => auth()->id(),
            'status'         => 'pending',
        ]);

        Notification::sendToApprovers('approval', 'Penjualan Baru',
            auth()->user()->name . ' menambahkan penjualan ' . $request->invoice_number . ' menunggu persetujuan.',
            route('sales.index'));

        $message = 'Penjualan berhasil disimpan dan menunggu persetujuan SPV.';

        return response()->json(['message' => $message]);
    }

    public function update(Request $request, Sale $sale)
    {
        if (!auth()->user()->canManage()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk mengedit.'], 403);
        }

        $request->validate([
            'date'           => 'required|date',
            'invoice_number' => 'required|string|unique:sales,invoice_number,' . $sale->id,
            'customer_id'    => 'required|exists:customers,id',
            'description'    => 'nullable|string|max:255',
            'quantity'       => 'required|numeric|min:0.01',
            'price'          => 'required|numeric|min:0',
            'noted'          => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $sale) {
            if ($sale->status === 'approved' && $sale->stock) {
                $sale->stock->delete();
            }

            $sale->update([
                'date'           => $request->date,
                'invoice_number' => $request->invoice_number,
                'customer_id'    => $request->customer_id,
                'description'    => $request->description,
                'quantity'       => $request->quantity,
                'price'          => $request->price,
                'amount'         => $request->quantity * $request->price,
                'noted'          => $request->noted,
                'status'         => 'pending',
                'approved_by'    => null,
                'approved_at'    => null,
            ]);
        });

        return response()->json(['message' => 'Penjualan berhasil diupdate dan menunggu persetujuan ulang.']);
    }

    public function destroy(Sale $sale)
    {
        if (!auth()->user()->canDelete()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menghapus data.'], 403);
        }

        DB::transaction(function () use ($sale) {
            if ($sale->status === 'approved') {
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

        $currentBalance = Stock::currentBalance();
        if ($sale->quantity > $currentBalance) {
            return response()->json([
                'message' => 'Stok tidak mencukupi untuk menyetujui penjualan ini. Stok saat ini: ' . number_format($currentBalance, 2) . ' L',
            ], 422);
        }

        DB::transaction(function () use ($sale) {
            $sale->update([
                'status'      => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            Stock::create([
                'date'           => $sale->date,
                'type'           => 'sale',
                'reference_id'   => $sale->id,
                'reference_type' => Sale::class,
                'party'          => $sale->customer->name,
                'qty_in'         => 0,
                'qty_out'        => $sale->quantity,
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

    public function invoice(Sale $sale)
    {
        $sale->load('customer', 'creator');
        return view('sales.invoice', compact('sale'));
    }
}
