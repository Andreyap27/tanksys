<?php

namespace App\Http\Controllers;

use App\Models\Capital;
use App\Models\Kapal;
use App\Models\Mobil;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Expense;
use App\Models\Lori;
use App\Models\LoriExpense;

class ReportController extends Controller
{
    private function getYears(): \Illuminate\Support\Collection
    {
        return collect([
            Purchase::selectRaw('YEAR(date) as y')->distinct(),
            Sale::selectRaw('YEAR(date) as y')->distinct(),
            Expense::selectRaw('YEAR(date) as y')->distinct(),
            Lori::selectRaw('YEAR(date) as y')->distinct(),
            LoriExpense::selectRaw('YEAR(date) as y')->distinct(),
            Capital::selectRaw('YEAR(date) as y')->distinct(),
        ])->flatMap(fn($q) => $q->pluck('y'))
          ->push(now()->year)
          ->unique()->sort()->values();
    }

    private function getYear(): int
    {
        return (int) request('year', now()->year);
    }

    public function purchase()
    {
        $year     = $this->getYear();
        $years    = $this->getYears();
        $kapalId  = request('kapal_id') ?: null;
        $kapals   = Kapal::orderBy('code')->get();

        $purchases = Purchase::where('status', 'approved')
            ->selectRaw('MONTH(date) as month, SUM(quantity) as total_qty, SUM(amount) as total_amount')
            ->whereYear('date', $year)
            ->when($kapalId, fn($q) => $q->where('kapal_id', $kapalId))
            ->groupBy('month')
            ->get()->keyBy('month');

        return view('report.purchase', compact('year', 'years', 'purchases', 'kapals', 'kapalId'));
    }

    public function sale()
    {
        $year    = $this->getYear();
        $years   = $this->getYears();
        $kapalId = request('kapal_id') ?: null;
        $kapals  = Kapal::orderBy('code')->get();

        $sales = Sale::where('status', 'approved')
            ->selectRaw('MONTH(date) as month, SUM(quantity) as total_qty, SUM(amount) as total_amount')
            ->whereYear('date', $year)
            ->when($kapalId, fn($q) => $q->where('kapal_id', $kapalId))
            ->groupBy('month')
            ->get()->keyBy('month');

        return view('report.sale', compact('year', 'years', 'sales', 'kapals', 'kapalId'));
    }

    public function expense()
    {
        $year    = $this->getYear();
        $years   = $this->getYears();
        $kapalId = request('kapal_id') ?: null;
        $kapals  = Kapal::orderBy('code')->get();

        $expensesByCategory = Expense::selectRaw('MONTH(date) as month, category, SUM(nominal) as total')
            ->whereYear('date', $year)
            ->where('category', '!=', 'Lori')
            ->when($kapalId, fn($q) => $q->where('kapal_id', $kapalId))
            ->groupBy('month', 'category')
            ->get()->groupBy('month');

        $expensesTotal = Expense::selectRaw('MONTH(date) as month, SUM(nominal) as total')
            ->whereYear('date', $year)
            ->where('category', '!=', 'Lori')
            ->when($kapalId, fn($q) => $q->where('kapal_id', $kapalId))
            ->groupBy('month')
            ->pluck('total', 'month');

        return view('report.expense', compact('year', 'years', 'expensesByCategory', 'expensesTotal', 'kapals', 'kapalId'));
    }

    public function profitLoss()
    {
        $year    = $this->getYear();
        $years   = $this->getYears();
        $kapalId = request('kapal_id') ?: null;
        $kapals  = Kapal::orderBy('code')->get();

        $purchases = Purchase::where('status', 'approved')
            ->selectRaw('MONTH(date) as month, SUM(amount) as total_amount')
            ->whereYear('date', $year)
            ->when($kapalId, fn($q) => $q->where('kapal_id', $kapalId))
            ->groupBy('month')
            ->get()->keyBy('month');

        $sales = Sale::where('status', 'approved')
            ->selectRaw('MONTH(date) as month, SUM(amount) as total_amount')
            ->whereYear('date', $year)
            ->when($kapalId, fn($q) => $q->where('kapal_id', $kapalId))
            ->groupBy('month')
            ->get()->keyBy('month');

        $expensesTotal = Expense::selectRaw('MONTH(date) as month, SUM(nominal) as total')
            ->whereYear('date', $year)
            ->when($kapalId, fn($q) => $q->where('kapal_id', $kapalId))
            ->groupBy('month')
            ->pluck('total', 'month');

        return view('report.profit-loss', compact('year', 'years', 'purchases', 'sales', 'expensesTotal', 'kapals', 'kapalId'));
    }

