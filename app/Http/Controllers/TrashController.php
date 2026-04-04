<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Capital;
use App\Models\Expense;
use App\Models\Lori;
use App\Models\LoriExpense;
use Illuminate\Http\Request;

class TrashController extends Controller
{
    public function index()
    {
        return view('trash.index');
    }

    public function data(Request $request)
    {
        $type = $request->get('type', 'all');
        $deletedRecords = collect();

        if ($type === 'all' || $type === 'purchase') {
            $purchases = Purchase::onlyTrashed()->with('creator')->get()->map(fn($p) => [
                'id'              => $p->id,
                'type'            => 'Purchase',
                'type_label'      => 'Pembelian',
                'date'            => $p->date->translatedFormat('d M Y'),
                'date_raw'        => $p->date->format('Y-m-d'),
                'description'     => $p->vendor ?? $p->description,
                'amount'          => number_format($p->amount, 0, ',', '.'),
                'amount_raw'      => $p->amount,
                'created_by'      => $p->creator?->name ?? '-',
                'deleted_at'      => $p->deleted_at->translatedFormat('d M Y H:i'),
                'deleted_at_raw'  => $p->deleted_at,
                'model_type'      => Purchase::class,
            ]);
            $deletedRecords = $deletedRecords->concat($purchases);
        }

        if ($type === 'all' || $type === 'sale') {
            $sales = Sale::onlyTrashed()->with('creator')->get()->map(fn($s) => [
                'id'              => $s->id,
                'type'            => 'Sale',
                'type_label'      => 'Penjualan',
                'date'            => $s->date->translatedFormat('d M Y'),
                'date_raw'        => $s->date->format('Y-m-d'),
                'description'     => $s->invoice_number ?? $s->description,
                'amount'          => number_format($s->amount, 0, ',', '.'),
                'amount_raw'      => $s->amount,
                'created_by'      => $s->creator?->name ?? '-',
                'deleted_at'      => $s->deleted_at->translatedFormat('d M Y H:i'),
                'deleted_at_raw'  => $s->deleted_at,
                'model_type'      => Sale::class,
            ]);
            $deletedRecords = $deletedRecords->concat($sales);
        }

        if ($type === 'all' || $type === 'capital') {
            $capitals = Capital::onlyTrashed()->with('creator')->get()->map(fn($c) => [
                'id'              => $c->id,
                'type'            => 'Capital',
                'type_label'      => 'Modal',
                'date'            => $c->date->translatedFormat('d M Y'),
                'date_raw'        => $c->date->format('Y-m-d'),
                'description'     => $c->name,
                'amount'          => number_format($c->nominal, 0, ',', '.'),
                'amount_raw'      => $c->nominal,
                'created_by'      => $c->creator?->name ?? '-',
                'deleted_at'      => $c->deleted_at->translatedFormat('d M Y H:i'),
                'deleted_at_raw'  => $c->deleted_at,
                'model_type'      => Capital::class,
            ]);
            $deletedRecords = $deletedRecords->concat($capitals);
        }

        if ($type === 'all' || $type === 'expense') {
            $expenses = Expense::onlyTrashed()->with('creator')->get()->map(fn($e) => [
                'id'              => $e->id,
                'type'            => 'Expense',
                'type_label'      => 'Pengeluaran',
                'date'            => $e->date->translatedFormat('d M Y'),
                'date_raw'        => $e->date->format('Y-m-d'),
                'description'     => $e->description,
                'amount'          => number_format($e->nominal, 0, ',', '.'),
                'amount_raw'      => $e->nominal,
                'created_by'      => $e->creator?->name ?? '-',
                'deleted_at'      => $e->deleted_at->translatedFormat('d M Y H:i'),
                'deleted_at_raw'  => $e->deleted_at,
                'model_type'      => Expense::class,
            ]);
            $deletedRecords = $deletedRecords->concat($expenses);
        }

        if ($type === 'all' || $type === 'lori') {
            $loris = Lori::onlyTrashed()->with('creator')->get()->map(fn($l) => [
                'id'              => $l->id,
                'type'            => 'Lori',
                'type_label'      => 'Lori',
                'date'            => $l->date->translatedFormat('d M Y'),
                'date_raw'        => $l->date->format('Y-m-d'),
                'description'     => $l->from . ' → ' . $l->to,
                'amount'          => number_format($l->price, 0, ',', '.'),
                'amount_raw'      => $l->price,
                'created_by'      => $l->creator?->name ?? '-',
                'deleted_at'      => $l->deleted_at->translatedFormat('d M Y H:i'),
                'deleted_at_raw'  => $l->deleted_at,
                'model_type'      => Lori::class,
            ]);
            $deletedRecords = $deletedRecords->concat($loris);
        }

        if ($type === 'all' || $type === 'lori_expense') {
            $loriExpenses = LoriExpense::onlyTrashed()->with('creator')->get()->map(fn($le) => [
                'id'              => $le->id,
                'type'            => 'LoriExpense',
                'type_label'      => 'Pengeluaran Lori',
                'date'            => $le->date->translatedFormat('d M Y'),
                'date_raw'        => $le->date->format('Y-m-d'),
                'description'     => $le->description,
                'amount'          => number_format($le->nominal, 0, ',', '.'),
                'amount_raw'      => $le->nominal,
                'created_by'      => $le->creator?->name ?? '-',
                'deleted_at'      => $le->deleted_at->translatedFormat('d M Y H:i'),
                'deleted_at_raw'  => $le->deleted_at,
                'model_type'      => LoriExpense::class,
            ]);
            $deletedRecords = $deletedRecords->concat($loriExpenses);
        }

        // Sort by deleted_at descending
        $deletedRecords = $deletedRecords->sortByDesc('deleted_at_raw')->values();

        return response()->json(['data' => $deletedRecords]);
    }

    public function restore(Request $request)
    {
        if (!auth()->user()->canRestore()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk restore data.'], 403);
        }

        $id = $request->get('id');
        $type = $request->get('type');

        $model = match ($type) {
            'Purchase' => Purchase::onlyTrashed()->find($id),
            'Sale' => Sale::onlyTrashed()->find($id),
            'Capital' => Capital::onlyTrashed()->find($id),
            'Expense' => Expense::onlyTrashed()->find($id),
            'Lori' => Lori::onlyTrashed()->find($id),
            'LoriExpense' => LoriExpense::onlyTrashed()->find($id),
            default => null
        };

        if (!$model) {
            return response()->json(['message' => 'Data tidak ditemukan.'], 404);
        }

        $model->restore();

        return response()->json(['message' => ucfirst(strtolower($type)) . ' berhasil di-restore.']);
    }

    public function forceDelete(Request $request)
    {
        if (!auth()->user()->canDelete()) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menghapus data.'], 403);
        }

        $id = $request->get('id');
        $type = $request->get('type');

        $model = match ($type) {
            'Purchase' => Purchase::onlyTrashed()->find($id),
            'Sale' => Sale::onlyTrashed()->find($id),
            'Capital' => Capital::onlyTrashed()->find($id),
            'Expense' => Expense::onlyTrashed()->find($id),
            'Lori' => Lori::onlyTrashed()->find($id),
            'LoriExpense' => LoriExpense::onlyTrashed()->find($id),
            default => null
        };

        if (!$model) {
            return response()->json(['message' => 'Data tidak ditemukan.'], 404);
        }

        $model->forceDelete();

        return response()->json(['message' => ucfirst(strtolower($type)) . ' berhasil dihapus permanen.']);
    }
}
