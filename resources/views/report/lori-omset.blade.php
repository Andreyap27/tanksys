@extends('layouts.app')
@section('title', 'Omset Mobil Tangki')
@section('content')

@php
    $pageTitle = 'Omset Mobil Tangki';
    $months = [
        1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April',
        5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
        9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember',
    ];
    $fmt = fn($n) => number_format((float)$n, 0, ',', '.');

    $gTotal = 0;
    foreach (range(1,12) as $m) {
        $gTotal += (float)($loris[$m] ?? 0);
    }
@endphp

@include('report._header')

<div class="card">
    <div class="card-header"><div class="card-title">Omset Mobil Tangki</div></div>
    <div class="card-content" style="padding:0;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Bulan</th>
                        <th class="text-right">Total Omset</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($months as $m => $name)
                        @php $income = (float)($loris[$m] ?? 0); @endphp
                        <tr>
                            <td>{{ $name }}</td>
                            <td class="text-right">{{ $income ? 'Rp '.$fmt($income) : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="report-total-row">
                        <td><strong>Total</strong></td>
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
@php $printRoute = ['section' => 'lori-omset', 'year' => $year]; if (!empty($mobilId)) $printRoute['mobil_id'] = $mobilId; @endphp
const printUrl = '{{ route('report.print', $printRoute) }}';
function openPrintModal()  { document.getElementById('printFrame').src = printUrl; document.getElementById('printModal').classList.add('active'); }
function closePrintModal() { document.getElementById('printModal').classList.remove('active'); document.getElementById('printFrame').src = ''; }
lucide.createIcons();
</script>
@endpush
