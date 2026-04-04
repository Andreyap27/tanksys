@extends('layouts.app')

@section('title', 'Data Penjualan')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@23.1.0/build/css/intlTelInput.css">
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title-text">Data Penjualan BBM</h1>
        <p class="page-subtitle">Kelola data penjualan bahan bakar</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('sales.trash') }}" class="btn btn-secondary">
            <i data-lucide="trash-2" style="width:16px;height:16px;"></i>
            Trash
        </a>
        @if(auth()->user()->canManage())
        <button class="btn btn-primary" onclick="openCreateModal()">
            <i data-lucide="plus" style="width:16px;height:16px;"></i>
            Tambah Penjualan
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
                <div class="dash-stat__value" id="salesTotalAmount">Rp 0</div>
            </div>
        </div>
        <div class="dash-stat__bg-icon"><i data-lucide="wallet" style="width:110px;height:110px;"></i></div>
    </div>
    <div class="dash-stat ds-purchase">
        <div class="dash-stat__header">
            <div class="dash-stat__icon"><i data-lucide="fuel" style="width:20px;height:20px;"></i></div>
            <div>
                <div class="dash-stat__label">Total Qty (Approved)</div>
                <div class="dash-stat__value" id="salesTotalQty">0 L</div>
            </div>
        </div>
        <div class="dash-stat__bg-icon"><i data-lucide="fuel" style="width:110px;height:110px;"></i></div>
    </div>
</div>

{{-- Kapal Tabs --}}
<div class="tab-bar" id="salesTabs">
    <button class="tab active" data-kapal-id="" onclick="switchSaleTab(this, '')"><i data-lucide="list" style="width:16px;height:16px;"></i> Semua</button>
</div>

<div class="card">
    <div class="card-toolbar"><div class="dt-search-slot"></div></div>
    <div class="card-content" style="padding:0;">
        <div class="table-wrap">
            <table id="salesTable" class="w-full">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>No Invoice</th>
                        <th>Customer</th>
                        <th>Deskripsi</th>
                        <th>Qty (L)</th>
                        <th>Harga/L</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

@include('sales.modals.create')
@include('sales.modals.edit')

{{-- Invoice Preview Modal --}}
<div class="modal-overlay" id="invoiceModal">
    <div class="modal-box" style="max-width:860px;width:95%;height:90vh;display:flex;flex-direction:column;">
        <div class="modal-header" style="flex-shrink:0;">
            <h3 class="modal-title">Preview Invoice</h3>
            <button class="modal-close-btn" onclick="closeInvoiceModal()">&times;</button>
        </div>
        <div style="flex:1;overflow:hidden;padding:0;">
            <iframe id="invoiceFrame" src="" style="width:100%;height:100%;border:none;display:block;"></iframe>
        </div>
        <div class="modal-footer" style="flex-shrink:0;">
            <button class="btn" onclick="closeInvoiceModal()">
                <i data-lucide="x" style="width:15px;height:15px;"></i>
                Tutup
            </button>
            <button class="btn btn-primary" onclick="printInvoice()">
                <i data-lucide="printer" style="width:15px;height:15px;"></i>
                Cetak
            </button>
        </div>
    </div>
</div>

