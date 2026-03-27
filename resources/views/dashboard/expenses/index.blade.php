@extends('layouts.app')

@section('title', 'Data Pengeluaran')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Data Pengeluaran</h2>
        <button class="btn btn-primary" onclick="openModal()">
            <i data-lucide="plus" style="width:16px;height:16px;"></i>
            Tambah Pengeluaran
        </button>
    </div>
    <div class="card-content">
        <div class="table-wrap">
            <table id="expensesTable" class="w-full">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Deskripsi</th>
                        <th>Kategori</th>
                        <th>Nominal</th>
                        <th>Noted</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal-overlay" id="expensesModal" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Tambah Pengeluaran</h3>
            <button class="modal-close-btn" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                    <input type="date" id="date" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Kategori <span class="text-danger">*</span></label>
                    <select id="category" class="form-select" required>
                        <option value="">-- Pilih Kategori --</option>
                        <option value="Gaji">Gaji</option>
                        <option value="Spare Part">Spare Part</option>
                        <option value="Jasa">Jasa</option>
                        <option value="BBM ME">BBM ME</option>
                        <option value="BBM AE">BBM AE</option>
                        <option value="Umum">Umum</option>
                        <option value="Fee">Fee</option>
                        <option value="Lori">Lori</option>
                    </select>
                </div>
                <div class="form-group full">
                    <label class="form-label">Deskripsi <span class="text-danger">*</span></label>
                    <input type="text" id="description" class="form-input" placeholder="Deskripsi pengeluaran" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Nominal (Rp) <span class="text-danger">*</span></label>
                    <input type="number" id="nominal" class="form-input" placeholder="0" min="0" step="1" required>
                </div>
                <div class="form-group full">
                    <label class="form-label">Noted</label>
                    <textarea id="noted" class="form-textarea" rows="3" placeholder="Catatan tambahan"></textarea>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn" onclick="closeModal()">Batal</button>
            <button class="btn btn-primary" onclick="saveExpense()">Simpan</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let table;
let editId = null;

const categoryBadge = {
    'Gaji':       'badge-info',
    'Spare Part': 'badge-warning',
    'Jasa':       'badge-info',
    'BBM ME':     'badge-danger',
    'BBM AE':     'badge-danger',
    'Umum':       'badge-success',
    'Fee':        'badge-warning',
    'Lori':       'badge-info',
};

$(document).ready(function () {
    table = $('#expensesTable').DataTable({
        ajax: {
            url: '{{ route('expenses.data') }}',
            type: 'GET',
        },
        columns: [
            { data: 'date' },
            { data: 'description' },
            {
                data: 'category',
                render: function (data) {
                    const cls = categoryBadge[data] || 'badge-info';
                    return `<span class="badge ${cls}">${data}</span>`;
                }
            },
            {
                data: 'nominal',
                render: function (data) {
                    return 'Rp ' + parseFloat(data).toLocaleString('id-ID');
                }
            },
            {
                data: 'noted',
                render: function (data) {
                    return data ? data : '<span class="text-muted">-</span>';
                }
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    return `
                        <button class="btn btn-sm btn-primary" onclick="editExpense(${row.id}, '${escHtml(row.date)}', '${escHtml(row.description)}', '${escHtml(row.category)}', '${row.nominal}', '${escHtml(row.noted)}')">
                            <i data-lucide="pencil" style="width:14px;height:14px;"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteExpense(${row.id}, '${escHtml(row.description)}')">
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
    return String(str).replace(/'/g, "\\'").replace(/"/g, '&quot;');
}

function openModal(title = 'Tambah Pengeluaran') {
    editId = null;
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('date').value = '';
    document.getElementById('description').value = '';
    document.getElementById('category').value = '';
    document.getElementById('nominal').value = '';
    document.getElementById('noted').value = '';
    document.getElementById('expensesModal').style.display = 'flex';
}

function editExpense(id, date, description, category, nominal, noted) {
    editId = id;
    document.getElementById('modalTitle').textContent = 'Edit Pengeluaran';
    document.getElementById('date').value = date;
    document.getElementById('description').value = description;
    document.getElementById('category').value = category;
    document.getElementById('nominal').value = nominal;
    document.getElementById('noted').value = noted || '';
    document.getElementById('expensesModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('expensesModal').style.display = 'none';
    editId = null;
}

function saveExpense() {
    const payload = {
        date: document.getElementById('date').value,
        description: document.getElementById('description').value,
        category: document.getElementById('category').value,
        nominal: document.getElementById('nominal').value,
        noted: document.getElementById('noted').value,
    };

    const request = editId
        ? axios.put(`/expenses/${editId}`, payload)
        : axios.post('{{ route('expenses.store') }}', payload);

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

function deleteExpense(id, description) {
    showConfirm(`Hapus pengeluaran <strong>${description}</strong>? Tindakan ini tidak dapat dibatalkan.`, function () {
        axios.delete(`/expenses/${id}`)
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
