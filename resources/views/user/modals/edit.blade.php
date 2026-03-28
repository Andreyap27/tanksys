<div class="modal-overlay" id="editModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3 class="modal-title">Edit User</h3>
            <button class="modal-close-btn" onclick="closeEditModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="editForm" onsubmit="return false;">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">ID Karyawan</label>
                        <input type="text" name="employee_id" class="form-input"
                            readonly style="background:#f5f5f4;cursor:not-allowed;">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-input" placeholder="Nama Lengkap" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jabatan <span class="text-danger">*</span></label>
                        <select name="role" class="form-select" required>
                            <option value="">-- Pilih Jabatan --</option>
                            <option value="Admin">Admin</option>
                            <option value="SPV">SPV</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-input" placeholder="Username" required>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn" onclick="closeEditModal()">
                <i data-lucide="x" style="width:15px;height:15px;"></i>
                Batal
            </button>
            <button class="btn btn-primary" onclick="updateUser()">
                <i data-lucide="save" style="width:15px;height:15px;"></i>
                Simpan
            </button>
        </div>
    </div>
</div>
