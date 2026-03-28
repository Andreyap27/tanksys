<div class="modal-overlay" id="{{ $modalId ?? 'createModal' }}" {{ isset($modalStyle) ? "style=\"{$modalStyle}\"" : '' }}>
    <div class="modal-box" {{ isset($boxStyle) ? "style=\"{$boxStyle}\"" : '' }}>
        <div class="modal-header">
            <h3 class="modal-title">Tambah Customer</h3>
            <button class="modal-close-btn" onclick="{{ $onClose ?? 'closeCreateModal()' }}">&times;</button>
        </div>
        <div class="modal-body">
            <form id="{{ $formId ?? 'createForm' }}" onsubmit="return false;">
                <div class="form-grid">
                    @if(!isset($hideCode) || !$hideCode)
                    <div class="form-group">
                        <label class="form-label">ID Customer</label>
                        <input type="text" name="customer_id" class="form-input" placeholder="Memuat..."
                            readonly style="background:#f5f5f4;cursor:not-allowed;">
                    </div>
                    @endif
                    <div class="form-group {{ isset($hideCode) && $hideCode ? 'full' : '' }}">
                        <label class="form-label">Nama Perusahaan / Perorangan <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-input" placeholder="Nama customer" required>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" class="form-textarea" rows="3" placeholder="Alamat lengkap"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nama PIC</label>
                        <input type="text" name="pic_name" class="form-input" placeholder="Nama PIC">
                    </div>
                    <div class="form-group">
                        <label class="form-label">No Contact</label>
                        <input type="tel" name="contact" id="{{ $contactId ?? 'createContact' }}" class="form-input" placeholder="8xxxxxxxxxx">
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn" onclick="{{ $onClose ?? 'closeCreateModal()' }}">
                <i data-lucide="x" style="width:15px;height:15px;"></i>
                Batal
            </button>
            <button class="btn btn-primary" onclick="{{ $onSave ?? 'storeCustomer()' }}">
                <i data-lucide="save" style="width:15px;height:15px;"></i>
                Simpan
            </button>
        </div>
    </div>
</div>
