<div class="modal-overlay" id="editModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3 class="modal-title">Edit Transaksi Bank</h3>
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
                        <label class="form-label">Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            <option value="" disabled hidden selected>-- Pilih Type --</option>
                            <option value="in">In (Kredit)</option>
                            <option value="out">Out (Debit)</option>
                        </select>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Amount <span class="text-danger">*</span></label>
                        <input type="text" name="amount" class="form-input fmt-price" inputmode="numeric"
                            placeholder="Rp 0" required>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Deskripsi <span class="text-danger">*</span></label>
                        <input type="text" name="description" class="form-input" placeholder="Deskripsi transaksi" required>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Note <span class="text-danger">*</span></label>
                        <select name="note" class="form-select" required>
                            <option value="" disabled hidden selected>-- Pilih Note --</option>
                            @foreach(\App\Models\BankTransaction::NOTES as $note)
                                <option value="{{ $note }}">{{ $note }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Job <span class="text-danger">*</span></label>
                        <input type="text" name="job" class="form-input" placeholder="Nama job / pekerjaan" required>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-danger" onclick="closeEditModal()">
                <i data-lucide="x" style="width:15px;height:15px;"></i>
                Batal
            </button>
            <button class="btn btn-primary" onclick="updateTrx()">
                <i data-lucide="save" style="width:15px;height:15px;"></i>
                Simpan
            </button>
        </div>
    </div>
</div>
