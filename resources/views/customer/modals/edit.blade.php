<div class="modal-overlay" id="editModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3 class="modal-title">Edit Customer</h3>
            <button class="modal-close-btn" onclick="closeEditModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="editForm" onsubmit="return false;">
                <div class="form-grid">
                    <div class="form-group full">
                        <label class="form-label">ID Customer</label>
                        <input type="text" name="customer_id" class="form-input"
                            readonly style="background:#f5f5f4;cursor:not-allowed;">
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Nama Perusahaan / Perorangan <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-input" placeholder="Nama customer" required>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" class="form-textarea" rows="3" placeholder="Alamat lengkap"></textarea>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Nama PIC</label>
                        <input type="text" name="pic_name" class="form-input" placeholder="Nama PIC">
                    </div>
                    <div class="form-group full">
                        <label class="form-label">No Contact</label>
                        <input type="tel" name="contact" id="editContact" class="form-input" placeholder="8xxxxxxxxxx">
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-danger" onclick="closeEditModal()">
                <i data-lucide="x" style="width:15px;height:15px;"></i>
                Batal
            </button>
            <button class="btn btn-primary" data-save-btn onclick="updateCustomer()">
                <i data-lucide="save" style="width:15px;height:15px;"></i>
                Simpan
            </button>
        </div>
    </div>
</div>
