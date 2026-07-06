@extends('layouts.app')
@section('title', 'Laporan Sale')
@section('content')

@php
    $pageTitle = 'Total Sale';
    $months = [
        1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April',
        5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
        9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember',
    ];
    $fmt    = fn($n) => number_format((float)$n, 0, ',', '.');
    $fmtQty = fn($n) => number_format((float)$n, 2, ',', '.');

    $gQty=0; $gExtra=0; $gAmtP=0; $gAmtA=0;
    foreach (range(1,12) as $m) {
        $gQty   += (float)($salesPaid->get($m)->total_qty      ?? 0) + (float)($salesApproved->get($m)->total_qty   ?? 0);
        $gExtra += (float)($salesPaid->get($m)->total_extra     ?? 0) + (float)($salesApproved->get($m)->total_extra  ?? 0);
        $gAmtP  += (float)($salesPaid->get($m)->total_amount    ?? 0);
        $gAmtA  += (float)($salesApproved->get($m)->total_amount ?? 0);
    }
    $gTot = $gQty + $gExtra;
@endphp

@include('report._header')

<div class="card">
    <div class="card-header"><div class="card-title">Total Sale</div></div>
    <div class="card-content" style="padding:0;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th rowspan="2">Bulan</th>
                        <th rowspan="2" class="text-right">Qty (L)</th>
                        <th rowspan="2" class="text-right">Extra (L)</th>
                        <th rowspan="2" class="text-right">Total Qty (L)</th>
                        <th colspan="2" class="text-center">Total Amount</th>
                    </tr>
                    <tr>
                        <th class="text-center" style="color:#16a34a;">Paid</th>
                        <th class="text-center" style="color:#dc2626;">Unpaid</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($months as $m => $name)
                        @php
                            $qty   = (float)($salesPaid->get($m)->total_qty      ?? 0) + (float)($salesApproved->get($m)->total_qty   ?? 0);
                            $extra = (float)($salesPaid->get($m)->total_extra     ?? 0) + (float)($salesApproved->get($m)->total_extra  ?? 0);
                            $amtP  = (float)($salesPaid->get($m)->total_amount    ?? 0);
                            $amtA  = (float)($salesApproved->get($m)->total_amount ?? 0);
                            $tot   = $qty + $extra;
                        @endphp
                        <tr>
                            <td>{{ $name }}</td>
                            <td class="text-right">{{ $qty   ? $fmtQty($qty)   : '-' }}</td>
                            <td class="text-right">{{ $extra ? $fmtQty($extra) : '-' }}</td>
                            <td class="text-right">{{ $tot   ? $fmtQty($tot)   : '-' }}</td>
                            <td class="text-right" style="color:#16a34a;font-weight:600;">{{ $amtP ? 'Rp '.$fmt($amtP) : '-' }}</td>
                            <td class="text-right" style="color:#dc2626;font-weight:600;">{{ $amtA ? 'Rp '.$fmt($amtA) : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="report-total-row">
                        <td><strong>Total</strong></td>
                        <td class="text-right"><strong>{{ $fmtQty($gQty) }}</strong></td>
                        <td class="text-right"><strong>{{ $fmtQty($gExtra) }}</strong></td>
                        <td class="text-right"><strong>{{ $fmtQty($gTot) }}</strong></td>
                        <td class="text-right" style="color:#16a34a;font-weight:700;">Rp {{ $fmt($gAmtP) }}</td>
                        <td class="text-right" style="color:#dc2626;font-weight:700;">Rp {{ $fmt($gAmtA) }}</td>
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
const basePrintUrl = '{{ route('report.print', ['section' => 'sale', 'year' => $year]) }}';
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
