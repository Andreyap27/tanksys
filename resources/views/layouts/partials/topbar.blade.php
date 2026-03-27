<header class="topbar">
    <div class="topbar-left">
        <button class="menu-btn" onclick="toggleSidebar()">
            <i data-lucide="menu" style="width: 1.25rem; height: 1.25rem;"></i>
        </button>
        <h1 class="page-title">@yield('title', 'Dashboard')</h1>
    </div>

    <div class="topbar-right">
        <!-- Notifications -->
        <button class="notification-btn" onclick="showInfo('Notifikasi', 'Tidak ada notifikasi baru')">
            <i data-lucide="bell" style="width: 1.25rem; height: 1.25rem;"></i>
            <span class="notification-dot"></span>
        </button>

        <!-- User Menu -->
        <div class="user-menu">
            <button class="user-btn" onclick="toggleUserMenu()">
                <div class="user-avatar">
                    {{ strtoupper(substr(auth()->user()->name ?? 'AD', 0, 2)) }}
                </div>
                <div class="user-info">
                    <div class="user-name">{{ auth()->user()->name ?? 'Admin' }}</div>
                    <div class="user-role">{{ auth()->user()->role ?? 'SPV' }}</div>
                </div>
                <i data-lucide="chevron-down" style="width: 1rem; height: 1rem; color: var(--muted-foreground);"></i>
            </button>

            <div class="dropdown-menu" id="userDropdown">
                <a href="{{ route('profile.edit') }}" class="dropdown-item">
                    <i data-lucide="user" style="width: 1rem; height: 1rem;"></i>
                    Profil Saya
                </a>
                <a href="{{ route('settings.index') }}" class="dropdown-item">
                    <i data-lucide="settings" style="width: 1rem; height: 1rem;"></i>
                    Pengaturan
                </a>
                <div class="dropdown-divider"></div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="button" class="dropdown-item danger" style="width: 100%;" onclick="confirmLogout(this.closest('form'))">
                        <i data-lucide="log-out" style="width: 1rem; height: 1rem;"></i>
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>

<script>
    function confirmLogout(form) {
        showConfirm({
            title: 'Konfirmasi Logout',
            message: 'Apakah Anda yakin ingin keluar dari sistem?',
            confirmText: 'Ya, Logout',
            cancelText: 'Batal',
            type: 'warning',
            onConfirm: function() {
                form.submit();
            }
        });
    }
</script>
