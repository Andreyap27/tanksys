@extends('layouts.app')
@section('title', 'Laporan Lori Expense - Trash')
@section('content')

@php
$pageTitle = 'Pengeluaran Mobil (Trash)';
$months = [
1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April',
5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember',
];
$fmt = fn($n) => number_format((float)$n, 0, ',', '.');
@endphp

@include('report._header')

<div class="card">
    <div class="card-header">
        <div class="card-title">Pengeluaran Mobil (Trash)</div>
    </div>
    <div class="card-content" style="padding:0;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Bulan</th>
                        @foreach($cats as $cat)
                        <th class="text-right">{{ $cat }}</th>
                        @endforeach
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($months as $m => $name)
                    <tr>
                        <td>{{ $name }}</td>
                        @foreach($cats as $cat)
                        @php
                        $amount = 0;
                        if ($loriExpensesByCategory->has($m)) {
                        $expenses = $loriExpensesByCategory->get($m);
                        $categoryExpense = $expenses->firstWhere('category', $cat);
                        $amount = (float)($categoryExpense->total ?? 0);
                        }
                        @endphp
                        <td class="text-right">{{ $amount ? 'Rp '.$fmt($amount) : '-' }}</td>
                        @endforeach
                        <td class="text-right"><strong>Rp {{ $fmt($loriExpensesTotal->get($m) ?? 0) }}</strong></td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="report-total-row">
                        <td><strong>Total</strong></td>
                        @php $grandTotal = 0; @endphp
                        @foreach($cats as $cat)
                        @php
                        $catTotal = 0;
                        foreach ($loriExpensesByCategory as $monthExpenses) {
                        $catExpense = $monthExpenses->firstWhere('category', $cat);
                        if ($catExpense) $catTotal += (float)$catExpense->total;
                        }
                        $grandTotal += $catTotal;
                        @endphp
                        <td class="text-right"><strong>Rp {{ $fmt($catTotal) }}</strong></td>
                        @endforeach
                        <td class="text-right"><strong>Rp {{ $fmt($grandTotal) }}</strong></td>
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
    const basePrintUrl = '{{ route('
    report.print ', ['
    section ' => '
    lori - expense ', '
    year ' => $year]) }}';

    function openPrintModal() {
        const mobilId = new URLSearchParams(window.location.search).get('mobil_id');
        const url = basePrintUrl + (mobilId ? '&mobil_id=' + encodeURIComponent(mobilId) : '');
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