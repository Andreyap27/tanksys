@extends('layouts.app')

@section('title', 'Laporan')

@section('content')

@php
    $months = [
        1  => 'Januari',   2  => 'Februari',  3  => 'Maret',
        4  => 'April',     5  => 'Mei',        6  => 'Juni',
        7  => 'Juli',      8  => 'Agustus',    9  => 'September',
        10 => 'Oktober',   11 => 'November',   12 => 'Desember',
    ];

    $categories = \App\Models\Expense::CATEGORIES;

    // Build expense matrix: [month][category] = total
    $expMatrix = [];
    foreach ($expensesByCategory as $m => $items) {
        foreach ($items as $item) {
            $expMatrix[(int)$m][$item->category] = (float)$item->total;
        }
    }

    $fmt    = fn($n) => number_format((float)$n, 0, ',', '.');
    $fmtQty = fn($n) => number_format((float)$n, 2, ',', '.');

    // Grand totals
    $gPurchaseQty = 0; $gPurchaseAmt = 0;
    $gSaleQty     = 0; $gSaleAmt     = 0;
    $gExpCat      = array_fill_keys($categories, 0);
    $gExpTotal    = 0; $gLoriAmt     = 0; $gLoriExp = 0;

    foreach (range(1, 12) as $m) {
        $gPurchaseQty += (float)($purchases->get($m)->total_qty    ?? 0);
        $gPurchaseAmt += (float)($purchases->get($m)->total_amount ?? 0);
        $gSaleQty     += (float)($sales->get($m)->total_qty        ?? 0);
        $gSaleAmt     += (float)($sales->get($m)->total_amount     ?? 0);
        $gExpTotal    += (float)($expensesTotal[$m]                ?? 0);
        foreach ($categories as $cat) {
            $gExpCat[$cat] += $expMatrix[$m][$cat] ?? 0;
        }
        $gLoriAmt += (float)($loris[$m]        ?? 0);
        $gLoriExp += (float)($loriExpenses[$m] ?? 0);
    }

    $gPL     = $gSaleAmt - $gPurchaseAmt - $gExpTotal;
    $gLoriPL = $gLoriAmt - $gLoriExp;
@endphp

<div class="page-header">
    <div>
        <h1 class="page-title-text">Laporan {{ $year }}</h1>
        <p class="page-subtitle">Rekapitulasi data tahunan</p>
    </div>
    <div class="page-actions">
        <form method="GET" action="{{ route('report.index') }}" style="display:flex;gap:0.5rem;align-items:center;" id="yearForm">
            <select name="year" class="form-select" style="width:auto;" onchange="document.getElementById('yearForm').submit()">
                @foreach($years as $y)
                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </form>
        <button class="btn btn-primary" onclick="window.print()">
            <i data-lucide="printer" style="width:15px;height:15px;"></i>
            Print
        </button>
    </div>
</div>

