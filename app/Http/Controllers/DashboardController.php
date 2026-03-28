<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Expense;
use App\Models\Lori;
use App\Models\Stock;

class DashboardController extends Controller
{
    public function index()
    {
        $now       = now();
        $thisMonth = $now->month;
        $thisYear  = $now->year;
        $prevMonth = $now->copy()->subMonth()->month;
        $prevYear  = $now->copy()->subMonth()->year;

        // ── This month ──────────────────────────────────────────────
        $salesAmt    = (float) Sale::where('status', 'approved')->whereYear('date', $thisYear)->whereMonth('date', $thisMonth)->sum('amount');
        $purchaseAmt = (float) Purchase::where('status', 'approved')->whereYear('date', $thisYear)->whereMonth('date', $thisMonth)->sum('amount');
        $expenseAmt  = (float) Expense::whereYear('date', $thisYear)->whereMonth('date', $thisMonth)->sum('nominal');
        $loriAmt     = (float) Lori::whereYear('date', $thisYear)->whereMonth('date', $thisMonth)->sum('price');
        $profitAmt   = $salesAmt - $purchaseAmt - $expenseAmt;
        $stockBal    = Stock::currentBalance();

        // ── Last month (for trend) ───────────────────────────────────
        $salesPrev    = (float) Sale::where('status', 'approved')->whereYear('date', $prevYear)->whereMonth('date', $prevMonth)->sum('amount');
        $purchasePrev = (float) Purchase::where('status', 'approved')->whereYear('date', $prevYear)->whereMonth('date', $prevMonth)->sum('amount');

        $trend = function ($now, $prev) {
            if ($prev == 0) return $now > 0 ? ['pct' => null, 'dir' => 'new'] : ['pct' => null, 'dir' => 'flat'];
            $pct = (($now - $prev) / $prev) * 100;
            return ['pct' => round(abs($pct), 1), 'dir' => $pct >= 0 ? 'up' : 'down'];
        };

        $salesTrend    = $trend($salesAmt, $salesPrev);
        $purchaseTrend = $trend($purchaseAmt, $purchasePrev);

        // ── Chart: last 6 months ────────────────────────────────────
        $chartMonths = collect(range(5, 0))->map(fn($i) => $now->copy()->subMonths($i));

        $chartLabels   = $chartMonths->map(fn($m) => $m->translatedFormat('M Y'))->values();
        $chartSales    = $chartMonths->map(fn($m) => (float) Sale::where('status', 'approved')->whereYear('date', $m->year)->whereMonth('date', $m->month)->sum('amount'))->values();
        $chartPurchase = $chartMonths->map(fn($m) => (float) Purchase::where('status', 'approved')->whereYear('date', $m->year)->whereMonth('date', $m->month)->sum('amount'))->values();
        $chartProfit   = $chartSales->zip($chartPurchase)->map(function ($pair) use ($chartMonths) {
            [$s, $p] = $pair;
            return round($s - $p, 2);
        })->values();

        // ── Expenses by category (this month) ───────────────────────
        $expByCategory = Expense::whereYear('date', $thisYear)
            ->whereMonth('date', $thisMonth)
            ->selectRaw('category, SUM(nominal) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        // ── Recent transactions ──────────────────────────────────────
        $recentSales    = Sale::with('customer')->latest('date')->limit(5)->get();
        $recentExpenses = Expense::latest('date')->limit(5)->get();

        return view('dashboard.index', compact(
            'salesAmt', 'purchaseAmt', 'expenseAmt', 'loriAmt', 'profitAmt', 'stockBal',
            'salesTrend', 'purchaseTrend',
            'chartLabels', 'chartSales', 'chartPurchase', 'chartProfit',
            'expByCategory', 'recentSales', 'recentExpenses'
        ));
    }
}