@include('customer.modals.create', [
    'modalId'   => 'quickCustomerModal',
    'formId'    => 'quickCustomerForm',
    'contactId' => 'quickCustomerContact',
    'onClose'   => 'closeQuickCustomerModal()',
    'onSave'    => 'storeQuickCustomer()',
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

let table;
let editId = null;
let activeSaleKapalId = '';
let quickCustomerContext = 'create';
const createForm         = document.getElementById('createForm');
const editForm           = document.getElementById('editForm');
const quickCustomerForm  = document.getElementById('quickCustomerForm');
const createModal        = document.getElementById('createModal');
const editModal          = document.getElementById('editModal');
const quickCustomerModal = document.getElementById('quickCustomerModal');

const itiQuickCustomer = window.intlTelInput(document.getElementById('quickCustomerContact'), {
    initialCountry: 'id',
    separateDialCode: true,
    utilsScript: 'https://cdn.jsdelivr.net/npm/intl-tel-input@23.1.0/build/js/utils.js',
});

// ── Kapal tabs ───────────────────────────────────────────────────────────────
let saleKapalList = [];
function loadSaleKapals() {
    axios.get('{{ route('kapal.list') }}').then(res => {
        saleKapalList = res.data;
        const tabBar = document.getElementById('salesTabs');
        const opts = res.data.map(k => `<option value="${k.id}">${k.code} — ${k.name}</option>`).join('');
        saleKapalList.forEach(k => {
            const btn = document.createElement('button');
            btn.className = 'tab';
            btn.dataset.kapalId = k.id;
            btn.innerHTML = `<i data-lucide="ship" style="width:16px;height:16px;"></i> ${k.name}`;
            btn.onclick = function() { switchSaleTab(this, k.id); };
            tabBar.appendChild(btn);
        });
        lucide.createIcons();
        document.getElementById('createSaleKapalSelect').innerHTML = '<option value="">-- Pilih Kapal --</option>' + opts;
        document.getElementById('editSaleKapalSelect').innerHTML   = '<option value="">-- Pilih Kapal --</option>' + opts;
    });
}

function switchSaleTab(btn, kapalId) {
    document.querySelectorAll('#salesTabs .tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    activeSaleKapalId = kapalId;
    table.ajax.reload(null, false);
}

// ── Mobil tabs ───────────────────────────────────────────────────────────────
function updateSalesSummary(api) {
    const rows = api.rows({ search: 'applied' }).data();
    let totalAmount = 0, totalQty = 0;
    for (let i = 0; i < rows.length; i++) {
        if (rows[i].status === 'approved') {
            totalAmount += parseFloat(rows[i].amount_raw) || 0;
            totalQty    += parseFloat(rows[i].quantity_raw) || 0;
        }
    }
    document.getElementById('salesTotalAmount').textContent = 'Rp ' + Currency.number(totalAmount);
    document.getElementById('salesTotalQty').textContent    = Currency.number(totalQty) + ' L';
}

// When kapal changes in create modal, reload invoice number
function onCreateSaleKapalChange() {
    const kapalId = document.getElementById('createSaleKapalSelect').value;
    const invInput = createForm.invoice_number;
    invInput.value = '';
    invInput.placeholder = 'Memuat...';
    const params = kapalId ? { kapal_id: kapalId } : {};
    axios.get('{{ route('sales.next-invoice') }}', { params })
        .then(res => { invInput.value = res.data.invoice_number; invInput.placeholder = ''; })
        .catch(() => { invInput.placeholder = 'Gagal memuat nomor'; });
}

// ── Select2 init ─────────────────────────────────────────────────────────────
function initSelect2() {
    $('#createCustomerSelect').select2({
        placeholder: '-- Pilih Customer --',
        allowClear: true,
        width: '100%',
        dropdownParent: $('#createModal .modal-box'),
    });
    $('#editCustomerSelect').select2({
        placeholder: '-- Pilih Customer --',
        allowClear: true,
        width: '100%',
        dropdownParent: $('#editModal .modal-box'),
    });
}

// ── Load customer options ─────────────────────────────────────────────────────
function loadCustomerOptions(selectedId = '', callback = null) {
    axios.get('{{ route('customer.data') }}').then(res => {
        const customers = res.data.data || res.data;
        const options = customers.map(c =>
            `<option value="${c.id}">${c.customer_id ? c.customer_id + ' — ' : ''}${c.name}</option>`
        ).join('');

        document.getElementById('createCustomerSelect').innerHTML = '<option value=""></option>' + options;
        document.getElementById('editCustomerSelect').innerHTML   = '<option value=""></option>' + options;

        if (selectedId) {
            $('#createCustomerSelect').val(selectedId).trigger('change');
            $('#editCustomerSelect').val(selectedId).trigger('change');
        } else {
            $('#createCustomerSelect').val(null).trigger('change');
            $('#editCustomerSelect').val(null).trigger('change');
        }

        if (callback) callback();
    });
}

// ── Input formatters ─────────────────────────────────────────────────────────
function setRaw(el, raw) { el.dataset.raw = raw; }
function getRaw(el)      { return parseFloat(el.dataset.raw) || 0; }

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

// ── DataTable ────────────────────────────────────────────────────────────────
$(document).ready(function () {
    initSelect2();
    loadSaleKapals();

    table = $('#salesTable').DataTable({
        ajax: {
            url: '{{ route('sales.data') }}',
            type: 'GET',
            data: function(d) {
                if (activeSaleKapalId) d.kapal_id = activeSaleKapalId;
            }
        },
        processing: true,
        columns: [
            { data: 'date' },
            { data: 'invoice_number' },
            { data: 'customer_name' },
            {
                data: 'description',
                render: (data) => data && data !== '-' ? data : '<span class="text-muted">-</span>'
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
                data: null,
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    let actions = '';

                    if (canManage) {
                        actions += `
                            <button class="icon-btn primary" title="Edit"
                                onclick="openEditModal('${row.id}', '${row.date_raw}', '${escHtml(row.invoice_number)}', '${escHtml(row.description)}', '${row.quantity_raw}', '${row.price_raw}', '${escHtml(row.noted)}', '${row.kapal_id || ''}')">
                                <i data-lucide="pencil" style="width:14px;height:14px;"></i>
                            </button>`;
                    }

                    if (canApprove && row.status === 'pending') {
                        actions += `
                            <button class="icon-btn success" title="Setujui"
                                onclick="approveSale('${row.id}', '${escHtml(row.invoice_number)}')">
                                <i data-lucide="check" style="width:14px;height:14px;"></i>
                            </button>
                            <button class="icon-btn warning" title="Tolak"
                                onclick="rejectSale('${row.id}', '${escHtml(row.invoice_number)}')">
                                <i data-lucide="x" style="width:14px;height:14px;"></i>
                            </button>`;
                    }

                    if (canDelete) {
                        actions += `
                        <button class="icon-btn danger" title="Hapus"
                            onclick="deleteSale('${row.id}', '${escHtml(row.invoice_number)}')">
                            <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                        </button>`;
                    }

                    actions += `
                        <button class="icon-btn" title="Invoice"
                            onclick="openInvoiceModal('/sales/${row.id}/invoice')">
                            <i data-lucide="file-text" style="width:14px;height:14px;"></i>
                        </button>`;

                    return `<div class="table-actions">${actions}</div>`;
                }
            }
        ],
        order: [[0, 'desc']],
        drawCallback: function () {
            lucide.createIcons();
            updateSalesSummary(this.api());
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
    createForm.invoice_number.value = '';
    createForm.invoice_number.placeholder = 'Memuat...';
    const kapalSel = document.getElementById('createSaleKapalSelect');
    if (activeSaleKapalId) { kapalSel.value = activeSaleKapalId; }
    loadCustomerOptions();
    createModal.classList.add('active');

    const params = activeSaleKapalId ? { kapal_id: activeSaleKapalId } : {};
    axios.get('{{ route('sales.next-invoice') }}', { params })
        .then(res => {
            createForm.invoice_number.value       = res.data.invoice_number;
            createForm.invoice_number.placeholder = '';
        })
        .catch(() => { createForm.invoice_number.placeholder = 'Gagal memuat nomor'; });
}

function closeCreateModal() {
    createModal.classList.remove('active');
}

function storeSale() {
    const customer = $('#createCustomerSelect').val();
    if (!customer) { showError('Validasi', 'Customer wajib dipilih.'); return; }

    const payload = {
        kapal_id:       document.getElementById('createSaleKapalSelect').value || null,
        date:           createForm.date.value,
        invoice_number: createForm.invoice_number.value,
        customer_id:    customer,
        description:    createForm.description.value,
        quantity:       getRaw(createForm.quantity),
        price:          getRaw(createForm.price),
        noted:          createForm.noted.value,
    };

    axios.post('{{ route('sales.store') }}', payload)
        .then(res => {
            showSuccess('Berhasil', res.data.message || 'Penjualan berhasil disimpan');
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

function openEditModal(id, date, invoice_number, customer_id, description, quantity, price, noted, kapalId) {
    editId = id;
    editForm.invoice_number.value = invoice_number;
    editForm.date.value           = date;
    editForm.description.value    = description !== '-' ? description : '';
    editForm.noted.value          = noted !== '-' ? noted : '';
    document.getElementById('editSaleKapalSelect').value = kapalId || '';

    setRaw(editForm.quantity, quantity);
    editForm.quantity.value = parseFloat(quantity)
        ? parseFloat(quantity).toLocaleString('id-ID', { maximumFractionDigits: 3 })
        : '';

    setRaw(editForm.price, price);
    editForm.price.value = parseInt(price) ? Currency.format(price) : '';

    loadCustomerOptions(customer_id);
    calcEditAmount();
    editModal.classList.add('active');
}

function closeEditModal() {
    editModal.classList.remove('active');
    editId = null;
}

function updateSale() {
    const customer = $('#editCustomerSelect').val();
    if (!customer) { showError('Validasi', 'Customer wajib dipilih.'); return; }

    const payload = {
        kapal_id:       document.getElementById('editSaleKapalSelect').value || null,
        date:           editForm.date.value,
        invoice_number: editForm.invoice_number.value,
        customer_id:    customer,
        description:    editForm.description.value,
        quantity:       getRaw(editForm.quantity),
        price:          getRaw(editForm.price),
        noted:          editForm.noted.value,
    };

    axios.put(`/sales/${editId}`, payload)
        .then(res => {
            showSuccess('Berhasil', res.data.message || 'Penjualan berhasil diupdate');
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

function deleteSale(id, invoice) {
    showConfirm({
        title: 'Konfirmasi Hapus',
        message: `Yakin ingin menghapus penjualan "${invoice}"? Tindakan ini tidak dapat dibatalkan.`,
        type: 'danger',
        confirmText: 'Ya, Hapus',
        onConfirm: async () => {
            try {
                const res = await axios.delete(`/sales/${id}`);
                showSuccess('Berhasil', res.data.message || 'Data berhasil dihapus');
                table.ajax.reload(null, false);
            } catch (err) {
                showError('Gagal', err.response?.data?.message || 'Gagal menghapus data');
            }
        }
    });
}

// ── Approve / Reject ─────────────────────────────────────────────────────────

function approveSale(id, invoice) {
    showConfirm({
        title: 'Konfirmasi Persetujuan',
        message: `Setujui penjualan "${invoice}"? Data akan masuk ke perhitungan stok dan laporan.`,
        type: 'warning',
        confirmText: 'Ya, Setujui',
        onConfirm: async () => {
            try {
                const res = await axios.post(`/sales/${id}/approve`);
                showSuccess('Disetujui', res.data.message);
                table.ajax.reload(null, false);
            } catch (err) {
                showError('Gagal', err.response?.data?.message || 'Gagal menyetujui data');
            }
        }
    });
}

function rejectSale(id, invoice) {
    showConfirm({
        title: 'Konfirmasi Penolakan',
        message: `Tolak penjualan "${invoice}"?`,
        type: 'danger',
        confirmText: 'Ya, Tolak',
        onConfirm: async () => {
            try {
                const res = await axios.post(`/sales/${id}/reject`);
                showSuccess('Ditolak', res.data.message);
                table.ajax.reload(null, false);
            } catch (err) {
                showError('Gagal', err.response?.data?.message || 'Gagal menolak data');
            }
        }
    });
}

// ── Invoice Modal ────────────────────────────────────────────────────────────

function openInvoiceModal(url) {
    document.getElementById('invoiceFrame').src = url;
    document.getElementById('invoiceModal').classList.add('active');
}

function closeInvoiceModal() {
    document.getElementById('invoiceModal').classList.remove('active');
    document.getElementById('invoiceFrame').src = '';
}

function printInvoice() {
    document.getElementById('invoiceFrame').contentWindow.print();
}

// ── Quick Add Customer ───────────────────────────────────────────────────────

function openQuickCustomerModal(context) {
    quickCustomerContext = context;
    quickCustomerForm.reset();
    itiQuickCustomer.setNumber('');
    quickCustomerModal.classList.add('active');
}

function closeQuickCustomerModal() {
    quickCustomerModal.classList.remove('active');
}

function storeQuickCustomer() {
    const payload = {
        name:     quickCustomerForm.name.value,
        address:  quickCustomerForm.address.value,
        pic_name: quickCustomerForm.pic_name.value,
        contact:  itiQuickCustomer.getNumber(),
    };

    axios.post('{{ route('customer.store') }}', payload)
        .then(res => {
            const newId = res.data.id;
            showSuccess('Berhasil', res.data.message || 'Customer berhasil ditambahkan');
            closeQuickCustomerModal();
            loadCustomerOptions(newId, () => {
                if (quickCustomerContext === 'create') {
                    $('#createCustomerSelect').val(newId).trigger('change');
                } else {
                    $('#editCustomerSelect').val(newId).trigger('change');
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
