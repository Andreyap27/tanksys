<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index()
    {
        return view('expenses.index', [
            'canManage' => auth()->user()->canManage(),
            'canDelete' => auth()->user()->canDelete(),
        ]);
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
            $request->only(['date', 'description', 'nominal', 'category', 'noted']),
        ));

        return response()->json(['message' => 'Pengeluaran berhasil diupdate.']);
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
        return view('expenses.trash', [
            'canRestore' => auth()->user()->canManage(),
            'canDelete'  => auth()->user()->canDelete(),
        ]);
    }

    public function trashData()
    {
        $kapalId  = request('kapal_id');
        $query    = Expense::onlyTrashed()->with('creator')->orderBy('date', 'desc');
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
        ]);
        return response()->json(['data' => $expenses]);
    }

    public function restore($id)
    {
        if (!auth()->user()->canManage()) {
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
