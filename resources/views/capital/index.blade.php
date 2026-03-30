@extends('layouts.app')

@section('title', 'Data Modal')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title-text">Data Modal Usaha</h1>
        <p class="page-subtitle">Kelola data modal usaha</p>
    </div>
    <div class="page-actions">
        @if($canManage)
        <button class="btn btn-primary" onclick="openCreateModal()">
            <i data-lucide="plus" style="width:16px;height:16px;"></i>
            Tambah Modal
        </button>
        @endif
    </div>
</div>
<div class="card">
    <div class="card-toolbar"><div class="dt-search-slot"></div></div>
    <div class="card-content" style="padding:0;">
        <div class="table-wrap">
            <table id="capitalTable" class="w-full">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama</th>
                        <th>Nominal</th>
                        <th>Catatan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

@include('capital.modals.create')
@include('capital.modals.edit')
@endsection

@push('scripts')
<script>
let table;
let editId = null;
const createForm  = document.getElementById('createForm');
const editForm    = document.getElementById('editForm');
const createModal = document.getElementById('createModal');
const editModal   = document.getElementById('editModal');
const canApprove  = @json($canApprove);
const canManage   = @json($canManage);
const canDelete   = @json($canDelete);

// ── Input formatter ───────────────────────────────────────────────────────────
function setRaw(el, raw) { el.dataset.raw = raw; }
function getRaw(el)      { return parseFloat(el.dataset.raw) || 0; }

document.querySelectorAll('.fmt-price').forEach(el => {
    el.addEventListener('input', function () {
        const raw = this.value.replace(/[^0-9]/g, '');
        this.value = raw;
        setRaw(this, raw);
    });
    el.addEventListener('blur', function () {
        const raw = parseInt(this.value.replace(/[^0-9]/g, '')) || 0;
        setRaw(this, raw);
        this.value = raw ? Currency.format(raw) : '';
    });
    el.addEventListener('focus', function () {
        this.value = this.dataset.raw || '';
    });
});

// ── DataTable ─────────────────────────────────────────────────────────────────
$(document).ready(function () {
    table = $('#capitalTable').DataTable({
        ajax: { url: '{{ route('capital.data') }}', type: 'GET' },
        columns: [
            { data: 'date' },
            { data: 'name' },
            { data: 'nominal', render: (data) => Currency.symbol + ' ' + data },
            { data: 'note',    render: (data) => data ? escHtml(data) : '-' },
            {
                data: 'status',
                render: function (data) {
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
                    const editBtn = canManage
                        ? `<button class="icon-btn primary" title="Edit"
                               onclick="openEditModal('${row.id}','${row.date_raw}','${escHtml(row.name)}','${row.nominal_raw}','${escHtml(row.note)}')">
                               <i data-lucide="pencil" style="width:14px;height:14px;"></i>
                           </button>`
                        : '';

                    const approveBtn = canApprove && row.status === 'pending'
                        ? `<button class="icon-btn success" title="Approve"
                               onclick="approveCapital('${row.id}')">
                               <i data-lucide="check-circle" style="width:14px;height:14px;"></i>
                           </button>
                           <button class="icon-btn danger" title="Reject"
                               onclick="rejectCapital('${row.id}')">
                               <i data-lucide="x-circle" style="width:14px;height:14px;"></i>
                           </button>`
                        : '';

                    const deleteBtn = canDelete
                        ? `<button class="icon-btn danger" title="Hapus"
                               onclick="deleteCapital('${row.id}','${escHtml(row.name)}')">
                               <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                           </button>`
                        : '';

                    return `<div class="table-actions">${editBtn}${approveBtn}${deleteBtn}</div>`;
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

// ── Create ────────────────────────────────────────────────────────────────────
function openCreateModal() {
    createForm.reset();
    createModal.classList.add('active');
}
function closeCreateModal() { createModal.classList.remove('active'); }

function storeCapital() {
    const payload = {
        date:    createForm.date.value,
        name:    createForm.name.value,
        nominal: getRaw(createForm.nominal),
        note:    createForm.note.value,
    };
    axios.post('{{ route('capital.store') }}', payload)
        .then(res => { showSuccess('Berhasil', res.data.message); closeCreateModal(); table.ajax.reload(null, false); })
        .catch(err => {
            const errors = err.response?.data?.errors;
            showError('Gagal', errors ? Object.values(errors).flat().join('\n') : err.response?.data?.message || 'Terjadi kesalahan');
        });
}

// ── Edit ──────────────────────────────────────────────────────────────────────
function openEditModal(id, date, name, nominal, note) {
    editId = id;
    editForm.date.value  = date;
    editForm.name.value  = name;
    setRaw(editForm.nominal, nominal);
    editForm.nominal.value = parseInt(nominal) ? Currency.format(nominal) : '';
    editForm.note.value  = note || '';
    editModal.classList.add('active');
}
function closeEditModal() { editModal.classList.remove('active'); editId = null; }

function updateCapital() {
    const payload = {
        date:    editForm.date.value,
        name:    editForm.name.value,
        nominal: getRaw(editForm.nominal),
        note:    editForm.note.value,
    };
    axios.put(`/capital/${editId}`, payload)
        .then(res => { showSuccess('Berhasil', res.data.message); closeEditModal(); table.ajax.reload(null, false); })
        .catch(err => {
            const errors = err.response?.data?.errors;
            showError('Gagal', errors ? Object.values(errors).flat().join('\n') : err.response?.data?.message || 'Terjadi kesalahan');
        });
}

// ── Approve / Reject ──────────────────────────────────────────────────────────
function approveCapital(id) {
    showConfirm({
        title: 'Konfirmasi Approve',
        message: 'Yakin ingin menyetujui data modal ini?',
        type: 'success',
        confirmText: 'Ya, Setujui',
        onConfirm: async () => {
            try {
                const res = await axios.post(`/capital/${id}/approve`);
                showSuccess('Berhasil', res.data.message);
                table.ajax.reload(null, false);
            } catch (err) {
                showError('Gagal', err.response?.data?.message || 'Gagal menyetujui data');
            }
        }
    });
}

function rejectCapital(id) {
    showConfirm({
        title: 'Konfirmasi Reject',
        message: 'Yakin ingin menolak data modal ini?',
        type: 'danger',
        confirmText: 'Ya, Tolak',
        onConfirm: async () => {
            try {
                const res = await axios.post(`/capital/${id}/reject`);
                showSuccess('Berhasil', res.data.message);
                table.ajax.reload(null, false);
            } catch (err) {
                showError('Gagal', err.response?.data?.message || 'Gagal menolak data');
            }
        }
    });
}

// ── Delete ────────────────────────────────────────────────────────────────────
function deleteCapital(id, name) {
    showConfirm({
        title: 'Konfirmasi Hapus',
        message: `Yakin ingin menghapus data modal "${name}"? Tindakan ini tidak dapat dibatalkan.`,
        type: 'danger',
        confirmText: 'Ya, Hapus',
        onConfirm: async () => {
            try {
                const res = await axios.delete(`/capital/${id}`);
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
