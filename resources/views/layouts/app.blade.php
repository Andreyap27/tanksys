<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'TankSys Pro') }} - @yield('title', 'Dashboard')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js" defer></script>

    <!-- Axios -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <!-- Styles -->
    @stack('styles')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="app-layout">
        <!-- Sidebar Overlay -->
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

        <!-- Sidebar -->
        @include('layouts.partials.sidebar')

        <!-- Main Content -->
        <div class="main-content">
            <!-- Topbar -->
            @include('layouts.partials.topbar')

            <!-- Content Area -->
            <div class="content-area">
                @yield('content')

                <!-- Footer -->
                <footer class="content-footer">
                    <span>&copy; {{ date('Y') }} <strong>PT. ANUGRAH ENERGI PETROLUM</strong> &mdash; All rights reserved.</span>
                    <span style="display:flex;align-items:center;gap:0.35rem;">
                        Developed with
                        <i data-lucide="heart" style="width:0.85rem;height:0.85rem;color:var(--destructive);fill:var(--destructive);"></i>
                        by
                        <a href="https://aikupos.com/" target="_blank" rel="noopener"
                           style="font-weight:700;color:var(--primary);text-decoration:none;letter-spacing:0.02em;"
                           onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                            AIKU TEAM
                        </a>
                    </span>
                </footer>
            </div>
        </div>
    </div>

    <!-- Global Alert Component -->
    @include('components.global-alert')

    <script>
        // ── Global Currency Formatter ────────────────────────────────────────
        const Currency = {
            locale:   'id-ID',
            currency: 'IDR',
            symbol:   'Rp',

            // Format angka → "Rp 1.500.000"
            format(value, opts = {}) {
                const num = parseFloat(value) || 0;
                const decimals = opts.decimals ?? 0;
                return this.symbol + ' ' + num.toLocaleString(this.locale, {
                    minimumFractionDigits: decimals,
                    maximumFractionDigits: decimals,
                });
            },

            // Format angka → "1.500.000" (tanpa simbol)
            number(value, decimals = 0) {
                return (parseFloat(value) || 0).toLocaleString(this.locale, {
                    minimumFractionDigits: decimals,
                    maximumFractionDigits: decimals,
                });
            },

            // Bersihkan string format → angka murni (untuk dikirim ke server)
            parse(value) {
                if (value === null || value === undefined) return 0;
                return parseFloat(String(value).replace(/[^0-9.]/g, '')) || 0;
            },
        };

        // Axios global CSRF token
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        axios.defaults.headers.common['Accept'] = 'application/json';

        // Global DataTables default config
        $(document).ready(function () {
            const dtEmptyHtml = `
                <div class="dt-empty-state">
                    <i data-lucide="inbox"></i>
                    <p class="dt-empty-title">Belum ada data</p>
                    <p class="dt-empty-sub">Data akan muncul di sini setelah ditambahkan</p>
                </div>`;

            const dtNoResultHtml = `
                <div class="dt-empty-state">
                    <i data-lucide="search-x"></i>
                    <p class="dt-empty-title">Data tidak ditemukan</p>
                    <p class="dt-empty-sub">Coba ubah kata pencarian Anda</p>
                </div>`;

            const dtProcessingHtml = `
                <div class="dt-processing-inner">
                    <div class="dt-spinner"></div>
                    <span>Memuat data...</span>
                </div>`;

            $.extend(true, $.fn.dataTable.defaults, {
                language: {
                    search: ' ',
                    searchPlaceholder: 'Cari data...',
                    lengthMenu: '_MENU_',
                    info: '_START_\u2013_END_ dari _TOTAL_ data',
                    infoEmpty: 'Tidak ada data',
                    infoFiltered: '(filter _MAX_)',
                    zeroRecords: dtNoResultHtml,
                    emptyTable: dtEmptyHtml,
                    paginate: { first: '«', last: '»', next: '›', previous: '‹' },
                    processing: dtProcessingHtml,
                },
                dom: 'ft<"dt-foot"<"dt-foot-left"il><"dt-foot-right"p>>',
                processing: true,
                pageLength: 25,
                order: [[0, 'desc']],
                responsive: true,
                autoWidth: false,
                initComplete: function () {
                    var container = this.api().table().container();
                    var $searchSlot = $(container).closest('.card').find('.dt-search-slot');
                    if ($searchSlot.length) {
                        $(container).find('.dataTables_filter').appendTo($searchSlot);
                    }
                },
            });

            // Global events — won't conflict with per-table drawCallback
            $(document).on('preXhr.dt', function (e, settings) {
                $(settings.nTable).closest('.dataTables_wrapper').addClass('dt-is-loading');
            });
            $(document).on('draw.dt', function (e, settings) {
                $(settings.nTable).closest('.dataTables_wrapper').removeClass('dt-is-loading');
                lucide.createIcons();
            });
        });

        // Initialize Lucide Icons
        lucide.createIcons();

        // ── Save Button Loading Animation ─────────────────────────────────────
        const _saveBtnSpinnerHtml = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" style="width:15px;height:15px;flex-shrink:0;animation:spin .7s linear infinite;"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" style="opacity:.3"/><path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>`;
        let _activeSaveBtn = null;

        document.addEventListener('click', function (e) {
            const btn = e.target.closest('[data-save-btn]');
            if (btn && !btn.disabled) {
                _activeSaveBtn = btn;
                btn._origHTML  = btn.innerHTML;
                btn.disabled   = true;
                btn.innerHTML  = _saveBtnSpinnerHtml + ' Menyimpan...';
            }
        }, true);

        axios.interceptors.response.use(
            function (response) { _resetSaveBtn(); return response; },
            function (error)    { _resetSaveBtn(); return Promise.reject(error); }
        );

        function _resetSaveBtn() {
            if (!_activeSaveBtn) return;
            const btn      = _activeSaveBtn;
            _activeSaveBtn = null;
            btn.disabled   = false;
            if (btn._origHTML) { btn.innerHTML = btn._origHTML; btn._origHTML = null; }
            lucide.createIcons();
        }

        // Sidebar Toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
        }

        // User Dropdown
        function toggleUserMenu() {
            const menu = document.getElementById('userDropdown');
            menu.classList.toggle('active');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const userMenu = document.querySelector('.user-menu');
            const dropdown = document.getElementById('userDropdown');
            if (!userMenu.contains(e.target)) {
                dropdown.classList.remove('active');
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
