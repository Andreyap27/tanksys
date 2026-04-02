<div class="modal-overlay" id="createModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3 class="modal-title">Tambah Pengeluaran</h3>
            <button class="modal-close-btn" onclick="closeCreateModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="createForm" onsubmit="return false;">
                <div class="form-grid">
                    <div class="form-group full">
                        <label class="form-label">Kapal</label>
                        <select name="kapal_id" id="createExpenseKapalSelect" class="form-select">
                            <option value="">-- Pilih Kapal --</option>
                        </select>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="date" class="form-input" required>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select name="category" class="form-select" required>
                            <option value="" disabled hidden selected>-- Pilih Kategori --</option>
                            @foreach(\App\Models\Expense::CATEGORIES as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Deskripsi <span class="text-danger">*</span></label>
                        <input type="text" name="description" class="form-input" placeholder="Deskripsi pengeluaran" required>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Nominal <span class="text-danger">*</span></label>
                        <input type="text" name="nominal" class="form-input fmt-price" inputmode="numeric"
                            placeholder="Rp 0" required>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Noted</label>
                        <textarea name="noted" class="form-textarea" rows="3" placeholder="Catatan tambahan"></textarea>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-danger" onclick="closeCreateModal()">
                <i data-lucide="x" style="width:15px;height:15px;"></i>
                Batal
            </button>
            <button class="btn btn-primary" data-save-btn onclick="storeExpense()">
                <i data-lucide="save" style="width:15px;height:15px;"></i>
                Simpan
            </button>
        </div>
    </div>
</div>
