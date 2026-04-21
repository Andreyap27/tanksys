<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Bank In/Out</title>
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
        .rpt-header {
            background: #fff;
            padding: 1.25rem 2rem 1rem;
            border-bottom: 3px solid #1a5cb8;
        }
        .rpt-logo { height: 60px; width: auto; object-fit: contain; }
        .rpt-company-name {
            font-size: 1.5rem; font-weight: 600; color: #1a5cb8;
            text-decoration: underline; text-underline-offset: 5px;
            text-decoration-thickness: 2px; letter-spacing: 0.03em;
            line-height: 1.2; margin-bottom: 0.3rem;
        }
        .rpt-company-address { font-size: 0.75rem; color: #555; line-height: 1.55; }
        .rpt-label {
            background: #f0f4fb; padding: 0.75rem 2.25rem;
            border-bottom: 1px solid #dbeafe;
            display: flex; align-items: baseline; justify-content: space-between;
        }
        .rpt-label-title { font-size: 0.95rem; font-weight: 700; color: #1a5cb8; text-transform: uppercase; letter-spacing: 0.05em; }
        .rpt-label-year  { font-size: 0.8rem; color: #64748b; font-weight: 500; }
        .rpt-body { padding: 1.75rem 2.25rem; }
        .rpt-table { width: 100%; border-collapse: collapse; font-size: 11.5px; }
        .rpt-table thead tr { background: #f0f4fb; }
        .rpt-table th {
            padding: 0.6rem 0.85rem; text-align: left;
            font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em;
            color: #1a5cb8; border-bottom: 2px solid #1a5cb8; font-weight: 700; white-space: nowrap;
        }
        .rpt-table th.r, .rpt-table td.r { text-align: right; }
        .rpt-table tbody tr:nth-child(even) { background: #f8fafc; }
        .rpt-table td { padding: 0.55rem 0.85rem; border-bottom: 1px solid #f0f4f8; color: #374151; }
        .rpt-table tfoot td {
            padding: 0.65rem 0.85rem; background: #eef2fb;
            border-top: 2px solid #1a5cb8; font-weight: 700; font-size: 12px; color: #1a1a1a;
        }
        .text-profit { color: #16a34a !important; font-weight: 700; }
        .text-loss   { color: #dc2626 !important; font-weight: 700; }
        .badge-in  { background:#dcfce7; color:#16a34a; padding:2px 8px; border-radius:99px; font-weight:600; font-size:11px; }
        .badge-out { background:#fee2e2; color:#dc2626; padding:2px 8px; border-radius:99px; font-weight:600; font-size:11px; }
        .print-bar {
            max-width: 960px; margin: 0.75rem auto 0; padding: 0 1rem;
            display: flex; justify-content: flex-end; gap: 0.5rem;
        }
        .print-bar button { padding:0.45rem 1.1rem; border:none; border-radius:6px; cursor:pointer; font-size:0.8rem; font-weight:600; }
        .btn-print { background:#1a5cb8; color:#fff; }
        .btn-close  { background:#e5e7eb; color:#333; }
        body.in-modal { background:#d1d5db; min-height:100%; padding:1.5rem 1rem; display:flex; flex-direction:column; align-items:center; }
        body.in-modal .print-bar { display:none; }
        body.in-modal .page { margin:0 auto; width:100%; max-width:960px; border-radius:4px; box-shadow:0 6px 32px rgba(0,0,0,0.22); }
        @media print {
            body, body.in-modal { background:#fff !important; display:block !important; padding:0 !important; min-height:unset !important; align-items:unset !important; }
            .print-bar { display:none !important; }
            .page, body.in-modal .page { margin:0 !important; box-shadow:none !important; border-radius:0 !important; max-width:100% !important; width:100% !important; }
            .rpt-table { font-size:10px; }
            .rpt-table th, .rpt-table td, .rpt-table tfoot td { padding:0.4rem 0.6rem; }
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
                        Komplek The Residence Blok A 22 RT 05/RW 07,<br>
                        Kel. Patam Lestari, Kec. Sekupang, Kota Batam, Kepulauan Riau 29427
                    </div>
                </td>
                <td style="width:20%;"></td>
            </tr>
        </table>
    </div>

    {{-- Report Label --}}
    <div class="rpt-label">
        <span class="rpt-label-title">Laporan Bank In / Out</span>
        <span class="rpt-label-year">
            {{ $dateFrom->translatedFormat('d M Y') }} — {{ $dateTo->translatedFormat('d M Y') }}
        </span>
    </div>

    <div class="rpt-body">
        @php
            $fmt       = fn($n) => number_format((float)$n, 0, ',', '.');
            $totalIn   = $transactions->where('type', 'in')->sum('amount');
            $totalOut  = $transactions->where('type', 'out')->sum('amount');
            $balance   = $totalIn - $totalOut;
        @endphp

        {{-- Table --}}
        <table class="rpt-table">
            <thead>
                <tr>
                    <th style="width:3%">#</th>
                    <th>Tanggal</th>
                    <th>Deskripsi</th>
                    <th>Note</th>
                    <th>Job</th>
                    <th>Type</th>
                    <th class="r">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $i => $t)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $t->date->translatedFormat('d M Y') }}</td>
                    <td>{{ $t->description }}</td>
                    <td>{{ $t->note }}</td>
                    <td>{{ $t->job }}</td>
                    <td><span class="{{ $t->type === 'in' ? 'badge-in' : 'badge-out' }}">{{ $t->type === 'in' ? 'In (Kredit)' : 'Out (Debit)' }}</span></td>
                    <td class="r {{ $t->type === 'in' ? 'text-profit' : 'text-loss' }}">
                        Rp {{ $fmt($t->amount) }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;color:#94a3b8;padding:2rem;">Tidak ada data.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6">Total Income</td>
                    <td class="r text-profit">Rp {{ $fmt($totalIn) }}</td>
                </tr>
                <tr>
                    <td colspan="6">Total Expenses</td>
                    <td class="r text-loss">Rp {{ $fmt($totalOut) }}</td>
                </tr>
                <tr>
                    <td colspan="6">Balance</td>
                    <td class="r {{ $balance >= 0 ? 'text-profit' : 'text-loss' }}">
                        {{ $balance < 0 ? '-' : '' }}Rp {{ $fmt(abs($balance)) }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
</body>
</html>
