@extends('layouts.guest')

@section('title', 'Ganti Password')

@section('content')
<div class="form-container">

    <div class="mobile-logo">
        <div class="mobile-logo-icon">
            <i data-lucide="fuel" style="width:1.5rem;height:1.5rem;"></i>
        </div>
        <div>
            <div class="mobile-logo-text">TankSys Pro</div>
            <div class="mobile-logo-sub">Fuel Management System</div>
        </div>
    </div>

    <div class="form-header">
        <h1 class="form-title">Ganti Password</h1>
        <p class="form-subtitle">Anda diwajibkan mengganti password sebelum melanjutkan</p>
    </div>

    @if ($errors->any())
        <div style="background:rgba(220,38,38,0.08);border:1px solid rgba(220,38,38,0.3);border-radius:var(--radius);padding:0.75rem 1rem;margin-bottom:1.25rem;">
            <p style="font-size:0.875rem;color:var(--destructive);">
                {{ $errors->first() }}
            </p>
        </div>
    @endif

    <form method="POST" action="{{ route('password.reset') }}" id="resetForm">
        @csrf

        <div class="form-group">
            <label class="form-label">Password Baru</label>
            <div class="input-group">
                <span class="input-icon">
                    <i data-lucide="lock" style="width:1.25rem;height:1.25rem;"></i>
                </span>
                <input
                    type="password"
                    name="password"
                    id="password"
                    placeholder="Masukkan password baru"
                    class="form-input has-start-icon has-end-icon"
                    required
                    autofocus
                />
                <button type="button" class="input-end-icon" onclick="togglePassword('password', 'eye1', 'eyeOff1')">
                    <i data-lucide="eye" id="eye1" style="width:1.25rem;height:1.25rem;"></i>
                    <i data-lucide="eye-off" id="eyeOff1" style="width:1.25rem;height:1.25rem;display:none;"></i>
                </button>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Konfirmasi Password</label>
            <div class="input-group">
                <span class="input-icon">
                    <i data-lucide="lock-keyhole" style="width:1.25rem;height:1.25rem;"></i>
                </span>
                <input
                    type="password"
                    name="password_confirmation"
                    id="password_confirmation"
                    placeholder="Ulangi password baru"
                    class="form-input has-start-icon has-end-icon"
                    required
                />
                <button type="button" class="input-end-icon" onclick="togglePassword('password_confirmation', 'eye2', 'eyeOff2')">
                    <i data-lucide="eye" id="eye2" style="width:1.25rem;height:1.25rem;"></i>
                    <i data-lucide="eye-off" id="eyeOff2" style="width:1.25rem;height:1.25rem;display:none;"></i>
                </button>
            </div>
        </div>

        <button type="submit" class="submit-btn" id="submitBtn">
            <span id="btnText">Simpan Password</span>
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

    function togglePassword(inputId, eyeId, eyeOffId) {
        const input  = document.getElementById(inputId);
        const eye    = document.getElementById(eyeId);
        const eyeOff = document.getElementById(eyeOffId);
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

    document.getElementById('resetForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        document.getElementById('btnText').textContent = 'Menyimpan...';
        document.getElementById('btnSpinner').style.display = 'inline-block';
        btn.disabled = true;
    });
</script>
@endpush
@endsection
