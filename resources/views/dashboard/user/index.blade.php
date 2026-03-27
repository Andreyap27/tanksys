@extends('layouts.app')

@section('title', 'Manajemen User')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Manajemen User</h2>
        <button class="btn btn-primary" onclick="openModal()">
            <i data-lucide="plus" style="width:16px;height:16px;"></i>
            Tambah User
        </button>
    </div>
    <div class="card-content">
        <div class="table-wrap">
            <table id="userTable" class="w-full">
                <thead>
                    <tr>
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

<!-- Modal -->
<div class="modal-overlay" id="userModal" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Tambah User</h3>
            <button class="modal-close-btn" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">ID Karyawan <span class="text-danger">*</span></label>
                    <input type="text" id="employee_id" class="form-input" placeholder="ID Karyawan" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" id="name" class="form-input" placeholder="Nama Lengkap" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Jabatan <span class="text-danger">*</span></label>
                    <select id="role" class="form-select" required>
                        <option value="">-- Pilih Jabatan --</option>
                        <option value="Admin">Admin</option>
                        <option value="SPV">SPV</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Username <span class="text-danger">*</span></label>
                    <input type="text" id="username" class="form-input" placeholder="Username" required>
                </div>
                <div class="form-group full">
                    <label class="form-label">Password <span id="passwordHint" class="text-muted" style="font-size:0.8em;"></span></label>
                    <input type="password" id="password" class="form-input" placeholder="Password">
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn" onclick="closeModal()">Batal</button>
            <button class="btn btn-primary" onclick="saveUser()">Simpan</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let table;
let editId = null;

$(document).ready(function () {
    table = $('#userTable').DataTable({
        ajax: {
            url: '{{ route('user.data') }}',
            type: 'GET',
        },
        columns: [
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
                        <button class="btn btn-sm btn-primary" onclick="editUser(${row.id}, '${escHtml(row.employee_id)}', '${escHtml(row.name)}', '${escHtml(row.role)}', '${escHtml(row.username)}')">
                            <i data-lucide="pencil" style="width:14px;height:14px;"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteUser(${row.id}, '${escHtml(row.name)}')">
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

function openModal(title = 'Tambah User') {
    editId = null;
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('employee_id').value = '';
    document.getElementById('name').value = '';
    document.getElementById('role').value = '';
    document.getElementById('username').value = '';
    document.getElementById('password').value = '';
    document.getElementById('password').required = true;
    document.getElementById('passwordHint').textContent = '* wajib diisi';
    document.getElementById('userModal').style.display = 'flex';
}

function editUser(id, employee_id, name, role, username) {
    editId = id;
    document.getElementById('modalTitle').textContent = 'Edit User';
    document.getElementById('employee_id').value = employee_id;
    document.getElementById('name').value = name;
    document.getElementById('role').value = role;
    document.getElementById('username').value = username;
    document.getElementById('password').value = '';
    document.getElementById('password').required = false;
    document.getElementById('passwordHint').textContent = '(kosongkan jika tidak ingin mengubah password)';
    document.getElementById('userModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('userModal').style.display = 'none';
    editId = null;
}

function saveUser() {
    const payload = {
        employee_id: document.getElementById('employee_id').value,
        name: document.getElementById('name').value,
        role: document.getElementById('role').value,
        username: document.getElementById('username').value,
        password: document.getElementById('password').value,
    };

    const request = editId
        ? axios.put(`/user/${editId}`, payload)
        : axios.post('{{ route('user.store') }}', payload);

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

function deleteUser(id, name) {
    showConfirm(`Hapus user <strong>${name}</strong>? Tindakan ini tidak dapat dibatalkan.`, function () {
        axios.delete(`/user/${id}`)
            .then(res => {
                showSuccess(res.data.message || 'User berhasil dihapus');
                table.ajax.reload(null, false);
            })
            .catch(err => {
                showError(err.response?.data?.message || 'Gagal menghapus data');
            });
    });
}
</script>
@endpush
