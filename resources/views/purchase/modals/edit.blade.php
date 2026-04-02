<div class="modal-overlay" id="editModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3 class="modal-title">Edit Pembelian</h3>
            <button class="modal-close-btn" onclick="closeEditModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="editForm" onsubmit="return false;">
                <div class="form-grid">
                    <div class="form-group full">
                        <label class="form-label">Kapal</label>
                        <select name="kapal_id" id="editKapalSelect" class="form-select">
                            <option value="">-- Pilih Kapal --</option>
                        </select>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="date" class="form-input" required>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Vendor <span class="text-danger">*</span></label>
                        <div style="display:flex;gap:0.5rem;align-items:flex-start;">
                            <select id="editVendorSelect" name="vendor" class="form-select" style="flex:1;min-width:0;">
                                <option value=""></option>
                            </select>
                            <button type="button" class="btn btn-primary" style="flex-shrink:0;padding:0.5rem 0.65rem;"
                                onclick="openQuickVendorModal('edit')" title="Tambah Vendor Baru">
                                <i data-lucide="plus" style="width:16px;height:16px;"></i>
                            </button>
                        </div>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Deskripsi</label>
                        <input type="text" name="description" class="form-input" placeholder="Deskripsi pembelian">
                    </div>
                    <div class="form-group full" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;">
                        <div class="form-group full" style="margin:0;">
                            <label class="form-label">Qty (Liter) <span class="text-danger">*</span></label>
                            <input type="text" name="quantity" class="form-input fmt-qty" inputmode="decimal"
                                placeholder="0" required>
                        </div>
                        <div class="form-group full" style="margin:0;">
                            <label class="form-label">Harga/Liter <span class="text-danger">*</span></label>
                            <input type="text" name="price" class="form-input fmt-price" inputmode="numeric"
                                placeholder="Rp 0" required>
                        </div>
                        <div class="form-group full" style="margin:0;">
                            <label class="form-label">Amount (otomatis)</label>
                            <input type="text" id="editAmountDisplay" class="form-input" readonly
                                placeholder="Rp 0" style="background:var(--muted);cursor:not-allowed;">
                        </div>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Noted</label>
                        <textarea name="noted" class="form-textarea" rows="3" placeholder="Catatan tambahan"></textarea>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-danger" onclick="closeEditModal()">
                <i data-lucide="x" style="width:15px;height:15px;"></i>
                Batal
            </button>
            <button class="btn btn-primary" data-save-btn onclick="updatePurchase()">
                <i data-lucide="save" style="width:15px;height:15px;"></i>
                Simpan
            </button>
        </div>
    </div>
</div>