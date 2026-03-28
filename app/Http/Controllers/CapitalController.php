<?php

namespace App\Http\Controllers;

use App\Models\Capital;
use Illuminate\Http\Request;

class CapitalController extends Controller
{
    public function index()
    {
        return view('capital.index');
    }

    public function data()
    {
        $capitals = Capital::with('creator')->latest()->get()->map(fn($c) => [
            'id'          => $c->id,
            'date'        => $c->date->translatedFormat('d M Y'),
            'date_raw'    => $c->date->format('Y-m-d'),
            'name'        => $c->name,
            'nominal'     => number_format($c->nominal, 0, ',', '.'),
            'nominal_raw' => $c->nominal,
            'note'        => $c->note ?? '',
        ]);
        return response()->json(['data' => $capitals]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'date'    => 'required|date',
            'name'    => 'required|string|max:255',
            'nominal' => 'required|numeric|min:0',
            'note'    => 'nullable|string',
        ]);

        Capital::create([
            'date'       => $request->date,
            'name'       => $request->name,
            'nominal'    => $request->nominal,
            'note'       => $request->note,
            'created_by' => auth()->id(),
        ]);

        return response()->json(['message' => 'Modal berhasil disimpan.']);
    }

    public function update(Request $request, Capital $capital)
    {
        $request->validate([
            'date'    => 'required|date',
            'name'    => 'required|string|max:255',
            'nominal' => 'required|numeric|min:0',
            'note'    => 'nullable|string',
        ]);

        $capital->update($request->only(['date', 'name', 'nominal', 'note']));

        return response()->json(['message' => 'Modal berhasil diupdate.']);
    }

    public function destroy(Capital $capital)
    {
        $capital->delete();
        return response()->json(['message' => 'Modal berhasil dihapus.']);
    }
}
