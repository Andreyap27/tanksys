<div class="modal-overlay" id="editModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3 class="modal-title">Edit Transaksi Petty Cash</h3>
            <button class="modal-close-btn" onclick="closeEditModal()">&times;</button>
        </div>
        <form id="editForm" onsubmit="return false;">
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group full">
                        <label class="form-label">Petty Cash</label>
                        <select name="petty_cash_id" id="editPettyCashSelect" class="form-select">
                            <option value="">-- Pilih Petty Cash --</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="date" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            <option value="" disabled hidden selected>-- Pilih Type --</option>
                            <option value="in">In (Kredit)</option>
                            <option value="out">Out (Debit)</option>
                        </select>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Deskripsi <span class="text-danger">*</span></label>
                        <input type="text" name="description" class="form-input" placeholder="Keterangan transaksi" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Amount <span class="text-danger">*</span></label>
                        <input type="text" name="amount" class="form-input fmt-price" placeholder="0" data-raw="0" required>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Catatan</label>
                        <textarea name="note" class="form-input" rows="2" placeholder="Opsional"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" onclick="closeEditModal()">
                    <i data-lucide="x" style="width:15px;height:15px;"></i> Batal
                </button>
                <button type="button" class="btn btn-primary" onclick="updateTrx()">
                    <i data-lucide="save" style="width:15px;height:15px;"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>
