@extends('layouts.app')

@section('title', 'Master Petty Cash')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title-text">Master Petty Cash</h1>
        <p class="page-subtitle">Kelola daftar petty cash</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="openCreateModal()">
            <i data-lucide="plus" style="width:16px;height:16px;"></i>
            Tambah Petty Cash
        </button>
    </div>
</div>

<div class="card">
    <div class="card-toolbar"><div class="dt-search-slot"></div></div>
    <div class="card-content" style="padding:0;">
        <div class="table-wrap">
            <table id="pettyCashTable" class="w-full">
                <thead>
                    <tr>
                        <th>Nama Petty Cash</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

{{-- Create Modal --}}
<div class="modal-overlay" id="createModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3 class="modal-title">Tambah Petty Cash</h3>
            <button class="modal-close-btn" onclick="closeCreateModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="createForm" onsubmit="return false;">
                <div class="form-group">
                    <label class="form-label">Nama Petty Cash <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-input" placeholder="Nama petty cash" required>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-danger" onclick="closeCreateModal()">
                <i data-lucide="x" style="width:15px;height:15px;"></i> Batal
            </button>
            <button class="btn btn-primary" onclick="storeItem()">
                <i data-lucide="save" style="width:15px;height:15px;"></i> Simpan
            </button>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal-overlay" id="editModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3 class="modal-title">Edit Petty Cash</h3>
            <button class="modal-close-btn" onclick="closeEditModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="editForm" onsubmit="return false;">
                <div class="form-group">
                    <label class="form-label">Nama Petty Cash <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-input" placeholder="Nama petty cash" required>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-danger" onclick="closeEditModal()">
                <i data-lucide="x" style="width:15px;height:15px;"></i> Batal
            </button>
            <button class="btn btn-primary" onclick="updateItem()">
                <i data-lucide="save" style="width:15px;height:15px;"></i> Simpan
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let table;
let editId = null;
const createForm  = document.getElementById('createForm');
const editForm    = document.getElementById('editForm');
const createModal = document.getElementById('createModal');
const editModal   = document.getElementById('editModal');

$(document).ready(function () {
    table = $('#pettyCashTable').DataTable({
        ajax: { url: '{{ route('petty-cash.data') }}', type: 'GET' },
        columns: [
            { data: 'name' },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    return `
                        <div class="table-actions">
                            <button class="icon-btn primary" title="Edit"
                                onclick="openEditModal('${row.id}','${escHtml(row.name)}')">
                                <i data-lucide="pencil" style="width:14px;height:14px;"></i>
                            </button>
                            <button class="icon-btn danger" title="Hapus"
                                onclick="deleteItem('${row.id}','${escHtml(row.name)}')">
                                <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                            </button>
                        </div>`;
                }
            }
        ],
        order: [[0, 'desc']],
        drawCallback: function () { lucide.createIcons(); }
    });
});

function escHtml(str) {
    if (str == null) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/'/g,"\\'").replace(/"/g,'&quot;');
}

function openCreateModal() { createForm.reset(); createModal.classList.add('active'); }
function closeCreateModal() { createModal.classList.remove('active'); }

function storeItem() {
    axios.post('{{ route('petty-cash.store') }}', { name: createForm.name.value })
        .then(res => { showSuccess('Berhasil', res.data.message); closeCreateModal(); table.ajax.reload(null, false); })
        .catch(err => {
            const errors = err.response?.data?.errors;
            showError('Gagal', errors ? Object.values(errors).flat().join('\n') : err.response?.data?.message || 'Terjadi kesalahan');
        });
}

function openEditModal(id, name) {
    editId = id;
    editForm.name.value = name;
    editModal.classList.add('active');
}
function closeEditModal() { editModal.classList.remove('active'); editId = null; }

function updateItem() {
    axios.put(`/petty-cash/${editId}`, { name: editForm.name.value })
        .then(res => { showSuccess('Berhasil', res.data.message); closeEditModal(); table.ajax.reload(null, false); })
        .catch(err => {
            const errors = err.response?.data?.errors;
            showError('Gagal', errors ? Object.values(errors).flat().join('\n') : err.response?.data?.message || 'Terjadi kesalahan');
        });
}

function deleteItem(id, name) {
    showConfirm({
        title: 'Konfirmasi Hapus',
        message: `Yakin ingin menghapus petty cash "${name}"?`,
        type: 'danger',
        confirmText: 'Ya, Hapus',
        onConfirm: async () => {
            try {
                const res = await axios.delete(`/petty-cash/${id}`);
                showSuccess('Berhasil', res.data.message);
                table.ajax.reload(null, false);
            } catch (err) {
                showError('Gagal', err.response?.data?.message || 'Gagal menghapus data');
            }
        }
    });
}
</script>
@endpush