    public function capital()
    {
        $year    = $this->getYear();
        $years   = $this->getYears();
        $kapalId = request('kapal_id') ?: null;
        $kapals  = Kapal::orderBy('code')->get();

        $capitals = Capital::where('status', 'approved')
            ->selectRaw('MONTH(date) as month, COUNT(*) as total_count, SUM(nominal) as total_nominal')
            ->whereYear('date', $year)
            ->when($kapalId, fn($q) => $q->where('kapal_id', $kapalId))
            ->groupBy('month')
            ->get()->keyBy('month');

        return view('report.capital', compact('year', 'years', 'capitals', 'kapals', 'kapalId'));
    }

    public function loriOmset()
    {
        $year    = $this->getYear();
        $years   = $this->getYears();
        $mobilId = request('mobil_id') ?: null;
        $mobils  = Mobil::orderBy('name')->get();

        $loris = Lori::selectRaw('MONTH(date) as month, SUM(price) as total_income')
            ->whereYear('date', $year)
            ->when($mobilId, fn($q) => $q->where('mobil_id', $mobilId))
            ->groupBy('month')
            ->pluck('total_income', 'month');

        return view('report.lori-omset', compact('year', 'years', 'loris', 'mobils', 'mobilId'));
    }

    public function loriExpense()
    {
        $year    = $this->getYear();
        $years   = $this->getYears();
        $mobilId = request('mobil_id') ?: null;
        $mobils  = Mobil::orderBy('name')->get();
        $cats    = LoriExpense::CATEGORIES;

        $loriExpensesByCategory = LoriExpense::selectRaw('MONTH(date) as month, category, SUM(nominal) as total')
            ->whereYear('date', $year)
            ->when($mobilId, fn($q) => $q->where('mobil_id', $mobilId))
            ->groupBy('month', 'category')
            ->get()->groupBy('month');

        $loriExpensesTotal = LoriExpense::selectRaw('MONTH(date) as month, SUM(nominal) as total')
            ->whereYear('date', $year)
            ->when($mobilId, fn($q) => $q->where('mobil_id', $mobilId))
            ->groupBy('month')
            ->pluck('total', 'month');

        return view('report.lori-expense', compact('year', 'years', 'loriExpensesByCategory', 'loriExpensesTotal', 'cats', 'mobils', 'mobilId'));
    }

    public function lori()
    {
        $year    = $this->getYear();
        $years   = $this->getYears();
        $mobilId = request('mobil_id') ?: null;
        $mobils  = Mobil::orderBy('name')->get();

        $loris = Lori::selectRaw('MONTH(date) as month, SUM(price) as total_income')
            ->whereYear('date', $year)
            ->when($mobilId, fn($q) => $q->where('mobil_id', $mobilId))
            ->groupBy('month')
            ->pluck('total_income', 'month');

        $loriExpenses = LoriExpense::selectRaw('MONTH(date) as month, SUM(nominal) as total')
            ->whereYear('date', $year)
            ->when($mobilId, fn($q) => $q->where('mobil_id', $mobilId))
            ->groupBy('month')
            ->pluck('total', 'month');

        return view('report.lori', compact('year', 'years', 'loris', 'loriExpenses', 'mobils', 'mobilId'));
    }

    public function purchaseTrash()
    {
        $year     = $this->getYear();
        $years    = $this->getYears();
        $kapalId  = request('kapal_id') ?: null;
        $kapals   = Kapal::orderBy('code')->get();

        $purchases = Purchase::onlyTrashed()
            ->selectRaw('MONTH(date) as month, SUM(quantity) as total_qty, SUM(amount) as total_amount')
            ->whereYear('date', $year)
            ->when($kapalId, fn($q) => $q->where('kapal_id', $kapalId))
            ->groupBy('month')
            ->get()->keyBy('month');

        return view('report.purchase-trash', compact('year', 'years', 'purchases', 'kapals', 'kapalId'));
    }

