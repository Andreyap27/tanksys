<?php

namespace App\Http\Controllers;

use App\Models\GajiSlip;
use App\Models\Karyawan;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GajiController extends Controller
{
    public function index()
    {
        $jabatans = Karyawan::whereNotNull('jabatan')->where('jabatan', '!=', '')
            ->distinct()->orderBy('jabatan')->pluck('jabatan');
        return view('gaji.index', compact('jabatans'));
    }

    public function printReport(Request $request)
    {
        $from    = $request->from;
        $to      = $request->to;
        $jabatan = $request->jabatan;

        $query = GajiSlip::with('karyawan')->orderBy('period', 'asc');

        if ($from) {
            $query->where('period', '>=', $from . '-01');
        }
        if ($to) {
            $query->where('period', '<=', Carbon::parse($to . '-01')->endOfMonth()->toDateString());
        }
        if ($jabatan) {
            $query->whereHas('karyawan', fn($q) => $q->where('jabatan', $jabatan));
        }

        $slips = $query->get();
        return view('gaji.print-report', compact('slips', 'from', 'to', 'jabatan'));
    }

    public function create()
    {
        return view('gaji.create');
    }

    public function edit(Karyawan $karyawan)
    {
        $hasSlips = $karyawan->slips()->exists();
        return view('gaji.edit', compact('karyawan', 'hasSlips'));
    }

    public function data()
    {
        try {
            $karyawans = Karyawan::orderBy('nama_karyawan', 'asc')->get()->map(fn($k) => [
                'id'             => $k->id,
                'nama_karyawan'  => $k->nama_karyawan,
                'no_ktp'         => $k->no_ktp ?? '-',
                'no_hp'          => $k->no_hp ?? '-',
                'jabatan'        => $k->jabatan ?? '-',
                'gaji_pokok'     => number_format($k->gaji_pokok, 0, ',', '.'),
                'gaji_pokok_raw' => $k->gaji_pokok,
                'tunjangan'      => number_format($k->tunjangan, 0, ',', '.'),
                'tunjangan_raw'  => $k->tunjangan,
                'noted'          => $k->noted ?? '-',
            ]);
            return response()->json(['data' => $karyawans]);
        } catch (\Exception $e) {
            return response()->json(['data' => [], 'error' => $e->getMessage()], 500);
        }
    }

    public function show(Karyawan $karyawan)
    {
        $slips = $karyawan->slips()
            ->orderBy('period', 'desc')
            ->get()
            ->map(fn($s) => [
                'id'                    => $s->id,
                'period'                => $s->period->translatedFormat('M Y'),
                'period_raw'            => $s->period->format('Y-m-d'),
                'gaji_pokok'            => number_format($s->gaji_pokok, 0, ',', '.'),
                'gaji_pokok_raw'        => $s->gaji_pokok,
                'tunjangan'             => number_format($s->tunjangan, 0, ',', '.'),
                'tunjangan_raw'         => $s->tunjangan,
                'extra'                 => number_format($s->extra, 0, ',', '.'),
                'extra_raw'             => $s->extra,
                'bonus'                 => number_format($s->bonus, 0, ',', '.'),
                'bonus_raw'             => $s->bonus,
                'potongan_pinjaman'     => number_format($s->potongan_pinjaman, 0, ',', '.'),
                'potongan_pinjaman_raw' => $s->potongan_pinjaman,
                'potongan_lain'         => number_format($s->potongan_lain, 0, ',', '.'),
                'potongan_lain_raw'     => $s->potongan_lain,
                'total'                 => number_format($s->total, 0, ',', '.'),
                'total_raw'             => $s->total,
                'noted'                 => $s->noted ?? '-',
                'status'                => $s->status,
            ]);

        $pinjamans = $karyawan->pinjamans()->get()->map(function ($p) use ($karyawan) {
            $installment  = $p->angsuran > 0 ? round((float) $p->pokok / $p->angsuran, 2) : 0;
            $approvedCount = $karyawan->slips()
                ->where('status', 'approved')
                ->whereDate('period', '>=', Carbon::parse($p->created_at)->startOfMonth()->toDateString())
                ->count();
            $maxDeductible = max(0, (float) $p->pokok - (float) $p->subsidi);
            $totalDeducted = min($approvedCount * $installment, $maxDeductible);
            $sisa          = max(0, $maxDeductible - $totalDeducted);
            return [
                'id'           => $p->id,
                'pokok'        => number_format($p->pokok, 0, ',', '.'),
                'pokok_raw'    => (float) $p->pokok,
                'angsuran'     => $p->angsuran,
                'subsidi'      => number_format($p->subsidi, 0, ',', '.'),
                'subsidi_raw'  => (float) $p->subsidi,
                'noted'        => $p->noted ?? '',
                'sisa'         => number_format($sisa, 0, ',', '.'),
                'sisa_raw'     => $sisa,
                'installment'  => $installment,
                'lunas'        => $sisa <= 0,
            ];
        });

        $totalMonthlyInstallment = $pinjamans->where('lunas', false)->sum('installment');

        return view('gaji.show', compact('karyawan', 'slips', 'pinjamans', 'totalMonthlyInstallment'));
    }

    public function summaryStats()
    {
        $karyawans = Karyawan::with(['pinjamans', 'slips' => fn($q) => $q->where('status', 'approved')])->get();
        $totalPinjamanAktif = 0;
        foreach ($karyawans as $k) {
            foreach ($k->pinjamans as $p) {
                $installment   = $p->angsuran > 0 ? (float) $p->pokok / $p->angsuran : 0;
                $approvedCount = $k->slips->filter(fn($s) => $s->period >= Carbon::parse($p->created_at)->startOfMonth())->count();
                $maxDeductible = max(0, (float) $p->pokok - (float) $p->subsidi);
                $sisa          = max(0, $maxDeductible - min($approvedCount * $installment, $maxDeductible));
                $totalPinjamanAktif += $sisa;
            }
        }
        return response()->json(['total_pinjaman_aktif' => $totalPinjamanAktif]);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->canManagePayroll()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menambah data.'], 403);
        }

        $request->validate([
            'nama_karyawan' => 'required|string|max:255',
            'no_ktp'        => 'nullable|string|max:16',
            'no_hp'         => 'nullable|string|max:20',
            'jabatan'       => 'nullable|string|max:100',
            'gaji_pokok'    => 'required|numeric|min:0',
            'tunjangan'     => 'nullable|numeric|min:0',
            'noted'         => 'nullable|string',
        ]);

        Karyawan::create([
            'nama_karyawan' => $request->nama_karyawan,
            'no_ktp'        => $request->no_ktp,
            'no_hp'         => $request->no_hp,
            'jabatan'       => $request->jabatan,
            'gaji_pokok'    => $request->gaji_pokok,
            'tunjangan'     => (float) ($request->tunjangan ?? 0),
            'noted'         => $request->noted,
            'created_by'    => auth()->id(),
        ]);

        return response()->json(['message' => 'Karyawan berhasil disimpan.']);
    }

    public function update(Request $request, Karyawan $karyawan)
    {
        if (!auth()->user()->canManagePayroll()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk mengedit.'], 403);
        }

        $request->validate([
            'nama_karyawan' => 'required|string|max:255',
            'no_ktp'        => 'nullable|string|max:16',
            'no_hp'         => 'nullable|string|max:20',
            'jabatan'       => 'nullable|string|max:100',
            'gaji_pokok'    => 'required|numeric|min:0',
            'tunjangan'     => 'nullable|numeric|min:0',
            'noted'         => 'nullable|string',
        ]);

        $karyawan->update([
            'nama_karyawan' => $request->nama_karyawan,
            'no_ktp'        => $request->no_ktp,
            'no_hp'         => $request->no_hp,
            'jabatan'       => $request->jabatan,
            'gaji_pokok'    => $request->gaji_pokok,
            'tunjangan'     => (float) ($request->tunjangan ?? 0),
            'noted'         => $request->noted,
        ]);

        return response()->json(['message' => 'Data karyawan berhasil diupdate.']);
    }

    public function destroy(Karyawan $karyawan)
    {
        if (!auth()->user()->canManagePayroll()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menghapus data.'], 403);
        }

        $karyawan->delete();
        return response()->json(['message' => 'Karyawan berhasil dihapus.']);
    }

    public function trash()
    {
        return view('gaji.trash');
    }

    public function trashData()
    {
        $karyawans = Karyawan::onlyTrashed()->with(['creator', 'deleter'])
            ->orderBy('nama_karyawan', 'asc')
            ->get()
            ->map(fn($k) => [
                'id'            => $k->id,
                'nama_karyawan' => $k->nama_karyawan,
                'no_ktp'        => $k->no_ktp ?? '-',
                'no_hp'         => $k->no_hp ?? '-',
                'jabatan'       => $k->jabatan ?? '-',
                'deleted_at'    => $k->deleted_at->translatedFormat('d M Y H:i'),
                'deleted_by'    => $k->deleter?->name ?? 'N/A',
            ]);
        return response()->json(['data' => $karyawans]);
    }

    public function restore($id)
    {
        if (!auth()->user()->canRestore()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk restore data.'], 403);
        }

        $karyawan = Karyawan::onlyTrashed()->find($id);
        if (!$karyawan) {
            return response()->json(['message' => 'Karyawan tidak ditemukan.'], 404);
        }

        $karyawan->restore();
        return response()->json(['message' => 'Karyawan berhasil di-restore.']);
    }

    public function forceDelete($id)
    {
        if (!auth()->user()->canDelete()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menghapus data.'], 403);
        }

        $karyawan = Karyawan::onlyTrashed()->find($id);
        if (!$karyawan) {
            return response()->json(['message' => 'Karyawan tidak ditemukan.'], 404);
        }

        $karyawan->forceDelete();
        return response()->json(['message' => 'Karyawan berhasil dihapus permanen.']);
    }
}
