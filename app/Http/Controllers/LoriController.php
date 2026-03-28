<?php

namespace App\Http\Controllers;

use App\Models\Lori;
use App\Models\Customer;
use Illuminate\Http\Request;

class LoriController extends Controller
{
    public function index()
    {
        return view('lori.index');
    }

    public function data()
    {
        $loris = Lori::with('customer')->latest()->get()->map(fn($l) => [
            'id'            => $l->id,
            'date'          => $l->date->translatedFormat('d M Y'),
            'date_raw'      => $l->date->format('Y-m-d'),
            'customer_name' => $l->customer->name,
            'customer_id'   => $l->customer_id,
            'from'          => $l->from,
            'to'            => $l->to,
            'price'         => number_format($l->price, 0, ',', '.'),
            'price_raw'     => $l->price,
        ]);
        return response()->json(['data' => $loris]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'date'        => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'from'        => 'required|string|max:255',
            'to'          => 'required|string|max:255',
            'price'       => 'required|numeric|min:0',
        ]);

        Lori::create([
            'date'        => $request->date,
            'customer_id' => $request->customer_id,
            'from'        => $request->from,
            'to'          => $request->to,
            'price'       => $request->price,
            'created_by'  => auth()->id(),
        ]);

        return response()->json(['message' => 'Data mobil tangki berhasil disimpan.']);
    }

    public function update(Request $request, Lori $lori)
    {
        $request->validate([
            'date'        => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'from'        => 'required|string|max:255',
            'to'          => 'required|string|max:255',
            'price'       => 'required|numeric|min:0',
        ]);

        $lori->update($request->only(['date', 'customer_id', 'from', 'to', 'price']));

        return response()->json(['message' => 'Data mobil tangki berhasil diupdate.']);
    }

    public function destroy(Lori $lori)
    {
        $lori->delete();
        return response()->json(['message' => 'Data mobil tangki berhasil dihapus.']);
    }
}
