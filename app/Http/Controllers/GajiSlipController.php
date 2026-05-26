<?php

namespace App\Http\Controllers;

use App\Models\GajiSlip;
use App\Models\Karyawan;
use App\Models\Notification;
use Illuminate\Http\Request;

class GajiSlipController extends Controller
{
    public function store(Request $request, Karyawan $karyawan)
    {
        $request->validate([
            'period'           => 'required|date',
            'extra'            => 'nullable|numeric|min:0',
            'bonus'            => 'nullable|numeric|min:0',
            'potongan_pinjaman'=> 'nullable|numeric|min:0',
            'potongan_lain'    => 'nullable|numeric|min:0',
            'noted'            => 'nullable|string',
        ]);

        $period = \Carbon\Carbon::parse($request->period)->startOfMonth()->toDateString();
        $exists = $karyawan->slips()
            ->whereYear('period', substr($period, 0, 4))
            ->whereMonth('period', substr($period, 5, 2))
            ->exists();
        if ($exists) {
            return response()->json(['message' => 'Slip gaji periode ' . \Carbon\Carbon::parse($period)->translatedFormat('M Y') . ' sudah pernah dibuat.'], 422);
        }

        $gajiPokok       = (float) $karyawan->gaji_pokok;
        $tunjangan       = (float) $karyawan->tunjangan;
        $extra           = (float) ($request->extra ?? 0);
        $bonus           = (float) ($request->bonus ?? 0);
        $defaultPotPinjaman = $karyawan->pinjamans()->get()->sum(function ($p) use ($karyawan) {
            $installment   = $p->angsuran > 0 ? (float) $p->pokok / $p->angsuran : 0;
            $approvedCount = $karyawan->slips()->where('status', 'approved')
                ->whereDate('period', '>=', \Carbon\Carbon::parse($p->created_at)->startOfMonth()->toDateString())
                ->count();
            $sisa = max(0, (float) $p->pokok - (float) $p->subsidi - min($approvedCount * $installment, max(0, (float) $p->pokok - (float) $p->subsidi)));
            return $sisa > 0 ? $installment : 0;
        });
        $potPinjaman = (float) ($request->potongan_pinjaman ?? $defaultPotPinjaman);
        $potLain         = (float) ($request->potongan_lain ?? 0);
        $total           = $gajiPokok + $tunjangan + $extra + $bonus - $potPinjaman - $potLain;

        GajiSlip::create([
            'karyawan_id'       => $karyawan->id,
            'period'            => $request->period,
            'gaji_pokok'        => $gajiPokok,
            'tunjangan'         => $tunjangan,
            'extra'             => $extra,
            'bonus'             => $bonus,
            'potongan_pinjaman' => $potPinjaman,
            'potongan_lain'     => $potLain,
            'total'             => $total,
            'noted'             => $request->noted,
            'status'            => 'pending',
            'created_by'        => auth()->id(),
        ]);

        Notification::sendToApprovers('approval', 'Slip Gaji Baru',
            auth()->user()->name . ' membuat slip gaji untuk ' . $karyawan->nama_karyawan . ' dan menunggu persetujuan.',
            route('gaji.show', $karyawan->id));

        return response()->json(['message' => 'Slip gaji berhasil dibuat dan menunggu persetujuan.']);
    }

    public function update(Request $request, GajiSlip $slip)
    {
        if (!auth()->user()->canManagePayroll()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk mengedit.'], 403);
        }

        $request->validate([
            'period'           => 'required|date',
            'extra'            => 'nullable|numeric|min:0',
            'bonus'            => 'nullable|numeric|min:0',
            'potongan_pinjaman'=> 'nullable|numeric|min:0',
            'potongan_lain'    => 'nullable|numeric|min:0',
            'noted'            => 'nullable|string',
        ]);

        $gajiPokok   = (float) $slip->gaji_pokok;
        $tunjangan   = (float) $slip->tunjangan;
        $extra       = (float) ($request->extra ?? 0);
        $bonus       = (float) ($request->bonus ?? 0);
        $potPinjaman = (float) ($request->potongan_pinjaman ?? 0);
        $potLain     = (float) ($request->potongan_lain ?? 0);
        $total       = $gajiPokok + $tunjangan + $extra + $bonus - $potPinjaman - $potLain;

        $slip->update([
            'period'            => $request->period,
            'extra'             => $extra,
            'bonus'             => $bonus,
            'potongan_pinjaman' => $potPinjaman,
            'potongan_lain'     => $potLain,
            'total'             => $total,
            'noted'             => $request->noted,
            'status'            => 'pending',
            'approved_by'       => null,
            'approved_at'       => null,
        ]);

        return response()->json(['message' => 'Slip gaji berhasil diupdate dan menunggu persetujuan ulang.']);
    }

    public function destroy(GajiSlip $slip)
    {
        if (!auth()->user()->canManagePayroll()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menghapus data.'], 403);
        }

        $slip->delete();
        return response()->json(['message' => 'Slip gaji berhasil dihapus.']);
    }

    public function approve(GajiSlip $slip)
    {
        if (!auth()->user()->canManagePayroll()) {
            return response()->json(['message' => 'Tidak memiliki akses untuk menyetujui.'], 403);
        }

        if ($slip->status !== 'pending') {
            return response()->json(['message' => 'Slip ini sudah diproses sebelumnya.'], 422);
        }

        $slip->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        Notification::send([$slip->created_by], 'info', 'Slip Gaji Disetujui',
            'Slip gaji ' . $slip->karyawan->nama_karyawan . ' periode ' . $slip->period->translatedFormat('M Y') . ' telah disetujui.',
            route('gaji.show', $slip->karyawan_id));

        return response()->json(['message' => 'Slip gaji berhasil disetujui.']);
    }

    public function reject(GajiSlip $slip)
    {
        if (!auth()->user()->canManagePayroll()) {
            return response()->json(['message' => 'Tidak memiliki akses.'], 403);
        }

        if ($slip->status !== 'pending') {
            return response()->json(['message' => 'Slip ini sudah diproses sebelumnya.'], 422);
        }

        $slip->update(['status' => 'rejected']);

        Notification::send([$slip->created_by], 'info', 'Slip Gaji Ditolak',
            'Slip gaji ' . $slip->karyawan->nama_karyawan . ' periode ' . $slip->period->translatedFormat('M Y') . ' telah ditolak.',
            route('gaji.show', $slip->karyawan_id));

        return response()->json(['message' => 'Slip gaji berhasil ditolak.']);
    }

    public function print(GajiSlip $slip)
    {
        $slip->load('karyawan', 'approver');
        return view('gaji.slip.print', compact('slip'));
    }
}
