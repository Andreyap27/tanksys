@extends('layouts.app')

@section('title', 'Data Stok BBM')

@section('content')
<div class="card" style="margin-bottom:1.5rem;">
    <div class="card-header">
        <h2 class="card-title">Saldo Stok BBM</h2>
    </div>
    <div class="card-content">
        <div style="display:flex;align-items:center;gap:1rem;">
            <div style="padding:1rem 2rem;background:var(--primary,#2563eb);color:#fff;border-radius:0.75rem;text-align:center;">
                <div style="font-size:0.85rem;opacity:0.85;">Saldo Saat Ini</div>
                <div style="font-size:2rem;font-weight:700;">
                    {{ number_format($balance ?? 0, 2, ',', '.') }} <span style="font-size:1rem;font-weight:400;">Liter</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Riwayat Stok</h2>
    </div>
    <div class="card-content">
        <div class="table-wrap">
            <table id="stockTable" class="w-full">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Vendor / Customer</th>
                        <th>Tipe</th>
                        <th>Masuk (L)</th>
                        <th>Keluar (L)</th>
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
    $('#stockTable').DataTable({
        ajax: {
            url: '{{ route('stock.data') }}',
            type: 'GET',
        },
        columns: [
            { data: 'date' },
            { data: 'party' },
            {
                data: 'type',
                render: function (data) {
                    if (data === 'Pembelian') {
                        return '<span class="badge badge-success">Pembelian</span>';
                    } else if (data === 'Penjualan') {
                        return '<span class="badge badge-danger">Penjualan</span>';
                    }
                    return `<span class="badge badge-info">${data}</span>`;
                }
            },
            {
                data: 'in',
                render: function (data) {
                    return data ? parseFloat(data).toLocaleString('id-ID') : '-';
                }
            },
            {
                data: 'out',
                render: function (data) {
                    return data ? parseFloat(data).toLocaleString('id-ID') : '-';
                }
            },
            {
                data: 'balance',
                render: function (data) {
                    return parseFloat(data).toLocaleString('id-ID');
                }
            },
        ],
        order: [[0, 'desc']],
        drawCallback: function () {
            lucide.createIcons();
        }
    });
});
</script>
@endpush
