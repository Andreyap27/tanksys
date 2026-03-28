<div class="modal-overlay" id="{{ $modalId ?? 'createModal' }}" {{ isset($modalStyle) ? "style=\"{$modalStyle}\"" : '' }}>
    <div class="modal-box" {{ isset($boxStyle) ? "style=\"{$boxStyle}\"" : '' }}>
        <div class="modal-header">
            <h3 class="modal-title">Tambah Vendor</h3>
            <button class="modal-close-btn" onclick="{{ $onClose ?? 'closeCreateModal()' }}">&times;</button>
        </div>
        <div class="modal-body">
            <form id="{{ $formId ?? 'createForm' }}" onsubmit="return false;">
                <div class="form-grid">
                    @if(!isset($hideCode) || !$hideCode)
                    <div class="form-group full">
                        <label class="form-label">Kode Vendor</label>
                        <input type="text" name="vendor_code" class="form-input" placeholder="Memuat..."
                            readonly style="background:#f5f5f4;cursor:not-allowed;">
                    </div>
                    @endif
                    <div class="form-group full">
                        <label class="form-label">Nama Vendor <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-input" placeholder="Nama vendor" required>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Nama PIC</label>
                        <input type="text" name="pic_name" class="form-input" placeholder="Nama penanggung jawab">
                    </div>
                    <div class="form-group full">
                        <label class="form-label">No Contact</label>
                        <input type="tel" id="{{ $contactId ?? 'createContact' }}" name="contact" class="form-input" placeholder="08xxxxxxxxxx">
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" class="form-input" placeholder="Alamat vendor" rows="3"></textarea>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn" onclick="{{ $onClose ?? 'closeCreateModal()' }}">
                <i data-lucide="x" style="width:15px;height:15px;"></i>
                Batal
            </button>
            <button class="btn btn-primary" data-save-btn onclick="{{ $onSave ?? 'storeVendor()' }}">
                <i data-lucide="save" style="width:15px;height:15px;"></i>
                Simpan
            </button>
        </div>
    </div>
</div>
