<?php

namespace App\Http\Controllers;

use App\Models\UangKoordinasi;
use App\Models\Notification;
use Illuminate\Http\Request;

class UangKoordinasiController extends Controller
{
    public function index()
    {
        return view('uang-koordinasi.index');
    }

    public function data()
    {
        try {
            $items = UangKoordinasi::orderBy('date', 'desc')->get()->map(fn($u) => [
                'id'          => $u->id,
                'date'        => $this->resolveDate($u)->translatedFormat('d M Y H:i'),
                'date_raw'    => $u->date->format('Y-m-d'),
                'nama'        => $u->nama,
                'jabatan'     => $u->jabatan ?? '-',
                'noted'       => $u->noted ?? '-',
                'nominal'     => number_format($u->nominal, 0, ',', '.'),
                'nominal_raw' => $u->nominal,
                'extra'       => number_format($u->extra, 0, ',', '.'),
                'extra_raw'   => $u->extra,
                'total'       => number_format($u->total, 0, ',', '.'),
                'total_raw'   => $u->total,
                'status'      => $u->status,
            ]);
            return response()->json(['data' => $items]);
        } catch (\Exception $e) {
            return response()->json(['data' => [], 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'date'    => 'required|date',
            'nama'    => 'required|string|max:255',
            'jabatan' => 'nullable|string|max:100',
            'noted'   => 'nullable|string',
            'nominal' => 'required|numeric|min:0',
            'extra'   => 'nullable|numeric|min:0',
        ]);

        $nominal = (float) $request->nominal;
        $extra   = (float) ($request->extra ?? 0);

        if (!auth()->user()->canManagePayroll()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menambah data.'], 403);
        }

        UangKoordinasi::create([
            'date'        => $request->date . ' ' . now()->format('H:i:s'),
            'nama'        => $request->nama,
            'jabatan'     => $request->jabatan,
            'noted'       => $request->noted,
            'nominal'     => $nominal,
            'extra'       => $extra,
            'total'       => $nominal + $extra,
            'date'        => $uangKoordinasi->date->toDateString() . ' ' . now()->format('H:i:s'),
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'created_by'  => auth()->id(),
        ]);

        return response()->json(['message' => 'Uang koordinasi berhasil disimpan dan disetujui.']);
    }

    public function show(UangKoordinasi $uangKoordinasi)
    {
        return response()->json([
            'id'      => $uangKoordinasi->id,
            'date'    => $uangKoordinasi->date->format('Y-m-d'),
            'nama'    => $uangKoordinasi->nama,
            'jabatan' => $uangKoordinasi->jabatan ?? '',
            'noted'   => $uangKoordinasi->noted ?? '',
            'nominal' => $uangKoordinasi->nominal,
            'extra'   => $uangKoordinasi->extra,
        ]);
    }

    public function update(Request $request, UangKoordinasi $uangKoordinasi)
    {
        if (!auth()->user()->canManagePayroll()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk mengedit.'], 403);
        }

        $request->validate([
            'date'    => 'required|date',
            'nama'    => 'required|string|max:255',
            'jabatan' => 'nullable|string|max:100',
            'noted'   => 'nullable|string',
            'nominal' => 'required|numeric|min:0',
            'extra'   => 'nullable|numeric|min:0',
        ]);

        $nominal = (float) $request->nominal;
        $extra   = (float) ($request->extra ?? 0);

        $uangKoordinasi->update([
            'date'        => $request->date . ' ' . now()->format('H:i:s'),
            'nama'        => $request->nama,
            'jabatan'     => $request->jabatan,
            'noted'       => $request->noted,
            'nominal'     => $nominal,
            'extra'       => $extra,
            'total'       => $nominal + $extra,
            'status'      => 'pending',
            'approved_by' => null,
            'approved_at' => null,
        ]);

        Notification::sendToApprovers('approval', 'Uang Koordinasi Diupdate',
            auth()->user()->name . ' mengubah uang koordinasi atas nama ' . $request->nama . ' dan menunggu persetujuan.',
            route('uang-koordinasi.index'));

        return response()->json(['message' => 'Uang koordinasi berhasil diupdate dan menunggu persetujuan ulang.']);
    }

    public function destroy(UangKoordinasi $uangKoordinasi)
    {
        if (!auth()->user()->canDelete()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menghapus data.'], 403);
        }

        $uangKoordinasi->delete();
        return response()->json(['message' => 'Uang koordinasi berhasil dihapus.']);
    }

    public function approve(UangKoordinasi $uangKoordinasi)
    {
        if (!auth()->user()->canManagePayroll()) {
            return response()->json(['message' => 'Tidak memiliki akses untuk menyetujui.'], 403);
        }

        if ($uangKoordinasi->status !== 'pending') {
            return response()->json(['message' => 'Data ini sudah diproses sebelumnya.'], 422);
        }

        $uangKoordinasi->update([
            'date'        => $uangKoordinasi->date->toDateString() . ' ' . now()->format('H:i:s'),
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        Notification::send([$uangKoordinasi->created_by], 'info', 'Uang Koordinasi Disetujui',
            'Uang koordinasi atas nama ' . $uangKoordinasi->nama . ' telah disetujui.',
            route('uang-koordinasi.index'));

        return response()->json(['message' => 'Uang koordinasi berhasil disetujui.']);
    }

    public function reject(UangKoordinasi $uangKoordinasi)
    {
        if (!auth()->user()->canManagePayroll()) {
            return response()->json(['message' => 'Tidak memiliki akses.'], 403);
        }

        if ($uangKoordinasi->status !== 'pending') {
            return response()->json(['message' => 'Data ini sudah diproses sebelumnya.'], 422);
        }

        $uangKoordinasi->update(['status' => 'rejected']);

        Notification::send([$uangKoordinasi->created_by], 'info', 'Uang Koordinasi Ditolak',
            'Uang koordinasi atas nama ' . $uangKoordinasi->nama . ' telah ditolak.',
            route('uang-koordinasi.index'));

        return response()->json(['message' => 'Uang koordinasi berhasil ditolak.']);
    }

    public function trash()
    {
        return view('uang-koordinasi.trash');
    }

    public function trashData()
    {
        $items = UangKoordinasi::onlyTrashed()->with(['creator', 'deleter'])
            ->orderBy('date', 'desc')
            ->get()
            ->map(fn($u) => [
                'id'         => $u->id,
                'date'       => $this->resolveDate($u)->translatedFormat('d M Y H:i'),
                'date_raw'   => $u->date->format('Y-m-d'),
                'nama'       => $u->nama,
                'jabatan'    => $u->jabatan ?? '-',
                'total'      => number_format($u->total, 0, ',', '.'),
                'total_raw'  => $u->total,
                'status'     => $u->status,
                'deleted_at' => $u->deleted_at->translatedFormat('d M Y H:i'),
                'deleted_by' => $u->deleter?->name ?? 'N/A',
            ]);
        return response()->json(['data' => $items]);
    }

    public function restore($id)
    {
        if (!auth()->user()->canRestore()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk restore data.'], 403);
        }

        $item = UangKoordinasi::onlyTrashed()->find($id);
        if (!$item) {
            return response()->json(['message' => 'Data tidak ditemukan.'], 404);
        }

        $item->restore();
        return response()->json(['message' => 'Uang koordinasi berhasil di-restore.']);
    }

    public function forceDelete($id)
    {
        if (!auth()->user()->canDelete()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menghapus data.'], 403);
        }

        $item = UangKoordinasi::onlyTrashed()->find($id);
        if (!$item) {
            return response()->json(['message' => 'Data tidak ditemukan.'], 404);
        }

        $item->forceDelete();
        return response()->json(['message' => 'Uang koordinasi berhasil dihapus permanen.']);
    }
}
