@extends('layouts.app')

@section('title', 'Data Customer')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Data Customer</h2>
        <button class="btn btn-primary" onclick="openModal()">
            <i data-lucide="plus" style="width:16px;height:16px;"></i>
            Tambah Customer
        </button>
    </div>
    <div class="card-content">
        <div class="table-wrap">
            <table id="customerTable" class="w-full">
                <thead>
                    <tr>
                        <th>Nama Perusahaan/Perorangan</th>
                        <th>Alamat</th>
                        <th>Nama PIC</th>
                        <th>No Contact</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal-overlay" id="customerModal" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Tambah Customer</h3>
            <button class="modal-close-btn" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-grid">
                <div class="form-group full">
                    <label class="form-label">Nama Perusahaan / Perorangan <span class="text-danger">*</span></label>
                    <input type="text" id="name" class="form-input" placeholder="Nama customer" required>
                </div>
                <div class="form-group full">
                    <label class="form-label">Alamat</label>
                    <textarea id="address" class="form-textarea" rows="3" placeholder="Alamat lengkap"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Nama PIC</label>
                    <input type="text" id="pic_name" class="form-input" placeholder="Nama PIC">
                </div>
                <div class="form-group">
                    <label class="form-label">No Contact</label>
                    <input type="text" id="contact" class="form-input" placeholder="08xxxxxxxxxx">
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn" onclick="closeModal()">Batal</button>
            <button class="btn btn-primary" onclick="saveCustomer()">Simpan</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let table;
let editId = null;

$(document).ready(function () {
    table = $('#customerTable').DataTable({
        ajax: {
            url: '{{ route('customer.data') }}',
            type: 'GET',
        },
        columns: [
            { data: 'name' },
            {
                data: 'address',
                render: function (data) {
                    return data ? data : '<span class="text-muted">-</span>';
                }
            },
            {
                data: 'pic_name',
                render: function (data) {
                    return data ? data : '<span class="text-muted">-</span>';
                }
            },
            {
                data: 'contact',
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
                        <button class="btn btn-sm btn-primary" onclick="editCustomer(${row.id}, '${escHtml(row.name)}', '${escHtml(row.address)}', '${escHtml(row.pic_name)}', '${escHtml(row.contact)}')">
                            <i data-lucide="pencil" style="width:14px;height:14px;"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteCustomer(${row.id}, '${escHtml(row.name)}')">
                            <i data-lucide="trash-2" style="width:14px;height:14px;"></i> Hapus
                        </button>
                    `;
                }
            }
        ],
        order: [[0, 'asc']],
        drawCallback: function () {
            lucide.createIcons();
        }
    });
});

function escHtml(str) {
    if (str == null) return '';
    return String(str).replace(/'/g, "\\'").replace(/"/g, '&quot;');
}

function openModal(title = 'Tambah Customer') {
    editId = null;
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('name').value = '';
    document.getElementById('address').value = '';
    document.getElementById('pic_name').value = '';
    document.getElementById('contact').value = '';
    document.getElementById('customerModal').style.display = 'flex';
}

function editCustomer(id, name, address, pic_name, contact) {
    editId = id;
    document.getElementById('modalTitle').textContent = 'Edit Customer';
    document.getElementById('name').value = name;
    document.getElementById('address').value = address || '';
    document.getElementById('pic_name').value = pic_name || '';
    document.getElementById('contact').value = contact || '';
    document.getElementById('customerModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('customerModal').style.display = 'none';
    editId = null;
}

function saveCustomer() {
    const payload = {
        name: document.getElementById('name').value,
        address: document.getElementById('address').value,
        pic_name: document.getElementById('pic_name').value,
        contact: document.getElementById('contact').value,
    };

    const request = editId
        ? axios.put(`/customer/${editId}`, payload)
        : axios.post('{{ route('customer.store') }}', payload);

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

function deleteCustomer(id, name) {
    showConfirm(`Hapus customer <strong>${name}</strong>? Tindakan ini tidak dapat dibatalkan.`, function () {
        axios.delete(`/customer/${id}`)
            .then(res => {
                showSuccess(res.data.message || 'Customer berhasil dihapus');
                table.ajax.reload(null, false);
            })
            .catch(err => {
                showError(err.response?.data?.message || 'Gagal menghapus data');
            });
    });
}
</script>
@endpush
