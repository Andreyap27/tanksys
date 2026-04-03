@extends('layouts.app')
@section('title', 'Laporan Capital - Trash')
@section('content')

@php
$pageTitle = 'Total Capital (Trash)';
$months = [
1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April',
5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember',
];
$fmt = fn($n) => number_format((float)$n, 0, ',', '.');
$gCount = 0; $gNom = 0;
foreach (range(1,12) as $m) {
$gCount += (int)($capitals->get($m)->total_count ?? 0);
$gNom += (float)($capitals->get($m)->total_nominal ?? 0);
}
@endphp

@include('report._header')

<div class="card">
    <div class="card-header">
        <div class="card-title">Total Capital (Trash)</div>
    </div>
    <div class="card-content" style="padding:0;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Bulan</th>
                        <th class="text-right">Jumlah</th>
                        <th class="text-right">Total Nominal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($months as $m => $name)
                    @php
                    $count = (int)($capitals->get($m)->total_count ?? 0);
                    $nom = (float)($capitals->get($m)->total_nominal ?? 0);
                    @endphp
                    <tr>
                        <td>{{ $name }}</td>
                        <td class="text-right">{{ $count ? $count : '-' }}</td>
                        <td class="text-right">{{ $nom ? 'Rp '.$fmt($nom) : '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="report-total-row">
                        <td><strong>Total</strong></td>
                        <td class="text-right"><strong>{{ $gCount }}</strong></td>
                        <td class="text-right"><strong>Rp {{ $fmt($gNom) }}</strong></td>
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
    const basePrintUrl = '{{ route('report.print', ['section' => 'capital','year' => $year]) }}&trash=1';

    function openPrintModal() {
        const kapalId = new URLSearchParams(window.location.search).get('kapal_id');
        const url = basePrintUrl + (kapalId ? '&kapal_id=' + encodeURIComponent(kapalId) : '');
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
