<?php

namespace App\Http\Controllers;

use App\Models\Mobil;
use Illuminate\Http\Request;

class MobilController extends Controller
{
    public function index()
    {
        if (auth()->user()->role !== 'Super Admin') {
            abort(403);
        }
        return view('mobil.index');
    }

    public function list()
    {
        return response()->json(Mobil::orderBy('name')->get(['id', 'name']));
    }

    public function data()
    {
        if (auth()->user()->role !== 'Super Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $mobils = Mobil::orderBy('created_at')->get()->map(fn($m) => [
            'id'         => $m->id,
            'name'       => $m->name,
            'plat_nomer' => $m->plat_nomer,
        ]);
        return response()->json(['data' => $mobils]);
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'Super Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name'       => 'required|string|max:255',
            'plat_nomer' => 'required|string|max:20',
        ]);

        Mobil::create($request->only(['name', 'plat_nomer']));

        return response()->json(['message' => 'Mobil berhasil ditambahkan.']);
    }

    public function update(Request $request, Mobil $mobil)
    {
        if (auth()->user()->role !== 'Super Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name'       => 'required|string|max:255',
            'plat_nomer' => 'required|string|max:20',
        ]);

        $mobil->update($request->only(['name', 'plat_nomer']));

        return response()->json(['message' => 'Mobil berhasil diupdate.']);
    }

    public function destroy(Mobil $mobil)
    {
        if (auth()->user()->role !== 'Super Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $mobil->delete();
        return response()->json(['message' => 'Mobil berhasil dihapus.']);
    }
}
