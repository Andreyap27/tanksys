@extends('layouts.app')
@section('title', 'Laporan Expense - Trash')
@section('content')

@php
$pageTitle = 'Total Expense (Trash)';
$months = [
1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April',
5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember',
];
$fmt = fn($n) => number_format((float)$n, 0, ',', '.');
$categories = \App\Models\Expense::CATEGORIES;
@endphp

@include('report._header')

<div class="card">
    <div class="card-header">
        <div class="card-title">Total Expense (Trash)</div>
    </div>
    <div class="card-content" style="padding:0;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Bulan</th>
                        @foreach($categories as $cat)
                        <th class="text-right">{{ $cat }}</th>
                        @endforeach
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($months as $m => $name)
                    <tr>
                        <td>{{ $name }}</td>
                        @php $monthTotal = 0; @endphp
                        @foreach($categories as $cat)
                        @php
                        $amount = 0;
                        if ($expensesByCategory->has($m)) {
                        $expenses = $expensesByCategory->get($m);
                        $categoryExpense = $expenses->firstWhere('category', $cat);
                        $amount = (float)($categoryExpense->total ?? 0);
                        }
                        $monthTotal += $amount;
                        @endphp
                        <td class="text-right">{{ $amount ? 'Rp '.$fmt($amount) : '-' }}</td>
                        @endforeach
                        <td class="text-right"><strong>Rp {{ $fmt($expensesTotal->get($m) ?? 0) }}</strong></td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="report-total-row">
                        <td><strong>Total</strong></td>
                        @php $grandTotal = 0; @endphp
                        @foreach($categories as $cat)
                        @php
                        $catTotal = 0;
                        foreach ($expensesByCategory as $monthExpenses) {
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
    expense ', '
    year ' => $year]) }}';

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