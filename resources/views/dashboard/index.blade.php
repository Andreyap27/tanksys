@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

@php
$fmt = fn($n) => number_format((float)$n, 0, ',', '.');
$fmtQty = fn($n) => number_format((float)$n, 2, ',', '.');
$isProfit = $profitAmt >= 0;
$profitClass = $isProfit ? 'ds-profit' : 'ds-loss';
$profitIcon = $isProfit ? 'trending-up' : 'trending-down';
@endphp

{{-- ── Page Header ──────────────────────────────────────────────────────────── --}}
<div class="page-header">
    <div>
        <h1 class="page-title-text">Dashboard</h1>
        <p class="page-subtitle">{{ now()->translatedFormat('l, d F Y') }}</p>
    </div>
</div>

{{-- ── Stat Cards ───────────────────────────────────────────────────────────── --}}
<div class="dash-grid">

    {{-- Sales --}}
    <div class="dash-stat ds-sales">
        <div class="dash-stat__header">
            <div class="dash-stat__icon">
                <i data-lucide="arrow-up-from-line" style="width:20px;height:20px;"></i>
            </div>
            <div>
                <div class="dash-stat__label">Penjualan Bulan Ini</div>
                <div class="dash-stat__value">Rp {{ $fmt($salesAmt) }}</div>
                <div class="dash-stat__trend {{ $salesTrend['dir'] === 'up' ? 'up' : ($salesTrend['dir'] === 'down' ? 'down' : 'flat') }}">
                    @if($salesTrend['dir'] === 'up')
                    <i data-lucide="trending-up"></i> +{{ $salesTrend['pct'] }}% vs bulan lalu
                    @elseif($salesTrend['dir'] === 'down')
                    <i data-lucide="trending-down"></i> -{{ $salesTrend['pct'] }}% vs bulan lalu
                    @elseif($salesTrend['dir'] === 'new')
                    <i data-lucide="star"></i> Baru bulan ini
                    @else
                    <i data-lucide="minus"></i> Sama seperti bulan lalu
                    @endif
                </div>
            </div>
        </div>
        <div class="dash-stat__bg-icon"><i data-lucide="arrow-up-from-line" style="width:110px;height:110px;"></i></div>
    </div>

    {{-- Purchase --}}
    <div class="dash-stat ds-purchase">
        <div class="dash-stat__header">
            <div class="dash-stat__icon">
                <i data-lucide="arrow-down-to-line" style="width:20px;height:20px;"></i>
            </div>
            <div>
                <div class="dash-stat__label">Pembelian Bulan Ini</div>
                <div class="dash-stat__value">Rp {{ $fmt($purchaseAmt) }}</div>
                <div class="dash-stat__trend {{ $purchaseTrend['dir'] === 'up' ? 'up' : ($purchaseTrend['dir'] === 'down' ? 'down' : 'flat') }}">
                    @if($purchaseTrend['dir'] === 'up')
                    <i data-lucide="trending-up"></i> +{{ $purchaseTrend['pct'] }}% vs bulan lalu
                    @elseif($purchaseTrend['dir'] === 'down')
                    <i data-lucide="trending-down"></i> -{{ $purchaseTrend['pct'] }}% vs bulan lalu
                    @elseif($purchaseTrend['dir'] === 'new')
                    <i data-lucide="star"></i> Baru bulan ini
                    @else
                    <i data-lucide="minus"></i> Sama seperti bulan lalu
                    @endif
                </div>
            </div>
        </div>
        <div class="dash-stat__bg-icon"><i data-lucide="arrow-down-to-line" style="width:110px;height:110px;"></i></div>
    </div>

    {{-- Profit / Loss --}}
    <div class="dash-stat {{ $profitClass }}">
        <div class="dash-stat__header">
            <div class="dash-stat__icon">
                <i data-lucide="{{ $profitIcon }}" style="width:20px;height:20px;"></i>
            </div>
            <div>
                <div class="dash-stat__label">Profit / Loss Bulan Ini</div>
                <div class="dash-stat__value" style="color:{{ $isProfit ? 'var(--success)' : 'var(--destructive)' }};">
                    {{ $profitAmt < 0 ? '-' : '' }}Rp {{ $fmt(abs($profitAmt)) }}
                </div>
                <div class="dash-stat__trend flat">
                    <i data-lucide="calculator"></i>
                    Sales − Purchase − Expenses
                </div>
            </div>
        </div>
        <div class="dash-stat__bg-icon"><i data-lucide="{{ $profitIcon }}" style="width:110px;height:110px;"></i></div>
    </div>

    {{-- Expenses --}}
    <div class="dash-stat ds-expense">
        <div class="dash-stat__header">
            <div class="dash-stat__icon">
                <i data-lucide="receipt" style="width:20px;height:20px;"></i>
            </div>
            <div>
                <div class="dash-stat__label">Pengeluaran Bulan Ini</div>
                <div class="dash-stat__value">Rp {{ $fmt($expenseAmt) }}</div>
                <div class="dash-stat__trend flat">
                    <i data-lucide="layers"></i>
                    {{ $expByCategory->count() }} kategori pengeluaran
                </div>
            </div>
        </div>
        <div class="dash-stat__bg-icon"><i data-lucide="receipt" style="width:110px;height:110px;"></i></div>
    </div>

    {{-- Stock --}}
    <div class="dash-stat ds-stock">
        <div class="dash-stat__header">
            <div class="dash-stat__icon">
                <i data-lucide="fuel" style="width:20px;height:20px;"></i>
            </div>
            <div>
                <div class="dash-stat__label">Saldo Stok BBM</div>
                <div class="dash-stat__value">{{ $fmtQty($stockBal) }} <span style="font-size:0.9rem;font-weight:500;color:var(--muted-foreground);">L</span></div>
                <div class="dash-stat__trend flat">
                    <i data-lucide="clock"></i>
                    Per {{ now()->translatedFormat('d M Y') }}
                </div>
            </div>
        </div>
        <div class="dash-stat__bg-icon"><i data-lucide="fuel" style="width:110px;height:110px;"></i></div>
    </div>

    {{-- Lori --}}
    <div class="dash-stat ds-lori">
        <div class="dash-stat__header">
            <div class="dash-stat__icon">
                <i data-lucide="truck" style="width:20px;height:20px;"></i>
            </div>
            <div>
                <div class="dash-stat__label">Mobil Tangki Bulan Ini</div>
                <div class="dash-stat__value">Rp {{ $fmt($loriAmt) }}</div>
                <div class="dash-stat__trend flat">
                    <i data-lucide="map-pin"></i>
                    Pendapatan pengiriman
                </div>
            </div>
        </div>
        <div class="dash-stat__bg-icon"><i data-lucide="truck" style="width:110px;height:110px;"></i></div>
    </div>

