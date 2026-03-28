<div class="modal-overlay" id="createModal">
    <div class="modal-box" style="max-width:56rem;width:90%;">
        <div class="modal-header">
            <h3 class="modal-title">Tambah Penjualan</h3>
            <button class="modal-close-btn" onclick="closeCreateModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="createForm" onsubmit="return false;">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">No Invoice</label>
                        <input type="text" name="invoice_number" class="form-input" placeholder="Memuat..."
                            readonly style="background:#f5f5f4;cursor:not-allowed;">
                    </div>
                    <div class="form-group">
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
                        <label class="form-label">Deskripsi</label>
                        <input type="text" name="description" class="form-input" placeholder="Deskripsi penjualan">
                    </div>
                    <div class="form-group full" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;">
                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Qty (Liter) <span class="text-danger">*</span></label>
                            <input type="text" name="quantity" class="form-input fmt-qty" inputmode="decimal"
                                placeholder="0" required>
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Harga/Liter <span class="text-danger">*</span></label>
                            <input type="text" name="price" class="form-input fmt-price" inputmode="numeric"
                                placeholder="Rp 0" required>
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Amount (otomatis)</label>
                            <input type="text" id="createAmountDisplay" class="form-input" readonly
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
            <button class="btn" onclick="closeCreateModal()">
                <i data-lucide="x" style="width:15px;height:15px;"></i>
                Batal
            </button>
            <button class="btn btn-primary" onclick="storeSale()">
                <i data-lucide="save" style="width:15px;height:15px;"></i>
                Simpan
            </button>
        </div>
    </div>
</div>
