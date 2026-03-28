<div class="modal-overlay" id="editModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3 class="modal-title">Edit Capital</h3>
            <button class="modal-close-btn" onclick="closeEditModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="editForm" onsubmit="return false;">
                <div class="form-grid">
                    <div class="form-group full">
                        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="date" class="form-input" required>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Nama <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-input" placeholder="Nama sumber modal" required>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Nominal <span class="text-danger">*</span></label>
                        <input type="text" name="nominal" class="form-input fmt-price" inputmode="numeric"
                            placeholder="Rp 0" required>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Catatan</label>
                        <textarea name="note" class="form-input" rows="3" placeholder="Catatan tambahan (opsional)"></textarea>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-danger" onclick="closeEditModal()">
                <i data-lucide="x" style="width:15px;height:15px;"></i>
                Batal
            </button>
            <button class="btn btn-primary" data-save-btn onclick="updateCapital()">
                <i data-lucide="save" style="width:15px;height:15px;"></i>
                Simpan
            </button>
        </div>
    </div>
</div>
