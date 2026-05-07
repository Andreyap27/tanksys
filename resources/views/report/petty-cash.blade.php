@extends('layouts.app')
@section('title', 'Laporan Petty Cash')
@section('content')

@php
    $pageTitle = 'Laporan Petty Cash';
    $months = [
        1=>'Januari', 2=>'Februari', 3=>'Maret',    4=>'April',
        5=>'Mei',     6=>'Juni',     7=>'Juli',      8=>'Agustus',
        9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember',
    ];
    $fmt = fn($n) => number_format((float)$n, 0, ',', '.');

    $gIn = $gOut = 0;
    foreach (range(1, 12) as $m) {
        $gIn  += (float)($pcIn[$m]  ?? 0);
        $gOut += (float)($pcOut[$m] ?? 0);
    }
    $gBalance = $gIn - $gOut;
@endphp

@include('report._header')

<div class="card">
    <div class="card-header"><div class="card-title">Laporan Petty Cash</div></div>
    <div class="card-content" style="padding:0;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Bulan</th>
                        <th class="text-right">In (Kredit)</th>
                        <th class="text-right">Out (Debit)</th>
                        <th class="text-right">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($months as $m => $name)
                        @php
                            $in      = (float)($pcIn[$m]  ?? 0);
                            $out     = (float)($pcOut[$m] ?? 0);
                            $balance = $in - $out;
                            $hasData = $in || $out;
                        @endphp
                        <tr>
                            <td>{{ $name }}</td>
                            <td class="text-right">{{ $in  ? 'Rp '.$fmt($in)  : '-' }}</td>
                            <td class="text-right">{{ $out ? 'Rp '.$fmt($out) : '-' }}</td>
                            <td class="text-right">
                                @if($hasData)
                                    {{ $balance < 0 ? '-' : '' }}Rp {{ $fmt(abs($balance)) }}
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
                        <td class="text-right"><strong>Rp {{ $fmt($gIn) }}</strong></td>
                        <td class="text-right"><strong>Rp {{ $fmt($gOut) }}</strong></td>
                        <td class="text-right">
                            <strong>{{ $gBalance < 0 ? '-' : '' }}Rp {{ $fmt(abs($gBalance)) }}</strong>
                        </td>
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
const basePrintUrl = '{{ route('report.print', ['section' => 'petty-cash', 'year' => $year]) }}';
function openPrintModal() {
    const pcId = new URLSearchParams(window.location.search).get('petty_cash_id');
    const url  = basePrintUrl + (pcId ? '&petty_cash_id=' + encodeURIComponent(pcId) : '');
    document.getElementById('printFrame').src = url;
    document.getElementById('printModal').classList.add('active');
}
function closePrintModal() {
    document.getElementById('printModal').classList.remove('active');
    document.getElementById('printFrame').src = '';
}
lucide.createIcons();
</script>
@endpush
