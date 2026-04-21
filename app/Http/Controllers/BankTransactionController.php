<?php

namespace App\Http\Controllers;

use App\Models\BankTransaction;
use Illuminate\Http\Request;

class BankTransactionController extends Controller
{
    public function index()
    {
        return view('bank.index');
    }

    public function data()
    {
        try {
            $transactions = BankTransaction::orderBy('date', 'desc')->get()->map(fn($t) => [
                'id'          => $t->id,
                'date'        => $t->date->translatedFormat('d M Y'),
                'date_raw'    => $t->date->format('Y-m-d'),
                'type'        => $t->type,
                'amount'      => number_format($t->amount, 0, ',', '.'),
                'amount_raw'  => $t->amount,
                'description' => $t->description,
                'note'        => $t->note,
                'job'         => $t->job,
            ]);
            return response()->json(['data' => $transactions]);
        } catch (\Exception $e) {
            return response()->json(['data' => [], 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'date'        => 'required|date',
            'type'        => 'required|in:in,out',
            'amount'      => 'required|numeric|min:0',
            'description' => 'required|string|max:255',
            'note'        => 'required|in:' . implode(',', BankTransaction::NOTES),
            'job'         => 'required|string|max:255',
        ]);

        BankTransaction::create([
            'date'        => $request->date,
            'type'        => $request->type,
            'amount'      => $request->amount,
            'description' => $request->description,
            'note'        => $request->note,
            'job'         => $request->job,
            'created_by'  => auth()->id(),
        ]);

        return response()->json(['message' => 'Transaksi berhasil disimpan.']);
    }

    public function update(Request $request, BankTransaction $bankTransaction)
    {
        if (!auth()->user()->canManage()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk mengedit.'], 403);
        }

        $request->validate([
            'date'        => 'required|date',
            'type'        => 'required|in:in,out',
            'amount'      => 'required|numeric|min:0',
            'description' => 'required|string|max:255',
            'note'        => 'required|in:' . implode(',', BankTransaction::NOTES),
            'job'         => 'required|string|max:255',
        ]);

        $bankTransaction->update($request->only(['date', 'type', 'amount', 'description', 'note', 'job']));

        return response()->json(['message' => 'Transaksi berhasil diupdate.']);
    }

    public function destroy(BankTransaction $bankTransaction)
    {
        if (!auth()->user()->canDelete()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menghapus data.'], 403);
        }

        $bankTransaction->delete();
        return response()->json(['message' => 'Transaksi berhasil dihapus.']);
    }

    public function printView()
    {
        $dateFrom = request('date_from')
            ? \Carbon\Carbon::parse(request('date_from'))->startOfDay()
            : now()->startOfMonth();
        $dateTo = request('date_to')
            ? \Carbon\Carbon::parse(request('date_to'))->endOfDay()
            : now()->endOfDay();

        $transactions = BankTransaction::whereBetween('date', [$dateFrom, $dateTo])
            ->orderBy('date', 'asc')
            ->get();

        return view('bank.print', compact('transactions', 'dateFrom', 'dateTo'));
    }

    public function trash()
    {
        return view('bank.trash');
    }

    public function trashData()
    {
        $transactions = BankTransaction::onlyTrashed()->with(['creator', 'deleter'])->orderBy('date', 'desc')
            ->get()->map(fn($t) => [
                'id'          => $t->id,
                'date'        => $t->date->translatedFormat('d M Y'),
                'date_raw'    => $t->date->format('Y-m-d'),
                'type'        => $t->type,
                'amount'      => number_format($t->amount, 0, ',', '.'),
                'amount_raw'  => $t->amount,
                'description' => $t->description,
                'note'        => $t->note,
                'job'         => $t->job,
                'deleted_at'  => $t->deleted_at->translatedFormat('d M Y H:i'),
                'deleted_by'  => $t->deleter?->name ?? 'N/A',
            ]);
        return response()->json(['data' => $transactions]);
    }

    public function restore($id)
    {
        if (!auth()->user()->canRestore()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk restore data.'], 403);
        }

        $transaction = BankTransaction::onlyTrashed()->find($id);
        if (!$transaction) {
            return response()->json(['message' => 'Transaksi tidak ditemukan.'], 404);
        }

        $transaction->restore();
        return response()->json(['message' => 'Transaksi berhasil di-restore.']);
    }

    public function forceDelete($id)
    {
        if (!auth()->user()->canDelete()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menghapus data.'], 403);
        }

        $transaction = BankTransaction::onlyTrashed()->find($id);
        if (!$transaction) {
            return response()->json(['message' => 'Transaksi tidak ditemukan.'], 404);
        }

        $transaction->forceDelete();
        return response()->json(['message' => 'Transaksi berhasil dihapus permanen.']);
    }
}
