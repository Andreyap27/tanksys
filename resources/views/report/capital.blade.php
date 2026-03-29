@extends('layouts.app')
@section('title', 'Laporan Capital')
@section('content')

@php
    $pageTitle = 'Total Capital';
    $months = [
        1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April',
        5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
        9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember',
    ];
    $fmt = fn($n) => number_format((float)$n, 0, ',', '.');

    $gCount = 0; $gNominal = 0;
    foreach (range(1,12) as $m) {
        $gCount   += (int)($capitals[$m]->total_count   ?? 0);
        $gNominal += (float)($capitals[$m]->total_nominal ?? 0);
    }
@endphp

@include('report._header')

<div class="card">
    <div class="card-header"><div class="card-title">Total Capital</div></div>
    <div class="card-content" style="padding:0;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Bulan</th>
                        <th class="text-right">Jumlah Transaksi</th>
                        <th class="text-right">Total Nominal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($months as $m => $name)
                        @php
                            $count   = (int)($capitals[$m]->total_count   ?? 0);
                            $nominal = (float)($capitals[$m]->total_nominal ?? 0);
                        @endphp
                        <tr>
                            <td>{{ $name }}</td>
                            <td class="text-right">{{ $count ?: '-' }}</td>
                            <td class="text-right">{{ $nominal ? 'Rp '.$fmt($nominal) : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="report-total-row">
                        <td><strong>Total</strong></td>
                        <td class="text-right"><strong>{{ $gCount }}</strong></td>
                        <td class="text-right"><strong>Rp {{ $fmt($gNominal) }}</strong></td>
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
const printUrl = '{{ route('report.print', ['section' => 'capital', 'year' => $year]) }}';
function openPrintModal()  { document.getElementById('printFrame').src = printUrl; document.getElementById('printModal').classList.add('active'); }
function closePrintModal() { document.getElementById('printModal').classList.remove('active'); document.getElementById('printFrame').src = ''; }
lucide.createIcons();
</script>
@endpush
