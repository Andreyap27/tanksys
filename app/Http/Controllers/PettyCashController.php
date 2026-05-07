<?php

namespace App\Http\Controllers;

use App\Models\PettyCash;
use Illuminate\Http\Request;

class PettyCashController extends Controller
{
    public function index()
    {
        if (auth()->user()->role !== 'Super Admin') abort(403);
        return view('petty-cash.index');
    }

    public function list()
    {
        return response()->json(PettyCash::orderBy('name')->get(['id', 'name']));
    }

    public function data()
    {
        if (auth()->user()->role !== 'Super Admin') abort(403);
        $items = PettyCash::orderBy('name')->get()->map(fn($p) => [
            'id'   => $p->id,
            'name' => $p->name,
        ]);
        return response()->json(['data' => $items]);
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'Super Admin') abort(403);
        $request->validate(['name' => 'required|string|max:255|unique:petty_cashes,name']);
        PettyCash::create(['name' => $request->name]);
        return response()->json(['message' => 'Petty cash berhasil ditambahkan.']);
    }

    public function update(Request $request, PettyCash $pettyCash)
    {
        if (auth()->user()->role !== 'Super Admin') abort(403);
        $request->validate(['name' => 'required|string|max:255|unique:petty_cashes,name,' . $pettyCash->id]);
        $pettyCash->update(['name' => $request->name]);
        return response()->json(['message' => 'Petty cash berhasil diupdate.']);
    }

    public function destroy(PettyCash $pettyCash)
    {
        if (auth()->user()->role !== 'Super Admin') abort(403);
        $pettyCash->delete();
        return response()->json(['message' => 'Petty cash berhasil dihapus.']);
    }
}
