@extends('layouts.app')

@section('title', 'Laporan')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-foreground">Laporan</h1>
            <p class="text-muted-foreground">Laporan keuangan dan operasional</p>
        </div>
        <div class="flex gap-2">
            <button class="inline-flex items-center gap-2 px-4 py-2.5 bg-card border border-border text-foreground rounded-lg hover:bg-muted transition-colors font-medium">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Export PDF
            </button>
            <button class="inline-flex items-center gap-2 px-4 py-2.5 bg-card border border-border text-foreground rounded-lg hover:bg-muted transition-colors font-medium">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Export Excel
            </button>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="bg-card border border-border rounded-xl p-4">
        <div class="flex flex-col sm:flex-row gap-4 items-end">
            <div class="flex-1 grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-foreground mb-1">Periode</label>
                    <select class="w-full h-10 px-3 bg-input border border-border rounded-lg">
                        <option value="today">Hari Ini</option>
                        <option value="week">Minggu Ini</option>
                        <option value="month" selected>Bulan Ini</option>
                        <option value="quarter">Kuartal Ini</option>
                        <option value="year">Tahun Ini</option>
                        <option value="custom">Custom</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-foreground mb-1">Dari Tanggal</label>
                    <input type="date" class="w-full h-10 px-3 bg-input border border-border rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-foreground mb-1">Sampai Tanggal</label>
                    <input type="date" class="w-full h-10 px-3 bg-input border border-border rounded-lg">
                </div>
            </div>
            <button class="h-10 px-6 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors font-medium">
                Terapkan
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-card border border-border rounded-xl p-6">
            <p class="text-sm text-muted-foreground">Total Penjualan</p>
            <p class="text-2xl font-bold text-foreground mt-1">Rp {{ number_format($totalSales ?? 245000000) }}</p>
            <p class="text-xs text-chart-2 mt-2 flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                </svg>
                +12.5%
            </p>
        </div>
        <div class="bg-card border border-border rounded-xl p-6">
            <p class="text-sm text-muted-foreground">Total Pembelian</p>
            <p class="text-2xl font-bold text-foreground mt-1">Rp {{ number_format($totalPurchases ?? 180000000) }}</p>
            <p class="text-xs text-chart-1 mt-2 flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                </svg>
                -5.2%
            </p>
        </div>
        <div class="bg-card border border-border rounded-xl p-6">
            <p class="text-sm text-muted-foreground">Total Pengeluaran</p>
            <p class="text-2xl font-bold text-chart-1 mt-1">Rp {{ number_format($totalExpenses ?? 45000000) }}</p>
        </div>
        <div class="bg-card border border-border rounded-xl p-6">
            <p class="text-sm text-muted-foreground">Profit Bersih</p>
            <p class="text-2xl font-bold text-chart-2 mt-1">Rp {{ number_format($netProfit ?? 20000000) }}</p>
            <p class="text-xs text-chart-2 mt-2 flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                </svg>
                +8.1% margin
            </p>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-card border border-border rounded-xl p-6">
            <h3 class="font-semibold text-foreground mb-6">Grafik Profit/Loss</h3>
            <div class="h-64">
                <canvas id="profitChart"></canvas>
            </div>
        </div>
        <div class="bg-card border border-border rounded-xl p-6">
            <h3 class="font-semibold text-foreground mb-6">Penjualan per Produk</h3>
            <div class="h-64">
                <canvas id="productChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Detailed Report Table -->
    <div class="bg-card border border-border rounded-xl overflow-hidden">
        <div class="p-6 border-b border-border">
            <h3 class="font-semibold text-foreground">Ringkasan Transaksi</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-muted/50">
                    <tr>
                        <th class="text-left text-xs font-medium text-muted-foreground uppercase tracking-wider px-6 py-3">Kategori</th>
                        <th class="text-left text-xs font-medium text-muted-foreground uppercase tracking-wider px-6 py-3">Jumlah Transaksi</th>
                        <th class="text-left text-xs font-medium text-muted-foreground uppercase tracking-wider px-6 py-3">Total Volume</th>
                        <th class="text-left text-xs font-medium text-muted-foreground uppercase tracking-wider px-6 py-3">Total Nilai</th>
                        <th class="text-left text-xs font-medium text-muted-foreground uppercase tracking-wider px-6 py-3">Perubahan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <tr class="hover:bg-muted/30 transition-colors">
                        <td class="px-6 py-4 font-medium text-foreground">Penjualan Solar</td>
                        <td class="px-6 py-4 text-sm text-foreground">156</td>
                        <td class="px-6 py-4 text-sm text-foreground">45,000 L</td>
                        <td class="px-6 py-4 text-sm font-semibold text-foreground">Rp 135,000,000</td>
                        <td class="px-6 py-4 text-sm text-chart-2">+15.2%</td>
                    </tr>
                    <tr class="hover:bg-muted/30 transition-colors">
                        <td class="px-6 py-4 font-medium text-foreground">Penjualan Pertalite</td>
                        <td class="px-6 py-4 text-sm text-foreground">98</td>
                        <td class="px-6 py-4 text-sm text-foreground">28,000 L</td>
                        <td class="px-6 py-4 text-sm font-semibold text-foreground">Rp 70,000,000</td>
                        <td class="px-6 py-4 text-sm text-chart-2">+8.7%</td>
                    </tr>
                    <tr class="hover:bg-muted/30 transition-colors">
                        <td class="px-6 py-4 font-medium text-foreground">Penjualan Pertamax</td>
                        <td class="px-6 py-4 text-sm text-foreground">45</td>
                        <td class="px-6 py-4 text-sm text-foreground">12,000 L</td>
                        <td class="px-6 py-4 text-sm font-semibold text-foreground">Rp 40,000,000</td>
                        <td class="px-6 py-4 text-sm text-chart-1">-3.1%</td>
                    </tr>
                    <tr class="bg-muted/30">
                        <td class="px-6 py-4 font-bold text-foreground">Total</td>
                        <td class="px-6 py-4 text-sm font-bold text-foreground">299</td>
                        <td class="px-6 py-4 text-sm font-bold text-foreground">85,000 L</td>
                        <td class="px-6 py-4 font-bold text-foreground">Rp 245,000,000</td>
                        <td class="px-6 py-4 text-sm font-bold text-chart-2">+12.5%</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Profit Chart
    const profitCtx = document.getElementById('profitChart').getContext('2d');
    new Chart(profitCtx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [
                {
                    label: 'Penjualan',
                    data: [180, 220, 195, 250, 230, 245],
                    backgroundColor: '#f97316',
                },
                {
                    label: 'Pembelian',
                    data: [150, 180, 160, 200, 190, 180],
                    backgroundColor: '#3b82f6',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: '#94a3b8' }
                }
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

    // Product Chart
    const productCtx = document.getElementById('productChart').getContext('2d');
    new Chart(productCtx, {
        type: 'pie',
        data: {
            labels: ['Solar', 'Pertalite', 'Pertamax', 'Pertamax Turbo'],
            datasets: [{
                data: [55, 28, 12, 5],
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
