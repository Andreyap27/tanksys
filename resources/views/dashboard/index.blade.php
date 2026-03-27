@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div>
        <h1 class="text-2xl font-bold text-foreground">Dashboard</h1>
        <p class="text-muted-foreground">Selamat datang di TankSys Pro</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Penjualan -->
        <div class="bg-card border border-border rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-muted-foreground">Total Penjualan</p>
                    <p class="text-2xl font-bold text-foreground mt-1">Rp {{ number_format($totalSales ?? 245000000) }}</p>
                    <p class="text-xs text-chart-2 mt-1 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                        </svg>
                        +12.5% dari bulan lalu
                    </p>
                </div>
                <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Pembelian -->
        <div class="bg-card border border-border rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-muted-foreground">Total Pembelian</p>
                    <p class="text-2xl font-bold text-foreground mt-1">Rp {{ number_format($totalPurchases ?? 180000000) }}</p>
                    <p class="text-xs text-chart-1 mt-1 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                        </svg>
                        -5.2% dari bulan lalu
                    </p>
                </div>
                <div class="w-12 h-12 rounded-full bg-chart-4/10 flex items-center justify-center">
                    <svg class="w-6 h-6 text-chart-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Profit -->
        <div class="bg-card border border-border rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-muted-foreground">Profit Bulan Ini</p>
                    <p class="text-2xl font-bold text-chart-2 mt-1">Rp {{ number_format($profit ?? 65000000) }}</p>
                    <p class="text-xs text-chart-2 mt-1 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                        </svg>
                        +18.3% dari bulan lalu
                    </p>
                </div>
                <div class="w-12 h-12 rounded-full bg-chart-2/10 flex items-center justify-center">
                    <svg class="w-6 h-6 text-chart-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Stok -->
        <div class="bg-card border border-border rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-muted-foreground">Total Stok BBM</p>
                    <p class="text-2xl font-bold text-foreground mt-1">{{ number_format($totalStock ?? 125000) }} L</p>
                    <p class="text-xs text-muted-foreground mt-1">
                        Terakhir update: {{ now()->format('d M Y') }}
                    </p>
                </div>
                <div class="w-12 h-12 rounded-full bg-chart-3/10 flex items-center justify-center">
                    <svg class="w-6 h-6 text-chart-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Sales Chart -->
        <div class="bg-card border border-border rounded-xl p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="font-semibold text-foreground">Grafik Penjualan</h3>
                    <p class="text-sm text-muted-foreground">6 bulan terakhir</p>
                </div>
                <select class="text-sm bg-input border border-border rounded-lg px-3 py-1.5">
                    <option>6 Bulan</option>
                    <option>12 Bulan</option>
                    <option>Tahun Ini</option>
                </select>
            </div>
            <div class="h-64 flex items-center justify-center text-muted-foreground">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        <!-- Stock Distribution -->
        <div class="bg-card border border-border rounded-xl p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="font-semibold text-foreground">Distribusi Stok</h3>
                    <p class="text-sm text-muted-foreground">Per jenis BBM</p>
                </div>
            </div>
            <div class="h-64 flex items-center justify-center text-muted-foreground">
                <canvas id="stockChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="bg-card border border-border rounded-xl">
        <div class="p-6 border-b border-border">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-foreground">Transaksi Terbaru</h3>
                    <p class="text-sm text-muted-foreground">5 transaksi terakhir</p>
                </div>
                <a href="{{ route('sales.index') }}" class="text-sm text-primary hover:text-primary/80 font-medium">
                    Lihat Semua
                </a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-muted/50">
                    <tr>
                        <th class="text-left text-xs font-medium text-muted-foreground uppercase tracking-wider px-6 py-3">No. Invoice</th>
                        <th class="text-left text-xs font-medium text-muted-foreground uppercase tracking-wider px-6 py-3">Customer</th>
                        <th class="text-left text-xs font-medium text-muted-foreground uppercase tracking-wider px-6 py-3">Produk</th>
                        <th class="text-left text-xs font-medium text-muted-foreground uppercase tracking-wider px-6 py-3">Jumlah</th>
                        <th class="text-left text-xs font-medium text-muted-foreground uppercase tracking-wider px-6 py-3">Total</th>
                        <th class="text-left text-xs font-medium text-muted-foreground uppercase tracking-wider px-6 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($recentTransactions ?? [] as $transaction)
                    <tr class="hover:bg-muted/30 transition-colors">
                        <td class="px-6 py-4 text-sm font-medium text-foreground">{{ $transaction->invoice_no }}</td>
                        <td class="px-6 py-4 text-sm text-foreground">{{ $transaction->customer->name }}</td>
                        <td class="px-6 py-4 text-sm text-foreground">{{ $transaction->product->name }}</td>
                        <td class="px-6 py-4 text-sm text-foreground">{{ number_format($transaction->quantity) }} L</td>
                        <td class="px-6 py-4 text-sm font-medium text-foreground">Rp {{ number_format($transaction->total) }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 text-xs font-medium rounded-full 
                                @if($transaction->status === 'completed') bg-chart-2/10 text-chart-2
                                @elseif($transaction->status === 'pending') bg-chart-4/10 text-chart-4
                                @else bg-chart-1/10 text-chart-1 @endif">
                                {{ ucfirst($transaction->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-muted-foreground">
                            <svg class="mx-auto h-12 w-12 text-muted-foreground/50 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            Belum ada transaksi
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Sales Chart
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Penjualan',
                data: [180, 220, 195, 250, 230, 245],
                borderColor: '#f97316',
                backgroundColor: 'rgba(249, 115, 22, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(255,255,255,0.1)' },
                    ticks: { color: '#94a3b8' }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#94a3b8' }
                }
            }
        }
    });

    // Stock Chart
    const stockCtx = document.getElementById('stockChart').getContext('2d');
    new Chart(stockCtx, {
        type: 'doughnut',
        data: {
            labels: ['Solar', 'Pertalite', 'Pertamax', 'Pertamax Turbo'],
            datasets: [{
                data: [45000, 35000, 30000, 15000],
                backgroundColor: ['#f97316', '#3b82f6', '#10b981', '#8b5cf6'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: '#94a3b8' }
                }
            }
        }
    });
</script>
@endpush
@endsection
