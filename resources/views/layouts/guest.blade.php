<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'TankSys Pro') }} - @yield('title', 'Login')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        :root {
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
            --destructive: #dc2626;
            --destructive-foreground: #ffffff;
            --success: #16a34a;
            --success-foreground: #ffffff;
            --border: #e7e5e4;
            --input: #e7e5e4;
            --ring: #d97706;
            --radius: 0.625rem;
            --sidebar: #1c1917;
            --sidebar-foreground: #e7e5e4;
            --sidebar-primary: #d97706;
            --sidebar-primary-foreground: #ffffff;
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

        .login-container {
            min-height: 100vh;
            display: flex;
        }

        /* Left Panel - Branding */
        .brand-panel {
            display: none;
            width: 50%;
            background-color: var(--sidebar);
            position: relative;
            overflow: hidden;
        }

        @media (min-width: 1024px) {
            .brand-panel {
                display: flex;
            }
        }

        .brand-pattern {
            position: absolute;
            inset: 0;
            opacity: 0.1;
        }

        .brand-content {
            position: relative;
            z-index: 10;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 3rem 4rem;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 2rem;
        }

        .brand-logo-icon {
            width: 3rem;
            height: 3rem;
            background-color: var(--sidebar-primary);
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--sidebar-primary-foreground);
        }

        .brand-logo-text {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--sidebar-foreground);
        }

        .brand-logo-sub {
            font-size: 0.875rem;
            color: var(--sidebar-muted);
        }

        .brand-tagline {
            font-size: 2rem;
            font-weight: 700;
            color: var(--sidebar-foreground);
            line-height: 1.3;
            margin-bottom: 1rem;
        }

        .brand-tagline span {
            color: var(--sidebar-primary);
        }

        .brand-description {
            font-size: 1.125rem;
            color: var(--sidebar-muted);
            max-width: 28rem;
            line-height: 1.6;
        }

        .brand-features {
            margin-top: 3rem;
        }

        .brand-feature {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--sidebar-foreground);
            margin-bottom: 1rem;
        }

        .brand-feature-dot {
            width: 0.5rem;
            height: 0.5rem;
            background-color: var(--sidebar-primary);
            border-radius: 50%;
        }

        .brand-feature span {
            font-size: 0.875rem;
        }

        .brand-decoration-1 {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 24rem;
            height: 24rem;
            background-color: rgba(217, 119, 6, 0.1);
            border-radius: 50%;
            filter: blur(64px);
        }

        .brand-decoration-2 {
            position: absolute;
            top: 5rem;
            right: 5rem;
            width: 16rem;
            height: 16rem;
            background-color: rgba(217, 119, 6, 0.05);
            border-radius: 50%;
            filter: blur(48px);
        }

        /* Right Panel - Form */
        .form-panel {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        @media (min-width: 1024px) {
            .form-panel {
                padding: 3rem;
            }
        }

        .form-container {
            width: 100%;
            max-width: 28rem;
        }

        /* Mobile Logo */
        .mobile-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            justify-content: center;
            margin-bottom: 2rem;
        }

        @media (min-width: 1024px) {
            .mobile-logo {
                display: none;
            }
        }

        .mobile-logo-icon {
            width: 2.5rem;
            height: 2.5rem;
            background-color: var(--primary);
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-foreground);
        }

        .mobile-logo-text {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--foreground);
        }

        .mobile-logo-sub {
            font-size: 0.75rem;
            color: var(--muted-foreground);
        }

        /* Form Header */
        .form-header {
            margin-bottom: 2rem;
        }

        .form-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--foreground);
        }

        .form-subtitle {
            color: var(--muted-foreground);
            margin-top: 0.5rem;
        }

        /* Form Elements */
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--foreground);
            margin-bottom: 0.5rem;
        }

        .input-group {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted-foreground);
        }

        .input-icon svg {
            width: 1.25rem;
            height: 1.25rem;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 0.75rem 0.75rem 2.5rem;
            font-size: 0.875rem;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            background-color: var(--input);
            color: var(--foreground);
            outline: none;
            font-family: inherit;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .form-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(217, 119, 6, 0.2);
        }

        .form-input.has-end-icon {
            padding-right: 2.5rem;
        }

        .input-end-icon {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--muted-foreground);
            padding: 0;
        }

        .input-end-icon:hover {
            color: var(--foreground);
        }

        .input-end-icon svg {
            width: 1.25rem;
            height: 1.25rem;
        }

        /* Form Options */
        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
        }

        .remember-me input {
            width: 1rem;
            height: 1rem;
            accent-color: var(--primary);
        }

        .remember-me span {
            font-size: 0.875rem;
            color: var(--muted-foreground);
        }

        .forgot-password {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--primary);
            text-decoration: none;
            transition: color 0.15s ease;
        }

        .forgot-password:hover {
            opacity: 0.8;
        }

        /* Submit Button */
        .submit-btn {
            width: 100%;
            padding: 0.75rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--primary-foreground);
            background-color: var(--primary);
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            transition: opacity 0.15s ease;
            font-family: inherit;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .submit-btn:hover:not(:disabled) {
            opacity: 0.9;
        }

        .submit-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .submit-btn .spinner {
            width: 1.25rem;
            height: 1.25rem;
            border: 2px solid currentColor;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Demo Box */
        .demo-box {
            margin-top: 2rem;
            padding: 1rem;
            background-color: var(--muted);
            border: 1px solid var(--border);
            border-radius: var(--radius);
        }

        .demo-box-title {
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--muted-foreground);
            margin-bottom: 0.5rem;
        }

        .demo-box-content {
            display: flex;
            gap: 1rem;
            font-size: 0.875rem;
        }

        .demo-box-content span {
            color: var(--muted-foreground);
        }

        .demo-box-content strong {
            color: var(--foreground);
        }

        /* Footer */
        .form-footer {
            text-align: center;
            margin-top: 2rem;
            font-size: 0.75rem;
            color: var(--muted-foreground);
        }

        /* Error Message */
        .error-message {
            color: var(--destructive);
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Left Panel - Branding -->
        <div class="brand-panel">
            <div class="brand-pattern">
                <svg width="100%" height="100%" viewBox="0 0 100 100" preserveAspectRatio="none">
                    <defs>
                        <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
                            <path d="M 10 0 L 0 0 0 10" fill="none" stroke="currentColor" stroke-width="0.5" />
                        </pattern>
                    </defs>
                    <rect width="100" height="100" fill="url(#grid)" />
                </svg>
            </div>

            <div class="brand-content">
                <div class="brand-logo">
                    <div class="brand-logo-icon">
                        <i data-lucide="fuel" style="width: 1.75rem; height: 1.75rem;"></i>
                    </div>
                    <div>
                        <div class="brand-logo-text">TankSys Pro</div>
                        <div class="brand-logo-sub">Fuel Management System</div>
                    </div>
                </div>

                <h2 class="brand-tagline">
                    Solusi Terpercaya untuk <span>Manajemen Bisnis BBM</span>
                </h2>
                <p class="brand-description">
                    Kelola pembelian, penjualan, stok, dan laporan keuangan bisnis bahan bakar Anda dengan mudah dan efisien.
                </p>

                <div class="brand-features">
                    <div class="brand-feature">
                        <div class="brand-feature-dot"></div>
                        <span>Dashboard Real-time</span>
                    </div>
                    <div class="brand-feature">
                        <div class="brand-feature-dot"></div>
                        <span>Manajemen Stok Otomatis</span>
                    </div>
                    <div class="brand-feature">
                        <div class="brand-feature-dot"></div>
                        <span>Laporan Profit/Loss</span>
                    </div>
                    <div class="brand-feature">
                        <div class="brand-feature-dot"></div>
                        <span>Multi-User Access</span>
                    </div>
                </div>
            </div>

            <div class="brand-decoration-1"></div>
            <div class="brand-decoration-2"></div>
        </div>

        <!-- Right Panel - Form -->
        <div class="form-panel">
            @yield('content')
        </div>
    </div>

    @include('components.global-alert')

    <script>
        lucide.createIcons();
    </script>

    @stack('scripts')
</body>
</html>
