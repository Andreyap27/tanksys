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
        <div class="notif-wrap" id="notifWrap">
            <button class="topbar-action-btn" onclick="toggleNotifDropdown()" title="Notifikasi" id="notifBtn">
                <i data-lucide="bell" style="width:1.1rem;height:1.1rem;"></i>
                <span class="notif-badge" id="notifBadge" style="display:none;"></span>
            </button>
            <div class="notif-dropdown" id="notifDropdown">
                <div class="notif-header">
                    <span class="notif-header-title">Notifikasi</span>
                    <button class="notif-read-all" onclick="markAllRead()" id="notifReadAllBtn" style="display:none;">
                        Tandai semua dibaca
                    </button>
                </div>
                <div class="notif-list" id="notifList">
                    <div class="notif-empty">Tidak ada notifikasi</div>
                </div>
            </div>
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

// ── Notification Dropdown ──────────────────────────────────────────────────
function toggleNotifDropdown() {
    const dropdown = document.getElementById('notifDropdown');
    const isOpen = dropdown.classList.contains('active');
    // Close user menu first
    document.getElementById('userDropdown')?.classList.remove('active');
    dropdown.classList.toggle('active');
    if (!isOpen) loadNotifications();
}

function loadNotifications() {
    axios.get('/notifications').then(res => {
        const { data, unread } = res.data;
        renderNotifications(data);
        updateBadge(unread);
        updateSidebarBadges(data);
    });
}

function renderNotifications(items) {
    const list = document.getElementById('notifList');
    const readAllBtn = document.getElementById('notifReadAllBtn');
    if (!items.length) {
        list.innerHTML = '<div class="notif-empty">Tidak ada notifikasi</div>';
        if (readAllBtn) readAllBtn.style.display = 'none';
        return;
    }
    const hasUnread = items.some(n => !n.is_read);
    if (readAllBtn) readAllBtn.style.display = hasUnread ? 'block' : 'none';
    list.innerHTML = items.map(n => `
        <div class="notif-item ${n.is_read ? '' : 'unread'}" onclick="handleNotifClick('${n.id}', '${n.url || ''}')">
            <div class="notif-icon ${n.type === 'approval' ? 'approval' : 'info'}">
                <i data-lucide="${n.type === 'approval' ? 'clock' : 'info'}" style="width:14px;height:14px;"></i>
            </div>
            <div class="notif-content">
                <div class="notif-title">${escNotif(n.title)}</div>
                <div class="notif-message">${escNotif(n.message)}</div>
                <div class="notif-time">${escNotif(n.created_at)}</div>
            </div>
            ${!n.is_read ? '<div class="notif-dot"></div>' : ''}
        </div>
    `).join('');
    lucide.createIcons();
}

function handleNotifClick(id, url) {
    axios.post(`/notifications/${id}/read`).then(() => {
        loadNotificationsCount();
        if (url) window.location.href = url;
        else loadNotifications();
    });
}

function markAllRead() {
    axios.post('/notifications/read-all').then(() => {
        loadNotifications();
        updateBadge(0);
    });
}

function updateBadge(count) {
    const badge = document.getElementById('notifBadge');
    if (!badge) return;
    if (count > 0) {
        badge.textContent = count > 99 ? '99+' : count;
        badge.style.display = 'flex';
    } else {
        badge.style.display = 'none';
    }
}

// Map URL pathname → sidebar badge element ID
const _sidebarBadgeMap = {
    '/purchase':      'sidebarBadge-purchase',
    '/sales':         'sidebarBadge-sales',
    '/capital':       'sidebarBadge-capital',
    '/expenses':      'sidebarBadge-expenses',
    '/lori':          'sidebarBadge-lori',
    '/lori-expense':  'sidebarBadge-lori-expense',
};

function updateSidebarBadges(items) {
    // Reset all
    Object.values(_sidebarBadgeMap).forEach(id => {
        const el = document.getElementById(id);
        if (el) el.style.display = 'none';
    });
    // Count unread per path
    const counts = {};
    items.forEach(function (n) {
        if (n.is_read || !n.url) return;
        try {
            const path = new URL(n.url).pathname.replace(/\/$/, '') || '/';
            counts[path] = (counts[path] || 0) + 1;
        } catch (e) {}
    });
    // Apply
    Object.entries(counts).forEach(function ([path, count]) {
        const id = _sidebarBadgeMap[path];
        if (!id) return;
        const el = document.getElementById(id);
        if (el) {
            el.textContent     = count > 99 ? '99+' : count;
            el.style.display   = 'inline-flex';
        }
    });
}

function loadNotificationsCount() {
    axios.get('/notifications').then(function (res) {
        updateBadge(res.data.unread);
        updateSidebarBadges(res.data.data);
    });
}

function escNotif(str) {
    if (str == null) return '';
    const d = document.createElement('div');
    d.textContent = String(str);
    return d.innerHTML;
}

// Close when clicking outside
document.addEventListener('click', function(e) {
    const wrap = document.getElementById('notifWrap');
    if (wrap && !wrap.contains(e.target)) {
        document.getElementById('notifDropdown')?.classList.remove('active');
    }
});

// Poll every 30 seconds
loadNotificationsCount();
setInterval(loadNotificationsCount, 30000);
</script>
