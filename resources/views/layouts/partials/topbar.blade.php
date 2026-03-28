<header class="topbar">

    {{-- Left: menu toggle + brand --}}
    <div class="topbar-left">
        <button class="menu-btn" onclick="toggleSidebar()" title="Toggle Menu">
            <i data-lucide="menu" style="width:1.25rem;height:1.25rem;"></i>
        </button>
        <div class="topbar-brand">
            <span class="topbar-brand-accent"></span>
            <span class="topbar-company-name">
                PT. <span>ANUGRAH ENERGI PETROLUM</span>
            </span>
        </div>
    </div>

    {{-- Center: live clock --}}
    <div class="topbar-clock">
        <div class="topbar-clock-time" id="topbarTime">--:--:--</div>
        <div class="topbar-clock-date" id="topbarDate">--</div>
    </div>

    {{-- Right: notification + user --}}
    <div class="topbar-right">
        <div class="topbar-action-btn" onclick="showInfo('Notifikasi', 'Tidak ada notifikasi baru')" title="Notifikasi">
            <i data-lucide="bell" style="width:1.1rem;height:1.1rem;"></i>
        </div>

        <div class="topbar-divider"></div>

        <div class="user-menu">
            <button class="user-btn" onclick="toggleUserMenu()">
                <div class="user-avatar">
                    {{ strtoupper(substr(auth()->user()->name ?? 'AD', 0, 2)) }}
                </div>
                <div class="user-info">
                    <div class="user-name">{{ auth()->user()->name ?? 'Admin' }}</div>
                    <div class="user-role">
                        <span class="role-badge">{{ auth()->user()->role ?? 'SPV' }}</span>
                    </div>
                </div>
                <i data-lucide="chevron-down" class="user-chevron"></i>
            </button>

            <div class="dropdown-menu" id="userDropdown">
                <div class="dropdown-header">
                    <div class="dropdown-user-avatar">
                        {{ strtoupper(substr(auth()->user()->name ?? 'AD', 0, 2)) }}
                    </div>
                    <div>
                        <div style="font-size:0.875rem;font-weight:600;color:var(--foreground);">{{ auth()->user()->name ?? 'Admin' }}</div>
                        <div style="font-size:0.75rem;color:var(--muted-foreground);">{{ auth()->user()->employee_id ?? '' }}</div>
                    </div>
                </div>
                <div class="dropdown-divider"></div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="button" class="dropdown-item danger" style="width:100%;" onclick="confirmLogout(this.closest('form'))">
                        <i data-lucide="log-out" style="width:1rem;height:1rem;"></i>
                        Keluar
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
        confirmText: 'Ya, Keluar',
        cancelText: 'Batal',
        type: 'warning',
        onConfirm: async function () { form.submit(); }
    });
}

// Live clock
(function () {
    const days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

    function updateClock() {
        const now  = new Date();
        const hh   = String(now.getHours()).padStart(2, '0');
        const mm   = String(now.getMinutes()).padStart(2, '0');
        const ss   = String(now.getSeconds()).padStart(2, '0');
        const day  = days[now.getDay()];
        const date = now.getDate();
        const mon  = months[now.getMonth()];
        const yr   = now.getFullYear();

        const timeEl = document.getElementById('topbarTime');
        const dateEl = document.getElementById('topbarDate');
        if (timeEl) timeEl.textContent = `${hh}:${mm}:${ss}`;
        if (dateEl) dateEl.textContent = `${day}, ${date} ${mon} ${yr}`;
    }

    updateClock();
    setInterval(updateClock, 1000);
})();
</script>
