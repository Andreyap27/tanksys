@extends('layouts.app')
@section('title', 'Laporan Profit / Loss')
@section('content')

@php
    $pageTitle = 'Profit / Loss';
    $months = [
        1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April',
        5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
        9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember',
    ];
    $fmt = fn($n) => number_format((float)$n, 0, ',', '.');

    $gPur = 0; $gSal = 0; $gExp = 0;
    foreach (range(1,12) as $m) {
        $gPur += (float)($purchases->get($m)->total_amount ?? 0);
        $gSal += (float)($sales->get($m)->total_amount    ?? 0);
        $gExp += (float)($expensesTotal[$m]               ?? 0);
    }
    $gPL = $gSal - $gPur - $gExp;
@endphp

@include('report._header')

<div class="card">
    <div class="card-header"><div class="card-title">Profit / Loss</div></div>
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
                            $pur     = (float)($purchases->get($m)->total_amount ?? 0);
                            $sal     = (float)($sales->get($m)->total_amount    ?? 0);
                            $exp     = (float)($expensesTotal[$m]               ?? 0);
                            $pl      = $sal - $pur - $exp;
                            $hasData = $pur || $sal || $exp;
                        @endphp
                        <tr>
                            <td>{{ $name }}</td>
                            <td class="text-right">{{ $pur ? 'Rp '.$fmt($pur) : '-' }}</td>
                            <td class="text-right">{{ $sal ? 'Rp '.$fmt($sal) : '-' }}</td>
                            <td class="text-right">{{ $exp ? 'Rp '.$fmt($exp) : '-' }}</td>
                            <td class="text-right" @if($hasData) style="color:{{ $pl >= 0 ? 'var(--success)' : 'var(--destructive)' }};font-weight:600;" @endif>
                                @if($hasData) {{ $pl < 0 ? '-' : '' }}Rp {{ $fmt(abs($pl)) }} @else - @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="report-total-row">
                        <td><strong>Total</strong></td>
                        <td class="text-right"><strong>Rp {{ $fmt($gPur) }}</strong></td>
                        <td class="text-right"><strong>Rp {{ $fmt($gSal) }}</strong></td>
                        <td class="text-right"><strong>Rp {{ $fmt($gExp) }}</strong></td>
                        <td class="text-right" style="color:{{ $gPL >= 0 ? 'var(--success)' : 'var(--destructive)' }};font-weight:700;">
                            <strong>{{ $gPL < 0 ? '-' : '' }}Rp {{ $fmt(abs($gPL)) }}</strong>
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
const printUrl = '{{ route('report.print', ['section' => 'profit-loss', 'year' => $year]) }}';
function openPrintModal()  { document.getElementById('printFrame').src = printUrl; document.getElementById('printModal').classList.add('active'); }
function closePrintModal() { document.getElementById('printModal').classList.remove('active'); document.getElementById('printFrame').src = ''; }
lucide.createIcons();
</script>
@endpush
