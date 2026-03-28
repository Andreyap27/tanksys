<div class="modal-overlay" id="editModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3 class="modal-title">Edit Vendor</h3>
            <button class="modal-close-btn" onclick="closeEditModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="editForm" onsubmit="return false;">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Kode Vendor</label>
                        <input type="text" name="vendor_code" class="form-input"
                            readonly style="background:#f5f5f4;cursor:not-allowed;">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nama Vendor <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-input" placeholder="Nama vendor" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nama PIC</label>
                        <input type="text" name="pic_name" class="form-input" placeholder="Nama penanggung jawab">
                    </div>
                    <div class="form-group">
                        <label class="form-label">No Contact</label>
                        <input type="tel" id="editContact" name="contact" class="form-input" placeholder="08xxxxxxxxxx">
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" class="form-input" placeholder="Alamat vendor" rows="3"></textarea>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn" onclick="closeEditModal()">
                <i data-lucide="x" style="width:15px;height:15px;"></i>
                Batal
            </button>
            <button class="btn btn-primary" onclick="updateVendor()">
                <i data-lucide="save" style="width:15px;height:15px;"></i>
                Simpan
            </button>
        </div>
    </div>
</div>
