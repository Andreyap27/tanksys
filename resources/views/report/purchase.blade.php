@extends('layouts.app')
@section('title', 'Laporan Purchase')
@section('content')

@php
    $pageTitle = 'Total Purchase';
    $months = [
        1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April',
        5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
        9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember',
    ];
    $fmt    = fn($n) => number_format((float)$n, 0, ',', '.');
    $fmtQty = fn($n) => number_format((float)$n, 2, ',', '.');
    $gQty = 0; $gAmt = 0;
    foreach (range(1,12) as $m) {
        $gQty += (float)($purchases->get($m)->total_qty    ?? 0);
        $gAmt += (float)($purchases->get($m)->total_amount ?? 0);
    }
@endphp

@include('report._header')

<div class="card">
    <div class="card-header"><div class="card-title">Total Purchase</div></div>
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
                            <td class="text-right">{{ $amt ? 'Rp '.$fmt($amt) : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="report-total-row">
                        <td><strong>Total</strong></td>
                        <td class="text-right"><strong>{{ $fmtQty($gQty) }}</strong></td>
                        <td class="text-right"><strong>Rp {{ $fmt($gAmt) }}</strong></td>
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
const basePrintUrl = '{{ route('report.print', ['section' => 'purchase', 'year' => $year]) }}';
function openPrintModal() {
    const kapalId = new URLSearchParams(window.location.search).get('kapal_id');
    const url = basePrintUrl + (kapalId ? '&kapal_id=' + encodeURIComponent(kapalId) : '');
    document.getElementById('printFrame').src = url;
    document.getElementById('printModal').classList.add('active');
}
function closePrintModal() { document.getElementById('printModal').classList.remove('active'); document.getElementById('printFrame').src = ''; }
lucide.createIcons();
</script>
@endpush
