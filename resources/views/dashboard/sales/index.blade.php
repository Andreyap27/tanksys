@extends('layouts.app')

@section('title', 'Data Penjualan')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Data Penjualan BBM</h2>
        <button class="btn btn-primary" onclick="openModal()">
            <i data-lucide="plus" style="width:16px;height:16px;"></i>
            Tambah Penjualan
        </button>
    </div>
    <div class="card-content">
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
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal-overlay" id="salesModal" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Tambah Penjualan</h3>
            <button class="modal-close-btn" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                    <input type="date" id="date" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">No Invoice <span class="text-danger">*</span></label>
                    <input type="text" id="invoice_number" class="form-input" placeholder="INV-XXXXX" required>
                </div>
                <div class="form-group full">
                    <label class="form-label">Customer <span class="text-danger">*</span></label>
                    <select id="customer_id" class="form-select" required>
                        <option value="">-- Memuat data customer... --</option>
                    </select>
                </div>
                <div class="form-group full">
                    <label class="form-label">Deskripsi</label>
                    <input type="text" id="description" class="form-input" placeholder="Deskripsi penjualan">
                </div>
                <div class="form-group">
                    <label class="form-label">Qty (Liter) <span class="text-danger">*</span></label>
                    <input type="number" id="quantity" class="form-input" placeholder="0" min="0" step="0.01" required oninput="calcAmount()">
                </div>
                <div class="form-group">
                    <label class="form-label">Harga/Liter <span class="text-danger">*</span></label>
                    <input type="number" id="price" class="form-input" placeholder="0" min="0" step="0.01" required oninput="calcAmount()">
                </div>
                <div class="form-group full">
                    <label class="form-label">Amount (otomatis)</label>
                    <input type="text" id="amount_display" class="form-input" readonly placeholder="Rp 0" style="background:var(--muted,#f3f4f6);cursor:not-allowed;">
                </div>
                <div class="form-group full">
                    <label class="form-label">Noted</label>
                    <textarea id="noted" class="form-textarea" rows="3" placeholder="Catatan tambahan"></textarea>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn" onclick="closeModal()">Batal</button>
            <button class="btn btn-primary" onclick="saveSale()">Simpan</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let table;
let editId = null;

$(document).ready(function () {
    table = $('#salesTable').DataTable({
        ajax: {
            url: '{{ route('sales.data') }}',
            type: 'GET',
        },
        columns: [
            { data: 'date' },
            { data: 'invoice_number' },
            { data: 'customer_name' },
            {
                data: 'description',
                render: function (data) {
                    return data ? data : '<span class="text-muted">-</span>';
                }
            },
            {
                data: 'quantity',
                render: function (data) {
                    return parseFloat(data).toLocaleString('id-ID');
                }
            },
            {
                data: 'price',
                render: function (data) {
                    return 'Rp ' + parseFloat(data).toLocaleString('id-ID');
                }
            },
            {
                data: 'amount',
                render: function (data) {
                    return 'Rp ' + parseFloat(data).toLocaleString('id-ID');
                }
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    return `
                        <button class="btn btn-sm btn-primary" onclick="editSale(${row.id})">
                            <i data-lucide="pencil" style="width:14px;height:14px;"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteSale(${row.id}, '${escHtml(row.invoice_number)}')">
                            <i data-lucide="trash-2" style="width:14px;height:14px;"></i> Hapus
                        </button>
                        <button class="btn btn-sm" onclick="window.open('/sales/${row.id}/invoice', '_blank')" title="Invoice">
                            <i data-lucide="file-text" style="width:14px;height:14px;"></i> Invoice
                        </button>
                    `;
                }
            }
        ],
        order: [[0, 'desc']],
        drawCallback: function () {
            lucide.createIcons();
        }
    });
});

function escHtml(str) {
    if (str == null) return '';
    return String(str).replace(/'/g, "\\'").replace(/"/g, '&quot;');
}

function calcAmount() {
    const qty = parseFloat(document.getElementById('quantity').value) || 0;
    const price = parseFloat(document.getElementById('price').value) || 0;
    const amount = qty * price;
    document.getElementById('amount_display').value = 'Rp ' + amount.toLocaleString('id-ID');
}

function loadCustomers(selectedId = null) {
    axios.get('/customer/data')
        .then(res => {
            const customers = res.data.data || res.data;
            const select = document.getElementById('customer_id');
            select.innerHTML = '<option value="">-- Pilih Customer --</option>';
            customers.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id;
                opt.textContent = c.name;
                if (selectedId && c.id == selectedId) opt.selected = true;
                select.appendChild(opt);
            });
        })
        .catch(() => {
            document.getElementById('customer_id').innerHTML = '<option value="">Gagal memuat customer</option>';
        });
}

function openModal(title = 'Tambah Penjualan') {
    editId = null;
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('date').value = '';
    document.getElementById('invoice_number').value = '';
    document.getElementById('description').value = '';
    document.getElementById('quantity').value = '';
    document.getElementById('price').value = '';
    document.getElementById('amount_display').value = '';
    document.getElementById('noted').value = '';
    loadCustomers();
    document.getElementById('salesModal').style.display = 'flex';
}

function editSale(id) {
    axios.get(`/sales/${id}`)
        .then(res => {
            const row = res.data;
            editId = id;
            document.getElementById('modalTitle').textContent = 'Edit Penjualan';
            document.getElementById('date').value = row.date;
            document.getElementById('invoice_number').value = row.invoice_number;
            document.getElementById('description').value = row.description || '';
            document.getElementById('quantity').value = row.quantity;
            document.getElementById('price').value = row.price;
            document.getElementById('noted').value = row.noted || '';
            calcAmount();
            loadCustomers(row.customer_id);
            document.getElementById('salesModal').style.display = 'flex';
        })
        .catch(() => {
            showError('Gagal memuat data');
        });
}

function closeModal() {
    document.getElementById('salesModal').style.display = 'none';
    editId = null;
}

function saveSale() {
    const payload = {
        date: document.getElementById('date').value,
        invoice_number: document.getElementById('invoice_number').value,
        customer_id: document.getElementById('customer_id').value,
        description: document.getElementById('description').value,
        quantity: document.getElementById('quantity').value,
        price: document.getElementById('price').value,
        noted: document.getElementById('noted').value,
    };

    const request = editId
        ? axios.put(`/sales/${editId}`, payload)
        : axios.post('{{ route('sales.store') }}', payload);

    request
        .then(res => {
            showSuccess(res.data.message || 'Data berhasil disimpan');
            closeModal();
            table.ajax.reload(null, false);
        })
        .catch(err => {
            const errors = err.response?.data?.errors;
            if (errors) {
                const msg = Object.values(errors).flat().join('\n');
                showError(msg);
            } else {
                showError(err.response?.data?.message || 'Terjadi kesalahan');
            }
        });
}

function deleteSale(id, invoice) {
    showConfirm(`Hapus penjualan <strong>${invoice}</strong>? Tindakan ini tidak dapat dibatalkan.`, function () {
        axios.delete(`/sales/${id}`)
            .then(res => {
                showSuccess(res.data.message || 'Data berhasil dihapus');
                table.ajax.reload(null, false);
            })
            .catch(err => {
                showError(err.response?.data?.message || 'Gagal menghapus data');
            });
    });
}
</script>
@endpush
