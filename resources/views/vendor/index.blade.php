@extends('layouts.app')

@section('title', 'Data Vendor')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@23.1.0/build/css/intlTelInput.css">
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title-text">Data Vendor</h1>
        <p class="page-subtitle">Kelola data vendor / supplier BBM</p>
    </div>
    <div class="page-actions">
        @if(auth()->user()->canManage())
        <button class="btn btn-primary" onclick="openCreateModal()">
            <i data-lucide="plus" style="width:16px;height:16px;"></i>
            Tambah Vendor
        </button>
        @endif
    </div>
</div>
<div class="card">
    <div class="card-toolbar"><div class="dt-search-slot"></div></div>
    <div class="card-content" style="padding:0;">
        <div class="table-wrap">
            <table id="vendorTable" class="w-full">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Vendor</th>
                        <th>Nama PIC</th>
                        <th>No Contact</th>
                        <th>Alamat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

@include('vendor.modals.create')
@include('vendor.modals.edit')
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@23.1.0/build/js/intlTelInput.min.js"></script>

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
    table = $('#vendorTable').DataTable({
        ajax: { url: '{{ route('vendor.data') }}', type: 'GET' },
        columns: [
            { data: 'vendor_code' },
            { data: 'name' },
            {
                data: 'pic_name',
                render: (data) => data !== '-' ? data : '<span class="text-muted">-</span>'
            },
            {
                data: 'contact',
                render: (data) => data !== '-' ? data : '<span class="text-muted">-</span>'
            },
            {
                data: 'address',
                render: (data) => data !== '-' ? data : '<span class="text-muted">-</span>'
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    return `
                        <div class="table-actions">
                            ${canManage ? `<button class="icon-btn primary" title="Edit"
                                onclick="openEditModal('${row.id}', '${escHtml(row.vendor_code)}', '${escHtml(row.name)}', '${escHtml(row.pic_name)}', '${escHtml(row.contact)}', '${escHtml(row.address)}')">
                                <i data-lucide="pencil" style="width:14px;height:14px;"></i>
                            </button>` : ''}
                            ${canDelete ? `<button class="icon-btn danger" title="Hapus"
                                onclick="deleteVendor('${row.id}', '${escHtml(row.name)}')">
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
    createForm.vendor_code.placeholder = 'Memuat...';
    createModal.classList.add('active');

    axios.get('{{ route('vendor.next-id') }}')
        .then(res => {
            createForm.vendor_code.value       = res.data.vendor_code;
            createForm.vendor_code.placeholder = '';
        })
        .catch(() => {
            createForm.vendor_code.placeholder = 'Gagal memuat kode';
        });
}

function closeCreateModal() {
    createModal.classList.remove('active');
}

function storeVendor() {
    const payload = {
        name:     createForm.name.value,
        pic_name: createForm.pic_name.value,
        contact:  itiCreate.getNumber(),
        address:  createForm.address.value,
    };

    axios.post('{{ route('vendor.store') }}', payload)
        .then(res => {
            showSuccess('Berhasil', res.data.message || 'Vendor berhasil ditambahkan');
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

function openEditModal(id, vendor_code, name, pic_name, contact, address) {
    editId = id;
    editForm.vendor_code.value = vendor_code;
    editForm.name.value        = name;
    editForm.pic_name.value    = pic_name !== '-' ? pic_name : '';
    itiEdit.setNumber(contact !== '-' ? contact : '');
    editForm.address.value     = address !== '-' ? address : '';
    editModal.classList.add('active');
}

function closeEditModal() {
    editModal.classList.remove('active');
    editId = null;
}

function updateVendor() {
    const payload = {
        name:     editForm.name.value,
        pic_name: editForm.pic_name.value,
        contact:  itiEdit.getNumber(),
        address:  editForm.address.value,
    };

    axios.put(`/vendor/${editId}`, payload)
        .then(res => {
            showSuccess('Berhasil', res.data.message || 'Vendor berhasil diupdate');
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

function deleteVendor(id, name) {
    showConfirm({
        title: 'Konfirmasi Hapus',
        message: `Yakin ingin menghapus vendor "${name}"? Tindakan ini tidak dapat dibatalkan.`,
        type: 'danger',
        confirmText: 'Ya, Hapus',
        onConfirm: async () => {
            try {
                const res = await axios.delete(`/vendor/${id}`);
                showSuccess('Berhasil', res.data.message || 'Vendor berhasil dihapus');
                table.ajax.reload(null, false);
            } catch (err) {
                showError('Gagal', err.response?.data?.message || 'Gagal menghapus data');
            }
        }
    });
}
</script>
@endpush
