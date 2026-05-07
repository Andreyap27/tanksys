<?php

namespace App\Http\Controllers;

use App\Models\PettyCashTransaction;
use Illuminate\Http\Request;

class PettyCashTransactionController extends Controller
{
    public function index()
    {
        return view('petty-cash-transaction.index');
    }

    public function data()
    {
        $query = PettyCashTransaction::query();
        if (request('petty_cash_id')) {
            $query->where('petty_cash_id', request('petty_cash_id'));
        }
        $rows = $query->orderBy('date', 'desc')->orderBy('created_at', 'desc')->get()->map(fn($t) => [
            'id'              => $t->id,
            'date'            => $t->date->translatedFormat('d M Y'),
            'date_raw'        => $t->date->format('Y-m-d'),
            'type'            => $t->type,
            'description'     => $t->description,
            'amount'          => number_format($t->amount, 0, ',', '.'),
            'amount_raw'      => $t->amount,
            'note'            => $t->note ?? '-',
            'petty_cash_id'   => $t->petty_cash_id,
            'petty_cash_name' => $t->pettyCash?->name ?? '-',
        ]);
        return response()->json(['data' => $rows]);
    }

    public function summary()
    {
        $query = PettyCashTransaction::query();
        if (request('petty_cash_id')) {
            $query->where('petty_cash_id', request('petty_cash_id'));
        }
        $rows  = $query->get();
        $kredit = $rows->where('type', 'in')->sum('amount');
        $debit  = $rows->where('type', 'out')->sum('amount');
        return response()->json([
            'kredit'  => $kredit,
            'debit'   => $debit,
            'balance' => $kredit - $debit,
        ]);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->canManage()) {
            return response()->json(['message' => 'Anda tidak memiliki izin.'], 403);
        }
        $request->validate([
            'petty_cash_id' => 'nullable|exists:petty_cashes,id',
            'type'          => 'required|in:in,out',
            'date'          => 'required|date',
            'description'   => 'required|string|max:255',
            'amount'        => 'required|numeric|min:0',
            'note'          => 'nullable|string',
        ]);
        PettyCashTransaction::create([
            'petty_cash_id' => $request->petty_cash_id ?: null,
            'type'          => $request->type,
            'date'          => $request->date,
            'description'   => $request->description,
            'amount'        => $request->amount,
            'note'          => $request->note ?: null,
            'created_by'    => auth()->id(),
        ]);
        return response()->json(['message' => 'Transaksi berhasil disimpan.']);
    }

    public function update(Request $request, PettyCashTransaction $pettyCashTransaction)
    {
        if (!auth()->user()->canManage()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk mengedit.'], 403);
        }
        $request->validate([
            'petty_cash_id' => 'nullable|exists:petty_cashes,id',
            'type'          => 'required|in:in,out',
            'date'          => 'required|date',
            'description'   => 'required|string|max:255',
            'amount'        => 'required|numeric|min:0',
            'note'          => 'nullable|string',
        ]);
        $pettyCashTransaction->update([
            'petty_cash_id' => $request->petty_cash_id ?: null,
            'type'          => $request->type,
            'date'          => $request->date,
            'description'   => $request->description,
            'amount'        => $request->amount,
            'note'          => $request->note ?: null,
        ]);
        return response()->json(['message' => 'Transaksi berhasil diupdate.']);
    }

    public function destroy(PettyCashTransaction $pettyCashTransaction)
    {
        if (!auth()->user()->canDelete()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menghapus.'], 403);
        }
        $pettyCashTransaction->delete();
        return response()->json(['message' => 'Transaksi berhasil dihapus.']);
    }

    public function trash()
    {
        return view('petty-cash-transaction.trash');
    }

    public function trashData()
    {
        $rows = PettyCashTransaction::onlyTrashed()->with(['pettyCash', 'deleter'])
            ->orderBy('date', 'desc')->get()->map(fn($t) => [
                'id'              => $t->id,
                'date'            => $t->date->translatedFormat('d M Y'),
                'date_raw'        => $t->date->format('Y-m-d'),
                'type'            => $t->type,
                'description'     => $t->description,
                'amount'          => number_format($t->amount, 0, ',', '.'),
                'amount_raw'      => $t->amount,
                'note'            => $t->note ?? '-',
                'petty_cash_name' => $t->pettyCash?->name ?? '-',
                'deleted_at'      => $t->deleted_at->translatedFormat('d M Y H:i'),
                'deleted_by'      => $t->deleter?->name ?? 'N/A',
            ]);
        return response()->json(['data' => $rows]);
    }

    public function restore($id)
    {
        if (!auth()->user()->canRestore()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk restore.'], 403);
        }
        $t = PettyCashTransaction::onlyTrashed()->find($id);
        if (!$t) return response()->json(['message' => 'Transaksi tidak ditemukan.'], 404);
        $t->restore();
        return response()->json(['message' => 'Transaksi berhasil di-restore.']);
    }

    public function forceDelete($id)
    {
        if (!auth()->user()->canDelete()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menghapus.'], 403);
        }
        $t = PettyCashTransaction::onlyTrashed()->find($id);
        if (!$t) return response()->json(['message' => 'Transaksi tidak ditemukan.'], 404);
        $t->forceDelete();
        return response()->json(['message' => 'Transaksi berhasil dihapus permanen.']);
    }
}
