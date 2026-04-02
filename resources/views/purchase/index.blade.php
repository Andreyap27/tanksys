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
        @if($canManage)
        <button class="btn btn-primary" onclick="openCreateModal()">
            <i data-lucide="plus" style="width:16px;height:16px;"></i>
            Tambah Pembelian
        </button>
        @endif
    </div>
</div>

{{-- Summary Cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1.25rem;margin-bottom:1.5rem;">
    <div class="dash-stat ds-purchase">
        <div class="dash-stat__header">
            <div class="dash-stat__icon"><i data-lucide="wallet" style="width:20px;height:20px;"></i></div>
            <div>
                <div class="dash-stat__label">Total Amount (Approved)</div>
                <div class="dash-stat__value" id="purchaseTotalAmount">Rp 0</div>
            </div>
        </div>
        <div class="dash-stat__bg-icon"><i data-lucide="wallet" style="width:110px;height:110px;"></i></div>
    </div>
    <div class="dash-stat ds-purchase">
        <div class="dash-stat__header">
            <div class="dash-stat__icon"><i data-lucide="fuel" style="width:20px;height:20px;"></i></div>
            <div>
                <div class="dash-stat__label">Total Qty (Approved)</div>
                <div class="dash-stat__value" id="purchaseTotalQty">0 L</div>
            </div>
        </div>
        <div class="dash-stat__bg-icon"><i data-lucide="fuel" style="width:110px;height:110px;"></i></div>
    </div>
</div>

{{-- Kapal Tabs --}}
<div class="tab-bar" id="purchaseTabs">
    <button class="tab active" data-kapal-id="" onclick="switchTab(this, '')"><i data-lucide="list" style="width:16px;height:16px;"></i> Semua</button>
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
                        <th>Status</th>
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
// Initialize Lucide icons for page load
document.addEventListener('DOMContentLoaded', () => {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});

const canApprove = @json($canApprove);
const canManage  = @json($canManage);
const canDelete  = @json($canDelete);
let table;
let editId = null;
let activeKapalId = '';
let activePurchaseMobilId = ''; // Removed from usage
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

// ── Kapal tabs ───────────────────────────────────────────────────────────────
let kapalList = [];
function loadKapals() {
    axios.get('{{ route('kapal.list') }}').then(res => {
        kapalList = res.data;
        const tabBar = document.getElementById('purchaseTabs');
        kapalList.forEach(k => {
            const btn = document.createElement('button');
            btn.className = 'tab';
            btn.dataset.kapalId = k.id;
            btn.innerHTML = `<i data-lucide="ship" style="width:16px;height:16px;"></i> ${k.name}`;
            btn.onclick = function() { switchTab(this, k.id); };
            tabBar.appendChild(btn);
        });
        lucide.createIcons();
        const opts = kapalList.map(k => `<option value="${k.id}">${k.code} — ${k.name}</option>`).join('');
        document.getElementById('createKapalSelect').innerHTML = '<option value="">-- Pilih Kapal --</option>' + opts;
        document.getElementById('editKapalSelect').innerHTML   = '<option value="">-- Pilih Kapal --</option>' + opts;
    });
}

function switchTab(btn, kapalId) {
    document.querySelectorAll('#purchaseTabs .tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    activeKapalId = kapalId;
    table.ajax.reload(null, false);
}

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

// ── Summary card ─────────────────────────────────────────────────────────────
function updatePurchaseSummary(api) {
    const rows = api.rows({ search: 'applied' }).data();
    let totalAmount = 0, totalQty = 0;
    for (let i = 0; i < rows.length; i++) {
        if (rows[i].status === 'approved') {
            totalAmount += parseFloat(rows[i].amount_raw) || 0;
            totalQty    += parseFloat(rows[i].quantity_raw) || 0;
        }
    }
    document.getElementById('purchaseTotalAmount').textContent = 'Rp ' + Currency.number(totalAmount);
    document.getElementById('purchaseTotalQty').textContent    = Currency.number(totalQty) + ' L';
}

// ── DataTable ────────────────────────────────────────────────────────────────
$(document).ready(function () {
    initSelect2();
    loadKapals();

    table = $('#purchaseTable').DataTable({
        ajax: {
            url: '{{ route('purchase.data') }}',
            type: 'GET',
            data: function(d) {
                if (activeKapalId) d.kapal_id = activeKapalId;
            }
        },
        processing: true,
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
                data: 'status',
                render: function(data) {
                    const map = {
                        pending:  '<span class="badge badge-warning">Pending</span>',
                        approved: '<span class="badge badge-success">Approved</span>',
                        rejected: '<span class="badge badge-danger">Rejected</span>',
                    };
                    return map[data] || data;
                }
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
                    let actions = '';

                    if (canManage) {
                        actions += `
                            <button class="icon-btn primary" title="Edit"
                                onclick="openEditModal(
                                    '${row.id}',
                                    '${row.date_raw}',
                                    '${escHtml(row.vendor)}',
                                    '${escHtml(row.description)}',
                                    '${row.quantity_raw}',
                                    '${row.price_raw}',
                                    '${escHtml(row.noted)}',
                                    '${row.kapal_id || ''}'
                                )">
                                <i data-lucide="pencil" style="width:14px;height:14px;"></i>
                            </button>`;
                    }

                    if (canApprove && row.status === 'pending') {
                        actions += `
                            <button class="icon-btn success" title="Setujui"
                                onclick="approvePurchase('${row.id}', '${escHtml(row.vendor)}')">
                                <i data-lucide="check" style="width:14px;height:14px;"></i>
                            </button>
                            <button class="icon-btn warning" title="Tolak"
                                onclick="rejectPurchase('${row.id}', '${escHtml(row.vendor)}')">
                                <i data-lucide="x" style="width:14px;height:14px;"></i>
                            </button>`;
                    }

                    if (canDelete) {
                        actions += `
                        <button class="icon-btn danger" title="Hapus"
                            onclick="deletePurchase('${row.id}', '${escHtml(row.vendor)}')">
                            <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                        </button>`;
                    }

                    return `<div class="table-actions">${actions}</div>`;
                }
            }
        ],
        order: [[0, 'desc']],
        drawCallback: function () {
            lucide.createIcons();
            updatePurchaseSummary(this.api());
        }
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
    if (activeKapalId) document.getElementById('createKapalSelect').value = activeKapalId;
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
        kapal_id:    document.getElementById('createKapalSelect').value || null,
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

function openEditModal(id, date, vendor, description, quantity, price, noted, kapalId) {
    editId = id;
    editForm.date.value        = date;
    editForm.description.value = description;
    editForm.noted.value       = noted;
    document.getElementById('editKapalSelect').value = kapalId || '';

    setRaw(editForm.quantity, quantity);
    editForm.quantity.value = parseFloat(quantity)
        ? parseFloat(quantity).toLocaleString('id-ID', { maximumFractionDigits: 3 })
        : '';

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
        kapal_id:    document.getElementById('editKapalSelect').value || null,
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

// ── Approve / Reject ─────────────────────────────────────────────────────────

function approvePurchase(id, vendor) {
    showConfirm({
        title: 'Konfirmasi Persetujuan',
        message: `Setujui pembelian dari "${vendor}"? Data akan masuk ke perhitungan stok dan laporan.`,
        type: 'warning',
        confirmText: 'Ya, Setujui',
        onConfirm: async () => {
            try {
                const res = await axios.post(`/purchase/${id}/approve`);
                showSuccess('Disetujui', res.data.message);
                table.ajax.reload(null, false);
            } catch (err) {
                showError('Gagal', err.response?.data?.message || 'Gagal menyetujui data');
            }
        }
    });
}

function rejectPurchase(id, vendor) {
    showConfirm({
        title: 'Konfirmasi Penolakan',
        message: `Tolak pembelian dari "${vendor}"?`,
        type: 'danger',
        confirmText: 'Ya, Tolak',
        onConfirm: async () => {
            try {
                const res = await axios.post(`/purchase/${id}/reject`);
                showSuccess('Ditolak', res.data.message);
                table.ajax.reload(null, false);
            } catch (err) {
                showError('Gagal', err.response?.data?.message || 'Gagal menolak data');
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
