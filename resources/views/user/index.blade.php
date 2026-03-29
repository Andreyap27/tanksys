@extends('layouts.app')

@section('title', 'Manajemen User')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title-text">Manajemen User</h1>
        <p class="page-subtitle">Kelola data pengguna sistem</p>
    </div>
    <div class="page-actions">
        @if($canManage)
        <button class="btn btn-primary" onclick="openCreateModal()">
            <i data-lucide="plus" style="width:16px;height:16px;"></i>
            Tambah User
        </button>
        @endif
    </div>
</div>
<div class="card">
    <div class="card-toolbar"><div class="dt-search-slot"></div></div>
    <div class="card-content" style="padding:0;">
        <div class="table-wrap">
            <table id="userTable" class="w-full">
                <thead>
                    <tr>
                        <th>ID Karyawan</th>
                        <th>Nama Karyawan</th>
                        <th>Jabatan</th>
                        <th>Username</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

@include('user.modals.create')
@include('user.modals.edit')
@include('user.modals.reset-password')
@endsection

@push('scripts')
<script>
let table;
let editId = null;
let resetId = null;
const canManage          = @json($canManage);
const canDelete          = @json($canDelete);
const createForm         = document.getElementById('createForm');
const editForm           = document.getElementById('editForm');
const resetPasswordForm  = document.getElementById('resetPasswordForm');
const createModal        = document.getElementById('createModal');
const editModal          = document.getElementById('editModal');
const resetPasswordModal = document.getElementById('resetPasswordModal');

$(document).ready(function () {
    table = $('#userTable').DataTable({
        ajax: { url: '{{ route('user.data') }}', type: 'GET' },
        columns: [
            { data: 'employee_id' },
            { data: 'name' },
            {
                data: 'role',
                render: function (data) {
                    let cls = data === 'SPV' ? 'badge-warning' : 'badge-info';
                    return `<span class="badge ${cls}">${data}</span>`;
                }
            },
            { data: 'username' },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    return `
                        <div class="table-actions">
                            ${canManage ? `
                            <button class="icon-btn primary" title="Edit"
                                onclick="openEditModal('${row.id}', '${escHtml(row.employee_id)}', '${escHtml(row.name)}', '${escHtml(row.role)}', '${escHtml(row.username)}')">
                                <i data-lucide="pencil" style="width:14px;height:14px;"></i>
                            </button>
                            <button class="icon-btn warning" title="Reset Password"
                                onclick="openResetPasswordModal('${row.id}', '${escHtml(row.name)}')">
                                <i data-lucide="key-round" style="width:14px;height:14px;"></i>
                            </button>` : ''}
                            ${canDelete ? `
                            <button class="icon-btn danger" title="Hapus"
                                onclick="deleteUser('${row.id}', '${escHtml(row.name)}')">
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

function checkUsername(input) {
    const hint = document.getElementById('usernameHint');
    const val  = input.value.trim();
    if (!val) { hint.style.display = 'none'; return; }

    axios.get('{{ route('user.check-username') }}', { params: { username: val } })
        .then(res => {
            if (res.data.available) {
                hint.textContent    = '✓ Username tersedia';
                hint.style.color    = 'var(--success)';
                hint.style.display  = 'block';
                input.setCustomValidity('');
            } else {
                hint.textContent    = '✗ Username sudah digunakan';
                hint.style.color    = 'var(--destructive)';
                hint.style.display  = 'block';
                input.setCustomValidity('Username sudah digunakan');
            }
        });
}

function openCreateModal() {
    createForm.reset();
    createForm.employee_id.placeholder = 'Memuat...';
    const hint = document.getElementById('usernameHint');
    if (hint) { hint.style.display = 'none'; }
    createForm.username.setCustomValidity('');
    createModal.classList.add('active');

    axios.get('{{ route('user.next-id') }}')
        .then(res => {
            createForm.employee_id.value = res.data.employee_id;
            createForm.employee_id.placeholder = '';
        })
        .catch(() => {
            createForm.employee_id.placeholder = 'Gagal memuat ID';
        });
}

function closeCreateModal() {
    createModal.classList.remove('active');
    const hint = document.getElementById('usernameHint');
    if (hint) { hint.style.display = 'none'; }
    createForm.username.setCustomValidity('');
}

function storeUser() {
    const payload = {
        name:     createForm.name.value,
        role:     createForm.role.value,
        username: createForm.username.value,
        password: createForm.password.value,
    };

    axios.post('{{ route('user.store') }}', payload)
        .then(res => {
            showSuccess('Berhasil', res.data.message || 'User berhasil ditambahkan');
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

function openEditModal(id, employee_id, name, role, username) {
    editId = id;
    editForm.employee_id.value = employee_id;
    editForm.name.value        = name;
    editForm.role.value        = role;
    editForm.username.value    = username;
    editModal.classList.add('active');
}

function closeEditModal() {
    editModal.classList.remove('active');
    editId = null;
}

function updateUser() {
    const payload = {
        name:     editForm.name.value,
        role:     editForm.role.value,
        username: editForm.username.value,
    };

    axios.put(`/user/${editId}`, payload)
        .then(res => {
            showSuccess('Berhasil', res.data.message || 'User berhasil diupdate');
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

// ── Reset Password ───────────────────────────────────────────────────────────

function openResetPasswordModal(id, name) {
    resetId = id;
    resetPasswordForm.reset();
    document.querySelector('#resetPasswordModal .modal-title').textContent = `Reset Password — ${name}`;
    resetPasswordModal.classList.add('active');
}

function closeResetPasswordModal() {
    resetPasswordModal.classList.remove('active');
    resetId = null;
}

function doResetPassword() {
    axios.post(`/user/${resetId}/reset-password`, {
        password: resetPasswordForm.password.value,
    })
        .then(res => {
            showSuccess('Berhasil', res.data.message || 'Password berhasil direset');
            closeResetPasswordModal();
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

function deleteUser(id, name) {
    showConfirm({
        title: 'Konfirmasi Hapus',
        message: `Yakin ingin menghapus user "${name}"? Tindakan ini tidak dapat dibatalkan.`,
        type: 'danger',
        confirmText: 'Ya, Hapus',
        onConfirm: async () => {
            try {
                const res = await axios.delete(`/user/${id}`);
                showSuccess('Berhasil', res.data.message || 'User berhasil dihapus');
                table.ajax.reload(null, false);
            } catch (err) {
                showError('Gagal', err.response?.data?.message || 'Gagal menghapus data');
            }
        }
    });
}
</script>
@endpush
