@extends('layouts.guest')

@section('title', 'Login')

@section('content')
<div class="form-container">

    <!-- Mobile Logo -->
    <div class="mobile-logo">
        <div class="mobile-logo-icon">
            <i data-lucide="fuel" style="width:1.5rem;height:1.5rem;"></i>
        </div>
        <div>
            <div class="mobile-logo-text">TankSys Pro</div>
            <div class="mobile-logo-sub">Fuel Management System</div>
        </div>
    </div>

    <!-- Company Brand -->
    <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.75rem;justify-content: center;">
        <img src="/images/logo.png" alt="Logo" style="width: 70%;">
    </div>

    <!-- Header -->
    <div class="form-header">
        <h1 class="form-title">Selamat Datang</h1>
        <p class="form-subtitle">Masuk ke akun Anda untuk melanjutkan</p>
    </div>

    @if ($errors->any())
        <div style="background:rgba(220,38,38,0.08);border:1px solid rgba(220,38,38,0.3);border-radius:var(--radius);padding:0.75rem 1rem;margin-bottom:1.25rem;">
            <p style="font-size:0.875rem;color:var(--destructive);">
                {{ $errors->first() }}
            </p>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" id="loginForm">
        @csrf

        <!-- Username -->
        <div class="form-group">
            <label class="form-label">Username</label>
            <div class="input-group">
                <span class="input-icon">
                    <i data-lucide="user" style="width:1.25rem;height:1.25rem;"></i>
                </span>
                <input
                    type="text"
                    name="username"
                    placeholder="Masukkan username"
                    value="{{ old('username') }}"
                    class="form-input has-start-icon"
                    required
                    autofocus
                />
            </div>
        </div>

        <!-- Password -->
        <div class="form-group">
            <label class="form-label">Password</label>
            <div class="input-group">
                <span class="input-icon">
                    <i data-lucide="lock" style="width:1.25rem;height:1.25rem;"></i>
                </span>
                <input
                    type="password"
                    name="password"
                    id="password"
                    placeholder="Masukkan password"
                    class="form-input has-start-icon has-end-icon"
                    required
                />
                <button type="button" class="input-end-icon" onclick="togglePassword()">
                    <i data-lucide="eye" id="eyeIcon" style="width:1.25rem;height:1.25rem;"></i>
                    <i data-lucide="eye-off" id="eyeOffIcon" style="width:1.25rem;height:1.25rem;display:none;"></i>
                </button>
            </div>
        </div>

        <!-- Remember Me -->
        <div class="form-options">
            <label class="remember-me">
                <input type="checkbox" name="remember" />
                <span>Ingat saya</span>
            </label>
        </div>

        <!-- Submit -->
        <button type="submit" class="submit-btn" id="submitBtn">
            <span id="btnText">Masuk</span>
            <span id="btnSpinner" class="spinner" style="display:none;"></span>
        </button>
    </form>

    <div class="form-footer">
        &copy; {{ date('Y') }} TankSys Pro. All rights reserved.
    </div>
</div>

@push('scripts')
<script>
    lucide.createIcons();

    function togglePassword() {
        const input = document.getElementById('password');
        const eye = document.getElementById('eyeIcon');
        const eyeOff = document.getElementById('eyeOffIcon');
        if (input.type === 'password') {
            input.type = 'text';
            eye.style.display = 'none';
            eyeOff.style.display = '';
        } else {
            input.type = 'password';
            eye.style.display = '';
            eyeOff.style.display = 'none';
        }
    }

    document.getElementById('loginForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        document.getElementById('btnText').textContent = 'Memproses...';
        document.getElementById('btnSpinner').style.display = 'inline-block';
        btn.disabled = true;
    });
</script>
@endpush
@endsection
