<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Notification;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index()
    {
        return view('expenses.index');
    }

    public function data()
    {
        $kapalId  = request('kapal_id');
        $query    = Expense::orderBy('date', 'desc');
        if ($kapalId) $query->where('kapal_id', $kapalId);
        $expenses = $query->get()->map(fn($e) => [
            'id'          => $e->id,
            'kapal_id'    => $e->kapal_id,
            'date'        => $e->date->translatedFormat('d M Y'),
            'date_raw'    => $e->date->format('Y-m-d'),
            'description' => $e->description,
            'category'    => $e->category,
            'nominal'     => number_format($e->nominal, 0, ',', '.'),
            'nominal_raw' => $e->nominal,
            'noted'       => $e->noted ?? '-',
            'status'      => $e->status ?? 'pending',
        ]);
        return response()->json(['data' => $expenses]);
    }

    public function capitalTotal()
    {
        $kapalId = request('kapal_id');
        $query   = \App\Models\Capital::where('status', 'approved');
        if ($kapalId) $query->where('kapal_id', $kapalId);
        return response()->json(['total' => (float) $query->sum('nominal')]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kapal_id'    => 'nullable|exists:kapals,id',
            'date'        => 'required|date',
            'description' => 'required|string|max:255',
            'nominal'     => 'required|numeric|min:0',
            'category'    => 'required|in:' . implode(',', Expense::CATEGORIES),
            'noted'       => 'nullable|string',
        ]);

        Expense::create([
            'kapal_id'    => $request->kapal_id ?: null,
            'date'        => $request->date,
            'description' => $request->description,
            'nominal'     => $request->nominal,
            'category'    => $request->category,
            'noted'       => $request->noted,
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'created_by'  => auth()->id(),
        ]);

        return response()->json(['message' => 'Pengeluaran berhasil disimpan.']);
    }

    public function update(Request $request, Expense $expense)
    {
        $request->validate([
            'kapal_id'    => 'nullable|exists:kapals,id',
            'date'        => 'required|date',
            'description' => 'required|string|max:255',
            'nominal'     => 'required|numeric|min:0',
            'category'    => 'required|in:' . implode(',', Expense::CATEGORIES),
            'noted'       => 'nullable|string',
        ]);

        $expense->update(array_merge(
            $request->only(['kapal_id', 'date', 'description', 'nominal', 'category', 'noted']),
            ['status' => 'pending', 'approved_by' => null, 'approved_at' => null],
        ));

        Notification::sendToApprovers('approval', 'Pengeluaran Diupdate',
            auth()->user()->name . ' mengubah pengeluaran "' . $request->description . '" dan menunggu persetujuan.',
            route('expenses.index'));

        return response()->json(['message' => 'Pengeluaran berhasil diupdate dan menunggu persetujuan ulang.']);
    }

    public function approve(Expense $expense)
    {
        if (!auth()->user()->canApprove()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menyetujui.'], 403);
        }

        $expense->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        Notification::send([$expense->created_by], 'info', 'Pengeluaran Disetujui',
            'Pengeluaran "' . $expense->description . '" telah disetujui.',
            route('expenses.index'));

        return response()->json(['message' => 'Pengeluaran berhasil disetujui.']);
    }

    public function reject(Expense $expense)
    {
        if (!auth()->user()->canApprove()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menolak.'], 403);
        }

        $expense->update([
            'status'      => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        Notification::send([$expense->created_by], 'info', 'Pengeluaran Ditolak',
            'Pengeluaran "' . $expense->description . '" telah ditolak.',
            route('expenses.index'));

        return response()->json(['message' => 'Pengeluaran berhasil ditolak.']);
    }

    public function destroy(Expense $expense)
    {
        if (!auth()->user()->canDelete()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menghapus data.'], 403);
        }

        $expense->delete();
        return response()->json(['message' => 'Pengeluaran berhasil dihapus.']);
    }

    public function trash()
    {
        return view('expenses.trash');
    }

    public function trashData()
    {
        $kapalId  = request('kapal_id');
        $query    = Expense::onlyTrashed()->with(['creator', 'deleter'])->orderBy('date', 'desc');
        if ($kapalId) $query->where('kapal_id', $kapalId);
        $expenses = $query->get()->map(fn($e) => [
            'id'         => $e->id,
            'kapal_id'   => $e->kapal_id,
            'date'       => $e->date->translatedFormat('d M Y'),
            'date_raw'   => $e->date->format('Y-m-d'),
            'description'=> $e->description,
            'category'   => $e->category,
            'nominal'    => number_format($e->nominal, 0, ',', '.'),
            'nominal_raw'=> $e->nominal,
            'noted'      => $e->noted ?? '',
            'deleted_at' => $e->deleted_at->translatedFormat('d M Y H:i'),
            'deleted_by' => $e->deleter?->name ?? 'N/A',
        ]);
        return response()->json(['data' => $expenses]);
    }

    public function restore($id)
    {
        if (!auth()->user()->canRestore()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk restore data.'], 403);
        }

        $expense = Expense::onlyTrashed()->find($id);
        if (!$expense) {
            return response()->json(['message' => 'Pengeluaran tidak ditemukan.'], 404);
        }

        $expense->restore();

        return response()->json(['message' => 'Pengeluaran berhasil di-restore.']);
    }

    public function forceDelete($id)
    {
        if (!auth()->user()->canDelete()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menghapus data.'], 403);
        }

        $expense = Expense::onlyTrashed()->find($id);
        if (!$expense) {
            return response()->json(['message' => 'Pengeluaran tidak ditemukan.'], 404);
        }

        $expense->forceDelete();

        return response()->json(['message' => 'Pengeluaran berhasil dihapus permanen.']);
    }
}
