{{-- Global Alert Component --}}

<!-- Alert Modal -->
<div class="confirm-overlay" id="alertOverlay" onclick="closeAlertOnOverlay(event)">
    <div class="confirm-dialog">
        <div class="confirm-content">
            <div class="confirm-body">
                <div class="confirm-icon" id="alertIcon">
                    <i data-lucide="check-circle-2"></i>
                </div>
                <h3 class="confirm-title" id="alertTitle"></h3>
                <p class="confirm-message" id="alertMessage"></p>
            </div>
            <div class="confirm-actions" style="justify-content:center;">
                <button class="btn" id="alertOkBtn" onclick="closeAlert()" style="min-width:7rem;">
                    <i data-lucide="check" style="width:15px;height:15px;"></i>
                    OK
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Confirm Dialog -->
<div class="confirm-overlay" id="confirmOverlay" onclick="closeConfirmOnOverlay(event)">
    <div class="confirm-dialog">
        <div class="confirm-content">
            <div class="confirm-body">
                <div class="confirm-icon" id="confirmIcon">
                    <i data-lucide="alert-circle"></i>
                </div>
                <h3 class="confirm-title" id="confirmTitle">Konfirmasi</h3>
                <p class="confirm-message" id="confirmMessage"></p>
            </div>
            <div class="confirm-actions">
                <button class="btn" id="confirmCancelBtn" onclick="closeConfirm()">
                    <i data-lucide="x" style="width:15px;height:15px;"></i>
                    Batal
                </button>
                <button class="btn" id="confirmOkBtn" onclick="executeConfirm()">
                    <span class="btn-text">
                        <i data-lucide="check" style="width:15px;height:15px;"></i>
                        Konfirmasi
                    </span>
                    <span class="btn-loading">
                        <span class="spinner"></span>
                        Loading...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // ── Alert Modal ──────────────────────────────────────────────
    const alertIcons = {
        success: 'check-circle-2',
        error:   'alert-circle',
        warning: 'alert-triangle',
        info:    'info'
    };

    const alertIconTypes = {
        success: 'success',
        error:   'danger',
        warning: 'warning',
        info:    'info'
    };

    function showAlert(options) {
        const type    = options.type || 'info';
        const overlay = document.getElementById('alertOverlay');
        const icon    = document.getElementById('alertIcon');
        const title   = document.getElementById('alertTitle');
        const message = document.getElementById('alertMessage');
        const okBtn   = document.getElementById('alertOkBtn');

        icon.className = 'confirm-icon ' + alertIconTypes[type];
        icon.innerHTML = `<i data-lucide="${alertIcons[type]}"></i>`;

        title.textContent = options.title || '';
        message.textContent = options.message || '';
        message.style.display = options.message ? 'block' : 'none';

        okBtn.className = 'btn btn-confirm-' + alertIconTypes[type];
        okBtn.style.minWidth = '7rem';

        overlay.classList.add('active');
        lucide.createIcons();
    }

    function closeAlert() {
        document.getElementById('alertOverlay').classList.remove('active');
    }

    function closeAlertOnOverlay(event) {
        if (event.target === event.currentTarget) closeAlert();
    }

    function showSuccess(title, message) { showAlert({ title, message, type: 'success' }); }
    function showError(title, message)   { showAlert({ title, message, type: 'error' }); }
    function showWarning(title, message) { showAlert({ title, message, type: 'warning' }); }
    function showInfo(title, message)    { showAlert({ title, message, type: 'info' }); }

    // ── Confirm Dialog ───────────────────────────────────────────
    let confirmCallback = null;
    let confirmCancelCallback = null;

    function showConfirm(options) {
        const overlay   = document.getElementById('confirmOverlay');
        const icon      = document.getElementById('confirmIcon');
        const title     = document.getElementById('confirmTitle');
        const message   = document.getElementById('confirmMessage');
        const cancelBtn = document.getElementById('confirmCancelBtn');
        const okBtn     = document.getElementById('confirmOkBtn');

        const type = options.type || 'info';

        icon.className = 'confirm-icon ' + type;
        const iconName = type === 'danger' ? 'alert-circle' : (type === 'warning' ? 'alert-triangle' : 'info');
        icon.innerHTML = `<i data-lucide="${iconName}"></i>`;

        title.textContent   = options.title || 'Konfirmasi';
        message.textContent = options.message || '';
        message.style.display = options.message ? 'block' : 'none';

        cancelBtn.textContent = options.cancelText || 'Batal';
        okBtn.querySelector('.btn-text').textContent = options.confirmText || 'Konfirmasi';
        okBtn.className = 'btn btn-confirm-' + type;

        confirmCallback       = options.onConfirm || null;
        confirmCancelCallback = options.onCancel  || null;

        overlay.classList.add('active');
        lucide.createIcons();
    }

    function closeConfirm() {
        document.getElementById('confirmOverlay').classList.remove('active');
        if (confirmCancelCallback) confirmCancelCallback();
        confirmCallback = null;
        confirmCancelCallback = null;
    }

    function closeConfirmOnOverlay(event) {
        if (event.target === event.currentTarget) closeConfirm();
    }

    async function executeConfirm() {
        const okBtn      = document.getElementById('confirmOkBtn');
        const btnText    = okBtn.querySelector('.btn-text');
        const btnLoading = okBtn.querySelector('.btn-loading');

        if (confirmCallback) {
            btnText.style.display    = 'none';
            btnLoading.style.display = 'flex';
            okBtn.disabled = true;
            try {
                await confirmCallback();
            } finally {
                btnText.style.display    = 'inline';
                btnLoading.style.display = 'none';
                okBtn.disabled = false;
            }
        }
        closeConfirm();
    }

    // ── Auto-show from session ────────────────────────────────────
    @if(session('success'))
        showSuccess('{{ session('success.title', 'Berhasil') }}', '{{ session('success.message', session('success')) }}');
    @endif
    @if(session('error'))
        showError('{{ session('error.title', 'Error') }}', '{{ session('error.message', session('error')) }}');
    @endif
    @if(session('warning'))
        showWarning('{{ session('warning.title', 'Peringatan') }}', '{{ session('warning.message', session('warning')) }}');
    @endif
    @if(session('info'))
        showInfo('{{ session('info.title', 'Info') }}', '{{ session('info.message', session('info')) }}');
    @endif
</script>
