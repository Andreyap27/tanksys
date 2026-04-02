<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} — {{ $year }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 12px;
            color: #1a1a1a;
            background: #f5f5f5;
            min-height: 100vh;
        }
        .page {
            max-width: 960px;
            margin: 1.5rem auto;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0,0,0,0.10);
        }

        /* ── Company Header ──────────────────────────────────────── */
        .rpt-header {
            background: #fff;
            padding: 1.25rem 2rem 1rem;
            border-bottom: 3px solid #1a5cb8;
        }
        .rpt-logo {
            height: 60px;
            width: auto;
            object-fit: contain;
        }
        .rpt-company-name {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1a5cb8;
            text-decoration: underline;
            text-underline-offset: 5px;
            text-decoration-thickness: 2px;
            letter-spacing: 0.03em;
            line-height: 1.2;
            margin-bottom: 0.3rem;
        }
        .rpt-company-address {
            font-size: 0.75rem;
            color: #555;
            line-height: 1.55;
        }

        /* ── Report label ────────────────────────────────────────── */
        .rpt-label {
            background: #f0f4fb;
            padding: 0.75rem 2.25rem;
            border-bottom: 1px solid #dbeafe;
            display: flex;
            align-items: baseline;
            justify-content: space-between;
        }
        .rpt-label-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: #1a5cb8;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .rpt-label-year {
            font-size: 0.8rem;
            color: #64748b;
            font-weight: 500;
        }

        /* ── Body ────────────────────────────────────────────────── */
        .rpt-body { padding: 1.75rem 2.25rem; }

        /* ── Table ───────────────────────────────────────────────── */
        .rpt-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11.5px;
        }
        .rpt-table thead tr {
            background: #f0f4fb;
        }
        .rpt-table th {
            padding: 0.6rem 0.85rem;
            text-align: left;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #1a5cb8;
            border-bottom: 2px solid #1a5cb8;
            font-weight: 700;
            white-space: nowrap;
        }
        .rpt-table th.r,
        .rpt-table td.r { text-align: right; }
        .rpt-table tbody tr:nth-child(even) { background: #f8fafc; }
        .rpt-table tbody tr:hover { background: #eef3fb; }
        .rpt-table td {
            padding: 0.55rem 0.85rem;
            border-bottom: 1px solid #f0f4f8;
            color: #374151;
        }
        .rpt-table tfoot td {
            padding: 0.65rem 0.85rem;
            background: #eef2fb;
            border-top: 2px solid #1a5cb8;
            font-weight: 700;
            font-size: 12px;
            color: #1a1a1a;
        }
        .text-profit { color: #16a34a; font-weight: 700; }
        .text-loss   { color: #dc2626; font-weight: 700; }

        /* ── Print bar ───────────────────────────────────────────── */
        .print-bar {
            max-width: 960px;
            margin: 0.75rem auto 0;
            padding: 0 1rem;
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
        }
        .print-bar button {
            padding: 0.45rem 1.1rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .btn-print { background: #1a5cb8; color: #fff; }
        .btn-close  { background: #e5e7eb; color: #333; }

        /* ── In-iframe mode (paper preview) ─────────────────────── */
        body.in-modal {
            background: #d1d5db;
            min-height: 100%;
            padding: 1.5rem 1rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        body.in-modal .print-bar { display: none; }
        body.in-modal .page {
            margin: 0 auto;
            width: 100%;
            max-width: 960px;
            border-radius: 4px;
            box-shadow: 0 6px 32px rgba(0,0,0,0.22);
        }

        /* ── Print media ─────────────────────────────────────────── */
        @media print {
            body, body.in-modal {
                background: #fff !important;
                display: block !important;
                padding: 0 !important;
                min-height: unset !important;
                align-items: unset !important;
            }
            .print-bar { display: none !important; }
            .page, body.in-modal .page {
                margin: 0 !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
            }
            .rpt-table { font-size: 10px; }
            .rpt-table th, .rpt-table td, .rpt-table tfoot td { padding: 0.4rem 0.6rem; }
            .rpt-table tbody tr:hover { background: transparent !important; }
        }
    </style>
</head>
<body>
<script>
    if (window.self !== window.top) {
        document.addEventListener('DOMContentLoaded', function () {
            document.body.classList.add('in-modal');
        });
    }
</script>

<div class="print-bar">
    <button class="btn-close" onclick="window.close()">Tutup</button>
    <button class="btn-print" onclick="window.print()">&#128438; Cetak</button>
</div>

<div class="page">

    {{-- Company Header --}}
    <div class="rpt-header">
        <table style="width:100%;border-collapse:collapse;">
            <tr>
                <td style="width:20%;vertical-align:middle;">
                    @if(file_exists(public_path('images/logo.png')))
                        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="rpt-logo">
                    @endif
                </td>
                <td style="text-align:center;vertical-align:middle;">
                    <div class="rpt-company-name">PT. ANUGRAH ENERGI PETROLUM</div>
                    <div class="rpt-company-address">
                        Komplek The Residence Blok A 22 RT 05/RW 07, </br>
                        Kel. Patam Lestari, Kec. Sekupang, Kota Batam, Kepulauan Riau 29427
                    </div>
                </td>
                <td style="width:20%;"></td>
            </tr>
        </table>
    </div>

    {{-- Report Label --}}
    <div class="rpt-label">
        <span class="rpt-label-title">Laporan {{ $title }}</span>
        <span class="rpt-label-year">Tahun {{ $year }}{{ !empty($kapalName) ? ' — ' . $kapalName : '' }}{{ !empty($mobilName) ? ' — ' . $mobilName : '' }}</span>
    </div>

    {{-- Report Content --}}
    <div class="rpt-body">

        @php
            $fmt    = fn($n) => number_format((float)$n, 0, ',', '.');
            $fmtQty = fn($n) => number_format((float)$n, 2, ',', '.');
        @endphp

        {{-- ── Purchase ───────────────────────────────────────────── --}}
        @if($section === 'purchase')
            @php
                $gQty = 0; $gAmt = 0;
                foreach (range(1,12) as $m) {
                    $gQty += (float)($purchases->get($m)->total_qty    ?? 0);
                    $gAmt += (float)($purchases->get($m)->total_amount ?? 0);
                }
            @endphp
            <table class="rpt-table">
                <thead>
                    <tr>
                        <th>Bulan</th>
                        <th class="r">Total Quantity (L)</th>
                        <th class="r">Total Amount</th>
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
                            <td class="r">{{ $qty ? $fmtQty($qty) : '-' }}</td>
                            <td class="r">{{ $amt ? 'Rp '.$fmt($amt) : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td>Total</td>
                        <td class="r">{{ $fmtQty($gQty) }}</td>
                        <td class="r">Rp {{ $fmt($gAmt) }}</td>
                    </tr>
                </tfoot>
            </table>

        {{-- ── Sale ───────────────────────────────────────────────── --}}
        @elseif($section === 'sale')
            @php
                $gQty = 0; $gAmt = 0;
                foreach (range(1,12) as $m) {
                    $gQty += (float)($sales->get($m)->total_qty    ?? 0);
                    $gAmt += (float)($sales->get($m)->total_amount ?? 0);
                }
            @endphp
            <table class="rpt-table">
                <thead>
                    <tr>
                        <th>Bulan</th>
                        <th class="r">Total Quantity (L)</th>
                        <th class="r">Total Amount</th>
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
                            <td class="r">{{ $qty ? $fmtQty($qty) : '-' }}</td>
                            <td class="r">{{ $amt ? 'Rp '.$fmt($amt) : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td>Total</td>
                        <td class="r">{{ $fmtQty($gQty) }}</td>
                        <td class="r">Rp {{ $fmt($gAmt) }}</td>
                    </tr>
                </tfoot>
            </table>

        {{-- ── Expense ─────────────────────────────────────────────── --}}
        @elseif($section === 'expense')
            @php
                $expMatrix  = [];
                foreach ($expensesByCategory as $m => $items) {
                    foreach ($items as $item) {
                        $expMatrix[(int)$m][$item->category] = (float)$item->total;
                    }
                }
                $gExpCat   = array_fill_keys($categories, 0);
                $gExpTotal = 0;
                foreach (range(1,12) as $m) {
                    $gExpTotal += (float)($expensesTotal[$m] ?? 0);
                    foreach ($categories as $cat) { $gExpCat[$cat] += $expMatrix[$m][$cat] ?? 0; }
                }
            @endphp
            <table class="rpt-table">
                <thead>
                    <tr>
                        <th>Bulan</th>
                        @foreach($categories as $cat)<th class="r">{{ $cat }}</th>@endforeach
                        <th class="r">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($months as $m => $name)
                        @php $rowTotal = (float)($expensesTotal[$m] ?? 0); @endphp
                        <tr>
                            <td>{{ $name }}</td>
                            @foreach($categories as $cat)
                                @php $val = $expMatrix[$m][$cat] ?? 0; @endphp
                                <td class="r">{{ $val ? 'Rp '.$fmt($val) : '-' }}</td>
                            @endforeach
                            <td class="r">{{ $rowTotal ? 'Rp '.$fmt($rowTotal) : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td>Total</td>
                        @foreach($categories as $cat)
                            <td class="r">{{ $gExpCat[$cat] ? 'Rp '.$fmt($gExpCat[$cat]) : '-' }}</td>
                        @endforeach
                        <td class="r">Rp {{ $fmt($gExpTotal) }}</td>
                    </tr>
                </tfoot>
            </table>

        {{-- ── Profit / Loss ───────────────────────────────────────── --}}
        @elseif($section === 'profit-loss')
            @php
                $gPur = 0; $gSal = 0; $gExp = 0;
                foreach (range(1,12) as $m) {
                    $gPur += (float)($purchases->get($m)->total_amount ?? 0);
                    $gSal += (float)($sales->get($m)->total_amount    ?? 0);
                    $gExp += (float)($expensesTotal[$m]               ?? 0);
                }
                $gPL = $gSal - $gPur - $gExp;
            @endphp
            <table class="rpt-table">
                <thead>
                    <tr>
                        <th>Bulan</th>
                        <th class="r">Total Purchase</th>
                        <th class="r">Total Sales</th>
                        <th class="r">Total Expenses</th>
                        <th class="r">Profit / Loss</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($months as $m => $name)
                        @php
                            $pur     = (float)($purchases->get($m)->total_amount ?? 0);
                            $sal     = (float)($sales->get($m)->total_amount    ?? 0);
                            $exp     = (float)($expensesTotal[$m]               ?? 0);
                            $pl      = $sal - $pur - $exp;
                            $hasData = $pur || $sal || $exp;
                        @endphp
                        <tr>
                            <td>{{ $name }}</td>
                            <td class="r">{{ $pur ? 'Rp '.$fmt($pur) : '-' }}</td>
                            <td class="r">{{ $sal ? 'Rp '.$fmt($sal) : '-' }}</td>
                            <td class="r">{{ $exp ? 'Rp '.$fmt($exp) : '-' }}</td>
                            <td class="r {{ $hasData ? ($pl >= 0 ? 'text-profit' : 'text-loss') : '' }}">
                                @if($hasData) {{ $pl < 0 ? '-' : '' }}Rp {{ $fmt(abs($pl)) }} @else - @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td>Total</td>
                        <td class="r">Rp {{ $fmt($gPur) }}</td>
                        <td class="r">Rp {{ $fmt($gSal) }}</td>
                        <td class="r">Rp {{ $fmt($gExp) }}</td>
                        <td class="r {{ $gPL >= 0 ? 'text-profit' : 'text-loss' }}">
                            {{ $gPL < 0 ? '-' : '' }}Rp {{ $fmt(abs($gPL)) }}
                        </td>
                    </tr>
                </tfoot>
            </table>

        {{-- ── Lori Omset ──────────────────────────────────────────── --}}
        @elseif($section === 'lori-omset')
            @php
                $gTotal = 0;
                foreach (range(1,12) as $m) { $gTotal += (float)($loris[$m] ?? 0); }
            @endphp
            <table class="rpt-table">
                <thead>
                    <tr>
                        <th>Bulan</th>
                        <th class="r">Total Omset</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($months as $m => $name)
                        @php $income = (float)($loris[$m] ?? 0); @endphp
                        <tr>
                            <td>{{ $name }}</td>
                            <td class="r">{{ $income ? 'Rp '.$fmt($income) : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td>Total</td>
                        <td class="r">Rp {{ $fmt($gTotal) }}</td>
                    </tr>
                </tfoot>
            </table>

        {{-- ── Lori Expense ────────────────────────────────────────── --}}
        @elseif($section === 'lori-expense')
            @php
                $expMatrix  = [];
                foreach ($loriExpensesByCategory as $m => $items) {
                    foreach ($items as $item) {
                        $expMatrix[(int)$m][$item->category] = (float)$item->total;
                    }
                }
                $gCatTotal = array_fill_keys($cats, 0);
                $gTotal    = 0;
                foreach (range(1,12) as $m) {
                    $gTotal += (float)($loriExpensesTotal[$m] ?? 0);
                    foreach ($cats as $cat) { $gCatTotal[$cat] += $expMatrix[$m][$cat] ?? 0; }
                }
            @endphp
            <table class="rpt-table">
                <thead>
                    <tr>
                        <th>Bulan</th>
                        @foreach($cats as $cat)<th class="r">{{ $cat }}</th>@endforeach
                        <th class="r">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($months as $m => $name)
                        @php $rowTotal = (float)($loriExpensesTotal[$m] ?? 0); @endphp
                        <tr>
                            <td>{{ $name }}</td>
                            @foreach($cats as $cat)
                                @php $val = $expMatrix[$m][$cat] ?? 0; @endphp
                                <td class="r">{{ $val ? 'Rp '.$fmt($val) : '-' }}</td>
                            @endforeach
                            <td class="r">{{ $rowTotal ? 'Rp '.$fmt($rowTotal) : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td>Total</td>
                        @foreach($cats as $cat)
                            <td class="r">{{ $gCatTotal[$cat] ? 'Rp '.$fmt($gCatTotal[$cat]) : '-' }}</td>
                        @endforeach
                        <td class="r">Rp {{ $fmt($gTotal) }}</td>
                    </tr>
                </tfoot>
            </table>

        {{-- ── Lori ────────────────────────────────────────────────── --}}
        @elseif($section === 'lori')
            @php
                $gAmt = 0; $gExp = 0;
                foreach (range(1,12) as $m) {
                    $gAmt += (float)($loris[$m]        ?? 0);
                    $gExp += (float)($loriExpenses[$m] ?? 0);
                }
                $gPL = $gAmt - $gExp;
            @endphp
            <table class="rpt-table">
                <thead>
                    <tr>
                        <th>Bulan</th>
                        <th class="r">Amount</th>
                        <th class="r">Expenses</th>
                        <th class="r">Profit / Loss</th>
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
                            <td class="r">{{ $income  ? 'Rp '.$fmt($income)  : '-' }}</td>
                            <td class="r">{{ $loriExp ? 'Rp '.$fmt($loriExp) : '-' }}</td>
                            <td class="r {{ $hasData ? ($pl >= 0 ? 'text-profit' : 'text-loss') : '' }}">
                                @if($hasData) {{ $pl < 0 ? '-' : '' }}Rp {{ $fmt(abs($pl)) }} @else - @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td>Total</td>
                        <td class="r">Rp {{ $fmt($gAmt) }}</td>
                        <td class="r">Rp {{ $fmt($gExp) }}</td>
                        <td class="r {{ $gPL >= 0 ? 'text-profit' : 'text-loss' }}">
                            {{ $gPL < 0 ? '-' : '' }}Rp {{ $fmt(abs($gPL)) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        @endif

    </div>{{-- /rpt-body --}}
</div>{{-- /page --}}

</body>
</html>
