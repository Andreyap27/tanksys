{{-- Global Alert Component --}}
<style>
    /* Alert Container */
    .alert-container {
        position: fixed;
        top: 1rem;
        right: 1rem;
        z-index: 99;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        max-width: 24rem;
        width: 100%;
    }

    /* Alert Toast */
    .alert-toast {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 1rem;
        border-radius: var(--radius);
        border: 1px solid;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        backdrop-filter: blur(4px);
        transform: translateX(100%);
        opacity: 0;
        transition: all 0.3s ease;
    }

    .alert-toast.show {
        transform: translateX(0);
        opacity: 1;
    }

    .alert-toast.success {
        background-color: rgba(22, 163, 74, 0.1);
        border-color: rgba(22, 163, 74, 0.3);
    }

    .alert-toast.error {
        background-color: rgba(220, 38, 38, 0.1);
        border-color: rgba(220, 38, 38, 0.3);
    }

    .alert-toast.warning {
        background-color: rgba(234, 179, 8, 0.1);
        border-color: rgba(234, 179, 8, 0.3);
    }

    .alert-toast.info {
        background-color: rgba(37, 99, 235, 0.1);
        border-color: rgba(37, 99, 235, 0.3);
    }

    .alert-icon {
        flex-shrink: 0;
    }

    .alert-icon svg {
        width: 1.25rem;
        height: 1.25rem;
    }

    .alert-toast.success .alert-icon svg { color: var(--success); }
    .alert-toast.error .alert-icon svg { color: var(--destructive); }
    .alert-toast.warning .alert-icon svg { color: var(--warning); }
    .alert-toast.info .alert-icon svg { color: var(--info); }

    .alert-content {
        flex: 1;
        min-width: 0;
    }

    .alert-title {
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--foreground);
    }

    .alert-message {
        font-size: 0.875rem;
        color: var(--muted-foreground);
        margin-top: 0.25rem;
    }

    .alert-close {
        background: none;
        border: none;
        padding: 0;
        cursor: pointer;
        color: var(--muted-foreground);
        transition: color 0.15s ease;
    }

    .alert-close:hover {
        color: var(--foreground);
    }

    .alert-close svg {
        width: 1rem;
        height: 1rem;
    }

    /* Confirm Dialog */
    .confirm-overlay {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 100;
        align-items: center;
        justify-content: center;
        background-color: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
    }

    .confirm-overlay.active {
        display: flex;
    }

    .confirm-dialog {
        position: relative;
        width: 100%;
        max-width: 28rem;
        margin: 1rem;
        background-color: var(--card);
        border-radius: var(--radius);
        border: 1px solid var(--border);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        animation: dialogIn 0.2s ease;
    }

    @keyframes dialogIn {
        from {
            opacity: 0;
            transform: scale(0.95);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .confirm-content {
        padding: 1.5rem;
    }

    .confirm-body {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .confirm-icon {
        margin-bottom: 1rem;
    }

    .confirm-icon svg {
        width: 2.5rem;
        height: 2.5rem;
    }

    .confirm-icon.danger svg { color: var(--destructive); }
    .confirm-icon.warning svg { color: var(--warning); }
    .confirm-icon.info svg { color: var(--info); }

    .confirm-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--card-foreground);
    }

    .confirm-message {
        font-size: 0.875rem;
        color: var(--muted-foreground);
        margin-top: 0.5rem;
    }

    .confirm-actions {
        display: flex;
        gap: 0.75rem;
        margin-top: 1.5rem;
    }

    .confirm-actions .btn {
        flex: 1;
    }

    .btn-confirm-danger {
        background-color: var(--destructive);
        color: var(--destructive-foreground);
        border-color: var(--destructive);
    }

    .btn-confirm-warning {
        background-color: var(--warning);
        color: var(--warning-foreground);
        border-color: var(--warning);
    }

    .btn-confirm-info {
        background-color: var(--info);
        color: var(--info-foreground);
        border-color: var(--info);
    }

    .btn-loading {
        display: none;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-loading .spinner {
        width: 1rem;
        height: 1rem;
        border: 2px solid currentColor;
        border-top-color: transparent;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }
</style>

<!-- Alert Container -->
<div class="alert-container" id="alertContainer"></div>

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
                <button class="btn" id="confirmCancelBtn" onclick="closeConfirm()">Batal</button>
                <button class="btn" id="confirmOkBtn" onclick="executeConfirm()">
                    <span class="btn-text">Konfirmasi</span>
                    <span class="btn-loading">
                        <span class="spinner"></span>
                        Loading...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Alert Templates -->
<template id="alertTemplate">
    <div class="alert-toast">
        <div class="alert-icon">
            <i data-lucide="check-circle-2"></i>
        </div>
        <div class="alert-content">
            <p class="alert-title"></p>
            <p class="alert-message"></p>
        </div>
        <button class="alert-close" onclick="closeAlert(this.closest('.alert-toast'))">
            <i data-lucide="x"></i>
        </button>
    </div>
</template>

<script>
    // Global Alert Functions
    let confirmCallback = null;
    let confirmCancelCallback = null;

    const alertIcons = {
        success: 'check-circle-2',
        error: 'alert-circle',
        warning: 'alert-triangle',
        info: 'info'
    };

    function showAlert(options) {
        const container = document.getElementById('alertContainer');
        const template = document.getElementById('alertTemplate');
        const clone = template.content.cloneNode(true);
        const alert = clone.querySelector('.alert-toast');
        
        const type = options.type || 'info';
        alert.classList.add(type);
        
        // Set icon
        const icon = alert.querySelector('.alert-icon i');
        icon.setAttribute('data-lucide', alertIcons[type]);
        
        // Set content
        alert.querySelector('.alert-title').textContent = options.title;
        const message = alert.querySelector('.alert-message');
        if (options.message) {
            message.textContent = options.message;
        } else {
            message.remove();
        }
        
        container.appendChild(clone);
        const addedAlert = container.lastElementChild;
        
        // Reinitialize icons
        lucide.createIcons();
        
        // Show animation
        setTimeout(() => {
            addedAlert.classList.add('show');
        }, 10);
        
        // Auto close
        const duration = options.duration !== undefined ? options.duration : (type === 'error' ? 7000 : 5000);
        if (duration > 0) {
            setTimeout(() => {
                closeAlert(addedAlert);
            }, duration);
        }
    }

    function closeAlert(alert) {
        if (!alert) return;
        alert.classList.remove('show');
        setTimeout(() => {
            alert.remove();
        }, 300);
    }

    function showSuccess(title, message) {
        showAlert({ title, message, type: 'success' });
    }

    function showError(title, message) {
        showAlert({ title, message, type: 'error' });
    }

    function showWarning(title, message) {
        showAlert({ title, message, type: 'warning' });
    }

    function showInfo(title, message) {
        showAlert({ title, message, type: 'info' });
    }

    function showConfirm(options) {
        const overlay = document.getElementById('confirmOverlay');
        const icon = document.getElementById('confirmIcon');
        const title = document.getElementById('confirmTitle');
        const message = document.getElementById('confirmMessage');
        const cancelBtn = document.getElementById('confirmCancelBtn');
        const okBtn = document.getElementById('confirmOkBtn');
        
        const type = options.type || 'info';
        
        // Set icon
        icon.className = 'confirm-icon ' + type;
        const iconName = type === 'danger' ? 'alert-circle' : (type === 'warning' ? 'alert-triangle' : 'info');
        icon.innerHTML = `<i data-lucide="${iconName}"></i>`;
        
        // Set content
        title.textContent = options.title;
        message.textContent = options.message || '';
        message.style.display = options.message ? 'block' : 'none';
        
        // Set button text
        cancelBtn.textContent = options.cancelText || 'Batal';
        okBtn.querySelector('.btn-text').textContent = options.confirmText || 'Konfirmasi';
        
        // Set button style
        okBtn.className = 'btn btn-confirm-' + type;
        
        // Store callbacks
        confirmCallback = options.onConfirm || null;
        confirmCancelCallback = options.onCancel || null;
        
        // Show dialog
        overlay.classList.add('active');
        
        // Reinitialize icons
        lucide.createIcons();
    }

    function closeConfirm() {
        const overlay = document.getElementById('confirmOverlay');
        overlay.classList.remove('active');
        
        if (confirmCancelCallback) {
            confirmCancelCallback();
        }
        
        confirmCallback = null;
        confirmCancelCallback = null;
    }

    function closeConfirmOnOverlay(event) {
        if (event.target === event.currentTarget) {
            closeConfirm();
        }
    }

    async function executeConfirm() {
        const okBtn = document.getElementById('confirmOkBtn');
        const btnText = okBtn.querySelector('.btn-text');
        const btnLoading = okBtn.querySelector('.btn-loading');
        
        if (confirmCallback) {
            // Show loading
            btnText.style.display = 'none';
            btnLoading.style.display = 'flex';
            okBtn.disabled = true;
            
            try {
                await confirmCallback();
            } finally {
                // Hide loading
                btnText.style.display = 'inline';
                btnLoading.style.display = 'none';
                okBtn.disabled = false;
            }
        }
        
        closeConfirm();
    }

    // Auto-show session alerts
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
