<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\KaryawanPinjaman;
use Illuminate\Http\Request;

class KaryawanPinjamanController extends Controller
{
    public function store(Request $request, Karyawan $karyawan)
    {
        if (!auth()->user()->canManagePayroll()) {
            return response()->json(['message' => 'Tidak memiliki izin.'], 403);
        }

        $request->validate([
            'pokok'    => 'required|numeric|min:1',
            'angsuran' => 'required|integer|min:1',
            'subsidi'  => 'nullable|numeric|min:0',
            'noted'    => 'nullable|string',
        ]);

        $karyawan->pinjamans()->create([
            'pokok'      => $request->pokok,
            'angsuran'   => $request->angsuran,
            'subsidi'    => (float) ($request->subsidi ?? 0),
            'noted'      => $request->noted,
            'created_by' => auth()->id(),
        ]);

        return response()->json(['message' => 'Pinjaman berhasil ditambahkan.']);
    }

    public function update(Request $request, KaryawanPinjaman $pinjaman)
    {
        if (!auth()->user()->canManagePayroll()) {
            return response()->json(['message' => 'Tidak memiliki izin.'], 403);
        }

        $request->validate([
            'pokok'    => 'required|numeric|min:1',
            'angsuran' => 'required|integer|min:1',
            'subsidi'  => 'nullable|numeric|min:0',
            'noted'    => 'nullable|string',
        ]);

        $pinjaman->update([
            'pokok'    => $request->pokok,
            'angsuran' => $request->angsuran,
            'subsidi'  => (float) ($request->subsidi ?? 0),
            'noted'    => $request->noted,
        ]);

        return response()->json(['message' => 'Pinjaman berhasil diupdate.']);
    }

    public function destroy(KaryawanPinjaman $pinjaman)
    {
        if (!auth()->user()->canManagePayroll()) {
            return response()->json(['message' => 'Tidak memiliki izin untuk menghapus.'], 403);
        }

        $pinjaman->delete();
        return response()->json(['message' => 'Pinjaman berhasil dihapus.']);
    }
}
