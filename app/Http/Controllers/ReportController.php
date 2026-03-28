<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Expense;
use App\Models\Lori;

class ReportController extends Controller
{
    private function getYears(): \Illuminate\Support\Collection
    {
        return collect([
            Purchase::selectRaw('YEAR(date) as y')->distinct(),
            Sale::selectRaw('YEAR(date) as y')->distinct(),
            Expense::selectRaw('YEAR(date) as y')->distinct(),
            Lori::selectRaw('YEAR(date) as y')->distinct(),
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
        $year  = $this->getYear();
        $years = $this->getYears();

        $purchases = Purchase::selectRaw('MONTH(date) as month, SUM(quantity) as total_qty, SUM(amount) as total_amount')
            ->whereYear('date', $year)
            ->groupBy('month')
            ->get()->keyBy('month');

        return view('report.purchase', compact('year', 'years', 'purchases'));
    }

    public function sale()
    {
        $year  = $this->getYear();
        $years = $this->getYears();

        $sales = Sale::selectRaw('MONTH(date) as month, SUM(quantity) as total_qty, SUM(amount) as total_amount')
            ->whereYear('date', $year)
            ->groupBy('month')
            ->get()->keyBy('month');

        return view('report.sale', compact('year', 'years', 'sales'));
    }

    public function expense()
    {
        $year  = $this->getYear();
        $years = $this->getYears();

        $expensesByCategory = Expense::selectRaw('MONTH(date) as month, category, SUM(nominal) as total')
            ->whereYear('date', $year)
            ->groupBy('month', 'category')
            ->get()->groupBy('month');

        $expensesTotal = Expense::selectRaw('MONTH(date) as month, SUM(nominal) as total')
            ->whereYear('date', $year)
            ->groupBy('month')
            ->pluck('total', 'month');

        return view('report.expense', compact('year', 'years', 'expensesByCategory', 'expensesTotal'));
    }

    public function profitLoss()
    {
        $year  = $this->getYear();
        $years = $this->getYears();

        $purchases = Purchase::selectRaw('MONTH(date) as month, SUM(amount) as total_amount')
            ->whereYear('date', $year)
            ->groupBy('month')
            ->get()->keyBy('month');

        $sales = Sale::selectRaw('MONTH(date) as month, SUM(amount) as total_amount')
            ->whereYear('date', $year)
            ->groupBy('month')
            ->get()->keyBy('month');

        $expensesTotal = Expense::selectRaw('MONTH(date) as month, SUM(nominal) as total')
            ->whereYear('date', $year)
            ->groupBy('month')
            ->pluck('total', 'month');

        return view('report.profit-loss', compact('year', 'years', 'purchases', 'sales', 'expensesTotal'));
    }

    public function lori()
    {
        $year  = $this->getYear();
        $years = $this->getYears();

        $loris = Lori::selectRaw('MONTH(date) as month, SUM(price) as total_income')
            ->whereYear('date', $year)
            ->groupBy('month')
            ->pluck('total_income', 'month');

        $loriExpenses = Expense::selectRaw('MONTH(date) as month, SUM(nominal) as total')
            ->whereYear('date', $year)
            ->where('category', 'Lori')
            ->groupBy('month')
            ->pluck('total', 'month');

        return view('report.lori', compact('year', 'years', 'loris', 'loriExpenses'));
    }

    public function printReport()
    {
        $year    = $this->getYear();
        $section = request('section', 'purchase');

        $months = [
            1=>'Januari', 2=>'Februari', 3=>'Maret',    4=>'April',
            5=>'Mei',     6=>'Juni',     7=>'Juli',      8=>'Agustus',
            9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember',
        ];

        $data = compact('year', 'section', 'months');

        switch ($section) {
            case 'purchase':
                $data['purchases'] = Purchase::selectRaw('MONTH(date) as month, SUM(quantity) as total_qty, SUM(amount) as total_amount')
                    ->whereYear('date', $year)->groupBy('month')->get()->keyBy('month');
                $data['title'] = 'Total Purchase';
                break;
            case 'sale':
                $data['sales'] = Sale::selectRaw('MONTH(date) as month, SUM(quantity) as total_qty, SUM(amount) as total_amount')
                    ->whereYear('date', $year)->groupBy('month')->get()->keyBy('month');
                $data['title'] = 'Total Sale';
                break;
            case 'expense':
                $data['expensesByCategory'] = Expense::selectRaw('MONTH(date) as month, category, SUM(nominal) as total')
                    ->whereYear('date', $year)->groupBy('month', 'category')->get()->groupBy('month');
                $data['expensesTotal'] = Expense::selectRaw('MONTH(date) as month, SUM(nominal) as total')
                    ->whereYear('date', $year)->groupBy('month')->pluck('total', 'month');
                $data['title'] = 'Total Expense';
                break;
            case 'profit-loss':
                $data['purchases'] = Purchase::selectRaw('MONTH(date) as month, SUM(amount) as total_amount')
                    ->whereYear('date', $year)->groupBy('month')->get()->keyBy('month');
                $data['sales'] = Sale::selectRaw('MONTH(date) as month, SUM(amount) as total_amount')
                    ->whereYear('date', $year)->groupBy('month')->get()->keyBy('month');
                $data['expensesTotal'] = Expense::selectRaw('MONTH(date) as month, SUM(nominal) as total')
                    ->whereYear('date', $year)->groupBy('month')->pluck('total', 'month');
                $data['title'] = 'Profit / Loss';
                break;
            case 'lori':
                $data['loris'] = Lori::selectRaw('MONTH(date) as month, SUM(price) as total_income')
                    ->whereYear('date', $year)->groupBy('month')->pluck('total_income', 'month');
                $data['loriExpenses'] = Expense::selectRaw('MONTH(date) as month, SUM(nominal) as total')
                    ->whereYear('date', $year)->where('category', 'Lori')->groupBy('month')->pluck('total', 'month');
                $data['title'] = 'Total Mobil Tangki (Lori)';
                break;
            default:
                abort(404);
        }

        return view('report.print', $data);
    }
}
