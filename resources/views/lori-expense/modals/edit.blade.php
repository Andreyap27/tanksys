<div class="modal-overlay" id="editModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3 class="modal-title">Edit Expense Mobil</h3>
            <button class="modal-close-btn" onclick="closeEditModal()">&times;</button>
        </div>
        <form id="editForm" onsubmit="return false;">
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group full">
                        <label class="form-label">Mobil</label>
                        <select name="mobil_id" id="editLoriExpenseMobilSelect" class="form-select">
                            <option value="">-- Pilih Mobil --</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="date" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Deskripsi</label>
                        <input type="text" name="description" class="form-input" placeholder="Keterangan pengeluaran" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kategori</label>
                        <select name="category" class="form-select" required>
                            <option value="" disabled hidden>-- Pilih Kategori --</option>
                            @foreach(\App\Models\LoriExpense::CATEGORIES as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nominal</label>
                        <input type="text" name="nominal" class="form-input fmt-price" placeholder="0" data-raw="0" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Catatan</label>
                        <textarea name="noted" class="form-input" rows="2" placeholder="Opsional"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" onclick="closeEditModal()">
                    <i data-lucide="x" style="width:15px;height:15px;"></i> Batal
                </button>
                <button type="button" class="btn btn-primary" data-save-btn onclick="updateExpense()">
                    <i data-lucide="save" style="width:15px;height:15px;"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>