</div>

{{-- ── Charts Row ───────────────────────────────────────────────────────────── --}}
<!-- <div class="dash-charts-grid">

    {{-- Sales vs Purchase Bar Chart --}}
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Penjualan vs Pembelian</div>
                <div style="font-size:0.72rem;color:var(--muted-foreground);margin-top:0.1rem;">6 bulan terakhir</div>
            </div>
        </div>
        <div class="card-content">
            <div style="height:240px;position:relative;">
                <canvas id="salesPurchaseChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Expenses Doughnut --}}
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Pengeluaran per Kategori</div>
                <div style="font-size:0.72rem;color:var(--muted-foreground);margin-top:0.1rem;">Bulan ini</div>
            </div>
        </div>
        <div class="card-content">
            <div style="height:240px;position:relative;">
                @if($expByCategory->isEmpty())
                    <div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--muted-foreground);font-size:0.8rem;">
                        <div style="text-align:center;">
                            <i data-lucide="inbox" style="width:2rem;height:2rem;display:block;margin:0 auto 0.5rem;opacity:0.4;"></i>
                            Belum ada pengeluaran bulan ini
                        </div>
                    </div>
                @else
                    <canvas id="expenseCategoryChart"></canvas>
                @endif
            </div>
        </div>
    </div>

</div> -->