    public function saleTrash()
    {
        $year    = $this->getYear();
        $years   = $this->getYears();
        $kapalId = request('kapal_id') ?: null;
        $kapals  = Kapal::orderBy('code')->get();

        $sales = Sale::onlyTrashed()
            ->selectRaw('MONTH(date) as month, SUM(quantity) as total_qty, SUM(amount) as total_amount')
            ->whereYear('date', $year)
            ->when($kapalId, fn($q) => $q->where('kapal_id', $kapalId))
            ->groupBy('month')
            ->get()->keyBy('month');

        return view('report.sale-trash', compact('year', 'years', 'sales', 'kapals', 'kapalId'));
    }

    public function expenseTrash()
    {
        $year    = $this->getYear();
        $years   = $this->getYears();
        $kapalId = request('kapal_id') ?: null;
        $kapals  = Kapal::orderBy('code')->get();

        $expensesByCategory = Expense::onlyTrashed()
            ->selectRaw('MONTH(date) as month, category, SUM(nominal) as total')
            ->whereYear('date', $year)
            ->where('category', '!=', 'Lori')
            ->when($kapalId, fn($q) => $q->where('kapal_id', $kapalId))
            ->groupBy('month', 'category')
            ->get()->groupBy('month');

        $expensesTotal = Expense::onlyTrashed()
            ->selectRaw('MONTH(date) as month, SUM(nominal) as total')
            ->whereYear('date', $year)
            ->where('category', '!=', 'Lori')
            ->when($kapalId, fn($q) => $q->where('kapal_id', $kapalId))
            ->groupBy('month')
            ->pluck('total', 'month');

        return view('report.expense-trash', compact('year', 'years', 'expensesByCategory', 'expensesTotal', 'kapals', 'kapalId'));
    }

    public function capitalTrash()
    {
        $year    = $this->getYear();
        $years   = $this->getYears();
        $kapalId = request('kapal_id') ?: null;
        $kapals  = Kapal::orderBy('code')->get();

        $capitals = Capital::onlyTrashed()
            ->selectRaw('MONTH(date) as month, COUNT(*) as total_count, SUM(nominal) as total_nominal')
            ->whereYear('date', $year)
            ->when($kapalId, fn($q) => $q->where('kapal_id', $kapalId))
            ->groupBy('month')
            ->get()->keyBy('month');

        return view('report.capital-trash', compact('year', 'years', 'capitals', 'kapals', 'kapalId'));
    }

    public function loriTrash()
    {
        $year    = $this->getYear();
        $years   = $this->getYears();
        $mobilId = request('mobil_id') ?: null;
        $mobils  = Mobil::orderBy('name')->get();

        $loris = Lori::onlyTrashed()
            ->selectRaw('MONTH(date) as month, SUM(price) as total_income')
            ->whereYear('date', $year)
            ->when($mobilId, fn($q) => $q->where('mobil_id', $mobilId))
            ->groupBy('month')
            ->pluck('total_income', 'month');

        $loriExpenses = LoriExpense::onlyTrashed()
            ->selectRaw('MONTH(date) as month, SUM(nominal) as total')
            ->whereYear('date', $year)
            ->when($mobilId, fn($q) => $q->where('mobil_id', $mobilId))
            ->groupBy('month')
            ->pluck('total', 'month');

        return view('report.lori-trash', compact('year', 'years', 'loris', 'loriExpenses', 'mobils', 'mobilId'));
    }

