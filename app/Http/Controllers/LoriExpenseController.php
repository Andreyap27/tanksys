<?php

namespace App\Http\Controllers;

use App\Models\LoriExpense;
use Illuminate\Http\Request;

class LoriExpenseController extends Controller
{
    public function index()
    {
        return view('lori-expense.index', [
            'canManage' => auth()->user()->canManage(),
            'canDelete' => auth()->user()->canDelete(),
        ]);
    }

    public function data()
    {
        $expenses = LoriExpense::latest()->get()->map(fn($e) => [
            'id'          => $e->id,
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

    public function store(Request $request)
    {
        $request->validate([
            'date'        => 'required|date',
            'description' => 'required|string|max:255',
            'category'    => 'required|in:' . implode(',', LoriExpense::CATEGORIES),
            'nominal'     => 'required|numeric|min:0',
            'noted'       => 'nullable|string',
        ]);

        LoriExpense::create([
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
            'date'        => 'required|date',
            'description' => 'required|string|max:255',
            'category'    => 'required|in:' . implode(',', LoriExpense::CATEGORIES),
            'nominal'     => 'required|numeric|min:0',
            'noted'       => 'nullable|string',
        ]);

        $loriExpense->update($request->only(['date', 'description', 'category', 'nominal', 'noted']));

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
}