{{-- ── Profit Trend + Tables ────────────────────────────────────────────────── --}}
<!-- <div class="dash-tables-grid">

    {{-- Recent Sales --}}
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Penjualan Terbaru</div>
                <div style="font-size:0.72rem;color:var(--muted-foreground);margin-top:0.1rem;">5 transaksi terakhir</div>
            </div>
            <a href="{{ route('sales.index') }}" class="btn" style="font-size:0.75rem;padding:0.35rem 0.75rem;">
                Lihat Semua
            </a>
        </div>
        <div class="card-content" style="padding:0;">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Customer</th>
                            <th class="text-right">Amount</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentSales as $s)
                        <tr>
                            <td><span style="font-size:0.75rem;font-weight:600;color:var(--primary);">{{ $s->invoice_number }}</span></td>
                            <td style="font-size:0.8rem;">{{ $s->customer->name ?? '-' }}</td>
                            <td class="text-right" style="font-weight:600;font-size:0.8rem;">Rp {{ $fmt($s->amount) }}</td>
                            <td style="font-size:0.75rem;color:var(--muted-foreground);">{{ $s->date->translatedFormat('d M Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" style="text-align:center;padding:2rem;color:var(--muted-foreground);">
                                <i data-lucide="inbox" style="width:1.5rem;height:1.5rem;display:block;margin:0 auto 0.4rem;opacity:0.4;"></i>
                                Belum ada penjualan
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Recent Expenses --}}
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Pengeluaran Terbaru</div>
                <div style="font-size:0.72rem;color:var(--muted-foreground);margin-top:0.1rem;">5 pengeluaran terakhir</div>
            </div>
            <a href="{{ route('expenses.index') }}" class="btn" style="font-size:0.75rem;padding:0.35rem 0.75rem;">
                Lihat Semua
            </a>
        </div>
        <div class="card-content" style="padding:0;">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Deskripsi</th>
                            <th>Kategori</th>
                            <th class="text-right">Nominal</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentExpenses as $e)
                        <tr>
                            <td style="font-size:0.8rem;max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $e->description }}</td>
                            <td><span class="badge badge-warning" style="font-size:0.65rem;">{{ $e->category }}</span></td>
                            <td class="text-right" style="font-weight:600;font-size:0.8rem;color:var(--destructive);">Rp {{ $fmt($e->nominal) }}</td>
                            <td style="font-size:0.75rem;color:var(--muted-foreground);">{{ $e->date->translatedFormat('d M Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" style="text-align:center;padding:2rem;color:var(--muted-foreground);">
                                <i data-lucide="inbox" style="width:1.5rem;height:1.5rem;display:block;margin:0 auto 0.4rem;opacity:0.4;"></i>
                                Belum ada pengeluaran
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div> -->

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Initialize Lucide icons for page load
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });

    const tickColor = '#78716c';
    const gridColor = 'rgba(0,0,0,0.05)';

    // ── Sales vs Purchase Chart ───────────────────────────────────────────────────
    new Chart(document.getElementById('salesPurchaseChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: @json($chartLabels),
            datasets: [{
                    label: 'Penjualan',
                    data: @json($chartSales),
                    backgroundColor: 'rgba(217,119,6,0.85)',
                    borderRadius: 5,
                    borderSkipped: false,
                },
                {
                    label: 'Pembelian',
                    data: @json($chartPurchase),
                    backgroundColor: 'rgba(37,99,235,0.75)',
                    borderRadius: 5,
                    borderSkipped: false,
                },
                {
                    label: 'Profit',
                    data: @json($chartProfit),
                    type: 'line',
                    borderColor: '#16a34a',
                    backgroundColor: 'rgba(22,163,74,0.08)',
                    borderWidth: 2,
                    pointBackgroundColor: '#16a34a',
                    pointRadius: 4,
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: tickColor,
                        font: {
                            size: 11
                        },
                        padding: 14,
                        boxWidth: 12
                    }
                },
                tooltip: {
                    callbacks: {
                        label: ctx => ' Rp ' + new Intl.NumberFormat('id-ID').format(ctx.raw)
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: gridColor
                    },
                    ticks: {
                        color: tickColor,
                        font: {
                            size: 10
                        },
                        callback: v => {
                            if (v >= 1e9) return 'Rp ' + (v / 1e9).toFixed(1) + 'M';
                            if (v >= 1e6) return 'Rp ' + (v / 1e6).toFixed(1) + 'jt';
                            if (v >= 1e3) return 'Rp ' + (v / 1e3).toFixed(0) + 'rb';
                            return 'Rp ' + v;
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: tickColor,
                        font: {
                            size: 11
                        }
                    }
                }
            }
        }
    });

    // ── Expense Category Doughnut ─────────────────────────────────────────────────
    @if($expByCategory->isNotEmpty())
    new Chart(document.getElementById('expenseCategoryChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: @json($expByCategory->keys()),
            datasets: [{
                data: @json($expByCategory->values()),
                backgroundColor: [
                    '#d97706', '#2563eb', '#16a34a', '#dc2626',
                    '#7c3aed', '#0891b2', '#ea580c', '#65a30d'
                ],
                borderWidth: 0,
                hoverOffset: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: tickColor,
                        font: {
                            size: 10
                        },
                        padding: 10,
                        boxWidth: 10
                    }
                },
                tooltip: {
                    callbacks: {
                        label: ctx => ' Rp ' + new Intl.NumberFormat('id-ID').format(ctx.raw)
                    }
                }
            }
        }
    });
    @endif

</script>
@endpush
