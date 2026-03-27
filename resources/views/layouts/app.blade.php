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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

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
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        :root {
            /* Primary - Amber/Orange for Fuel Company */
            --background: #fafaf9;
            --foreground: #1c1917;
            --card: #ffffff;
            --card-foreground: #1c1917;
            --primary: #d97706;
            --primary-foreground: #ffffff;
            --secondary: #f5f5f4;
            --secondary-foreground: #292524;
            --muted: #f5f5f4;
            --muted-foreground: #78716c;
            --accent: #fef3c7;
            --accent-foreground: #92400e;
            --destructive: #dc2626;
            --destructive-foreground: #ffffff;
            --success: #16a34a;
            --success-foreground: #ffffff;
            --warning: #eab308;
            --warning-foreground: #1c1917;
            --info: #2563eb;
            --info-foreground: #ffffff;
            --border: #e7e5e4;
            --input: #e7e5e4;
            --ring: #d97706;
            --radius: 0.625rem;
            
            /* Sidebar */
            --sidebar: #1c1917;
            --sidebar-foreground: #e7e5e4;
            --sidebar-primary: #d97706;
            --sidebar-primary-foreground: #ffffff;
            --sidebar-accent: #292524;
            --sidebar-accent-foreground: #f5f5f4;
            --sidebar-border: #3f3f46;
            --sidebar-muted: #a8a29e;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background);
            color: var(--foreground);
        }

        /* Utility Classes */
        .bg-background { background-color: var(--background); }
        .bg-card { background-color: var(--card); }
        .bg-primary { background-color: var(--primary); }
        .bg-secondary { background-color: var(--secondary); }
        .bg-muted { background-color: var(--muted); }
        .bg-sidebar { background-color: var(--sidebar); }
        .bg-sidebar-accent { background-color: var(--sidebar-accent); }
        
        .text-foreground { color: var(--foreground); }
        .text-card-foreground { color: var(--card-foreground); }
        .text-primary { color: var(--primary); }
        .text-primary-foreground { color: var(--primary-foreground); }
        .text-muted-foreground { color: var(--muted-foreground); }
        .text-sidebar-foreground { color: var(--sidebar-foreground); }
        .text-sidebar-muted { color: var(--sidebar-muted); }
        .text-success { color: var(--success); }
        .text-destructive { color: var(--destructive); }
        .text-warning { color: var(--warning); }
        .text-info { color: var(--info); }
        
        .border-border { border-color: var(--border); }
        .border-sidebar-border { border-color: var(--sidebar-border); }

        /* Layout */
        .app-layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 15rem;
            background-color: var(--sidebar);
            border-right: 1px solid var(--sidebar-border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 50;
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }

        .sidebar.open {
            transform: translateX(0);
        }

        @media (min-width: 1024px) {
            .sidebar {
                position: static;
                transform: translateX(0);
            }
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 40;
        }

        .sidebar-overlay.active {
            display: block;
        }

        @media (min-width: 1024px) {
            .sidebar-overlay {
                display: none !important;
            }
        }

        .sidebar-logo {
            padding: 1rem;
            border-bottom: 1px solid var(--sidebar-border);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .logo-icon {
            width: 2.25rem;
            height: 2.25rem;
            background-color: var(--sidebar-primary);
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--sidebar-primary-foreground);
        }

        .logo-text {
            font-size: 1rem;
            font-weight: 600;
            color: var(--sidebar-foreground);
        }

        .logo-sub {
            font-size: 0.75rem;
            color: var(--sidebar-muted);
        }

        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            padding: 0.75rem;
        }

        .nav-section {
            padding: 0.5rem 0.75rem;
            font-size: 0.625rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--sidebar-muted);
            margin-bottom: 0.25rem;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            color: var(--sidebar-muted);
            text-decoration: none;
            transition: all 0.15s ease;
            margin-bottom: 0.25rem;
        }

        .nav-item:hover {
            background-color: var(--sidebar-accent);
            color: var(--sidebar-foreground);
        }

        .nav-item.active {
            background-color: var(--sidebar-accent);
            color: var(--sidebar-accent-foreground);
            font-weight: 500;
            border: 1px solid var(--sidebar-border);
        }

        .nav-item i, .nav-item svg {
            width: 1rem;
            height: 1rem;
            flex-shrink: 0;
        }

        .sidebar-footer {
            padding: 1rem;
            border-top: 1px solid var(--sidebar-border);
            text-align: center;
            font-size: 0.625rem;
            color: var(--sidebar-muted);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            width: 100%;
        }

        @media (min-width: 1024px) {
            .main-content {
                margin-left: 15rem;
                width: calc(100% - 15rem);
            }
        }

        /* Topbar */
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 1rem;
            background-color: var(--card);
            border-bottom: 1px solid var(--border);
        }

        @media (min-width: 1024px) {
            .topbar {
                padding: 0.75rem 1.5rem;
            }
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .page-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--foreground);
        }

        .menu-btn {
            display: block;
            padding: 0.5rem;
            background: none;
            border: none;
            cursor: pointer;
            color: var(--foreground);
        }

        @media (min-width: 1024px) {
            .menu-btn {
                display: none;
            }
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .notification-btn {
            position: relative;
            padding: 0.5rem;
            background: none;
            border: none;
            cursor: pointer;
            color: var(--muted-foreground);
        }

        .notification-btn:hover {
            color: var(--foreground);
        }

        .notification-dot {
            position: absolute;
            top: 0.375rem;
            right: 0.375rem;
            width: 0.5rem;
            height: 0.5rem;
            background-color: var(--destructive);
            border-radius: 50%;
        }

        .user-menu {
            position: relative;
        }

        .user-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.375rem 0.5rem;
            background: none;
            border: none;
            cursor: pointer;
        }

        .user-avatar {
            width: 2rem;
            height: 2rem;
            background-color: rgba(217, 119, 6, 0.1);
            color: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .user-info {
            display: none;
            text-align: left;
        }

        @media (min-width: 640px) {
            .user-info {
                display: block;
            }
        }

        .user-name {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--foreground);
        }

        .user-role {
            font-size: 0.75rem;
            color: var(--muted-foreground);
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 0.5rem;
            background-color: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            min-width: 12rem;
            z-index: 50;
        }

        .dropdown-menu.active {
            display: block;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            color: var(--foreground);
            text-decoration: none;
            cursor: pointer;
        }

        .dropdown-item:hover {
            background-color: var(--muted);
        }

        .dropdown-item.danger {
            color: var(--destructive);
        }

        .dropdown-divider {
            height: 1px;
            background-color: var(--border);
            margin: 0.25rem 0;
        }

        /* Content Area */
        .content-area {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
        }

        @media (min-width: 1024px) {
            .content-area {
                padding: 1.5rem;
            }
        }

        /* Card Component */
        .card {
            background-color: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            margin-bottom: 1rem;
        }

        .card-header {
            padding: 1rem;
            border-bottom: 1px solid var(--border);
        }

        .card-title {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--foreground);
        }

        .card-content {
            padding: 1rem;
        }

        /* Form Elements */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(1, 1fr);
            gap: 1rem;
        }

        @media (min-width: 768px) {
            .form-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        .form-label {
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--muted-foreground);
        }

        .form-input, .form-select, .form-textarea {
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            border: 1px solid var(--border);
            border-radius: calc(var(--radius) - 2px);
            background-color: var(--card);
            color: var(--foreground);
            outline: none;
            font-family: inherit;
        }

        .form-input:focus, .form-select:focus, .form-textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(217, 119, 6, 0.2);
        }

        .form-input:disabled, .form-input[readonly] {
            background-color: var(--muted);
            color: var(--muted-foreground);
        }

        .form-textarea {
            resize: none;
            min-height: 60px;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: calc(var(--radius) - 2px);
            border: 1px solid var(--border);
            background-color: var(--card);
            color: var(--foreground);
            cursor: pointer;
            transition: all 0.15s ease;
            font-family: inherit;
        }

        .btn:hover {
            background-color: var(--muted);
        }

        .btn-primary {
            background-color: var(--primary);
            color: var(--primary-foreground);
            border-color: var(--primary);
        }

        .btn-primary:hover {
            opacity: 0.9;
        }

        .btn-success {
            background-color: var(--success);
            color: var(--success-foreground);
            border-color: var(--success);
        }

        .btn-danger {
            color: var(--destructive);
        }

        .btn-danger:hover {
            background-color: rgba(220, 38, 38, 0.1);
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        /* Table */
        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }

        th {
            text-align: left;
            padding: 0.5rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--muted-foreground);
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }

        th.text-right {
            text-align: right;
        }

        th.text-center {
            text-align: center;
        }

        td {
            padding: 0.625rem 0.75rem;
            border-bottom: 1px solid var(--border);
            color: var(--foreground);
            vertical-align: middle;
        }

        td.text-right {
            text-align: right;
        }

        td.text-center {
            text-align: center;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tbody tr:hover td {
            background-color: var(--muted);
        }

        tfoot td {
            font-weight: 600;
            background-color: var(--muted);
        }

        /* Badge */
        .badge {
            display: inline-block;
            padding: 0.125rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.625rem;
            font-weight: 500;
        }

        .badge-success {
            background-color: rgba(22, 163, 74, 0.1);
            color: var(--success);
        }

        .badge-danger {
            background-color: rgba(220, 38, 38, 0.1);
            color: var(--destructive);
        }

        .badge-warning {
            background-color: rgba(234, 179, 8, 0.1);
            color: var(--warning);
        }

        .badge-info {
            background-color: rgba(37, 99, 235, 0.1);
            color: var(--info);
        }

        /* Stats Card */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(1, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        @media (min-width: 640px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        .stat-card {
            background-color: var(--secondary);
            border-radius: var(--radius);
            padding: 1rem;
        }

        .stat-label {
            font-size: 0.75rem;
            color: var(--muted-foreground);
            margin-bottom: 0.25rem;
        }

        .stat-value {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--foreground);
        }

        .stat-sub {
            font-size: 0.75rem;
            color: var(--muted-foreground);
            margin-top: 0.25rem;
        }

        .stat-icon {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.5rem;
            background-color: rgba(217, 119, 6, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
        }

        /* Tab Bar */
        .tab-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .tab {
            padding: 0.5rem 1rem;
            border-radius: var(--radius);
            font-size: 0.875rem;
            font-weight: 500;
            background: none;
            border: none;
            cursor: pointer;
            color: var(--muted-foreground);
            transition: all 0.15s ease;
        }

        .tab:hover {
            background-color: var(--muted);
        }

        .tab.active {
            background-color: var(--secondary);
            color: var(--secondary-foreground);
            border: 1px solid var(--border);
        }

        /* Report Table */
        .report-table th,
        .report-table td {
            border: 1px solid var(--border);
        }

        .report-table thead th {
            background-color: var(--muted);
        }

        /* Summary Card */
        .summary-card {
            padding: 1rem;
            border-radius: var(--radius);
            margin-bottom: 1.5rem;
        }

        .summary-card.success {
            background-color: rgba(22, 163, 74, 0.05);
            border: 1px solid rgba(22, 163, 74, 0.2);
        }

        .summary-card.danger {
            background-color: rgba(220, 38, 38, 0.05);
            border: 1px solid rgba(220, 38, 38, 0.2);
        }

        .summary-card.primary {
            background-color: rgba(217, 119, 6, 0.05);
            border: 1px solid rgba(217, 119, 6, 0.2);
        }
    </style>

    <!-- DataTables Custom Theme -->
    <style>
        /* Override DataTables to match template */
        table.dataTable thead th,
        table.dataTable thead td {
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--muted-foreground);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid var(--border);
            padding: 0.625rem 0.75rem;
            background-color: var(--muted);
        }
        table.dataTable tbody td {
            padding: 0.625rem 0.75rem;
            font-size: 0.875rem;
            color: var(--foreground);
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }
        table.dataTable tbody tr:hover td { background-color: var(--muted); }
        table.dataTable tbody tr:last-child td { border-bottom: none; }
        table.dataTable { border-collapse: collapse !important; width: 100% !important; }
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate { font-size: 0.875rem; color: var(--muted-foreground); padding: 0.75rem 0; }
        .dataTables_wrapper .dataTables_filter input {
            padding: 0.375rem 0.75rem; border: 1px solid var(--border);
            border-radius: calc(var(--radius) - 2px); background-color: var(--card);
            color: var(--foreground); outline: none; margin-left: 0.5rem;
        }
        .dataTables_wrapper .dataTables_filter input:focus { border-color: var(--primary); }
        .dataTables_wrapper .dataTables_length select {
            padding: 0.25rem 0.5rem; border: 1px solid var(--border);
            border-radius: calc(var(--radius) - 2px); background-color: var(--card);
            color: var(--foreground); margin: 0 0.25rem;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.25rem 0.625rem; border-radius: calc(var(--radius) - 2px);
            border: 1px solid transparent; color: var(--foreground) !important; cursor: pointer;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: var(--muted) !important; border-color: var(--border) !important; color: var(--foreground) !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: var(--primary) !important; border-color: var(--primary) !important; color: #fff !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
            color: var(--muted-foreground) !important; background: none !important;
        }
        /* Modal Overlay */
        .modal-overlay {
            display: none; position: fixed; inset: 0; z-index: 60;
            align-items: center; justify-content: center;
            background-color: rgba(0,0,0,0.5); backdrop-filter: blur(4px);
        }
        .modal-overlay.active { display: flex; }
        .modal-box {
            background-color: var(--card); border: 1px solid var(--border);
            border-radius: var(--radius); width: 100%; max-width: 36rem;
            margin: 1rem; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
            animation: dialogIn 0.2s ease; max-height: 90vh; overflow-y: auto;
        }
        .modal-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 1rem 1.25rem; border-bottom: 1px solid var(--border);
        }
        .modal-title { font-size: 1rem; font-weight: 600; color: var(--foreground); }
        .modal-body { padding: 1.25rem; }
        .modal-footer {
            display: flex; justify-content: flex-end; gap: 0.5rem;
            padding: 1rem 1.25rem; border-top: 1px solid var(--border);
        }
        .modal-close-btn {
            background: none; border: none; cursor: pointer;
            color: var(--muted-foreground); padding: 0.25rem;
        }
        .modal-close-btn:hover { color: var(--foreground); }
    </style>

    @stack('styles')
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
            </div>
        </div>
    </div>

    <!-- Global Alert Component -->
    @include('components.global-alert')

    <script>
        // Axios global CSRF token
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        axios.defaults.headers.common['Accept'] = 'application/json';

        // Global DataTables default config
        $(document).ready(function () {
            $.extend(true, $.fn.dataTable.defaults, {
                language: {
                    search: 'Cari:',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                    infoEmpty: 'Tidak ada data',
                    infoFiltered: '(filter dari _MAX_ total data)',
                    zeroRecords: 'Data tidak ditemukan',
                    emptyTable: 'Tidak ada data tersedia',
                    paginate: { first: 'Pertama', last: 'Terakhir', next: '›', previous: '‹' },
                    processing: '<div style="padding:1rem;color:var(--muted-foreground)">Memuat data...</div>',
                },
                processing: true,
                pageLength: 25,
                order: [[0, 'desc']],
                responsive: true,
                autoWidth: false,
            });
        });

        // Initialize Lucide Icons
        lucide.createIcons();

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
