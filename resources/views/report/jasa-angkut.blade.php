@extends('layouts.app')
@section('title', 'Laporan Jasa Angkut')
@section('content')

@php
    $pageTitle = 'Laporan Jasa Angkut';
    $months = [
        1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April',
        5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
        9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember',
    ];
    $fmt    = fn($n) => number_format((float)$n, 0, ',', '.');
    $fmtQty = fn($n) => number_format((float)$n, 2, ',', '.');

    $gQty = 0; $gTotal = 0;
    foreach ($byMonth as $rows) {
        foreach ($rows as $p) {
            $q = (float)$p->quantity + (float)$p->extra;
            $gQty   += $q;
            $gTotal += $q * (float)$p->kapal_price;
        }
    }
@endphp

<div class="page-header">
    <div>
        <h1 class="page-title-text">{{ $pageTitle }}</h1>
        <p class="page-subtitle">Laporan tahun {{ $year }}</p>
    </div>
    <div class="page-actions">
        <form method="GET" action="{{ request()->url() }}" style="display:flex;gap:0.5rem;align-items:center;" id="yearForm">
            <select name="year" class="form-select" style="width:auto;" onchange="document.getElementById('yearForm').submit()">
                @foreach($years as $y)
                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </form>
        <button class="btn btn-primary" onclick="openPrintModal()">
            <i data-lucide="printer" style="width:15px;height:15px;"></i>
            Print
        </button>
    </div>
</div>

<div class="card">
    <div class="card-header"><div class="card-title">Jasa Angkut Kapal per Bulan</div></div>
    <div class="card-content" style="padding:0;">
        <div class="table-wrap">
            <table class="table-grid">
                <thead>
                    <tr>
                        <th>Bulan</th>
                        <th class="text-right">Total Qty (L)</th>
                        <th class="text-right">Total Jasa Angkut</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($months as $m => $name)
                        @php
                            $rows  = $byMonth->get($m, collect());
                            $mQty  = 0; $mTotal = 0;
                            foreach ($rows as $p) {
                                $q = (float)$p->quantity + (float)$p->extra;
                                $mQty   += $q;
                                $mTotal += $q * (float)$p->kapal_price;
                            }
                            $hasData = $rows->isNotEmpty();
                        @endphp

                        <tr @if($hasData) class="month-summary-row" onclick="toggleMonth({{ $m }})" style="cursor:pointer;" @endif>
                            <td>
                                @if($hasData)
                                <span id="chevron-{{ $m }}" style="display:inline-block;margin-right:6px;font-size:0.7rem;transition:transform 0.2s;">▶</span>
                                @endif
                                <strong>{{ $name }}</strong>
                            </td>
                            <td class="text-right">{{ $hasData ? $fmtQty($mQty) : '-' }}</td>
                            <td class="text-right" @if($hasData) style="font-weight:600;" @endif>
                                {{ $hasData ? 'Rp '.$fmt($mTotal) : '-' }}
                            </td>
                        </tr>

                        @if($hasData)
                        <tr id="detail-{{ $m }}" style="display:none;">
                            <td colspan="3" style="padding:0;">
                                <table style="width:100%;border-top:1px solid var(--border);">
                                    <thead>
                                        <tr style="background:var(--muted);font-size:0.82rem;">
                                            <th style="padding:6px 12px 6px 2.5rem;text-align:left;font-weight:600;">Tanggal</th>
                                            <th style="padding:6px 12px;text-align:left;font-weight:600;">Kapal</th>
                                            <th style="padding:6px 12px;text-align:right;font-weight:600;">Qty (L)</th>
                                            <th style="padding:6px 12px;text-align:right;font-weight:600;">Harga / L</th>
                                            <th style="padding:6px 12px;text-align:right;font-weight:600;">Total Jasa Angkut</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($rows as $p)
                                            @php
                                                $q     = (float)$p->quantity + (float)$p->extra;
                                                $total = $q * (float)$p->kapal_price;
                                            @endphp
                                            <tr style="font-size:0.85rem;border-top:1px solid var(--border);">
                                                <td style="padding:6px 12px 6px 2.5rem;">{{ \Carbon\Carbon::parse($p->date)->format('d/m/Y') }}</td>
                                                <td style="padding:6px 12px;">{{ $p->kapal_name }}</td>
                                                <td style="padding:6px 12px;text-align:right;">{{ $fmtQty($q) }}</td>
                                                <td style="padding:6px 12px;text-align:right;">Rp {{ $fmt($p->kapal_price) }}</td>
                                                <td style="padding:6px 12px;text-align:right;font-weight:600;">Rp {{ $fmt($total) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        @endif
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="report-total-row">
                        <td><strong>Total</strong></td>
                        <td class="text-right"><strong>{{ $fmtQty($gQty) }}</strong></td>
                        <td class="text-right"><strong>Rp {{ $fmt($gTotal) }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

@include('report._print_modal')
@endsection

@push('scripts')
<script>
function toggleMonth(m) {
    const detail  = document.getElementById('detail-' + m);
    const chevron = document.getElementById('chevron-' + m);
    const open = detail.style.display !== 'none';
    detail.style.display    = open ? 'none' : '';
    chevron.style.transform = open ? '' : 'rotate(90deg)';
}

const basePrintUrl = '{{ route('report.jasa-angkut.print', ['year' => $year]) }}';
function openPrintModal() {
    document.getElementById('printFrame').src = basePrintUrl;
    document.getElementById('printModal').classList.add('active');
}
function closePrintModal() {
    document.getElementById('printModal').classList.remove('active');
    document.getElementById('printFrame').src = '';
}

lucide.createIcons();
</script>
@endpush
