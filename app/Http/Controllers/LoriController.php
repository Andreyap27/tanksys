<?php

namespace App\Http\Controllers;

use App\Models\Lori;
use App\Models\Customer;
use Illuminate\Http\Request;

class LoriController extends Controller
{
    public function index()
    {
        return view('lori.index', [
            'canManage' => auth()->user()->canManage(),
            'canDelete' => auth()->user()->canDelete(),
        ]);
    }

    public function data()
    {
        $mobilId = request('mobil_id');
        $query   = Lori::with('customer')->orderBy('date', 'desc');
        if ($mobilId) $query->where('mobil_id', $mobilId);
        $loris = $query->get()->map(fn($l) => [
            'id'            => $l->id,
            'mobil_id'      => $l->mobil_id,
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
            'mobil_id'    => 'nullable|exists:mobils,id',
            'date'        => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'from'        => 'required|string|max:255',
            'to'          => 'required|string|max:255',
            'price'       => 'required|numeric|min:0',
        ]);

        Lori::create([
            'mobil_id'    => $request->mobil_id ?: null,
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
            'mobil_id'    => 'nullable|exists:mobils,id',
            'date'        => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'from'        => 'required|string|max:255',
            'to'          => 'required|string|max:255',
            'price'       => 'required|numeric|min:0',
        ]);

        $lori->update(array_merge(
            $request->only(['date', 'customer_id', 'from', 'to', 'price']),
            ['mobil_id' => $request->mobil_id ?: null]
        ));

        return response()->json(['message' => 'Data mobil tangki berhasil diupdate.']);
    }

    public function destroy(Lori $lori)
    {
        if (!auth()->user()->canDelete()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menghapus data.'], 403);
        }

        $lori->delete();
        return response()->json(['message' => 'Data mobil tangki berhasil dihapus.']);
    }

    public function trash()
    {
        return view('lori.trash', [
            'canRestore' => auth()->user()->canManage(),
            'canDelete'  => auth()->user()->canDelete(),
        ]);
    }

    public function trashData()
    {
        $mobilId = request('mobil_id');
        $query   = Lori::onlyTrashed()->with('creator', 'customer')->orderBy('date', 'desc');
        if ($mobilId) $query->where('mobil_id', $mobilId);
        $loris   = $query->get()->map(fn($l) => [
            'id'         => $l->id,
            'mobil_id'   => $l->mobil_id,
            'date'       => $l->date->translatedFormat('d M Y'),
            'date_raw'   => $l->date->format('Y-m-d'),
            'customer'   => $l->customer->name ?? '-',
            'from'       => $l->from,
            'to'         => $l->to,
            'price'      => number_format($l->price, 0, ',', '.'),
            'price_raw'  => $l->price,
            'deleted_at' => $l->deleted_at->translatedFormat('d M Y H:i'),
        ]);
        return response()->json(['data' => $loris]);
    }

    public function restore($id)
    {
        if (!auth()->user()->canManage()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk restore data.'], 403);
        }

        $lori = Lori::onlyTrashed()->find($id);
        if (!$lori) {
            return response()->json(['message' => 'Data mobil tangki tidak ditemukan.'], 404);
        }

        $lori->restore();

        return response()->json(['message' => 'Data mobil tangki berhasil di-restore.']);
    }

    public function forceDelete($id)
    {
        if (!auth()->user()->canDelete()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menghapus data.'], 403);
        }

        $lori = Lori::onlyTrashed()->find($id);
        if (!$lori) {
            return response()->json(['message' => 'Data mobil tangki tidak ditemukan.'], 404);
        }

        $lori->forceDelete();

        return response()->json(['message' => 'Data mobil tangki berhasil dihapus permanen.']);
    }
}
