<div class="modal-overlay" id="createModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3 class="modal-title">Tambah Lori</h3>
            <button class="modal-close-btn" onclick="closeCreateModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="createForm" onsubmit="return false;">
                <div class="form-grid">
                    <div class="form-group full">
                        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="date" class="form-input" required>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Customer <span class="text-danger">*</span></label>
                        <div style="display:flex;gap:0.5rem;align-items:flex-start;">
                            <select id="createCustomerSelect" name="customer_id" class="form-select" style="flex:1;min-width:0;">
                                <option value=""></option>
                            </select>
                            <button type="button" class="btn btn-primary" style="flex-shrink:0;padding:0.5rem 0.65rem;"
                                onclick="openQuickCustomerModal('create')" title="Tambah Customer Baru">
                                <i data-lucide="plus" style="width:16px;height:16px;"></i>
                            </button>
                        </div>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Dari (From) <span class="text-danger">*</span></label>
                        <input type="text" name="from" class="form-input" placeholder="Lokasi asal" required>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Ke (To) <span class="text-danger">*</span></label>
                        <input type="text" name="to" class="form-input" placeholder="Lokasi tujuan" required>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Harga <span class="text-danger">*</span></label>
                        <input type="text" name="price" class="form-input fmt-price" inputmode="numeric"
                            placeholder="Rp 0" required>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-danger" onclick="closeCreateModal()">
                <i data-lucide="x" style="width:15px;height:15px;"></i>
                Batal
            </button>
            <button class="btn btn-primary" data-save-btn onclick="storeLori()">
                <i data-lucide="save" style="width:15px;height:15px;"></i>
                Simpan
            </button>
        </div>
    </div>
</div>
