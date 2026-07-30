<aside class="sidebar" id="sidebar">
    <!-- Company Logo -->
    <div class="sidebar-logo">
        <div class="company-logo-wrap">
            <img src="/images/logo.png" alt="Logo Perusahaan" class="company-logo-img">
        </div>
        <div class="logo-info">
            <div class="logo-text">TankSys Pro</div>
            <div class="logo-sub">Manajemen Bisnis</div>
        </div>
        <button class="sidebar-close-btn" onclick="toggleSidebar()">
            <i data-lucide="x" style="width:1.1rem;height:1.1rem;"></i>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
        @php
            $isFinance    = auth()->user()->isFinance();
            $isSuperAdmin = auth()->user()->role === 'Super Admin';
            $isSPV        = auth()->user()->isSPV();
            $isMarketing  = auth()->user()->isMarketing();
        @endphp

        @if($isMarketing)
        {{-- Marketing: restricted to Dashboard, Purchase, Sales, Pemakaian Stok --}}
        <div class="nav-section">Menu</div>
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i data-lucide="layout-dashboard"></i>
            <span>Dashboard</span>
        </a>
        <div class="nav-section">Transaksi</div>
        <a href="{{ route('purchase.index') }}" class="nav-item {{ request()->routeIs('purchase.*') ? 'active' : '' }}">
            <i data-lucide="arrow-down-to-line"></i>
            <span>Purchase</span>
            <span class="sidebar-notif-badge" id="sidebarBadge-purchase" style="display:none;"></span>
        </a>
        <a href="{{ route('sales.index') }}" class="nav-item {{ request()->routeIs('sales.*') ? 'active' : '' }}">
            <i data-lucide="arrow-up-from-line"></i>
            <span>Sales</span>
            <span class="sidebar-notif-badge" id="sidebarBadge-sales" style="display:none;"></span>
        </a>
        <a href="{{ route('stock-usage.index') }}" class="nav-item {{ request()->routeIs('stock-usage.*') ? 'active' : '' }}">
            <i data-lucide="flame"></i>
            <span>Pemakaian Stok</span>
        </a>

        @else

        {{-- ── Non-Finance menus ────────────────────────────────────────── --}}
        @if(!$isFinance)

        <!-- Master -->
        <div class="nav-section">Master</div>
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i data-lucide="layout-dashboard"></i>
            <span>Dashboard</span>
        </a>
        @if($isSuperAdmin)
        <a href="{{ route('user.index') }}" class="nav-item {{ request()->routeIs('user.*') ? 'active' : '' }}">
            <i data-lucide="users"></i>
            <span>User</span>
        </a>
        <a href="{{ route('kapal.index') }}" class="nav-item {{ request()->routeIs('kapal.*') ? 'active' : '' }}">
            <i data-lucide="ship"></i>
            <span>Kapal</span>
        </a>
        <a href="{{ route('mobil-master.index') }}" class="nav-item {{ request()->routeIs('mobil-master.*') ? 'active' : '' }}">
            <i data-lucide="car"></i>
            <span>Mobil</span>
        </a>
        <a href="{{ route('petty-cash.index') }}" class="nav-item {{ request()->routeIs('petty-cash.index') ? 'active' : '' }}">
            <i data-lucide="wallet"></i>
            <span>Petty Cash</span>
        </a>
        @endif
        <a href="{{ route('customer.index') }}" class="nav-item {{ request()->routeIs('customer.*') ? 'active' : '' }}">
            <i data-lucide="building-2"></i>
            <span>Customer</span>
        </a>
        <a href="{{ route('vendor.index') }}" class="nav-item {{ request()->routeIs('vendor.*') ? 'active' : '' }}">
            <i data-lucide="factory"></i>
            <span>Vendor</span>
        </a>

        <!-- Transaksi -->
        <div class="nav-section">Transaksi</div>
        {{-- <a href="{{ route('stock.index') }}" class="nav-item {{ request()->routeIs('stock.index') || request()->routeIs('stock.data') || request()->routeIs('stock.summary') ? 'active' : '' }}">
            <i data-lucide="package"></i>
            <span>Stock BBM</span>
        </a> --}}
        <a href="{{ route('stock-usage.index') }}" class="nav-item {{ request()->routeIs('stock-usage.*') ? 'active' : '' }}">
            <i data-lucide="flame"></i>
            <span>Pemakaian Stok</span>
        </a>
        <a href="{{ route('purchase.index') }}" class="nav-item {{ request()->routeIs('purchase.*') ? 'active' : '' }}">
            <i data-lucide="arrow-down-to-line"></i>
            <span>Purchase</span>
            <span class="sidebar-notif-badge" id="sidebarBadge-purchase" style="display:none;"></span>
        </a>
        <a href="{{ route('sales.index') }}" class="nav-item {{ request()->routeIs('sales.*') ? 'active' : '' }}">
            <i data-lucide="arrow-up-from-line"></i>
            <span>Sales</span>
            <span class="sidebar-notif-badge" id="sidebarBadge-sales" style="display:none;"></span>
        </a>
        <a href="{{ route('expenses.index') }}" class="nav-item {{ request()->routeIs('expenses.*') ? 'active' : '' }}">
            <i data-lucide="receipt"></i>
            <span>Expenses Ship</span>
            <span class="sidebar-notif-badge" id="sidebarBadge-expenses" style="display:none;"></span>
        </a>
        <a href="{{ route('petty-cash-transaction.index') }}" class="nav-item {{ request()->routeIs('petty-cash-transaction.*') ? 'active' : '' }}">
            <i data-lucide="coins"></i>
            <span>Petty Cash</span>
        </a>
        <a href="{{ route('capital.index') }}" class="nav-item {{ request()->routeIs('capital.*') ? 'active' : '' }}">
            <i data-lucide="landmark"></i>
            <span>Capital</span>
            <span class="sidebar-notif-badge" id="sidebarBadge-capital" style="display:none;"></span>
        </a>
        @php $loriActive = request()->routeIs('lori.index') || request()->routeIs('lori-expense.*') || (request()->routeIs('lori.*') && !request()->routeIs('lori-expense.*')); @endphp
        <div class="nav-group">
            <div class="nav-group-header {{ $loriActive ? 'open' : '' }}" onclick="toggleNavGroup(this)">
                <i data-lucide="truck"></i>
                <span>Mobil</span>
                <i data-lucide="chevron-right" class="nav-chevron"></i>
            </div>
            <div class="nav-sub {{ $loriActive ? 'open' : '' }}">
                <a href="{{ route('lori.index') }}" class="nav-sub-item {{ request()->routeIs('lori.index') || (request()->routeIs('lori.*') && !request()->routeIs('lori-expense.*')) ? 'active' : '' }}">
                    Sale
                </a>
                <a href="{{ route('lori-expense.index') }}" class="nav-sub-item {{ request()->routeIs('lori-expense.*') ? 'active' : '' }}">
                    Expenses
                </a>
            </div>
        </div>
        <a href="{{ route('hutang.index') }}" class="nav-item {{ request()->routeIs('hutang.*') ? 'active' : '' }}">
            <i data-lucide="hand-coins"></i>
            <span>Hutang</span>
        </a>
        <a href="{{ route('piutang.index') }}" class="nav-item {{ request()->routeIs('piutang.*') ? 'active' : '' }}">
            <i data-lucide="wallet"></i>
            <span>Piutang</span>
        </a>

        @endif {{-- !isFinance --}}

        {{-- ── Gaji & Uang Koordinasi: Finance + Super Admin + SPV ────────── --}}
        @if($isFinance || $isSuperAdmin || $isSPV)
        @if($isFinance)<div class="nav-section">Transaksi</div>@endif
        <a href="{{ route('gaji.index') }}" class="nav-item {{ request()->routeIs('gaji.*') && !request()->routeIs('gaji.slip.*') ? 'active' : '' }}">
            <i data-lucide="banknote"></i>
            <span>Gaji</span>
        </a>
        <a href="{{ route('uang-koordinasi.index') }}" class="nav-item {{ request()->routeIs('uang-koordinasi.*') ? 'active' : '' }}">
            <i data-lucide="handshake"></i>
            <span>Uang Koordinasi</span>
        </a>
        @endif

        {{-- ── Report: non-Finance only ─────────────────────────────────── --}}
        @if(!$isFinance)
        <div class="nav-section">Report</div>
        <a href="{{ route('bank.index') }}" class="nav-item {{ request()->routeIs('bank.*') ? 'active' : '' }}">
            <i data-lucide="landmark"></i>
            <span>Bank In/Out</span>
        </a>
        @php $laporanActive = request()->routeIs('report.*'); @endphp
        <div class="nav-group">
            <div class="nav-group-header {{ $laporanActive ? 'open' : '' }}" onclick="toggleNavGroup(this)">
                <i data-lucide="file-bar-chart"></i>
                <span>Laporan</span>
                <i data-lucide="chevron-right" class="nav-chevron"></i>
            </div>
            <div class="nav-sub {{ $laporanActive ? 'open' : '' }}">
                <a href="{{ route('report.purchase') }}" class="nav-sub-item {{ request()->routeIs('report.purchase') ? 'active' : '' }}">
                    Total Purchase
                </a>
                <a href="{{ route('report.sale') }}" class="nav-sub-item {{ request()->routeIs('report.sale') ? 'active' : '' }}">
                    Total Sale
                </a>
                <a href="{{ route('report.expense') }}" class="nav-sub-item {{ request()->routeIs('report.expense') ? 'active' : '' }}">
                    Total Expense
                </a>
                <a href="{{ route('report.capital') }}" class="nav-sub-item {{ request()->routeIs('report.capital') ? 'active' : '' }}">
                    Total Capital
                </a>
                <a href="{{ route('report.profit-loss') }}" class="nav-sub-item {{ request()->routeIs('report.profit-loss') ? 'active' : '' }}">
                    Profit / Loss
                </a>
                <a href="{{ route('report.lori-omset') }}" class="nav-sub-item {{ request()->routeIs('report.lori-omset') ? 'active' : '' }}">
                    Omset Mobil
                </a>
                <a href="{{ route('report.lori-expense') }}" class="nav-sub-item {{ request()->routeIs('report.lori-expense') ? 'active' : '' }}">
                    Expense Mobil
                </a>
                <a href="{{ route('report.lori') }}" class="nav-sub-item {{ request()->routeIs('report.lori') ? 'active' : '' }}">
                    P/L Mobil
                </a>
                <a href="{{ route('report.petty-cash') }}" class="nav-sub-item {{ request()->routeIs('report.petty-cash') ? 'active' : '' }}">
                    Petty Cash
                </a>
                <a href="{{ route('report.stock-card') }}" class="nav-sub-item {{ request()->routeIs('report.stock-card') ? 'active' : '' }}">
                    Stok Card
                </a>
                {{-- <a href="{{ route('report.jasa-angkut') }}" class="nav-sub-item {{ request()->routeIs('report.jasa-angkut') ? 'active' : '' }}">
                    Jasa Angkut
                </a> --}}
            </div>
        </div>
        @endif {{-- !isFinance --}}

        @endif {{-- !isMarketing --}}

    </nav>

    <!-- User Profile Strip -->
    <div class="sidebar-profile">
        <div class="sidebar-profile-avatar">
            {{ strtoupper(substr(auth()->user()->name ?? 'AD', 0, 2)) }}
        </div>
        <div class="sidebar-profile-info">
            <div class="sidebar-profile-name">{{ auth()->user()->name ?? 'Admin' }}</div>
            <div class="sidebar-profile-role">{{ auth()->user()->role ?? 'SPV' }}</div>
        </div>
        <form action="{{ route('logout') }}" method="POST" style="margin:0;">
            @csrf
            <button type="submit" class="sidebar-profile-logout" title="Keluar">
                <i data-lucide="log-out" style="width:0.95rem;height:0.95rem;"></i>
            </button>
        </form>
    </div>
</aside>

<script>
    function toggleNavGroup(header) {
        header.classList.toggle('open');
        header.nextElementSibling.classList.toggle('open');
    }
</script>
