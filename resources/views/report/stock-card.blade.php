@extends('layouts.app')
@section('title', 'Stok Card BBM')
@section('content')

@php
    $pageTitle = 'Stok Card BBM';
    $fmtQty    = fn($n) => number_format((float)$n, 2, ',', '.');

    $typeBadge = [
        'purchase'     => ['label' => 'Pembelian',      'class' => 'badge-success'],
        'sale'         => ['label' => 'Penjualan',      'class' => 'badge-danger'],
        'transfer_in'  => ['label' => 'Transfer Masuk', 'class' => 'badge-info'],
        'transfer_out' => ['label' => 'Transfer Keluar','class' => 'badge-warning'],
        'usage'        => ['label' => 'Pemakaian',      'class' => 'badge-secondary'],
    ];
@endphp

@include('report._header')

{{-- Month Filter --}}
<div style="margin-bottom:1rem;">
    <form method="GET" action="{{ route('report.stock-card') }}" style="display:flex;gap:0.5rem;align-items:center;">
        <input type="hidden" name="year" value="{{ $year }}">
        <select name="month" class="form-select" style="width:auto;" onchange="this.form.submit()">
            <option value="">-- Semua Bulan --</option>
            @foreach($months as $m => $name)
                <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>{{ $name }}</option>
            @endforeach
        </select>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            Stok Card BBM — {{ $year }}
            @if($selectedMonth) · {{ $months[$selectedMonth] }} @endif
        </div>
    </div>
    <div class="card-content" style="padding:0;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Tipe</th>
                        <th>Keterangan</th>
                        <th class="text-right">Masuk (L)</th>
                        <th class="text-right">Keluar (L)</th>
                        <th class="text-right">Pemakaian (L)</th>
                        <th class="text-right">Saldo (L)</th>
                    </tr>
                </thead>
                <tbody>
                    @if($openingBalance != 0)
                    <tr style="background:var(--muted);font-style:italic;">
                        <td colspan="3" style="color:var(--muted-foreground);font-size:0.82rem;">Saldo Awal Periode</td>
                        <td class="text-right">-</td>
                        <td class="text-right">-</td>
                        <td class="text-right">-</td>
                        <td class="text-right"><strong>{{ $fmtQty($openingBalance) }}</strong></td>
                    </tr>
                    @endif

                    @forelse($stocks as $s)
                        @php
                            $isUsage = $s->type === 'usage';
                            $badge   = $typeBadge[$s->type] ?? ['label' => $s->type, 'class' => 'badge-secondary'];
                        @endphp
                        <tr>
                            <td>{{ $s->date->translatedFormat('d M Y') }}</td>
                            <td><span class="badge {{ $badge['class'] }}">{{ $badge['label'] }}</span></td>
                            <td>{{ $s->party ?: '-' }}</td>
                            <td class="text-right">{{ $s->qty_in > 0 ? $fmtQty($s->qty_in) : '-' }}</td>
                            <td class="text-right">{{ !$isUsage && $s->qty_out > 0 ? $fmtQty($s->qty_out) : '-' }}</td>
                            <td class="text-right">{{ $isUsage && $s->qty_out > 0 ? $fmtQty($s->qty_out) : '-' }}</td>
                            <td class="text-right"><strong>{{ $fmtQty($s->running_balance) }}</strong></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center" style="color:var(--muted-foreground);padding:2rem;">
                                Tidak ada data untuk periode ini
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="report-total-row">
                        <td colspan="3"><strong>Total</strong></td>
                        <td class="text-right"><strong>{{ $fmtQty($totalMasuk) }}</strong></td>
                        <td class="text-right"><strong>{{ $fmtQty($totalKeluar) }}</strong></td>
                        <td class="text-right"><strong>{{ $fmtQty($totalPemakaian) }}</strong></td>
                        <td class="text-right"><strong>{{ $fmtQty($saldoAkhir) }}</strong></td>
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
function openPrintModal() {
    const params = new URLSearchParams(window.location.search);
    params.set('year', '{{ $year }}');
    const url = '{{ route('report.stock-card.print') }}?' + params.toString();
    document.getElementById('printFrame').src = url;
    document.getElementById('printModal').classList.add('active');
    if (typeof lucide !== 'undefined') lucide.createIcons();
}
function closePrintModal() {
    document.getElementById('printModal').classList.remove('active');
    document.getElementById('printFrame').src = '';
}
lucide.createIcons();
</script>
@endpush
