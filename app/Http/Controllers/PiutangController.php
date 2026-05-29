<?php

namespace App\Http\Controllers;

use App\Models\Piutang;
use Illuminate\Http\Request;

class PiutangController extends Controller
{
    public function index()
    {
        return view('piutang.index');
    }

    public function data()
    {
        $rows = Piutang::orderBy('date', 'desc')->get()->map(fn($p) => [
            'id'          => $p->id,
            'date'        => $this->resolveDate($p)->translatedFormat('d M Y H:i'),
            'date_raw'    => $p->date->format('Y-m-d H:i:s'),
            'nama'        => $p->nama,
            'description' => $p->description,
            'nominal'     => number_format($p->nominal, 0, ',', '.'),
            'nominal_raw' => (float) $p->nominal,
            'note'        => $p->note ?? '-',
            'status'      => $p->status,
            'created_by'  => $p->creator?->name ?? '-',
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

        Piutang::create([
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

        return response()->json(['message' => 'Piutang berhasil disimpan.']);
    }

    public function update(Request $request, Piutang $piutang)
    {
        $request->validate([
            'date'        => 'required|date',
            'nama'        => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'nominal'     => 'required|numeric|min:0',
            'note'        => 'nullable|string',
        ]);

        $piutang->update([
            'date'        => $request->date . ' ' . now()->format('H:i:s'),
            'nama'        => $request->nama,
            'description' => $request->description,
            'nominal'     => $request->nominal,
            'note'        => $request->note,
            'status'      => 'pending',
            'approved_by' => null,
            'approved_at' => null,
        ]);

        return response()->json(['message' => 'Piutang berhasil diupdate.']);
    }

    public function approve(Piutang $piutang)
    {
        if (!auth()->user()->canApprove()) {
            return response()->json(['message' => 'Tidak memiliki akses.'], 403);
        }

        if ($piutang->status !== 'pending') {
            return response()->json(['message' => 'Hanya piutang berstatus pending yang dapat disetujui.'], 422);
        }

        $piutang->update([
            'date'        => $piutang->date->toDateString() . ' ' . now()->format('H:i:s'),
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return response()->json(['message' => 'Piutang berhasil disetujui.']);
    }

    public function reject(Piutang $piutang)
    {
        if (!auth()->user()->canApprove()) {
            return response()->json(['message' => 'Tidak memiliki akses.'], 403);
        }

        if ($piutang->status !== 'pending') {
            return response()->json(['message' => 'Hanya piutang berstatus pending yang dapat ditolak.'], 422);
        }

        $piutang->update([
            'status'      => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return response()->json(['message' => 'Piutang berhasil ditolak.']);
    }

    public function paid(Piutang $piutang)
    {
        if (!auth()->user()->canApprove()) {
            return response()->json(['message' => 'Tidak memiliki akses.'], 403);
        }

        if ($piutang->status !== 'approved') {
            return response()->json(['message' => 'Hanya piutang berstatus approved yang dapat ditandai paid.'], 422);
        }

        $piutang->update(['status' => 'paid']);

        return response()->json(['message' => 'Piutang berhasil ditandai sebagai paid.']);
    }

    public function destroy(Piutang $piutang)
    {
        if (!auth()->user()->canDelete()) {
            return response()->json(['message' => 'Tidak memiliki akses.'], 403);
        }

        $piutang->delete();
        return response()->json(['message' => 'Piutang berhasil dihapus.']);
    }

    public function trash()
    {
        return view('piutang.trash');
    }

    public function trashData()
    {
        $rows = Piutang::onlyTrashed()->orderBy('date', 'desc')->get()->map(fn($p) => [
            'id'          => $p->id,
            'date'        => $this->resolveDate($p)->translatedFormat('d M Y H:i'),
            'nama'        => $p->nama,
            'description' => $p->description,
            'nominal'     => number_format($p->nominal, 0, ',', '.'),
            'note'        => $p->note ?? '-',
            'status'      => $p->status,
            'deleted_at'  => $p->deleted_at->translatedFormat('d M Y H:i'),
            'deleted_by'  => $p->deleter?->name ?? 'N/A',
        ]);

        return response()->json(['data' => $rows]);
    }

    public function restore($id)
    {
        if (!auth()->user()->canRestore()) {
            return response()->json(['message' => 'Tidak memiliki akses.'], 403);
        }

        $piutang = Piutang::onlyTrashed()->findOrFail($id);
        $piutang->restore();

        return response()->json(['message' => 'Piutang berhasil di-restore.']);
    }

    public function forceDelete($id)
    {
        if (!auth()->user()->canDelete()) {
            return response()->json(['message' => 'Tidak memiliki akses.'], 403);
        }

        Piutang::onlyTrashed()->findOrFail($id)->forceDelete();
        return response()->json(['message' => 'Piutang berhasil dihapus permanen.']);
    }
}
