<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Customer;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index()
    {
        return view('dashboard.sales.index');
    }

    public function data()
    {
        $sales = Sale::with('customer')->latest()->get()->map(fn($s) => [
            'id'             => $s->id,
            'date'           => $s->date->format('d/m/Y'),
            'invoice_number' => $s->invoice_number,
            'customer'       => $s->customer->name,
            'description'    => $s->description ?? '-',
            'quantity'       => number_format($s->quantity, 2),
            'price'          => number_format($s->price, 0, ',', '.'),
            'amount'         => number_format($s->amount, 0, ',', '.'),
            'noted'          => $s->noted ?? '-',
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

        $currentBalance = Stock::currentBalance();
        if ($request->quantity > $currentBalance) {
            return response()->json([
                'message' => 'Stok tidak mencukupi. Stok saat ini: ' . number_format($currentBalance, 2) . ' L',
                'errors'  => ['quantity' => ['Stok tidak mencukupi.']],
            ], 422);
        }

        DB::transaction(function () use ($request) {
            $sale = Sale::create([
                'date'           => $request->date,
                'invoice_number' => $request->invoice_number,
                'customer_id'    => $request->customer_id,
                'description'    => $request->description,
                'quantity'       => $request->quantity,
                'price'          => $request->price,
                'amount'         => $request->quantity * $request->price,
                'noted'          => $request->noted,
                'created_by'     => auth()->id(),
            ]);

            $customer = Customer::find($request->customer_id);
            Stock::create([
                'date'           => $request->date,
                'type'           => 'sale',
                'reference_id'   => $sale->id,
                'reference_type' => Sale::class,
                'party'          => $customer->name,
                'qty_in'         => 0,
                'qty_out'        => $request->quantity,
                'balance'        => Stock::currentBalance() - $request->quantity,
            ]);
        });

        return response()->json(['message' => 'Penjualan berhasil disimpan.']);
    }

    public function update(Request $request, Sale $sale)
    {
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
            $diff = $request->quantity - $sale->quantity;

            $sale->update([
                'date'           => $request->date,
                'invoice_number' => $request->invoice_number,
                'customer_id'    => $request->customer_id,
                'description'    => $request->description,
                'quantity'       => $request->quantity,
                'price'          => $request->price,
                'amount'         => $request->quantity * $request->price,
                'noted'          => $request->noted,
            ]);

            $stock = $sale->stock;
            if ($stock) {
                $customer = Customer::find($request->customer_id);
                $stock->update([
                    'date'    => $request->date,
                    'party'   => $customer->name,
                    'qty_out' => $request->quantity,
                    'balance' => $stock->balance - $diff,
                ]);
            }
        });

        return response()->json(['message' => 'Penjualan berhasil diupdate.']);
    }

    public function destroy(Sale $sale)
    {
        DB::transaction(function () use ($sale) {
            $sale->stock?->delete();
            $sale->delete();
        });

        return response()->json(['message' => 'Penjualan berhasil dihapus.']);
    }

    public function invoice(Sale $sale)
    {
        $sale->load('customer', 'creator');
        return view('dashboard.sales.invoice', compact('sale'));
    }
}