{{-- ── 1. Total Purchase ──────────────────────────────────────────────────────── --}}
<div class="card report-section" style="margin-bottom:1.25rem;">
    <div class="card-header">
        <div class="card-title">Total Purchase</div>
    </div>
    <div class="card-content" style="padding:0;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Bulan</th>
                        <th class="text-right">Total Quantity (L)</th>
                        <th class="text-right">Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($months as $m => $name)
                        @php
                            $qty = (float)($purchases->get($m)->total_qty    ?? 0);
                            $amt = (float)($purchases->get($m)->total_amount ?? 0);
                        @endphp
                        <tr>
                            <td>{{ $name }}</td>
                            <td class="text-right">{{ $qty ? $fmtQty($qty) : '-' }}</td>
                            <td class="text-right">{{ $amt ? 'Rp ' . $fmt($amt) : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="report-total-row">
                        <td><strong>Total</strong></td>
                        <td class="text-right"><strong>{{ $fmtQty($gPurchaseQty) }}</strong></td>
                        <td class="text-right"><strong>Rp {{ $fmt($gPurchaseAmt) }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

{{-- ── 2. Total Sale ──────────────────────────────────────────────────────────── --}}
<div class="card report-section" style="margin-bottom:1.25rem;">
    <div class="card-header">
        <div class="card-title">Total Sale</div>
    </div>
    <div class="card-content" style="padding:0;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Bulan</th>
                        <th class="text-right">Total Quantity (L)</th>
                        <th class="text-right">Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($months as $m => $name)
                        @php
                            $qty = (float)($sales->get($m)->total_qty    ?? 0);
                            $amt = (float)($sales->get($m)->total_amount ?? 0);
                        @endphp
                        <tr>
                            <td>{{ $name }}</td>
                            <td class="text-right">{{ $qty ? $fmtQty($qty) : '-' }}</td>
                            <td class="text-right">{{ $amt ? 'Rp ' . $fmt($amt) : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="report-total-row">
                        <td><strong>Total</strong></td>
                        <td class="text-right"><strong>{{ $fmtQty($gSaleQty) }}</strong></td>
                        <td class="text-right"><strong>Rp {{ $fmt($gSaleAmt) }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

{{-- ── 3. Total Expense ───────────────────────────────────────────────────────── --}}
<div class="card report-section" style="margin-bottom:1.25rem;">
    <div class="card-header">
        <div class="card-title">Total Expense</div>
    </div>
    <div class="card-content" style="padding:0;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Bulan</th>
                        @foreach($categories as $cat)
                            <th class="text-right">{{ $cat }}</th>
                        @endforeach
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($months as $m => $name)
                        @php $rowTotal = (float)($expensesTotal[$m] ?? 0); @endphp
                        <tr>
                            <td>{{ $name }}</td>
                            @foreach($categories as $cat)
                                @php $val = $expMatrix[$m][$cat] ?? 0; @endphp
                                <td class="text-right">{{ $val ? 'Rp ' . $fmt($val) : '-' }}</td>
                            @endforeach
                            <td class="text-right">{{ $rowTotal ? 'Rp ' . $fmt($rowTotal) : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="report-total-row">
                        <td><strong>Total</strong></td>
                        @foreach($categories as $cat)
                            <td class="text-right"><strong>{{ $gExpCat[$cat] ? 'Rp ' . $fmt($gExpCat[$cat]) : '-' }}</strong></td>
                        @endforeach
                        <td class="text-right"><strong>Rp {{ $fmt($gExpTotal) }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

{{-- ── 4. Profit / Loss ───────────────────────────────────────────────────────── --}}
<div class="card report-section" style="margin-bottom:1.25rem;">
    <div class="card-header">
        <div class="card-title">Profit / Loss</div>
    </div>
    <div class="card-content" style="padding:0;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Bulan</th>
                        <th class="text-right">Total Purchase</th>
                        <th class="text-right">Total Sales</th>
                        <th class="text-right">Total Expenses</th>
                        <th class="text-right">Profit / Loss</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($months as $m => $name)
                        @php
                            $pur = (float)($purchases->get($m)->total_amount ?? 0);
                            $sal = (float)($sales->get($m)->total_amount     ?? 0);
                            $exp = (float)($expensesTotal[$m]                ?? 0);
                            $pl  = $sal - $pur - $exp;
                            $hasData = $pur || $sal || $exp;
                        @endphp
                        <tr>
                            <td>{{ $name }}</td>
                            <td class="text-right">{{ $pur ? 'Rp ' . $fmt($pur) : '-' }}</td>
                            <td class="text-right">{{ $sal ? 'Rp ' . $fmt($sal) : '-' }}</td>
                            <td class="text-right">{{ $exp ? 'Rp ' . $fmt($exp) : '-' }}</td>
                            <td class="text-right" @if($hasData) style="color:{{ $pl >= 0 ? 'var(--success)' : 'var(--destructive)' }};font-weight:600;" @endif>
                                @if($hasData)
                                    {{ $pl < 0 ? '-' : '' }}Rp {{ $fmt(abs($pl)) }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="report-total-row">
                        <td><strong>Total</strong></td>
                        <td class="text-right"><strong>Rp {{ $fmt($gPurchaseAmt) }}</strong></td>
                        <td class="text-right"><strong>Rp {{ $fmt($gSaleAmt) }}</strong></td>
                        <td class="text-right"><strong>Rp {{ $fmt($gExpTotal) }}</strong></td>
                        <td class="text-right" style="color:{{ $gPL >= 0 ? 'var(--success)' : 'var(--destructive)' }};font-weight:700;">
                            <strong>{{ $gPL < 0 ? '-' : '' }}Rp {{ $fmt(abs($gPL)) }}</strong>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

{{-- ── 5. Total Mobil Tangki ──────────────────────────────────────────────────── --}}
<div class="card report-section">
    <div class="card-header">
        <div class="card-title">Total Mobil Tangki (Lori)</div>
    </div>
    <div class="card-content" style="padding:0;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Bulan</th>
                        <th class="text-right">Amount</th>
                        <th class="text-right">Expenses</th>
                        <th class="text-right">Profit / Loss</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($months as $m => $name)
                        @php
                            $income  = (float)($loris[$m]        ?? 0);
                            $loriExp = (float)($loriExpenses[$m] ?? 0);
                            $pl      = $income - $loriExp;
                            $hasData = $income || $loriExp;
                        @endphp
                        <tr>
                            <td>{{ $name }}</td>
                            <td class="text-right">{{ $income  ? 'Rp ' . $fmt($income)  : '-' }}</td>
                            <td class="text-right">{{ $loriExp ? 'Rp ' . $fmt($loriExp) : '-' }}</td>
                            <td class="text-right" @if($hasData) style="color:{{ $pl >= 0 ? 'var(--success)' : 'var(--destructive)' }};font-weight:600;" @endif>
                                @if($hasData)
                                    {{ $pl < 0 ? '-' : '' }}Rp {{ $fmt(abs($pl)) }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="report-total-row">
                        <td><strong>Total</strong></td>
                        <td class="text-right"><strong>Rp {{ $fmt($gLoriAmt) }}</strong></td>
                        <td class="text-right"><strong>Rp {{ $fmt($gLoriExp) }}</strong></td>
                        <td class="text-right" style="color:{{ $gLoriPL >= 0 ? 'var(--success)' : 'var(--destructive)' }};font-weight:700;">
                            <strong>{{ $gLoriPL < 0 ? '-' : '' }}Rp {{ $fmt(abs($gLoriPL)) }}</strong>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    lucide.createIcons();
</script>
@endpush
