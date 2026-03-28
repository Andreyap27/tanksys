@extends('layouts.app')
@section('title', 'Laporan Expense')
@section('content')

@php
    $pageTitle = 'Total Expense';
    $months = [
        1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April',
        5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
        9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember',
    ];
    $categories = \App\Models\Expense::CATEGORIES;
    $fmt = fn($n) => number_format((float)$n, 0, ',', '.');

    $expMatrix = [];
    foreach ($expensesByCategory as $m => $items) {
        foreach ($items as $item) {
            $expMatrix[(int)$m][$item->category] = (float)$item->total;
        }
    }

    $gExpCat   = array_fill_keys($categories, 0);
    $gExpTotal = 0;
    foreach (range(1,12) as $m) {
        $gExpTotal += (float)($expensesTotal[$m] ?? 0);
        foreach ($categories as $cat) {
            $gExpCat[$cat] += $expMatrix[$m][$cat] ?? 0;
        }
    }
@endphp

@include('report._header')

<div class="card">
    <div class="card-header"><div class="card-title">Total Expense</div></div>
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
                        @php $rowTotal = (float)($expensesTotal[$m] ?? 0); @endphp
                        <tr>
                            <td>{{ $name }}</td>
                            @foreach($categories as $cat)
                                @php $val = $expMatrix[$m][$cat] ?? 0; @endphp
                                <td class="text-right">{{ $val ? 'Rp '.$fmt($val) : '-' }}</td>
                            @endforeach
                            <td class="text-right">{{ $rowTotal ? 'Rp '.$fmt($rowTotal) : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="report-total-row">
                        <td><strong>Total</strong></td>
                        @foreach($categories as $cat)
                            <td class="text-right"><strong>{{ $gExpCat[$cat] ? 'Rp '.$fmt($gExpCat[$cat]) : '-' }}</strong></td>
                        @endforeach
                        <td class="text-right"><strong>Rp {{ $fmt($gExpTotal) }}</strong></td>
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
const printUrl = '{{ route('report.print', ['section' => 'expense', 'year' => $year]) }}';
function openPrintModal()  { document.getElementById('printFrame').src = printUrl; document.getElementById('printModal').classList.add('active'); }
function closePrintModal() { document.getElementById('printModal').classList.remove('active'); document.getElementById('printFrame').src = ''; }
lucide.createIcons();
</script>
@endpush
