@extends('layouts.app')

@section('title', 'Data Lori')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Data Lori</h2>
        <button class="btn btn-primary" onclick="openModal()">
            <i data-lucide="plus" style="width:16px;height:16px;"></i>
            Tambah Lori
        </button>
    </div>
    <div class="card-content">
        <div class="table-wrap">
            <table id="loriTable" class="w-full">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Customer</th>
                        <th>Rute</th>
                        <th>Harga</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal-overlay" id="loriModal" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Tambah Lori</h3>
            <button class="modal-close-btn" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                    <input type="date" id="date" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Customer <span class="text-danger">*</span></label>
                    <select id="customer_id" class="form-select" required>
                        <option value="">-- Memuat data customer... --</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Dari (From) <span class="text-danger">*</span></label>
                    <input type="text" id="from" class="form-input" placeholder="Lokasi asal" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Ke (To) <span class="text-danger">*</span></label>
                    <input type="text" id="to" class="form-input" placeholder="Lokasi tujuan" required>
                </div>
                <div class="form-group full">
                    <label class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                    <input type="number" id="price" class="form-input" placeholder="0" min="0" step="1" required>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn" onclick="closeModal()">Batal</button>
            <button class="btn btn-primary" onclick="saveLori()">Simpan</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let table;
let editId = null;

$(document).ready(function () {
    table = $('#loriTable').DataTable({
        ajax: {
            url: '{{ route('lori.data') }}',
            type: 'GET',
        },
        columns: [
            { data: 'date' },
            { data: 'customer_name' },
            {
                data: null,
                render: function (data, type, row) {
                    return `${escHtml(row.from)} &rarr; ${escHtml(row.to)}`;
                }
            },
            {
                data: 'price',
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
                        <button class="btn btn-sm btn-primary" onclick="editLori(${row.id})">
                            <i data-lucide="pencil" style="width:14px;height:14px;"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteLori(${row.id}, '${escHtml(row.customer_name)}')">
                            <i data-lucide="trash-2" style="width:14px;height:14px;"></i> Hapus
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
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/'/g, "\\'").replace(/"/g, '&quot;');
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

function openModal(title = 'Tambah Lori') {
    editId = null;
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('date').value = '';
    document.getElementById('from').value = '';
    document.getElementById('to').value = '';
    document.getElementById('price').value = '';
    loadCustomers();
    document.getElementById('loriModal').style.display = 'flex';
}

function editLori(id) {
    axios.get(`/lori/${id}`)
        .then(res => {
            const row = res.data;
            editId = id;
            document.getElementById('modalTitle').textContent = 'Edit Lori';
            document.getElementById('date').value = row.date;
            document.getElementById('from').value = row.from;
            document.getElementById('to').value = row.to;
            document.getElementById('price').value = row.price;
            loadCustomers(row.customer_id);
            document.getElementById('loriModal').style.display = 'flex';
        })
        .catch(() => {
            showError('Gagal memuat data');
        });
}

function closeModal() {
    document.getElementById('loriModal').style.display = 'none';
    editId = null;
}

function saveLori() {
    const payload = {
        date: document.getElementById('date').value,
        customer_id: document.getElementById('customer_id').value,
        from: document.getElementById('from').value,
        to: document.getElementById('to').value,
        price: document.getElementById('price').value,
    };

    const request = editId
        ? axios.put(`/lori/${editId}`, payload)
        : axios.post('{{ route('lori.store') }}', payload);

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

function deleteLori(id, customerName) {
    showConfirm(`Hapus data lori untuk <strong>${customerName}</strong>? Tindakan ini tidak dapat dibatalkan.`, function () {
        axios.delete(`/lori/${id}`)
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
