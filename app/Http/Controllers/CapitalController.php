<?php

namespace App\Http\Controllers;

use App\Models\Capital;
use App\Models\Notification;
use Illuminate\Http\Request;

class CapitalController extends Controller
{
    public function index()
    {
        $canApprove = auth()->user()->canApprove();
        $canManage  = auth()->user()->canManage();
        $canDelete  = auth()->user()->canDelete();
        return view('capital.index', compact('canApprove', 'canManage', 'canDelete'));
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
            'status'      => $c->status,
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
            'status'     => 'pending',
            'created_by' => auth()->id(),
        ]);

        Notification::sendToApprovers('approval', 'Modal Baru',
            auth()->user()->name . ' menambahkan modal "' . $request->name . '" menunggu persetujuan.',
            route('capital.index'));

        return response()->json(['message' => 'Modal berhasil disimpan dan menunggu persetujuan.']);
    }

    public function update(Request $request, Capital $capital)
    {
        if (!auth()->user()->canManage()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk mengedit.'], 403);
        }

        $request->validate([
            'date'    => 'required|date',
            'name'    => 'required|string|max:255',
            'nominal' => 'required|numeric|min:0',
            'note'    => 'nullable|string',
        ]);

        $capital->update(array_merge(
            $request->only(['date', 'name', 'nominal', 'note']),
            ['status' => 'pending', 'approved_by' => null, 'approved_at' => null]
        ));

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
}
