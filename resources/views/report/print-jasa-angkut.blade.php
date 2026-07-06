<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Jasa Angkut — {{ $year }}</title>
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
            background: #f0f4fb;
            padding: 0.75rem 2.25rem;
            border-bottom: 1px solid #dbeafe;
            display: flex; align-items: baseline; justify-content: space-between;
        }
        .rpt-label-title {
            font-size: 0.95rem; font-weight: 700; color: #1a5cb8;
            text-transform: uppercase; letter-spacing: 0.05em;
        }
        .rpt-label-year { font-size: 0.8rem; color: #64748b; font-weight: 500; }
        .rpt-body { padding: 1.75rem 2.25rem; }
        .rpt-table { width: 100%; border-collapse: collapse; font-size: 11.5px; }
        .rpt-table th {
            padding: 0.6rem 0.85rem; text-align: left;
            font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em;
            color: #1a5cb8; border-bottom: 2px solid #1a5cb8; font-weight: 700; white-space: nowrap;
        }
        .rpt-table th.r, .rpt-table td.r { text-align: right; }
        .rpt-table td { padding: 0.5rem 0.85rem; border-bottom: 1px solid #f0f4f8; color: #374151; }
        .rpt-table tfoot td {
            padding: 0.65rem 0.85rem; background: #eef2fb;
            border-top: 2px solid #1a5cb8; font-weight: 700; font-size: 12px; color: #1a1a1a;
        }
        .month-header td {
            background: #f0f4fb; font-weight: 700; color: #1a5cb8;
            padding: 0.45rem 0.85rem; font-size: 0.75rem;
            text-transform: uppercase; letter-spacing: 0.04em;
            border-top: 2px solid #dbeafe; border-bottom: 1px solid #dbeafe;
        }
        .month-subtotal td {
            background: #f8fafc; font-weight: 600;
            padding: 0.5rem 0.85rem; border-top: 1px solid #e2e8f0;
            border-bottom: 2px solid #e2e8f0;
        }
        .rpt-table tbody tr:not(.month-header):not(.month-subtotal):nth-child(even) { background: #f8fafc; }
        .print-bar {
            max-width: 960px; margin: 0.75rem auto 0; padding: 0 1rem;
            display: flex; justify-content: flex-end; gap: 0.5rem;
        }
        .print-bar button {
            padding: 0.45rem 1.1rem; border: none; border-radius: 6px;
            cursor: pointer; font-size: 0.8rem; font-weight: 600;
        }
        .btn-print { background: #1a5cb8; color: #fff; }
        .btn-close  { background: #e5e7eb; color: #333; }
        body.in-modal {
            background: #d1d5db; min-height: 100%; padding: 1.5rem 1rem;
            display: flex; flex-direction: column; align-items: center;
        }
        body.in-modal .print-bar { display: none; }
        body.in-modal .page { margin: 0 auto; width: 100%; max-width: 960px; border-radius: 4px; box-shadow: 0 6px 32px rgba(0,0,0,0.22); }
        @media print {
            body, body.in-modal { background: #fff !important; display: block !important; padding: 0 !important; min-height: unset !important; align-items: unset !important; }
            .print-bar { display: none !important; }
            .page, body.in-modal .page { margin: 0 !important; box-shadow: none !important; border-radius: 0 !important; max-width: 100% !important; width: 100% !important; }
            .rpt-table { font-size: 10px; }
            .rpt-table th, .rpt-table td { padding: 0.4rem 0.6rem; }
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
                        Retail B.R-03 Baloi Apartment, Jl. Permata Baloi, RT/RW 004/008,<br>
                        Kel. Baloi Indah, Kec. Lubuk Baja, Batam, 29444
                    </div>
                </td>
                <td style="width:20%;"></td>
            </tr>
        </table>
    </div>

    <div class="rpt-label">
        <span class="rpt-label-title">Laporan Jasa Angkut Kapal</span>
        <span class="rpt-label-year">Tahun {{ $year }}</span>
    </div>

    <div class="rpt-body">
        @php
            $months = [
                1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April',
                5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
                9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember',
            ];
            $fmt    = fn($n) => number_format((float)$n, 0, ',', '.');
            $fmtQty = fn($n) => number_format((float)$n, 2, ',', '.');
            $gQty = 0; $gTotal = 0;
        @endphp

        <table class="rpt-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Kapal</th>
                    <th class="r">Qty (L)</th>
                    <th class="r">Harga / L</th>
                    <th class="r">Total Jasa Angkut</th>
                </tr>
            </thead>
            <tbody>
                @foreach($months as $m => $name)
                    @php
                        $rows  = $byMonth->get($m, collect());
                        if ($rows->isEmpty()) continue;
                        $mQty = 0; $mTotal = 0;
                        foreach ($rows as $p) {
                            $q = (float)$p->quantity + (float)$p->extra;
                            $mQty   += $q;
                            $mTotal += $q * (float)$p->kapal_price;
                        }
                        $gQty   += $mQty;
                        $gTotal += $mTotal;
                    @endphp
                    <tr class="month-header">
                        <td colspan="5">{{ $name }}</td>
                    </tr>
                    @foreach($rows as $p)
                        @php
                            $q     = (float)$p->quantity + (float)$p->extra;
                            $total = $q * (float)$p->kapal_price;
                        @endphp
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($p->date)->format('d/m/Y') }}</td>
                            <td>{{ $p->kapal_name }}</td>
                            <td class="r">{{ $fmtQty($q) }}</td>
                            <td class="r">Rp {{ $fmt($p->kapal_price) }}</td>
                            <td class="r" style="font-weight:600;">Rp {{ $fmt($total) }}</td>
                        </tr>
                    @endforeach
                    <tr class="month-subtotal">
                        <td colspan="2" class="r">Subtotal {{ $name }}</td>
                        <td class="r">{{ $fmtQty($mQty) }}</td>
                        <td></td>
                        <td class="r">Rp {{ $fmt($mTotal) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2"><strong>Total Keseluruhan</strong></td>
                    <td class="r"><strong>{{ $fmtQty($gQty) }}</strong></td>
                    <td></td>
                    <td class="r"><strong>Rp {{ $fmt($gTotal) }}</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
</body>
</html>
