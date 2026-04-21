<?php

namespace App\Http\Controllers;

use App\Models\LoriExpense;
use App\Models\Lori;
use Illuminate\Http\Request;

class LoriExpenseController extends Controller
{
    public function index()
    {
        return view('lori-expense.index');
    }

    public function data()
    {
        try {
            $mobilId  = request('mobil_id');
            $query    = LoriExpense::orderBy('date', 'desc');
            if ($mobilId) $query->where('mobil_id', $mobilId);
            $expenses = $query->get()->map(fn($e) => [
                'id'          => $e->id,
                'mobil_id'    => $e->mobil_id,
                'date'        => $e->date->translatedFormat('d M Y'),
                'date_raw'    => $e->date->format('Y-m-d'),
                'description' => $e->description,
                'category'    => $e->category,
                'nominal'     => number_format($e->nominal, 0, ',', '.'),
                'nominal_raw' => $e->nominal,
                'noted'       => $e->noted ?? '-',
            ]);
            return response()->json(['data' => $expenses]);
        } catch (\Exception $e) {
            return response()->json(['data' => [], 'error' => $e->getMessage()], 500);
        }
    }

    public function summary()
    {
        $mobilId = request('mobil_id');

        $debitQuery  = LoriExpense::query();
        $kreditQuery = Lori::query();

        if ($mobilId) {
            $debitQuery->where('mobil_id', $mobilId);
            $kreditQuery->where('mobil_id', $mobilId);
        }

        $debit  = (float) $debitQuery->sum('nominal');
        $kredit = (float) $kreditQuery->sum('price');

        return response()->json([
            'debit'   => $debit,
            'kredit'  => $kredit,
            'balance' => $kredit - $debit,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'mobil_id'    => 'nullable|exists:mobils,id',
            'date'        => 'required|date',
            'description' => 'required|string|max:255',
            'category'    => 'required|in:' . implode(',', LoriExpense::CATEGORIES),
            'nominal'     => 'required|numeric|min:0',
            'noted'       => 'nullable|string',
        ]);

        LoriExpense::create([
            'mobil_id'    => $request->mobil_id ?: null,
            'date'        => $request->date,
            'description' => $request->description,
            'category'    => $request->category,
            'nominal'     => $request->nominal,
            'noted'       => $request->noted,
            'created_by'  => auth()->id(),
        ]);

        return response()->json(['message' => 'Expense berhasil disimpan.']);
    }

    public function update(Request $request, LoriExpense $loriExpense)
    {
        if (!auth()->user()->canManage()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk mengedit.'], 403);
        }

        $request->validate([
            'mobil_id'    => 'nullable|exists:mobils,id',
            'date'        => 'required|date',
            'description' => 'required|string|max:255',
            'category'    => 'required|in:' . implode(',', LoriExpense::CATEGORIES),
            'nominal'     => 'required|numeric|min:0',
            'noted'       => 'nullable|string',
        ]);

        $loriExpense->update(array_merge(
            $request->only(['date', 'description', 'category', 'nominal', 'noted']),
            ['mobil_id' => $request->mobil_id ?: null]
        ));

        return response()->json(['message' => 'Expense berhasil diupdate.']);
    }

    public function destroy(LoriExpense $loriExpense)
    {
        if (!auth()->user()->canDelete()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menghapus data.'], 403);
        }

        $loriExpense->delete();
        return response()->json(['message' => 'Expense berhasil dihapus.']);
    }

    public function trash()
    {
        return view('lori-expense.trash');
    }

    public function trashData()
    {
        $mobilId      = request('mobil_id');
        $query        = LoriExpense::onlyTrashed()->with(['creator', 'deleter'])->orderBy('date', 'desc');
        if ($mobilId) $query->where('mobil_id', $mobilId);
        $loriExpenses = $query->get()->map(fn($le) => [
            'id'         => $le->id,
            'mobil_id'   => $le->mobil_id,
            'date'       => $le->date->translatedFormat('d M Y'),
            'date_raw'   => $le->date->format('Y-m-d'),
            'description'=> $le->description,
            'category'   => $le->category,
            'nominal'    => number_format($le->nominal, 0, ',', '.'),
            'nominal_raw'=> $le->nominal,
            'noted'      => $le->noted ?? '',
            'deleted_at' => $le->deleted_at->translatedFormat('d M Y H:i'),
            'deleted_by' => $le->deleter?->name ?? 'N/A',
        ]);
        return response()->json(['data' => $loriExpenses]);
    }

    public function restore($id)
    {
        if (!auth()->user()->canRestore()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk restore data.'], 403);
        }

        $loriExpense = LoriExpense::onlyTrashed()->find($id);
        if (!$loriExpense) {
            return response()->json(['message' => 'Expense tidak ditemukan.'], 404);
        }

        $loriExpense->restore();

        return response()->json(['message' => 'Expense berhasil di-restore.']);
    }

    public function forceDelete($id)
    {
        if (!auth()->user()->canDelete()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menghapus data.'], 403);
        }

        $loriExpense = LoriExpense::onlyTrashed()->find($id);
        if (!$loriExpense) {
            return response()->json(['message' => 'Expense tidak ditemukan.'], 404);
        }

        $loriExpense->forceDelete();

        return response()->json(['message' => 'Expense berhasil dihapus permanen.']);
    }
}
