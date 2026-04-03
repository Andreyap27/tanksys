@extends('layouts.app')
@section('title', 'Laporan Lori Profit/Loss - Trash')
@section('content')

@php
$pageTitle = 'Profit / Loss Mobil (Trash)';
$months = [
1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April',
5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember',
];
$fmt = fn($n) => number_format((float)$n, 0, ',', '.');
$gIncome = 0; $gExpense = 0; $gProfit = 0;
foreach (range(1,12) as $m) {
$income = (float)($loris->get($m) ?? 0);
$expense = (float)($loriExpenses->get($m) ?? 0);
$profit = $income - $expense;
$gIncome += $income;
$gExpense += $expense;
$gProfit += $profit;
}
@endphp

@include('report._header')

<div class="card">
    <div class="card-header">
        <div class="card-title">Profit / Loss Mobil (Trash)</div>
    </div>
    <div class="card-content" style="padding:0;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Bulan</th>
                        <th class="text-right">Total Income</th>
                        <th class="text-right">Total Expense</th>
                        <th class="text-right">Profit/Loss</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($months as $m => $name)
                    @php
                    $income = (float)($loris->get($m) ?? 0);
                    $expense = (float)($loriExpenses->get($m) ?? 0);
                    $profit = $income - $expense;
                    @endphp
                    <tr>
                        <td>{{ $name }}</td>
                        <td class="text-right">{{ $income ? 'Rp '.$fmt($income) : '-' }}</td>
                        <td class="text-right">{{ $expense ? 'Rp '.$fmt($expense) : '-' }}</td>
                        <td class="text-right" style="color:{{ $profit >= 0 ? '#16a34a' : '#dc2626' }}">
                            <strong>{{ $profit ? 'Rp '.$fmt($profit) : '-' }}</strong>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="report-total-row">
                        <td><strong>Total</strong></td>
                        <td class="text-right"><strong>Rp {{ $fmt($gIncome) }}</strong></td>
                        <td class="text-right"><strong>Rp {{ $fmt($gExpense) }}</strong></td>
                        <td class="text-right" style="color:{{ $gProfit >= 0 ? '#16a34a' : '#dc2626' }}">
                            <strong>Rp {{ $fmt($gProfit) }}</strong>
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
const basePrintUrl = '{{ route('report.print', ['section' => 'lori','year' => $year]) }}&trash=1';

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