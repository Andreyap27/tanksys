@extends('layouts.app')

@section('title', 'Data Stok BBM')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title-text">Stok BBM</h1>
        <p class="page-subtitle">Riwayat dan saldo stok bahan bakar</p>
    </div>
</div>

<div class="stock-summary-grid">
    {{-- Saldo --}}
    <div class="stock-card stock-card--balance">
        <div class="stock-card__header">
            <div class="stock-card__icon">
                <i data-lucide="fuel" style="width:20px;height:20px;"></i>
            </div>
            <div>
                <div class="stock-card__label">Saldo Saat Ini</div>
                <div class="stock-card__value">{{ number_format($balance ?? 0, 2, ',', '.') }}</div>
                <div class="stock-card__unit">Liter tersedia</div>
            </div>
        </div>
        <div class="stock-card__bg-icon">
            <i data-lucide="fuel" style="width:110px;height:110px;"></i>
        </div>
    </div>

    {{-- Total Masuk --}}
    <div class="stock-card stock-card--in">
        <div class="stock-card__header">
            <div class="stock-card__icon">
                <i data-lucide="arrow-down-to-line" style="width:20px;height:20px;"></i>
            </div>
            <div>
                <div class="stock-card__label">Total Masuk</div>
                <div class="stock-card__value">{{ number_format($totalIn ?? 0, 2, ',', '.') }}</div>
                <div class="stock-card__unit">Liter diterima</div>
            </div>
        </div>
        <div class="stock-card__bg-icon">
            <i data-lucide="arrow-down-to-line" style="width:110px;height:110px;"></i>
        </div>
    </div>

    {{-- Total Keluar --}}
    <div class="stock-card stock-card--out">
        <div class="stock-card__header">
            <div class="stock-card__icon">
                <i data-lucide="arrow-up-from-line" style="width:20px;height:20px;"></i>
            </div>
            <div>
                <div class="stock-card__label">Total Keluar</div>
                <div class="stock-card__value">{{ number_format($totalOut ?? 0, 2, ',', '.') }}</div>
                <div class="stock-card__unit">Liter terpakai</div>
            </div>
        </div>
        <div class="stock-card__bg-icon">
            <i data-lucide="arrow-up-from-line" style="width:110px;height:110px;"></i>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-toolbar"><div class="dt-search-slot"></div></div>
    <div class="card-content" style="padding:0;">
        <div class="table-wrap">
            <table id="stockTable" class="w-full">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Vendor / Customer</th>
                        <th>Tipe</th>
                        <th>Qty (L)</th>
                        <th>Saldo (L)</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    lucide.createIcons();

    $('#stockTable').DataTable({
        ajax: { url: '{{ route('stock.data') }}', type: 'GET' },
        columns: [
            { data: 'date' },
            { data: 'party' },
            {
                data: 'type',
                render: function (data) {
                    if (data === 'Pembelian') return '<span class="badge badge-success">Pembelian</span>';
                    if (data === 'Penjualan') return '<span class="badge badge-danger">Penjualan</span>';
                    return `<span class="badge badge-info">${data}</span>`;
                }
            },
            {
                data: null,
                render: function (data, type, row) {
                    if (row.qty_in) {
                        return `<span style="color:var(--success);font-weight:600;">+${row.qty_in}</span>`;
                    }
                    if (row.qty_out) {
                        return `<span style="color:var(--destructive);font-weight:600;">-${row.qty_out}</span>`;
                    }
                    return '-';
                }
            },
            {
                data: 'balance',
                render: function (data) {
                    return `<span style="font-weight:600;">${data}</span>`;
                }
            },
        ],
        order: [[0, 'desc']],
        drawCallback: function () { lucide.createIcons(); }
    });
});
</script>
@endpush
