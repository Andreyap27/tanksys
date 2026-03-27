<aside class="sidebar" id="sidebar">
    <!-- Logo -->
    <div class="sidebar-logo">
        <div class="logo-icon">
            <i data-lucide="fuel" style="width: 1.25rem; height: 1.25rem;"></i>
        </div>
        <div>
            <div class="logo-text">TankSys Pro</div>
            <div class="logo-sub">Manajemen Bisnis</div>
        </div>
        <button class="menu-btn" onclick="toggleSidebar()" style="margin-left: auto;">
            <i data-lucide="x" style="width: 1.25rem; height: 1.25rem;"></i>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
        <!-- Master -->
        <div class="nav-section">Master</div>
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i data-lucide="layout-dashboard"></i>
            Dashboard
        </a>
        <a href="{{ route('user.index') }}" class="nav-item {{ request()->routeIs('user.*') ? 'active' : '' }}">
            <i data-lucide="users"></i>
            User
        </a>
        <a href="{{ route('customer.index') }}" class="nav-item {{ request()->routeIs('customer.*') ? 'active' : '' }}">
            <i data-lucide="building-2"></i>
            Customer
        </a>

        <!-- Transaksi -->
        <div class="nav-section">Transaksi</div>
        <a href="{{ route('purchase.index') }}" class="nav-item {{ request()->routeIs('purchase.*') ? 'active' : '' }}">
            <i data-lucide="arrow-down-to-line"></i>
            Purchase
        </a>
        <a href="{{ route('stock.index') }}" class="nav-item {{ request()->routeIs('stock.*') ? 'active' : '' }}">
            <i data-lucide="package"></i>
            Stock
        </a>
        <a href="{{ route('sales.index') }}" class="nav-item {{ request()->routeIs('sales.*') ? 'active' : '' }}">
            <i data-lucide="arrow-up-from-line"></i>
            Sales
        </a>
        <a href="{{ route('capital.index') }}" class="nav-item {{ request()->routeIs('capital.*') ? 'active' : '' }}">
            <i data-lucide="wallet"></i>
            Capital
        </a>
        <a href="{{ route('expenses.index') }}" class="nav-item {{ request()->routeIs('expenses.*') ? 'active' : '' }}">
            <i data-lucide="receipt"></i>
            Expenses
        </a>
        <a href="{{ route('lori.index') }}" class="nav-item {{ request()->routeIs('lori.*') ? 'active' : '' }}">
            <i data-lucide="truck"></i>
            Mobil Tangki
        </a>

        <!-- Laporan -->
        <div class="nav-section">Laporan</div>
        <a href="{{ route('report.index') }}" class="nav-item {{ request()->routeIs('report.*') ? 'active' : '' }}">
            <i data-lucide="file-bar-chart"></i>
            Report
        </a>
    </nav>

    <!-- Footer -->
    <div class="sidebar-footer">
        &copy; {{ date('Y') }} TankSys Pro
    </div>
</aside>
