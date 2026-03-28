<div class="modal-overlay" id="resetPasswordModal">
    <div class="modal-box" style="max-width:420px;">
        <div class="modal-header">
            <h3 class="modal-title">Reset Password</h3>
            <button class="modal-close-btn" onclick="closeResetPasswordModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p class="text-muted" style="margin-bottom:1rem;font-size:0.875rem;">
                Password baru akan langsung aktif dan dapat digunakan untuk login.
            </p>
            <form id="resetPasswordForm" onsubmit="return false;">
                <div class="form-group full">
                    <label class="form-label">Password Baru <span class="text-danger">*</span></label>
                    <input type="password" name="password" class="form-input"
                        placeholder="Minimal 6 karakter" required autocomplete="new-password">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-danger" onclick="closeResetPasswordModal()">
                <i data-lucide="x" style="width:15px;height:15px;"></i>
                Batal
            </button>
            <button class="btn btn-warning" data-save-btn onclick="doResetPassword()">
                <i data-lucide="key-round" style="width:15px;height:15px;"></i>
                Reset Password
            </button>
        </div>
    </div>
</div>
