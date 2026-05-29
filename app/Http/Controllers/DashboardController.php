<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Expense;
use App\Models\Lori;
use App\Models\Stock;
use App\Models\PettyCashTransaction;
use App\Models\UangKoordinasi;

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
        $salesAmt    = (float) Sale::whereIn('status', ['approved', 'paid'])->whereYear('date', $thisYear)->whereMonth('date', $thisMonth)->sum('amount');
        $purchaseAmt = (float) Purchase::whereIn('status', ['approved', 'paid'])->whereYear('date', $thisYear)->whereMonth('date', $thisMonth)->sum('amount');
        $expenseAmt  = (float) Expense::whereYear('date', $thisYear)->whereMonth('date', $thisMonth)->sum('nominal');
        $pcDebitAmt  = (float) PettyCashTransaction::where('type', 'out')->whereYear('date', $thisYear)->whereMonth('date', $thisMonth)->sum('amount');
        $ukAmt       = (float) UangKoordinasi::where('status', 'approved')->whereYear('date', $thisYear)->whereMonth('date', $thisMonth)->sum('total');
        $totalExpenseAmt = $expenseAmt + $pcDebitAmt;
        $loriAmt     = (float) Lori::whereYear('date', $thisYear)->whereMonth('date', $thisMonth)->sum('price');
        // Penjualan - Pembelian - Petty Cash Pemakaian - Uang Koordinasi - Expenses Ship
        $profitAmt   = $salesAmt - $purchaseAmt - $pcDebitAmt - $ukAmt - $expenseAmt;
        $stockBal = Stock::currentBalance();

        $purchaseByWarna = Stock::where('type', 'purchase')
            ->selectRaw('warna, SUM(qty_in) as total')
            ->groupBy('warna')->pluck('total', 'warna');
        $purchaseLtr = (float) $purchaseByWarna->sum();

        $saleByWarna = Stock::where('type', 'sale')
            ->selectRaw('warna, SUM(qty_out) as total')
            ->groupBy('warna')->pluck('total', 'warna');
        $saleLtr = (float) $saleByWarna->sum();

        $saldoByWarna = collect(['biru', 'kuning'])->mapWithKeys(function ($w) {
            $in  = (float) Stock::where('warna', $w)->sum('qty_in');
            $out = (float) Stock::where('warna', $w)->sum('qty_out');
            return [$w => $in - $out];
        });

        // ── Last month (for trend) ───────────────────────────────────
        $salesPrev    = (float) Sale::whereIn('status', ['approved', 'paid'])->whereYear('date', $prevYear)->whereMonth('date', $prevMonth)->sum('amount');
        $purchasePrev = (float) Purchase::whereIn('status', ['approved', 'paid'])->whereYear('date', $prevYear)->whereMonth('date', $prevMonth)->sum('amount');

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
        $chartSales    = $chartMonths->map(fn($m) => (float) Sale::whereIn('status', ['approved', 'paid'])->whereYear('date', $m->year)->whereMonth('date', $m->month)->sum('amount'))->values();
        $chartPurchase = $chartMonths->map(fn($m) => (float) Purchase::whereIn('status', ['approved', 'paid'])->whereYear('date', $m->year)->whereMonth('date', $m->month)->sum('amount'))->values();
        $chartProfit   = $chartMonths->map(function ($m) {
            $s  = (float) Sale::whereIn('status', ['approved', 'paid'])->whereYear('date', $m->year)->whereMonth('date', $m->month)->sum('amount');
            $p  = (float) Purchase::whereIn('status', ['approved', 'paid'])->whereYear('date', $m->year)->whereMonth('date', $m->month)->sum('amount');
            $e  = (float) Expense::whereYear('date', $m->year)->whereMonth('date', $m->month)->sum('nominal');
            $pc = (float) PettyCashTransaction::where('type', 'out')->whereYear('date', $m->year)->whereMonth('date', $m->month)->sum('amount');
            $uk = (float) UangKoordinasi::where('status', 'approved')->whereYear('date', $m->year)->whereMonth('date', $m->month)->sum('total');
            return round($s - $p - $e - $pc - $uk, 2);
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

        $expenseAmt = $totalExpenseAmt;

        return view('dashboard.index', compact(
            'salesAmt', 'purchaseAmt', 'expenseAmt', 'loriAmt', 'profitAmt', 'stockBal',
            'purchaseLtr', 'saleLtr', 'purchaseByWarna', 'saleByWarna', 'saldoByWarna',
            'salesTrend', 'purchaseTrend',
            'chartLabels', 'chartSales', 'chartPurchase', 'chartProfit',
            'expByCategory', 'recentSales', 'recentExpenses'
        ));
    }
}
