<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Expense;
use App\Models\Lori;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        $year = request('year', now()->year);

        $purchases = Purchase::selectRaw('MONTH(date) as month, SUM(quantity) as total_qty, SUM(amount) as total_amount')
            ->whereYear('date', $year)
            ->groupBy('month')
            ->pluck(null, 'month');

        $sales = Sale::selectRaw('MONTH(date) as month, SUM(quantity) as total_qty, SUM(amount) as total_amount')
            ->whereYear('date', $year)
            ->groupBy('month')
            ->pluck(null, 'month');

        $expensesByCategory = Expense::selectRaw('MONTH(date) as month, category, SUM(nominal) as total')
            ->whereYear('date', $year)
            ->groupBy('month', 'category')
            ->get()
            ->groupBy('month');

        $expensesTotal = Expense::selectRaw('MONTH(date) as month, SUM(nominal) as total')
            ->whereYear('date', $year)
            ->groupBy('month')
            ->pluck('total', 'month');

        $loris = Lori::selectRaw('MONTH(date) as month, SUM(price) as total_income')
            ->whereYear('date', $year)
            ->groupBy('month')
            ->pluck('total_income', 'month');

        $loriExpenses = Expense::selectRaw('MONTH(date) as month, SUM(nominal) as total')
            ->whereYear('date', $year)
            ->where('category', 'Lori')
            ->groupBy('month')
            ->pluck('total', 'month');

        return view('dashboard.report.index', compact(
            'year', 'purchases', 'sales',
            'expensesByCategory', 'expensesTotal',
            'loris', 'loriExpenses'
        ));
    }
}
