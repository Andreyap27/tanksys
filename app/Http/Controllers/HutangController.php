<?php

namespace App\Http\Controllers;

use App\Models\Hutang;
use Illuminate\Http\Request;

class HutangController extends Controller
{
    public function index()
    {
        return view('hutang.index');
    }

    public function data()
    {
        $rows = Hutang::orderBy('date', 'desc')->get()->map(fn($h) => [
            'id'          => $h->id,
            'date'        => $this->resolveDate($h)->translatedFormat('d M Y H:i'),
            'date_raw'    => $h->date->format('Y-m-d H:i:s'),
            'nama'        => $h->nama,
            'description' => $h->description,
            'nominal'     => number_format($h->nominal, 0, ',', '.'),
            'nominal_raw' => (float) $h->nominal,
            'note'        => $h->note ?? '-',
            'status'      => $h->status,
            'created_by'  => $h->creator?->name ?? '-',
        ]);

        return response()->json(['data' => $rows]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'date'        => 'required|date',
            'nama'        => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'nominal'     => 'required|numeric|min:0',
            'note'        => 'nullable|string',
        ]);

        Hutang::create([
            'date'        => $request->date . ' ' . now()->format('H:i:s'),
            'nama'        => $request->nama,
            'description' => $request->description,
            'nominal'     => $request->nominal,
            'note'        => $request->note,
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'created_by'  => auth()->id(),
        ]);

        return response()->json(['message' => 'Hutang berhasil disimpan.']);
    }

    public function update(Request $request, Hutang $hutang)
    {
        $request->validate([
            'date'        => 'required|date',
            'nama'        => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'nominal'     => 'required|numeric|min:0',
            'note'        => 'nullable|string',
        ]);

        $hutang->update([
            'date'        => $request->date . ' ' . now()->format('H:i:s'),
            'nama'        => $request->nama,
            'description' => $request->description,
            'nominal'     => $request->nominal,
            'note'        => $request->note,
            'status'      => 'pending',
            'approved_by' => null,
            'approved_at' => null,
        ]);

        return response()->json(['message' => 'Hutang berhasil diupdate.']);
    }

    public function approve(Hutang $hutang)
    {
        if (!auth()->user()->canApprove()) {
            return response()->json(['message' => 'Tidak memiliki akses.'], 403);
        }

        if ($hutang->status !== 'pending') {
            return response()->json(['message' => 'Hanya hutang berstatus pending yang dapat disetujui.'], 422);
        }

        $hutang->update([
            'date'        => $hutang->date->toDateString() . ' ' . now()->format('H:i:s'),
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return response()->json(['message' => 'Hutang berhasil disetujui.']);
    }

    public function reject(Hutang $hutang)
    {
        if (!auth()->user()->canApprove()) {
            return response()->json(['message' => 'Tidak memiliki akses.'], 403);
        }

        if ($hutang->status !== 'pending') {
            return response()->json(['message' => 'Hanya hutang berstatus pending yang dapat ditolak.'], 422);
        }

        $hutang->update([
            'status'      => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return response()->json(['message' => 'Hutang berhasil ditolak.']);
    }

    public function paid(Hutang $hutang)
    {
        if (!auth()->user()->canApprove()) {
            return response()->json(['message' => 'Tidak memiliki akses.'], 403);
        }

        if ($hutang->status !== 'approved') {
            return response()->json(['message' => 'Hanya hutang berstatus approved yang dapat ditandai paid.'], 422);
        }

        $hutang->update(['status' => 'paid']);

        return response()->json(['message' => 'Hutang berhasil ditandai sebagai paid.']);
    }

    public function destroy(Hutang $hutang)
    {
        if (!auth()->user()->canDelete()) {
            return response()->json(['message' => 'Tidak memiliki akses.'], 403);
        }

        $hutang->delete();
        return response()->json(['message' => 'Hutang berhasil dihapus.']);
    }

    public function trash()
    {
        return view('hutang.trash');
    }

    public function trashData()
    {
        $rows = Hutang::onlyTrashed()->orderBy('date', 'desc')->get()->map(fn($h) => [
            'id'          => $h->id,
            'date'        => $this->resolveDate($h)->translatedFormat('d M Y H:i'),
            'nama'        => $h->nama,
            'description' => $h->description,
            'nominal'     => number_format($h->nominal, 0, ',', '.'),
            'note'        => $h->note ?? '-',
            'status'      => $h->status,
            'deleted_at'  => $h->deleted_at->translatedFormat('d M Y H:i'),
            'deleted_by'  => $h->deleter?->name ?? 'N/A',
        ]);

        return response()->json(['data' => $rows]);
    }

    public function restore($id)
    {
        if (!auth()->user()->canRestore()) {
            return response()->json(['message' => 'Tidak memiliki akses.'], 403);
        }

        $hutang = Hutang::onlyTrashed()->findOrFail($id);
        $hutang->restore();

        return response()->json(['message' => 'Hutang berhasil di-restore.']);
    }

    public function forceDelete($id)
    {
        if (!auth()->user()->canDelete()) {
            return response()->json(['message' => 'Tidak memiliki akses.'], 403);
        }

        Hutang::onlyTrashed()->findOrFail($id)->forceDelete();
        return response()->json(['message' => 'Hutang berhasil dihapus permanen.']);
    }
}
