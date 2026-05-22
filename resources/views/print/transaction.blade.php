<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} — Print</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI',Arial,sans-serif; font-size:12px; color:#1a1a1a; background:#f5f5f5; min-height:100vh; }
        .page { max-width:1100px; margin:1.5rem auto; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,0.10); }
        .rpt-header { background:#fff; padding:1.25rem 2rem 1rem; border-bottom:3px solid #1a5cb8; }
        .rpt-company-name { font-size:1.4rem; font-weight:700; color:#1a5cb8; text-decoration:underline; text-underline-offset:5px; text-decoration-thickness:2px; }
        .rpt-company-address { font-size:0.75rem; color:#555; line-height:1.55; }
        .rpt-label { background:#f0f4fb; padding:0.65rem 2rem; border-bottom:1px solid #dbeafe; display:flex; align-items:baseline; justify-content:space-between; flex-wrap:wrap; gap:0.35rem; }
        .rpt-label-title { font-size:0.9rem; font-weight:700; color:#1a5cb8; text-transform:uppercase; letter-spacing:0.05em; }
        .rpt-label-sub { font-size:0.75rem; color:#64748b; }
        .rpt-body { padding:1.25rem 2rem; }
        table { width:100%; border-collapse:collapse; font-size:11px; }
        thead tr { background:#f0f4fb; }
        th { padding:0.5rem 0.7rem; text-align:left; font-size:0.68rem; text-transform:uppercase; letter-spacing:0.05em; color:#1a5cb8; border-bottom:2px solid #1a5cb8; font-weight:700; white-space:nowrap; }
        th.r, td.r { text-align:right; }
        th.c, td.c { text-align:center; }
        tbody tr:nth-child(even) { background:#f8fafc; }
        td { padding:0.45rem 0.7rem; border-bottom:1px solid #f0f4f8; color:#374151; }
        tfoot td { padding:0.55rem 0.7rem; background:#eef2fb; border-top:2px solid #1a5cb8; font-weight:700; font-size:12px; color:#1a1a1a; }
        td.r, tfoot td.r { white-space:nowrap; }
        .summary-box { margin-top:1.25rem; padding-top:0.85rem; border-top:2px solid #1a5cb8; page-break-inside:avoid; display:flex; flex-direction:column; align-items:flex-end; gap:0.3rem; }
        .summary-box .s-row { display:flex; justify-content:space-between; gap:3rem; font-size:11.5px; }
        .summary-box .s-label { color:#374151; }
        .summary-box .s-val { font-weight:700; white-space:nowrap; }
        .summary-box .s-balance { border-top:1px solid #cbd5e1; padding-top:0.25rem; margin-top:0.1rem; }
        .summary-box .s-balance .s-label,
        .summary-box .s-balance .s-val { color:#1a5cb8; font-size:12.5px; }
        .badge { display:inline-block; padding:0.15rem 0.5rem; border-radius:9999px; font-size:0.68rem; font-weight:600; }
        .badge-approved { background:#dcfce7; color:#16a34a; }
        .badge-pending  { background:#fef9c3; color:#a16207; }
        .badge-rejected { background:#fee2e2; color:#dc2626; }
        .badge-paid     { background:#dbeafe; color:#1d4ed8; }
        .badge-in  { background:#dcfce7; color:#16a34a; }
        .badge-out { background:#fee2e2; color:#dc2626; }
        .print-bar { max-width:1100px; margin:0.75rem auto 0; padding:0 1rem; display:flex; justify-content:flex-end; gap:0.5rem; }
        .print-bar button { padding:0.45rem 1.1rem; border:none; border-radius:6px; cursor:pointer; font-size:0.8rem; font-weight:600; }
        .btn-print { background:#1a5cb8; color:#fff; }
        .btn-close  { background:#e5e7eb; color:#333; }
        body.in-modal { background:#d1d5db; min-height:100%; padding:1.5rem 1rem; display:flex; flex-direction:column; align-items:center; }
        body.in-modal .print-bar { display:none; }
        body.in-modal .page { margin:0 auto; width:100%; max-width:1100px; border-radius:4px; box-shadow:0 6px 32px rgba(0,0,0,0.22); }
        @media print {
            body, body.in-modal { background:#fff !important; display:block !important; padding:0 !important; min-height:unset !important; align-items:unset !important; }
            .print-bar { display:none !important; }
            .page, body.in-modal .page { margin:0 !important; box-shadow:none !important; border-radius:0 !important; max-width:100% !important; width:100% !important; }
            table { font-size:9.5px; }
            th, td, tfoot td { padding:0.35rem 0.5rem; }
        }
    </style>
</head>
<body>
<script>
    if (window.self !== window.top) {
        document.addEventListener('DOMContentLoaded', function() { document.body.classList.add('in-modal'); });
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
                <td style="width:18%;vertical-align:middle;">
                    @if(file_exists(public_path('images/logo.png')))
                        <img src="{{ asset('images/logo.png') }}" alt="Logo" style="height:55px;width:auto;object-fit:contain;">
                    @endif
                </td>
                <td style="text-align:center;vertical-align:middle;">
                    <div class="rpt-company-name">PT. ANUGRAH ENERGI PETROLUM</div>
                    <div class="rpt-company-address">
                        Retail B.R-03 Baloi Apartment, Jl. Permata Baloi, RT/RW 004/008,<br>
                        Kel. Baloi Indah, Kec. Lubuk Baja, Batam, 29444
                    </div>
                </td>
                <td style="width:18%;"></td>
            </tr>
        </table>
    </div>
    <div class="rpt-label">
        <span class="rpt-label-title">{{ $title }}</span>
        <span class="rpt-label-sub">
            @if($dateFrom || $dateTo)
                {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->translatedFormat('d M Y') : '...' }}
                &ndash;
                {{ $dateTo   ? \Carbon\Carbon::parse($dateTo)->translatedFormat('d M Y')   : '...' }}
            @else
                Semua Periode
            @endif
            @if($kapalName) &mdash; {{ $kapalName }} @endif
            @if($mobilName) &mdash; {{ $mobilName }} @endif
            @if($pcName)    &mdash; {{ $pcName }}    @endif
        </span>
    </div>
    <div class="rpt-body">

    @php $fmtD = fn($d) => \Carbon\Carbon::parse($d)->translatedFormat('d M Y'); @endphp

    {{-- ── STOCK ──────────────────────────────────────────────────────── --}}
    @if($section === 'stock')
    <table>
        <thead><tr>
            <th style="width:3%">#</th>
            <th>Tanggal</th><th>Vendor / Customer</th><th>Tipe</th><th>Warna</th>
            <th class="r">Masuk (L)</th><th class="r">Keluar (L)</th><th class="r">Saldo (L)</th>
        </tr></thead>
        <tbody>
            @forelse($rows as $i => $r)
            <tr>
                <td class="c">{{ $i+1 }}</td>
                <td>{{ $r['date'] }}</td><td>{{ $r['party'] }}</td><td>{{ $r['type'] }}</td>
                <td>{{ $r['warna'] }}</td>
                <td class="r">{{ $r['qty_in'] }}</td><td class="r">{{ $r['qty_out'] }}</td>
                <td class="r"><strong>{{ $r['balance'] }}</strong></td>
            </tr>
            @empty<tr><td colspan="8" style="text-align:center;color:#888;">Tidak ada data</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- ── PURCHASE ────────────────────────────────────────────────────── --}}
    @elseif($section === 'purchase')
    @php
        $gQty=0; $gExtra=0; $gShort=0; $gAmt=0;
        $gQtyPaid=0; $gExtraPaid=0; $gShortPaid=0; $gAmtPaid=0;
        $gQtyUnpaid=0; $gExtraUnpaid=0; $gShortUnpaid=0; $gAmtUnpaid=0;
        foreach($rows as $r){
            $gQty+=$r->quantity; $gExtra+=$r->extra; $gShort+=$r->short; $gAmt+=$r->amount;
            if($r->status==='paid'){
                $gQtyPaid+=$r->quantity; $gExtraPaid+=$r->extra; $gShortPaid+=$r->short; $gAmtPaid+=$r->amount;
            } else {
                $gQtyUnpaid+=$r->quantity; $gExtraUnpaid+=$r->extra; $gShortUnpaid+=$r->short; $gAmtUnpaid+=$r->amount;
            }
        }
    @endphp
    <table>
        <thead><tr>
            <th style="width:3%">#</th>
            <th>Tanggal</th><th>Vendor</th><th>Deskripsi</th><th>Warna</th>
            <th class="r">Qty (L)</th><th class="r">Extra (L)</th><th class="r">Short (L)</th>
            <th class="r">Harga/L</th><th class="c">Status</th><th class="r">Amount</th>
        </tr></thead>
        <tbody>
            @forelse($rows as $i => $r)
            <tr>
                <td class="c">{{ $i+1 }}</td>
                <td>{{ $r->date->translatedFormat('d M Y') }}</td>
                <td>{{ $r->vendor }}</td><td>{{ $r->description ?? '-' }}</td>
                <td>{{ ucfirst($r->warna ?? '-') }}</td>
                <td class="r">{{ $fmtQty($r->quantity) }}</td>
                <td class="r">{{ $r->extra > 0 ? $fmtQty($r->extra) : '-' }}</td>
                <td class="r" style="{{ $r->short > 0 ? 'color:#dc2626;' : '' }}">{{ $r->short > 0 ? $fmtQty($r->short) : '-' }}</td>
                <td class="r">Rp {{ $fmt($r->price) }}</td>
                <td class="c"><span class="badge badge-{{ $r->status }}">{{ ucfirst($r->status) }}</span></td>
                <td class="r">Rp {{ $fmt($r->amount) }}</td>
            </tr>
            @empty<tr><td colspan="11" style="text-align:center;color:#888;">Tidak ada data</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5"><strong>Total</strong></td>
                <td class="r">{{ $fmtQty($gQty) }}</td>
                <td class="r">{{ $fmtQty($gExtra) }}</td>
                <td class="r">{{ $gShort > 0 ? $fmtQty($gShort) : '-' }}</td>
                <td></td><td></td>
                <td class="r">Rp {{ $fmt($gAmt) }}</td>
            </tr>
            <tr style="background:#dcfce7;">
                <td colspan="5" style="color:#16a34a;"><strong>Total Paid</strong></td>
                <td class="r" style="color:#16a34a;">{{ $fmtQty($gQtyPaid) }}</td>
                <td class="r" style="color:#16a34a;">{{ $fmtQty($gExtraPaid) }}</td>
                <td class="r" style="color:#16a34a;">{{ $gShortPaid > 0 ? $fmtQty($gShortPaid) : '-' }}</td>
                <td></td><td></td>
                <td class="r" style="color:#16a34a;">Rp {{ $fmt($gAmtPaid) }}</td>
            </tr>
            <tr style="background:#fee2e2;">
                <td colspan="5" style="color:#dc2626;"><strong>Total Unpaid</strong></td>
                <td class="r" style="color:#dc2626;">{{ $fmtQty($gQtyUnpaid) }}</td>
                <td class="r" style="color:#dc2626;">{{ $fmtQty($gExtraUnpaid) }}</td>
                <td class="r" style="color:#dc2626;">{{ $gShortUnpaid > 0 ? $fmtQty($gShortUnpaid) : '-' }}</td>
                <td></td><td></td>
                <td class="r" style="color:#dc2626;">Rp {{ $fmt($gAmtUnpaid) }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- ── SALES ───────────────────────────────────────────────────────── --}}
    @elseif($section === 'sales')
    @php
        $gQty=0; $gExtra=0; $gShort=0; $gAmt=0;
        $gQtyPaid=0; $gExtraPaid=0; $gShortPaid=0; $gAmtPaid=0;
        $gQtyUnpaid=0; $gExtraUnpaid=0; $gShortUnpaid=0; $gAmtUnpaid=0;
        foreach($rows as $r){
            $gQty+=$r->quantity; $gExtra+=$r->extra; $gShort+=$r->short; $gAmt+=$r->amount;
            if($r->status==='paid'){
                $gQtyPaid+=$r->quantity; $gExtraPaid+=$r->extra; $gShortPaid+=$r->short; $gAmtPaid+=$r->amount;
            } else {
                $gQtyUnpaid+=$r->quantity; $gExtraUnpaid+=$r->extra; $gShortUnpaid+=$r->short; $gAmtUnpaid+=$r->amount;
            }
        }
    @endphp
    <table>
        <thead><tr>
            <th style="width:3%">#</th>
            <th>Tanggal</th><th>Invoice</th><th>Customer</th><th>Warna</th>
            <th class="r">Qty (L)</th><th class="r">Extra (L)</th><th class="r">Short (L)</th>
            <th class="r">Harga/L</th><th class="c">Status</th><th class="r">Amount</th>
        </tr></thead>
        <tbody>
            @forelse($rows as $i => $r)
            <tr>
                <td class="c">{{ $i+1 }}</td>
                <td>{{ $r->date->translatedFormat('d M Y') }}</td>
                <td>{{ $r->invoice_number }}</td>
                <td>{{ $r->customer->name ?? '-' }}</td>
                <td>{{ ucfirst($r->warna ?? '-') }}</td>
                <td class="r">{{ $fmtQty($r->quantity) }}</td>
                <td class="r">{{ $r->extra > 0 ? $fmtQty($r->extra) : '-' }}</td>
                <td class="r" style="{{ $r->short > 0 ? 'color:#dc2626;' : '' }}">{{ $r->short > 0 ? $fmtQty($r->short) : '-' }}</td>
                <td class="r">Rp {{ $fmt($r->price) }}</td>
                <td class="c"><span class="badge badge-{{ $r->status }}">{{ ucfirst($r->status) }}</span></td>
                <td class="r">Rp {{ $fmt($r->amount) }}</td>
            </tr>
            @empty<tr><td colspan="11" style="text-align:center;color:#888;">Tidak ada data</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5"><strong>Total</strong></td>
                <td class="r">{{ $fmtQty($gQty) }}</td>
                <td class="r">{{ $fmtQty($gExtra) }}</td>
                <td class="r">{{ $gShort > 0 ? $fmtQty($gShort) : '-' }}</td>
                <td></td><td></td>
                <td class="r">Rp {{ $fmt($gAmt) }}</td>
            </tr>
            <tr style="background:#dcfce7;">
                <td colspan="5" style="color:#16a34a;"><strong>Total Paid</strong></td>
                <td class="r" style="color:#16a34a;">{{ $fmtQty($gQtyPaid) }}</td>
                <td class="r" style="color:#16a34a;">{{ $fmtQty($gExtraPaid) }}</td>
                <td class="r" style="color:#16a34a;">{{ $gShortPaid > 0 ? $fmtQty($gShortPaid) : '-' }}</td>
                <td></td><td></td>
                <td class="r" style="color:#16a34a;">Rp {{ $fmt($gAmtPaid) }}</td>
            </tr>
            <tr style="background:#fee2e2;">
                <td colspan="5" style="color:#dc2626;"><strong>Total Unpaid</strong></td>
                <td class="r" style="color:#dc2626;">{{ $fmtQty($gQtyUnpaid) }}</td>
                <td class="r" style="color:#dc2626;">{{ $fmtQty($gExtraUnpaid) }}</td>
                <td class="r" style="color:#dc2626;">{{ $gShortUnpaid > 0 ? $fmtQty($gShortUnpaid) : '-' }}</td>
                <td></td><td></td>
                <td class="r" style="color:#dc2626;">Rp {{ $fmt($gAmtUnpaid) }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- ── EXPENSES SHIP ───────────────────────────────────────────────── --}}
    @elseif($section === 'expenses')
    @php
        $gNom=0; $gNomPaid=0; $gNomUnpaid=0;
        foreach($rows as $r){
            $gNom+=$r->nominal;
            if($r->status==='paid') $gNomPaid+=$r->nominal;
            else $gNomUnpaid+=$r->nominal;
        }
    @endphp
    <table>
        <thead><tr>
            <th style="width:3%">#</th>
            <th>Tanggal</th><th>Kategori</th><th>Deskripsi</th>
            <th class="r">Nominal</th><th class="c">Status</th>
        </tr></thead>
        <tbody>
            @forelse($rows as $i => $r)
            <tr>
                <td class="c">{{ $i+1 }}</td>
                <td>{{ $r->date->translatedFormat('d M Y') }}</td>
                <td>{{ $r->category }}</td><td>{{ $r->description ?? '-' }}</td>
                <td class="r">Rp {{ $fmt($r->nominal) }}</td>
                <td class="c"><span class="badge badge-{{ $r->status }}">{{ ucfirst($r->status) }}</span></td>
            </tr>
            @empty<tr><td colspan="6" style="text-align:center;color:#888;">Tidak ada data</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4"><strong>Total</strong></td>
                <td class="r">Rp {{ $fmt($gNom) }}</td><td></td>
            </tr>
            <tr style="background:#dcfce7;">
                <td colspan="4" style="color:#16a34a;"><strong>Total Paid</strong></td>
                <td class="r" style="color:#16a34a;">Rp {{ $fmt($gNomPaid) }}</td><td></td>
            </tr>
            <tr style="background:#fee2e2;">
                <td colspan="4" style="color:#dc2626;"><strong>Total Unpaid</strong></td>
                <td class="r" style="color:#dc2626;">Rp {{ $fmt($gNomUnpaid) }}</td><td></td>
            </tr>
        </tfoot>
    </table>

    {{-- ── PETTY CASH ──────────────────────────────────────────────────── --}}
    @elseif($section === 'petty-cash')
    @php $gIn=0; $gOut=0; foreach($rows as $r){ if($r->type==='in') $gIn+=$r->amount; else $gOut+=$r->amount; } @endphp
    <table>
        <thead><tr>
            <th style="width:3%">#</th>
            <th>Tanggal</th><th>Petty Cash</th><th>Deskripsi</th>
            <th class="c">Tipe</th><th class="r">Amount</th>
        </tr></thead>
        <tbody>
            @forelse($rows as $i => $r)
            <tr>
                <td class="c">{{ $i+1 }}</td>
                <td>{{ $r->date->translatedFormat('d M Y') }}</td>
                <td>{{ $r->pettyCash->name ?? '-' }}</td>
                <td>{{ $r->description }}</td>
                <td class="c"><span class="badge badge-{{ $r->type }}">{{ $r->type === 'in' ? 'Kredit' : 'Debit' }}</span></td>
                <td class="r">Rp {{ $fmt($r->amount) }}</td>
            </tr>
            @empty<tr><td colspan="6" style="text-align:center;color:#888;">Tidak ada data</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="summary-box">
        <div class="s-row">
            <span class="s-label">In (Kredit)</span>
            <span class="s-val">Rp {{ $fmt($gIn) }}</span>
        </div>
        <div class="s-row">
            <span class="s-label">Out (Debit)</span>
            <span class="s-val">Rp {{ $fmt($gOut) }}</span>
        </div>
        <div class="s-row s-balance">
            <span class="s-label">Balance</span>
            <span class="s-val">Rp {{ $fmt($gIn - $gOut) }}</span>
        </div>
    </div>

    {{-- ── CAPITAL ─────────────────────────────────────────────────────── --}}
    @elseif($section === 'capital')
    @php $gNom=0; foreach($rows as $r){ $gNom+=$r->nominal; } @endphp
    <table>
        <thead><tr>
            <th style="width:3%">#</th>
            <th>Tanggal</th><th>Nama</th><th>Catatan</th>
            <th class="r">Nominal</th><th class="c">Status</th>
        </tr></thead>
        <tbody>
            @forelse($rows as $i => $r)
            <tr>
                <td class="c">{{ $i+1 }}</td>
                <td>{{ $r->date->translatedFormat('d M Y') }}</td>
                <td>{{ $r->name }}</td><td>{{ $r->note ?? '-' }}</td>
                <td class="r">Rp {{ $fmt($r->nominal) }}</td>
                <td class="c"><span class="badge badge-{{ $r->status }}">{{ ucfirst($r->status) }}</span></td>
            </tr>
            @empty<tr><td colspan="6" style="text-align:center;color:#888;">Tidak ada data</td></tr>
            @endforelse
        </tbody>
        <tfoot><tr>
            <td colspan="4"><strong>Total</strong></td>
            <td class="r">Rp {{ $fmt($gNom) }}</td><td></td>
        </tr></tfoot>
    </table>

    {{-- ── STOCK TRANSFER ──────────────────────────────────────────────── --}}
    @elseif($section === 'stock-transfer')
    @php $gQty=0; foreach($rows as $r){ $gQty+=$r->quantity; } @endphp
    <table>
        <thead><tr>
            <th style="width:3%">#</th>
            <th>Tanggal</th><th>Dari Kapal</th><th>Ke Kapal</th><th>Warna</th>
            <th class="r">Qty (L)</th><th>Catatan</th>
        </tr></thead>
        <tbody>
            @forelse($rows as $i => $r)
            <tr>
                <td class="c">{{ $i+1 }}</td>
                <td>{{ $r->date->translatedFormat('d M Y') }}</td>
                <td>{{ $r->fromKapal->name ?? '-' }}</td>
                <td>{{ $r->toKapal->name ?? '-' }}</td>
                <td>{{ ucfirst($r->warna) }}</td>
                <td class="r">{{ $fmtQty($r->quantity) }}</td>
                <td>{{ $r->note ?? '-' }}</td>
            </tr>
            @empty<tr><td colspan="7" style="text-align:center;color:#888;">Tidak ada data</td></tr>
            @endforelse
        </tbody>
        <tfoot><tr>
            <td colspan="5"><strong>Total</strong></td>
            <td class="r">{{ $fmtQty($gQty) }}</td><td></td>
        </tr></tfoot>
    </table>

    {{-- ── STOCK USAGE ─────────────────────────────────────────────────── --}}
    @elseif($section === 'stock-usage')
    @php $gQty=0; foreach($rows as $r){ $gQty+=$r->quantity; } @endphp
    <table>
        <thead><tr>
            <th style="width:3%">#</th>
            <th>Tanggal</th><th>Kapal</th><th>Keperluan</th><th>Warna</th>
            <th class="r">Qty (L)</th>
        </tr></thead>
        <tbody>
            @forelse($rows as $i => $r)
            <tr>
                <td class="c">{{ $i+1 }}</td>
                <td>{{ $r->date->translatedFormat('d M Y') }}</td>
                <td>{{ $r->kapal->name ?? '-' }}</td>
                <td>{{ $r->keperluan ?? '-' }}</td>
                <td>{{ ucfirst($r->warna) }}</td>
                <td class="r">{{ $fmtQty($r->quantity) }}</td>
            </tr>
            @empty<tr><td colspan="6" style="text-align:center;color:#888;">Tidak ada data</td></tr>
            @endforelse
        </tbody>
        <tfoot><tr>
            <td colspan="5"><strong>Total</strong></td>
            <td class="r">{{ $fmtQty($gQty) }}</td>
        </tr></tfoot>
    </table>

    {{-- ── LORI SALE ───────────────────────────────────────────────────── --}}
    @elseif($section === 'lori')
    @php $gAmt=0; foreach($rows as $r){ $gAmt+=$r->price; } @endphp
    <table>
        <thead><tr>
            <th style="width:3%">#</th>
            <th>Tanggal</th><th>Mobil</th><th>Customer</th><th>Dari</th><th>Ke</th>
            <th class="r">Harga</th>
        </tr></thead>
        <tbody>
            @forelse($rows as $i => $r)
            <tr>
                <td class="c">{{ $i+1 }}</td>
                <td>{{ $r->date->translatedFormat('d M Y') }}</td>
                <td>{{ $r->mobil->name ?? '-' }}</td>
                <td>{{ $r->customer->name ?? '-' }}</td>
                <td>{{ $r->from ?? '-' }}</td>
                <td>{{ $r->to ?? '-' }}</td>
                <td class="r">Rp {{ $fmt($r->price) }}</td>
            </tr>
            @empty<tr><td colspan="7" style="text-align:center;color:#888;">Tidak ada data</td></tr>
            @endforelse
        </tbody>
        <tfoot><tr>
            <td colspan="6"><strong>Total</strong></td>
            <td class="r">Rp {{ $fmt($gAmt) }}</td>
        </tr></tfoot>
    </table>

    {{-- ── LORI EXPENSE ────────────────────────────────────────────────── --}}
    @elseif($section === 'lori-expense')
    @php $gNom=0; foreach($rows as $r){ if($r->type==='out') $gNom+=$r->nominal; } @endphp
    <table>
        <thead><tr>
            <th style="width:3%">#</th>
            <th>Tanggal</th><th>Mobil</th><th>Kategori</th><th>Deskripsi</th>
            <th class="c">Tipe</th><th class="r">Nominal</th>
        </tr></thead>
        <tbody>
            @forelse($rows as $i => $r)
            <tr>
                <td class="c">{{ $i+1 }}</td>
                <td>{{ $r->date->translatedFormat('d M Y') }}</td>
                <td>{{ $r->mobil->name ?? '-' }}</td>
                <td>{{ $r->category }}</td>
                <td>{{ $r->description ?? '-' }}</td>
                <td class="c"><span class="badge badge-{{ $r->type }}">{{ $r->type === 'in' ? 'Kredit' : 'Debit' }}</span></td>
                <td class="r">Rp {{ $fmt($r->nominal) }}</td>
            </tr>
            @empty<tr><td colspan="7" style="text-align:center;color:#888;">Tidak ada data</td></tr>
            @endforelse
        </tbody>
        <tfoot><tr>
            <td colspan="6"><strong>Total Debit</strong></td>
            <td class="r">Rp {{ $fmt($gNom) }}</td>
        </tr></tfoot>
    </table>

    {{-- ── PIUTANG ─────────────────────────────────────────────────────── --}}
    @elseif($section === 'piutang')
    @php
        $gNom=0; $gNomPaid=0; $gNomUnpaid=0;
        foreach($rows as $r){
            $gNom+=$r->nominal;
            if($r->status==='paid') $gNomPaid+=$r->nominal;
            else $gNomUnpaid+=$r->nominal;
        }
    @endphp
    <table>
        <thead><tr>
            <th style="width:3%">#</th>
            <th>Tanggal</th><th>Nama</th><th>Deskripsi</th><th>Note</th>
            <th class="r">Nominal</th><th class="c">Status</th>
        </tr></thead>
        <tbody>
            @forelse($rows as $i => $r)
            <tr>
                <td class="c">{{ $i+1 }}</td>
                <td>{{ $r->date->translatedFormat('d M Y') }}</td>
                <td>{{ $r->nama }}</td>
                <td>{{ $r->description }}</td>
                <td>{{ $r->note ?? '-' }}</td>
                <td class="r">Rp {{ $fmt($r->nominal) }}</td>
                <td class="c">
                    @php
                        $badgeCls = match($r->status) {
                            'paid'     => 'badge-approved',
                            'approved' => 'badge-in',
                            'pending'  => 'badge-pending',
                            'rejected' => 'badge-rejected',
                            default    => '',
                        };
                    @endphp
                    <span class="badge {{ $badgeCls }}">{{ ucfirst($r->status) }}</span>
                </td>
            </tr>
            @empty<tr><td colspan="7" style="text-align:center;color:#888;">Tidak ada data</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5"><strong>Total</strong></td>
                <td class="r">Rp {{ $fmt($gNom) }}</td><td></td>
            </tr>
            <tr style="background:#dcfce7;">
                <td colspan="5" style="color:#16a34a;"><strong>Total Paid</strong></td>
                <td class="r" style="color:#16a34a;">Rp {{ $fmt($gNomPaid) }}</td><td></td>
            </tr>
            <tr style="background:#fee2e2;">
                <td colspan="5" style="color:#dc2626;"><strong>Total Unpaid</strong></td>
                <td class="r" style="color:#dc2626;">Rp {{ $fmt($gNomUnpaid) }}</td><td></td>
            </tr>
        </tfoot>
    </table>

    {{-- ── HUTANG ──────────────────────────────────────────────────────── --}}
    @elseif($section === 'hutang')
    @php
        $gNom=0; $gNomPaid=0; $gNomUnpaid=0;
        foreach($rows as $r){
            $gNom+=$r->nominal;
            if($r->status==='paid') $gNomPaid+=$r->nominal;
            else $gNomUnpaid+=$r->nominal;
        }
    @endphp
    <table>
        <thead><tr>
            <th style="width:3%">#</th>
            <th>Tanggal</th><th>Nama</th><th>Deskripsi</th><th>Note</th>
            <th class="r">Nominal</th><th class="c">Status</th>
        </tr></thead>
        <tbody>
            @forelse($rows as $i => $r)
            <tr>
                <td class="c">{{ $i+1 }}</td>
                <td>{{ $r->date->translatedFormat('d M Y') }}</td>
                <td>{{ $r->nama }}</td>
                <td>{{ $r->description }}</td>
                <td>{{ $r->note ?? '-' }}</td>
                <td class="r">Rp {{ $fmt($r->nominal) }}</td>
                <td class="c">
                    @php
                        $badgeCls = match($r->status) {
                            'paid'     => 'badge-approved',
                            'approved' => 'badge-in',
                            'pending'  => 'badge-pending',
                            'rejected' => 'badge-rejected',
                            default    => '',
                        };
                    @endphp
                    <span class="badge {{ $badgeCls }}">{{ ucfirst($r->status) }}</span>
                </td>
            </tr>
            @empty<tr><td colspan="7" style="text-align:center;color:#888;">Tidak ada data</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5"><strong>Total</strong></td>
                <td class="r">Rp {{ $fmt($gNom) }}</td><td></td>
            </tr>
            <tr style="background:#dcfce7;">
                <td colspan="5" style="color:#16a34a;"><strong>Total Paid</strong></td>
                <td class="r" style="color:#16a34a;">Rp {{ $fmt($gNomPaid) }}</td><td></td>
            </tr>
            <tr style="background:#fef9c3;">
                <td colspan="5" style="color:#a16207;"><strong>Total Unpaid</strong></td>
                <td class="r" style="color:#a16207;">Rp {{ $fmt($gNomUnpaid) }}</td><td></td>
            </tr>
        </tfoot>
    </table>
    @endif

    </div>
</div>
</body>
</html>
