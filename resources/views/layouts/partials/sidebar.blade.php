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
        <!-- Master -->
        <div class="nav-section">Master</div>
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i data-lucide="layout-dashboard"></i>
            <span>Dashboard</span>
        </a>
        @if(auth()->user()->role === 'Super Admin')
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
        <a href="{{ route('purchase.index') }}" class="nav-item {{ request()->routeIs('purchase.*') ? 'active' : '' }}">
            <i data-lucide="arrow-down-to-line"></i>
            <span>Purchase</span>
            <span class="sidebar-notif-badge" id="sidebarBadge-purchase" style="display:none;"></span>
        </a>
        <a href="{{ route('stock.index') }}" class="nav-item {{ request()->routeIs('stock.*') ? 'active' : '' }}">
            <i data-lucide="package"></i>
            <span>Stock</span>
        </a>
        <a href="{{ route('sales.index') }}" class="nav-item {{ request()->routeIs('sales.*') ? 'active' : '' }}">
            <i data-lucide="arrow-up-from-line"></i>
            <span>Sales</span>
            <span class="sidebar-notif-badge" id="sidebarBadge-sales" style="display:none;"></span>
        </a>
        <a href="{{ route('capital.index') }}" class="nav-item {{ request()->routeIs('capital.*') ? 'active' : '' }}">
            <i data-lucide="wallet"></i>
            <span>Capital</span>
            <span class="sidebar-notif-badge" id="sidebarBadge-capital" style="display:none;"></span>
        </a>
        <a href="{{ route('expenses.index') }}" class="nav-item {{ request()->routeIs('expenses.*') ? 'active' : '' }}">
            <i data-lucide="receipt"></i>
            <span>Expenses</span>
            <span class="sidebar-notif-badge" id="sidebarBadge-expenses" style="display:none;"></span>
        </a>

        <!-- Laporan -->
        <div class="nav-section">Laporan</div>
        @php
        $laporanActive = request()->routeIs('report.purchase') || request()->routeIs('report.sale') || request()->routeIs('report.expense') || request()->routeIs('report.capital');
        $loriActive = request()->routeIs('lori.index') || request()->routeIs('lori-expense.*') || request()->routeIs('report.lori-omset') || request()->routeIs('report.lori-expense') || request()->routeIs('report.lori');
        @endphp
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
            </div>
        </div>
        <div class="nav-group">
            <div class="nav-group-header {{ $loriActive ? 'open' : '' }}" onclick="toggleNavGroup(this)">
                <i data-lucide="truck"></i>
                <span>Mobil</span>
                <i data-lucide="chevron-right" class="nav-chevron"></i>
            </div>
            <div class="nav-sub {{ $loriActive ? 'open' : '' }}">
                <a href="{{ route('lori.index') }}" class="nav-sub-item {{ request()->routeIs('lori.index') || (request()->routeIs('lori.*') && !request()->routeIs('lori-expense.*')) ? 'active' : '' }}">
                    Sale
                    <span class="sidebar-notif-badge" id="sidebarBadge-lori" style="display:none;"></span>
                </a>
                <a href="{{ route('lori-expense.index') }}" class="nav-sub-item {{ request()->routeIs('lori-expense.*') ? 'active' : '' }}">
                    Expenses
                    <span class="sidebar-notif-badge" id="sidebarBadge-lori-expense" style="display:none;"></span>
                </a>
                <a href="{{ route('report.lori-omset') }}" class="nav-sub-item {{ request()->routeIs('report.lori-omset') ? 'active' : '' }}">
                    Laporan Omset
                </a>
                <a href="{{ route('report.lori-expense') }}" class="nav-sub-item {{ request()->routeIs('report.lori-expense') ? 'active' : '' }}">
                    Laporan Expense
                </a>
                <a href="{{ route('report.lori') }}" class="nav-sub-item {{ request()->routeIs('report.lori') ? 'active' : '' }}">
                    Profit / Loss
                </a>
            </div>
        </div>
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