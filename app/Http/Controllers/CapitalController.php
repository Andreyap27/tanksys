<?php

namespace App\Http\Controllers;

use App\Models\Capital;
use App\Models\Notification;
use Illuminate\Http\Request;

class CapitalController extends Controller
{
    public function index()
    {
        return view('capital.index');
    }

    public function data()
    {
        $kapalId  = request('kapal_id');
        $query    = Capital::with('creator')->orderBy('date', 'desc');
        if ($kapalId) $query->where('kapal_id', $kapalId);
        $capitals = $query->get()->map(fn($c) => [
            'id'          => $c->id,
            'kapal_id'    => $c->kapal_id,
            'date'        => $c->date->translatedFormat('d M Y'),
            'date_raw'    => $c->date->format('Y-m-d'),
            'name'        => $c->name,
            'nominal'     => number_format($c->nominal, 0, ',', '.'),
            'nominal_raw' => $c->nominal,
            'note'        => $c->note ?? '',
            'status'      => $c->status,
        ]);
        return response()->json(['data' => $capitals]);
    }

    public function summary()
    {
        $kapalId = request('kapal_id');
        $query   = Capital::where('status', 'approved');
        if ($kapalId) $query->where('kapal_id', $kapalId);

        $totals = $query->selectRaw('name, SUM(nominal) as total')
            ->groupBy('name')
            ->pluck('total', 'name');

        return response()->json([
            'pt_aldive'   => (float) ($totals['PT ALDIVE'] ?? 0),
            'rudi_hartono'=> (float) ($totals['RUDI HARTONO'] ?? 0),
            'total'       => (float) $totals->sum(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kapal_id' => 'nullable|exists:kapals,id',
            'date'     => 'required|date',
            'name'     => 'required|in:PT ALDIVE,RUDI HARTONO',
            'nominal'  => 'required|numeric|min:0',
            'note'     => 'nullable|string',
        ]);

        Capital::create([
            'kapal_id'   => $request->kapal_id ?: null,
            'date'       => $request->date,
            'name'       => $request->name,
            'nominal'    => $request->nominal,
            'note'       => $request->note,
            'status'     => 'approved',
            'approved_by'=> auth()->id(),
            'approved_at'=> now(),
            'created_by' => auth()->id(),
        ]);

        return response()->json(['message' => 'Modal berhasil disimpan.']);
    }

    public function update(Request $request, Capital $capital)
    {
        if (!auth()->user()->canManage()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk mengedit.'], 403);
        }

        $request->validate([
            'kapal_id' => 'nullable|exists:kapals,id',
            'date'     => 'required|date',
            'name'     => 'required|in:PT ALDIVE,RUDI HARTONO',
            'nominal'  => 'required|numeric|min:0',
            'note'     => 'nullable|string',
        ]);

        $capital->update(array_merge(
            $request->only(['kapal_id', 'date', 'name', 'nominal', 'note']),
            ['status' => 'pending', 'approved_by' => null, 'approved_at' => null],
        ));

        Notification::sendToApprovers('approval', 'Modal Diupdate',
            auth()->user()->name . ' mengubah modal "' . $request->name . '" dan menunggu persetujuan.',
            route('capital.index'));

        return response()->json(['message' => 'Modal berhasil diupdate dan menunggu persetujuan ulang.']);
    }

    public function destroy(Capital $capital)
    {
        if (!auth()->user()->canDelete()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menghapus data.'], 403);
        }

        $capital->delete();
        return response()->json(['message' => 'Modal berhasil dihapus.']);
    }

    public function approve(Capital $capital)
    {
        if (!auth()->user()->canApprove()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menyetujui.'], 403);
        }

        $capital->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        Notification::send([$capital->created_by], 'info', 'Modal Disetujui',
            'Modal "' . $capital->name . '" telah disetujui.',
            route('capital.index'));

        return response()->json(['message' => 'Modal berhasil disetujui.']);
    }

    public function reject(Capital $capital)
    {
        if (!auth()->user()->canApprove()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menolak.'], 403);
        }

        $capital->update([
            'status'      => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        Notification::send([$capital->created_by], 'info', 'Modal Ditolak',
            'Modal "' . $capital->name . '" telah ditolak.',
            route('capital.index'));

        return response()->json(['message' => 'Modal berhasil ditolak.']);
    }

    public function trash()
    {
        return view('capital.trash');
    }

    public function trashData()
    {
        $kapalId = request('kapal_id');
        $query   = Capital::onlyTrashed()->with(['creator', 'deleter'])->orderBy('date', 'desc');
        if ($kapalId) $query->where('kapal_id', $kapalId);
        $capitals = $query->get()->map(fn($c) => [
            'id'         => $c->id,
            'kapal_id'   => $c->kapal_id,
            'date'       => $c->date->translatedFormat('d M Y'),
            'date_raw'   => $c->date->format('Y-m-d'),
            'name'       => $c->name,
            'nominal'    => number_format($c->nominal, 0, ',', '.'),
            'nominal_raw'=> $c->nominal,
            'note'       => $c->note ?? '',
            'status'     => $c->status,
            'deleted_at' => $c->deleted_at->translatedFormat('d M Y H:i'),
            'deleted_by' => $c->deleter?->name ?? 'N/A',
        ]);
        return response()->json(['data' => $capitals]);
    }

    public function restore($id)
    {
        if (!auth()->user()->canRestore()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk restore data.'], 403);
        }

        $capital = Capital::onlyTrashed()->find($id);
        if (!$capital) {
            return response()->json(['message' => 'Modal tidak ditemukan.'], 404);
        }

        $capital->restore();

        return response()->json(['message' => 'Modal berhasil di-restore.']);
    }

    public function forceDelete($id)
    {
        if (!auth()->user()->canDelete()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menghapus data.'], 403);
        }

        $capital = Capital::onlyTrashed()->find($id);
        if (!$capital) {
            return response()->json(['message' => 'Modal tidak ditemukan.'], 404);
        }

        $capital->forceDelete();

        return response()->json(['message' => 'Modal berhasil dihapus permanen.']);
    }
}
