@extends('layouts.app')

@section('title', 'Data Pembelian')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@23.1.0/build/css/intlTelInput.css">
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title-text">Data Pembelian BBM</h1>
        <p class="page-subtitle">Kelola data pembelian bahan bakar</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="openCreateModal()">
            <i data-lucide="plus" style="width:16px;height:16px;"></i>
            Tambah Pembelian
        </button>
    </div>
</div>
<div class="card">
    <div class="card-toolbar"><div class="dt-search-slot"></div></div>
    <div class="card-content" style="padding:0;">
        <div class="table-wrap">
            <table id="purchaseTable" class="w-full">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Vendor</th>
                        <th>Deskripsi</th>
                        <th>Qty (L)</th>
                        <th>Harga/L</th>
                        <th>Amount</th>
                        <th>Noted</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

@include('purchase.modals.create')
@include('purchase.modals.edit')
@include('vendor.modals.create', [
    'modalId'   => 'quickVendorModal',
    'formId'    => 'quickVendorForm',
    'contactId' => 'quickVendorContact',
    'onClose'   => 'closeQuickVendorModal()',
    'onSave'    => 'storeQuickVendor()',
    'hideCode'  => true,
    'modalStyle'=> 'z-index:1100;',
])
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@23.1.0/build/js/intlTelInput.min.js"></script>
<script>
let table;
let editId = null;
let quickVendorContext = 'create';
const createForm       = document.getElementById('createForm');
const editForm         = document.getElementById('editForm');
const quickVendorForm  = document.getElementById('quickVendorForm');
const createModal      = document.getElementById('createModal');
const editModal        = document.getElementById('editModal');
const quickVendorModal = document.getElementById('quickVendorModal');

const itiQuickVendor = window.intlTelInput(document.getElementById('quickVendorContact'), {
    initialCountry: 'id',
    separateDialCode: true,
    utilsScript: 'https://cdn.jsdelivr.net/npm/intl-tel-input@23.1.0/build/js/utils.js',
});

// ── Select2 init ─────────────────────────────────────────────────────────────
function initSelect2() {
    $('#createVendorSelect').select2({
        placeholder: '-- Pilih Vendor --',
        allowClear: true,
        width: '100%',
        dropdownParent: $('#createModal .modal-box'),
    });
    $('#editVendorSelect').select2({
        placeholder: '-- Pilih Vendor --',
        allowClear: true,
        width: '100%',
        dropdownParent: $('#editModal .modal-box'),
    });
}

// ── Load vendor options ──────────────────────────────────────────────────────
function loadVendorOptions(selectedName = '', callback = null) {
    axios.get('{{ route('vendor.list') }}').then(res => {
        const options = res.data.map(v =>
            `<option value="${v.name}">${v.vendor_code} — ${v.name}</option>`
        ).join('');
        const base = '<option value=""></option>';

        document.getElementById('createVendorSelect').innerHTML = base + options;
        document.getElementById('editVendorSelect').innerHTML   = base + options;

        if (selectedName) {
            $('#createVendorSelect').val(selectedName).trigger('change');
            $('#editVendorSelect').val(selectedName).trigger('change');
        } else {
            $('#createVendorSelect').val(null).trigger('change');
            $('#editVendorSelect').val(null).trigger('change');
        }

        if (callback) callback();
    });
}

// ── Input formatters ─────────────────────────────────────────────────────────
function setRaw(el, raw) {
    el.dataset.raw = raw;
}
function getRaw(el) {
    return parseFloat(el.dataset.raw) || 0;
}

// Qty: thousand-separator, allow decimals
document.querySelectorAll('.fmt-qty').forEach(el => {
    el.addEventListener('input', function () {
        let val = this.value.replace(/[^0-9.]/g, '');
        const parts = val.split('.');
        if (parts.length > 2) val = parts[0] + '.' + parts.slice(1).join('');
        this.value = val;
        setRaw(this, val);
        triggerAmountCalc(this);
    });
    el.addEventListener('blur', function () {
        const raw = parseFloat(this.value) || 0;
        setRaw(this, raw);
        if (raw) this.value = raw.toLocaleString('id-ID', { maximumFractionDigits: 3 });
    });
    el.addEventListener('focus', function () {
        this.value = this.dataset.raw || '';
    });
});

