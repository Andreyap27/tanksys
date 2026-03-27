@extends('layouts.app')

@section('title', 'Data Modal')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Data Modal Usaha</h2>
        <button class="btn btn-primary" onclick="openModal()">
            <i data-lucide="plus" style="width:16px;height:16px;"></i>
            Tambah Modal
        </button>
    </div>
    <div class="card-content">
        <div class="table-wrap">
            <table id="capitalTable" class="w-full">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama</th>
                        <th>Nominal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal-overlay" id="capitalModal" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Tambah Modal</h3>
            <button class="modal-close-btn" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                    <input type="date" id="date" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Nama <span class="text-danger">*</span></label>
                    <input type="text" id="name" class="form-input" placeholder="Nama sumber modal" required>
                </div>
                <div class="form-group full">
                    <label class="form-label">Nominal (Rp) <span class="text-danger">*</span></label>
                    <input type="number" id="nominal" class="form-input" placeholder="0" min="0" step="1" required>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn" onclick="closeModal()">Batal</button>
            <button class="btn btn-primary" onclick="saveCapital()">Simpan</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let table;
let editId = null;

$(document).ready(function () {
    table = $('#capitalTable').DataTable({
        ajax: {
            url: '{{ route('capital.data') }}',
            type: 'GET',
        },
        columns: [
            { data: 'date' },
            { data: 'name' },
            {
                data: 'nominal',
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
                        <button class="btn btn-sm btn-primary" onclick="editCapital(${row.id}, '${escHtml(row.date)}', '${escHtml(row.name)}', '${row.nominal}')">
                            <i data-lucide="pencil" style="width:14px;height:14px;"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteCapital(${row.id}, '${escHtml(row.name)}')">
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

function openModal(title = 'Tambah Modal') {
    editId = null;
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('date').value = '';
    document.getElementById('name').value = '';
    document.getElementById('nominal').value = '';
    document.getElementById('capitalModal').style.display = 'flex';
}

function editCapital(id, date, name, nominal) {
    editId = id;
    document.getElementById('modalTitle').textContent = 'Edit Modal';
    document.getElementById('date').value = date;
    document.getElementById('name').value = name;
    document.getElementById('nominal').value = nominal;
    document.getElementById('capitalModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('capitalModal').style.display = 'none';
    editId = null;
}

function saveCapital() {
    const payload = {
        date: document.getElementById('date').value,
        name: document.getElementById('name').value,
        nominal: document.getElementById('nominal').value,
    };

    const request = editId
        ? axios.put(`/capital/${editId}`, payload)
        : axios.post('{{ route('capital.store') }}', payload);

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

function deleteCapital(id, name) {
    showConfirm(`Hapus data modal <strong>${name}</strong>? Tindakan ini tidak dapat dibatalkan.`, function () {
        axios.delete(`/capital/${id}`)
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
