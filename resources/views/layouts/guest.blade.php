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
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>html, body { height: 100%; overflow: hidden; }</style>
</head>
<body>
    <div class="login-container">
        <!-- Left Panel - Image -->
        <div class="brand-panel">
            <img src="/images/login.jpg" alt="Login Illustration" class="brand-image">
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
