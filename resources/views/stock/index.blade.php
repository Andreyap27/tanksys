@extends('layouts.app')

@section('title', 'Data Stok BBM')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title-text">Stok BBM</h1>
        <p class="page-subtitle">Riwayat dan saldo stok bahan bakar</p>
    </div>
</div>

@php
    $warnaConfig = [
        'merah'  => ['label' => 'Merah',  'color' => '#dc2626', 'bg' => 'rgba(220,38,38,0.08)',  'iconBg' => 'rgba(220,38,38,0.12)'],
        'biru'   => ['label' => 'Biru',   'color' => '#2563eb', 'bg' => 'rgba(37,99,235,0.08)',  'iconBg' => 'rgba(37,99,235,0.12)'],
        'kuning' => ['label' => 'Kuning', 'color' => '#ca8a04', 'bg' => 'rgba(202,138,4,0.08)', 'iconBg' => 'rgba(202,138,4,0.12)'],
    ];
@endphp
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1.25rem;margin-bottom:1.5rem;">
    @foreach($warnaConfig as $key => $cfg)
    <div style="background:{{ $cfg['bg'] }};border-radius:var(--radius-lg,0.75rem);padding:1.25rem;position:relative;overflow:hidden;">
        <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1rem;">
            <div style="background:{{ $cfg['iconBg'] }};color:{{ $cfg['color'] }};border-radius:0.5rem;padding:0.5rem;display:flex;">
                <i data-lucide="droplets" style="width:20px;height:20px;"></i>
            </div>
            <span style="font-weight:700;font-size:1rem;color:{{ $cfg['color'] }};">BBM {{ $cfg['label'] }}</span>
        </div>
        <div style="display:flex;flex-direction:column;gap:0.5rem;">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <span style="font-size:0.75rem;color:var(--muted-foreground);">Saldo</span>
                <span id="stock_balance_{{ $key }}" style="font-weight:700;font-size:1rem;color:{{ $cfg['color'] }};">
                    {{ number_format($byWarna[$key]['balance'] ?? 0, 2, ',', '.') }} L
                </span>
            </div>
            <div style="border-top:1px solid {{ $cfg['color'] }}22;"></div>
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <span style="font-size:0.75rem;color:var(--muted-foreground);">Total Masuk</span>
                <span id="stock_in_{{ $key }}" style="font-weight:600;font-size:0.875rem;color:var(--success,#16a34a);">
                    +{{ number_format($byWarna[$key]['in'] ?? 0, 2, ',', '.') }} L
                </span>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <span style="font-size:0.75rem;color:var(--muted-foreground);">Total Keluar</span>
                <span id="stock_out_{{ $key }}" style="font-weight:600;font-size:0.875rem;color:var(--destructive,#dc2626);">
                    -{{ number_format($byWarna[$key]['out'] ?? 0, 2, ',', '.') }} L
                </span>
            </div>
        </div>
        <div style="position:absolute;right:-1rem;bottom:-1rem;color:{{ $cfg['color'] }};opacity:0.08;pointer-events:none;">
            <i data-lucide="droplets" style="width:110px;height:110px;"></i>
        </div>
    </div>
    @endforeach

    {{-- Extra Card --}}
    <div style="background:rgba(124,58,237,0.08);border-radius:var(--radius-lg,0.75rem);padding:1.25rem;position:relative;overflow:hidden;">
        <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1rem;">
            <div style="background:rgba(124,58,237,0.12);color:#7c3aed;border-radius:0.5rem;padding:0.5rem;display:flex;">
                <i data-lucide="plus-circle" style="width:20px;height:20px;"></i>
            </div>
            <span style="font-weight:700;font-size:1rem;color:#7c3aed;">Total Extra</span>
        </div>
        <table style="width:100%;border-collapse:collapse;font-size:0.75rem;">
            <thead>
                <tr style="color:var(--muted-foreground);">
                    <th style="text-align:left;font-weight:500;padding-bottom:0.4rem;">Warna</th>
                    <th style="text-align:right;font-weight:500;padding-bottom:0.4rem;">Qty (L)</th>
                    <th style="text-align:right;font-weight:500;padding-bottom:0.4rem;">Extra (L)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($warnaConfig as $key => $cfg)
                <tr>
                    <td style="padding:0.2rem 0;display:flex;align-items:center;gap:0.35rem;">
                        <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:{{ $cfg['color'] }};flex-shrink:0;"></span>
                        <span style="color:{{ $cfg['color'] }};font-weight:600;">{{ $cfg['label'] }}</span>
                    </td>
                    <td style="text-align:right;font-weight:600;color:#7c3aed;padding:0.2rem 0;">
                        <span id="stock_qty_{{ $key }}">{{ number_format($extras[$key]['qty'] ?? 0, 2, ',', '.') }}</span>
                    </td>
                    <td style="text-align:right;font-weight:600;color:#7c3aed;padding:0.2rem 0;">
                        <span id="stock_extra_{{ $key }}">{{ number_format($extras[$key]['extra'] ?? 0, 2, ',', '.') }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div style="position:absolute;right:-1rem;bottom:-1rem;color:#7c3aed;opacity:0.08;pointer-events:none;">
            <i data-lucide="plus-circle" style="width:110px;height:110px;"></i>
        </div>
    </div>
</div>

{{-- Kapal Tabs --}}
<div class="tab-bar" id="stockTabs">
    <button class="tab active" data-kapal-id="" onclick="switchStockTab(this, '')"><i data-lucide="list" style="width:16px;height:16px;"></i> Semua</button>
    @foreach($kapals as $k)
    <button class="tab" data-kapal-id="{{ $k->id }}" onclick="switchStockTab(this, '{{ $k->id }}')"><i data-lucide="ship" style="width:16px;height:16px;"></i> {{ $k->name }}</button>
    @endforeach
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
let stockTable;
let activeStockKapalId = '';

function switchStockTab(btn, kapalId) {
    document.querySelectorAll('#stockTabs .tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    activeStockKapalId = kapalId;
    refreshStockSummary(kapalId);
    stockTable.ajax.reload(null, false);
}

function refreshStockSummary(kapalId) {
    const params = {};
    if (kapalId) params.kapal_id = kapalId;
    axios.get('{{ route('stock.summary') }}', { params }).then(res => {
        const fmt = n => new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n);
        ['merah', 'biru', 'kuning'].forEach(w => {
            const d = res.data.byWarna?.[w] ?? { balance: 0, in: 0, out: 0 };
            document.getElementById(`stock_balance_${w}`).textContent = fmt(d.balance) + ' L';
            document.getElementById(`stock_in_${w}`).textContent      = '+' + fmt(d.in)  + ' L';
            document.getElementById(`stock_out_${w}`).textContent     = '-' + fmt(d.out) + ' L';

            const ex = res.data.extras?.[w] ?? { qty: 0, extra: 0 };
            document.getElementById(`stock_qty_${w}`).textContent   = fmt(ex.qty)   + ' L';
            document.getElementById(`stock_extra_${w}`).textContent = fmt(ex.extra) + ' L';
        });
    });
}

$(document).ready(function () {
    lucide.createIcons();

    stockTable = $('#stockTable').DataTable({
        ajax: {
            url: '{{ route('stock.data') }}',
            type: 'GET',
            data: function(d) {
                if (activeStockKapalId) d.kapal_id = activeStockKapalId;
            }
        },
        processing: true,
        columns: [
            { data: 'date' },
            { data: 'party' },
            {
                data: 'type',
                render: function (data) {
                    const map = {
                        'Pembelian':      '<span class="badge badge-success">Pembelian</span>',
                        'Penjualan':      '<span class="badge badge-danger">Penjualan</span>',
                        'Transfer Masuk': '<span class="badge" style="background:#7c3aed;color:#fff;">Transfer Masuk</span>',
                        'Transfer Keluar':'<span class="badge" style="background:#9333ea;color:#fff;">Transfer Keluar</span>',
                        'Pemakaian':      '<span class="badge" style="background:#ea580c;color:#fff;">Pemakaian</span>',
                    };
                    return map[data] || `<span class="badge badge-info">${data}</span>`;
                }
            },
            {
                data: null,
                render: function (data, type, row) {
                    if (row.qty_in)  return `<span style="color:var(--success);font-weight:600;">+${row.qty_in}</span>`;
                    if (row.qty_out) return `<span style="color:var(--destructive);font-weight:600;">-${row.qty_out}</span>`;
                    return '-';
                }
            },
            {
                data: 'balance',
                render: function (data) { return `<span style="font-weight:600;">${data}</span>`; }
            },
        ],
        order: [[0, 'desc']],
        drawCallback: function () { lucide.createIcons(); }
    });
});
</script>
@endpush
