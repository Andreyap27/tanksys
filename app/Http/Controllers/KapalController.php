<?php

namespace App\Http\Controllers;

use App\Models\Kapal;
use Illuminate\Http\Request;

class KapalController extends Controller
{
    public function index()
    {
        if (auth()->user()->role !== 'Super Admin') {
            abort(403);
        }
        return view('kapal.index');
    }

    public function list()
    {
        return response()->json(Kapal::orderBy('code')->get(['id', 'code', 'name']));
    }

    public function data()
    {
        if (auth()->user()->role !== 'Super Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $kapals = Kapal::orderBy('code')->get()->map(fn($k) => [
            'id'   => $k->id,
            'code' => $k->code,
            'name' => $k->name,
        ]);
        return response()->json(['data' => $kapals]);
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'Super Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Kapal::create([
            'code' => Kapal::generateNextCode(),
            'name' => $request->name,
        ]);

        return response()->json(['message' => 'Kapal berhasil ditambahkan.']);
    }

    public function update(Request $request, Kapal $kapal)
    {
        if (auth()->user()->role !== 'Super Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $kapal->update(['name' => $request->name]);

        return response()->json(['message' => 'Kapal berhasil diupdate.']);
    }

    public function destroy(Kapal $kapal)
    {
        if (auth()->user()->role !== 'Super Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $kapal->delete();
        return response()->json(['message' => 'Kapal berhasil dihapus.']);
    }
}