    public function loriExpenseTrash()
    {
        $year    = $this->getYear();
        $years   = $this->getYears();
        $mobilId = request('mobil_id') ?: null;
        $mobils  = Mobil::orderBy('name')->get();
        $cats    = LoriExpense::CATEGORIES;

        $loriExpensesByCategory = LoriExpense::onlyTrashed()
            ->selectRaw('MONTH(date) as month, category, SUM(nominal) as total')
            ->whereYear('date', $year)
            ->when($mobilId, fn($q) => $q->where('mobil_id', $mobilId))
            ->groupBy('month', 'category')
            ->get()->groupBy('month');

        $loriExpensesTotal = LoriExpense::onlyTrashed()
            ->selectRaw('MONTH(date) as month, SUM(nominal) as total')
            ->whereYear('date', $year)
            ->when($mobilId, fn($q) => $q->where('mobil_id', $mobilId))
            ->groupBy('month')
            ->pluck('total', 'month');

        return view('report.lori-expense-trash', compact('year', 'years', 'loriExpensesByCategory', 'loriExpensesTotal', 'cats', 'mobils', 'mobilId'));
    }

    public function printReport()
    {
        $year      = $this->getYear();
        $section   = request('section', 'purchase');
        $kapalId   = request('kapal_id') ?: null;
        $kapalName = $kapalId ? optional(Kapal::find($kapalId))->name : null;
        $mobilId   = request('mobil_id') ?: null;
        $mobilName = $mobilId ? optional(Mobil::find($mobilId))->name : null;
        $isTrash   = request('trash', false);

        $months = [
            1=>'Januari', 2=>'Februari', 3=>'Maret',    4=>'April',
            5=>'Mei',     6=>'Juni',     7=>'Juli',      8=>'Agustus',
            9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember',
        ];

        $data = compact('year', 'section', 'months', 'kapalId', 'kapalName', 'mobilId', 'mobilName', 'isTrash');

        switch ($section) {
            case 'purchase':
                $query = $isTrash ? Purchase::onlyTrashed() : Purchase::where('status', 'approved');
                $data['purchases'] = $query
                    ->selectRaw('MONTH(date) as month, SUM(quantity) as total_qty, SUM(amount) as total_amount')
                    ->whereYear('date', $year)
                    ->when($kapalId, fn($q) => $q->where('kapal_id', $kapalId))
                    ->groupBy('month')->get()->keyBy('month');
                $data['title'] = $isTrash ? 'Total Purchase (Trash)' : 'Total Purchase';
                break;
            case 'sale':
                $query = $isTrash ? Sale::onlyTrashed() : Sale::where('status', 'approved');
                $data['sales'] = $query
                    ->selectRaw('MONTH(date) as month, SUM(quantity) as total_qty, SUM(amount) as total_amount')
                    ->whereYear('date', $year)
                    ->when($kapalId, fn($q) => $q->where('kapal_id', $kapalId))
                    ->groupBy('month')->get()->keyBy('month');
                $data['title'] = $isTrash ? 'Total Sale (Trash)' : 'Total Sale';
                break;
            case 'expense':
                $allExpCats    = \App\Models\Expense::EXPENSE_CATEGORIES;
                $selectedCats  = request()->has('categories')
                    ? array_values(array_intersect(request('categories', []), $allExpCats))
                    : $allExpCats;
                if (empty($selectedCats)) $selectedCats = $allExpCats;
                $data['categories'] = $selectedCats;
                $query = $isTrash ? Expense::onlyTrashed() : Expense::query();
                $data['expensesByCategory'] = $query->selectRaw('MONTH(date) as month, category, SUM(nominal) as total')
                    ->whereYear('date', $year)->whereIn('category', $selectedCats)
                    ->when($kapalId, fn($q) => $q->where('kapal_id', $kapalId))
                    ->groupBy('month', 'category')->get()->groupBy('month');
                $query = $isTrash ? Expense::onlyTrashed() : Expense::query();
                $data['expensesTotal'] = $query->selectRaw('MONTH(date) as month, SUM(nominal) as total')
                    ->whereYear('date', $year)->whereIn('category', $selectedCats)
                    ->when($kapalId, fn($q) => $q->where('kapal_id', $kapalId))
                    ->groupBy('month')->pluck('total', 'month');
                $data['title'] = $isTrash ? 'Total Expense (Trash)' : 'Total Expense';
                break;
            case 'profit-loss':
                $data['purchases'] = Purchase::where('status', 'approved')
                    ->selectRaw('MONTH(date) as month, SUM(amount) as total_amount')
                    ->whereYear('date', $year)
                    ->when($kapalId, fn($q) => $q->where('kapal_id', $kapalId))
                    ->groupBy('month')->get()->keyBy('month');
                $data['sales'] = Sale::where('status', 'approved')
                    ->selectRaw('MONTH(date) as month, SUM(amount) as total_amount')
                    ->whereYear('date', $year)
                    ->when($kapalId, fn($q) => $q->where('kapal_id', $kapalId))
                    ->groupBy('month')->get()->keyBy('month');
                $data['expensesTotal'] = Expense::selectRaw('MONTH(date) as month, SUM(nominal) as total')
                    ->whereYear('date', $year)
                    ->when($kapalId, fn($q) => $q->where('kapal_id', $kapalId))
                    ->groupBy('month')->pluck('total', 'month');
                $data['title'] = 'Profit / Loss';
                break;
            case 'capital':
                $query = $isTrash ? Capital::onlyTrashed() : Capital::where('status', 'approved');
                $data['capitals'] = $query
                    ->selectRaw('MONTH(date) as month, COUNT(*) as total_count, SUM(nominal) as total_nominal')
                    ->whereYear('date', $year)
                    ->when($kapalId, fn($q) => $q->where('kapal_id', $kapalId))
                    ->groupBy('month')->get()->keyBy('month');
                $data['title'] = $isTrash ? 'Total Capital (Trash)' : 'Total Capital';
                break;
            case 'lori-omset':
                $data['loris'] = Lori::selectRaw('MONTH(date) as month, SUM(price) as total_income')
                    ->whereYear('date', $year)
                    ->when($mobilId, fn($q) => $q->where('mobil_id', $mobilId))
                    ->groupBy('month')->pluck('total_income', 'month');
                $data['title'] = 'Omset Mobil Tangki';
                break;
            case 'lori-expense':
                $allLoriCats  = LoriExpense::CATEGORIES;
                $selectedCats = request()->has('categories')
                    ? array_values(array_intersect(request('categories', []), $allLoriCats))
                    : $allLoriCats;
                if (empty($selectedCats)) $selectedCats = $allLoriCats;
                $cats = $selectedCats;
                $data['cats'] = $cats;
                $query = $isTrash ? LoriExpense::onlyTrashed() : LoriExpense::query();
                $data['loriExpensesByCategory'] = $query->selectRaw('MONTH(date) as month, category, SUM(nominal) as total')
                    ->whereYear('date', $year)->whereIn('category', $cats)
                    ->when($mobilId, fn($q) => $q->where('mobil_id', $mobilId))
                    ->groupBy('month', 'category')->get()->groupBy('month');
                $query = $isTrash ? LoriExpense::onlyTrashed() : LoriExpense::query();
                $data['loriExpensesTotal'] = $query->selectRaw('MONTH(date) as month, SUM(nominal) as total')
                    ->whereYear('date', $year)->whereIn('category', $cats)
                    ->when($mobilId, fn($q) => $q->where('mobil_id', $mobilId))
                    ->groupBy('month')->pluck('total', 'month');
                $data['title'] = $isTrash ? 'Expenses Mobil Tangki (Trash)' : 'Expenses Mobil Tangki';
                break;
            case 'lori':
                $query = $isTrash ? Lori::onlyTrashed() : Lori::query();
                $data['loris'] = $query->selectRaw('MONTH(date) as month, SUM(price) as total_income')
                    ->whereYear('date', $year)
                    ->when($mobilId, fn($q) => $q->where('mobil_id', $mobilId))
                    ->groupBy('month')->pluck('total_income', 'month');
                $query = $isTrash ? LoriExpense::onlyTrashed() : LoriExpense::query();
                $data['loriExpenses'] = $query->selectRaw('MONTH(date) as month, SUM(nominal) as total')
                    ->whereYear('date', $year)
                    ->when($mobilId, fn($q) => $q->where('mobil_id', $mobilId))
                    ->groupBy('month')->pluck('total', 'month');
                $data['title'] = $isTrash ? 'Profit / Loss Mobil Tangki (Trash)' : 'Profit / Loss Mobil Tangki';
                break;
            default:
                abort(404);
        }

        return view('report.print', $data);
    }
}
