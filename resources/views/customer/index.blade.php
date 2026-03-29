@extends('layouts.app')

@section('title', 'Data Customer')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@23.1.0/build/css/intlTelInput.css">
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title-text">Data Customer</h1>
        <p class="page-subtitle">Kelola data pelanggan</p>
    </div>
    <div class="page-actions">
        @if($canManage)
        <button class="btn btn-primary" onclick="openCreateModal()">
            <i data-lucide="plus" style="width:16px;height:16px;"></i>
            Tambah Customer
        </button>
        @endif
    </div>
</div>
<div class="card">
    <div class="card-toolbar"><div class="dt-search-slot"></div></div>
    <div class="card-content" style="padding:0;">
        <div class="table-wrap">
            <table id="customerTable" class="w-full">
                <thead>
                    <tr>
                        <th>ID</th>
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

@include('customer.modals.create')
@include('customer.modals.edit')
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@23.1.0/build/js/intlTelInput.min.js"></script>
<script>const canManage = @json($canManage); const canDelete = @json($canDelete);</script>
<script>
let table;
let editId = null;
const createForm  = document.getElementById('createForm');
const editForm    = document.getElementById('editForm');
const createModal = document.getElementById('createModal');
const editModal   = document.getElementById('editModal');

// intl-tel-input instances
const itiCreate = window.intlTelInput(document.getElementById('createContact'), {
    initialCountry: 'id',
    separateDialCode: true,
    utilsScript: 'https://cdn.jsdelivr.net/npm/intl-tel-input@23.1.0/build/js/utils.js',
});

const itiEdit = window.intlTelInput(document.getElementById('editContact'), {
    initialCountry: 'id',
    separateDialCode: true,
    utilsScript: 'https://cdn.jsdelivr.net/npm/intl-tel-input@23.1.0/build/js/utils.js',
});

$(document).ready(function () {
    table = $('#customerTable').DataTable({
        ajax: { url: '{{ route('customer.data') }}', type: 'GET' },
        columns: [
            { data: 'customer_id' },
            { data: 'name' },
            {
                data: 'address',
                render: (data) => data && data !== '-' ? data : '<span class="text-muted">-</span>'
            },
            {
                data: 'pic_name',
                render: (data) => data && data !== '-' ? data : '<span class="text-muted">-</span>'
            },
            {
                data: 'contact',
                render: (data) => data && data !== '-' ? data : '<span class="text-muted">-</span>'
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    return `
                        <div class="table-actions">
                            ${canManage ? `<button class="icon-btn primary" title="Edit"
                                onclick="openEditModal('${row.id}', '${escHtml(row.customer_id)}', '${escHtml(row.name)}', '${escHtml(row.address)}', '${escHtml(row.pic_name)}', '${escHtml(row.contact)}')">
                                <i data-lucide="pencil" style="width:14px;height:14px;"></i>
                            </button>` : ''}
                            ${canDelete ? `<button class="icon-btn danger" title="Hapus"
                                onclick="deleteCustomer('${row.id}', '${escHtml(row.name)}')">
                                <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                            </button>` : ''}
                        </div>
                    `;
                }
            }
        ],
        order: [[0, 'asc']],
        drawCallback: function () { lucide.createIcons(); }
    });
});

function escHtml(str) {
    if (str == null) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/'/g, "\\'").replace(/"/g, '&quot;');
}

// ── Create ──────────────────────────────────────────────────────────────────

function openCreateModal() {
    createForm.reset();
    itiCreate.setNumber('');
    createForm.customer_id.placeholder = 'Memuat...';
    createModal.classList.add('active');

    axios.get('{{ route('customer.next-id') }}')
        .then(res => {
            createForm.customer_id.value       = res.data.customer_id;
            createForm.customer_id.placeholder = '';
        })
        .catch(() => {
            createForm.customer_id.placeholder = 'Gagal memuat ID';
        });
}

function closeCreateModal() {
    createModal.classList.remove('active');
}

function storeCustomer() {
    const payload = {
        name:     createForm.name.value,
        address:  createForm.address.value,
        pic_name: createForm.pic_name.value,
        contact:  itiCreate.getNumber(),
    };

    axios.post('{{ route('customer.store') }}', payload)
        .then(res => {
            showSuccess('Berhasil', res.data.message || 'Customer berhasil ditambahkan');
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

function openEditModal(id, customer_id, name, address, pic_name, contact) {
    editId = id;
    editForm.customer_id.value = customer_id;
    editForm.name.value        = name;
    editForm.address.value     = address !== '-' ? address : '';
    editForm.pic_name.value    = pic_name !== '-' ? pic_name : '';
    itiEdit.setNumber(contact !== '-' ? contact : '');
    editModal.classList.add('active');
}

function closeEditModal() {
    editModal.classList.remove('active');
    editId = null;
}

function updateCustomer() {
    const payload = {
        name:     editForm.name.value,
        address:  editForm.address.value,
        pic_name: editForm.pic_name.value,
        contact:  itiEdit.getNumber(),
    };

    axios.put(`/customer/${editId}`, payload)
        .then(res => {
            showSuccess('Berhasil', res.data.message || 'Customer berhasil diupdate');
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

function deleteCustomer(id, name) {
    showConfirm({
        title: 'Konfirmasi Hapus',
        message: `Yakin ingin menghapus customer "${name}"? Tindakan ini tidak dapat dibatalkan.`,
        type: 'danger',
        confirmText: 'Ya, Hapus',
        onConfirm: async () => {
            try {
                const res = await axios.delete(`/customer/${id}`);
                showSuccess('Berhasil', res.data.message || 'Customer berhasil dihapus');
                table.ajax.reload(null, false);
            } catch (err) {
                showError('Gagal', err.response?.data?.message || 'Gagal menghapus data');
            }
        }
    });
}
</script>
@endpush
