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
        $expenses = Expense::latest()->get()->map(fn($e) => [
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
            'nominal'     => 'required|numeric|min:0',
            'category'    => 'required|in:' . implode(',', Expense::CATEGORIES),
            'noted'       => 'nullable|string',
        ]);

        Expense::create([
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
            'date'        => 'required|date',
            'description' => 'required|string|max:255',
            'nominal'     => 'required|numeric|min:0',
            'category'    => 'required|in:' . implode(',', Expense::CATEGORIES),
            'noted'       => 'nullable|string',
        ]);

        $expense->update($request->only(['date', 'description', 'nominal', 'category', 'noted']));

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
}