// Price: integer rupiah, currency format
document.querySelectorAll('.fmt-price').forEach(el => {
    el.addEventListener('input', function () {
        const raw = this.value.replace(/[^0-9]/g, '');
        this.value = raw;
        setRaw(this, raw);
        triggerAmountCalc(this);
    });
    el.addEventListener('blur', function () {
        const raw = parseInt(this.value.replace(/[^0-9]/g, '')) || 0;
        setRaw(this, raw);
        if (raw) this.value = Currency.format(raw);
        else this.value = '';
    });
    el.addEventListener('focus', function () {
        this.value = this.dataset.raw || '';
    });
});

function triggerAmountCalc(el) {
    const form = el.closest('form');
    if (!form) return;
    if (form.id === 'createForm') calcCreateAmount();
    if (form.id === 'editForm')   calcEditAmount();
}

// ── DataTable ────────────────────────────────────────────────────────────────
$(document).ready(function () {
    initSelect2();

    table = $('#purchaseTable').DataTable({
        ajax: { url: '{{ route('purchase.data') }}', type: 'GET' },
        columns: [
            { data: 'date' },
            { data: 'vendor' },
            {
                data: 'description',
                render: (data) => data ? data : '<span class="text-muted">-</span>'
            },
            { data: 'quantity' },
            {
                data: 'price',
                render: (data) => Currency.symbol + ' ' + data
            },
            {
                data: 'amount',
                render: (data) => Currency.symbol + ' ' + data
            },
            {
                data: 'noted',
                render: (data) => data ? data : '<span class="text-muted">-</span>'
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    return `
                        <div class="table-actions">
                            <button class="icon-btn primary" title="Edit"
                                onclick="openEditModal(
                                    '${row.id}',
                                    '${row.date_raw}',
                                    '${escHtml(row.vendor)}',
                                    '${escHtml(row.description)}',
                                    '${row.quantity_raw}',
                                    '${row.price_raw}',
                                    '${escHtml(row.noted)}'
                                )">
                                <i data-lucide="pencil" style="width:14px;height:14px;"></i>
                            </button>
                            <button class="icon-btn danger" title="Hapus"
                                onclick="deletePurchase('${row.id}', '${escHtml(row.vendor)}')">
                                <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        order: [[0, 'desc']],
        drawCallback: function () { lucide.createIcons(); }
    });
});

function escHtml(str) {
    if (str == null) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/'/g, "\\'").replace(/"/g, '&quot;');
}

// ── Create ──────────────────────────────────────────────────────────────────

function calcCreateAmount() {
    const qty   = getRaw(createForm.quantity);
    const price = getRaw(createForm.price);
    document.getElementById('createAmountDisplay').value = Currency.format(qty * price);
}

function openCreateModal() {
    createForm.reset();
    document.getElementById('createAmountDisplay').value = '';
    loadVendorOptions();
    createModal.classList.add('active');
}

function closeCreateModal() {
    createModal.classList.remove('active');
}

function storePurchase() {
    const vendor = $('#createVendorSelect').val();
    if (!vendor) { showError('Validasi', 'Vendor wajib dipilih.'); return; }

    const payload = {
        date:        createForm.date.value,
        vendor:      vendor,
        description: createForm.description.value,
        quantity:    getRaw(createForm.quantity),
        price:       getRaw(createForm.price),
        noted:       createForm.noted.value,
    };

    axios.post('{{ route('purchase.store') }}', payload)
        .then(res => {
            showSuccess('Berhasil', res.data.message || 'Pembelian berhasil disimpan');
            closeCreateModal();
            table.ajax.reload(null, false);
        })
        .catch(err => {
            const errors = err.response?.data?.errors;
            if (errors) {
                showError('Gagal', Object.values(errors).flat().join('\n'));
            } else {
                showError('Gagal', err.response?.data?.message || 'Terjadi kesalahan');
            }
        });
}

// ── Edit ────────────────────────────────────────────────────────────────────

function calcEditAmount() {
    const qty   = getRaw(editForm.quantity);
    const price = getRaw(editForm.price);
    document.getElementById('editAmountDisplay').value = Currency.format(qty * price);
}

function openEditModal(id, date, vendor, description, quantity, price, noted) {
    editId = id;
    editForm.date.value        = date;
    editForm.description.value = description;
    editForm.noted.value       = noted;

    // Set qty with formatting
    setRaw(editForm.quantity, quantity);
    editForm.quantity.value = parseFloat(quantity)
        ? parseFloat(quantity).toLocaleString('id-ID', { maximumFractionDigits: 3 })
        : '';

    // Set price with formatting
    setRaw(editForm.price, price);
    editForm.price.value = parseInt(price) ? Currency.format(price) : '';

    loadVendorOptions(vendor);
    calcEditAmount();
    editModal.classList.add('active');
}

function closeEditModal() {
    editModal.classList.remove('active');
    editId = null;
}

function updatePurchase() {
    const vendor = $('#editVendorSelect').val();
    if (!vendor) { showError('Validasi', 'Vendor wajib dipilih.'); return; }

    const payload = {
        date:        editForm.date.value,
        vendor:      vendor,
        description: editForm.description.value,
        quantity:    getRaw(editForm.quantity),
        price:       getRaw(editForm.price),
        noted:       editForm.noted.value,
    };

    axios.put(`/purchase/${editId}`, payload)
        .then(res => {
            showSuccess('Berhasil', res.data.message || 'Pembelian berhasil diupdate');
            closeEditModal();
            table.ajax.reload(null, false);
        })
        .catch(err => {
            const errors = err.response?.data?.errors;
            if (errors) {
                showError('Gagal', Object.values(errors).flat().join('\n'));
            } else {
                showError('Gagal', err.response?.data?.message || 'Terjadi kesalahan');
            }
        });
}

// ── Delete ──────────────────────────────────────────────────────────────────

function deletePurchase(id, vendor) {
    showConfirm({
        title: 'Konfirmasi Hapus',
        message: `Yakin ingin menghapus pembelian dari "${vendor}"? Tindakan ini tidak dapat dibatalkan.`,
        type: 'danger',
        confirmText: 'Ya, Hapus',
        onConfirm: async () => {
            try {
                const res = await axios.delete(`/purchase/${id}`);
                showSuccess('Berhasil', res.data.message || 'Data berhasil dihapus');
                table.ajax.reload(null, false);
            } catch (err) {
                showError('Gagal', err.response?.data?.message || 'Gagal menghapus data');
            }
        }
    });
}

// ── Quick Add Vendor ─────────────────────────────────────────────────────────

function openQuickVendorModal(context) {
    quickVendorContext = context;
    quickVendorForm.reset();
    itiQuickVendor.setNumber('');
    quickVendorModal.classList.add('active');
}

function closeQuickVendorModal() {
    quickVendorModal.classList.remove('active');
}

function storeQuickVendor() {
    const payload = {
        name:     quickVendorForm.name.value,
        pic_name: quickVendorForm.pic_name.value,
        contact:  itiQuickVendor.getNumber(),
        address:  quickVendorForm.address.value,
    };

    axios.post('{{ route('vendor.store') }}', payload)
        .then(res => {
            const newName = quickVendorForm.name.value;
            showSuccess('Berhasil', res.data.message || 'Vendor berhasil ditambahkan');
            closeQuickVendorModal();
            // Reload vendor options and auto-select the new vendor in the right modal
            loadVendorOptions(newName, () => {
                if (quickVendorContext === 'create') {
                    $('#createVendorSelect').val(newName).trigger('change');
                } else {
                    $('#editVendorSelect').val(newName).trigger('change');
                }
            });
        })
        .catch(err => {
            const errors = err.response?.data?.errors;
            if (errors) {
                showError('Gagal', Object.values(errors).flat().join('\n'));
            } else {
                showError('Gagal', err.response?.data?.message || 'Terjadi kesalahan');
            }
        });
}
</script>
@endpush
