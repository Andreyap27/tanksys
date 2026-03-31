@extends('layouts.app')

@section('title', 'Data Pengeluaran')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title-text">Data Pengeluaran</h1>
        <p class="page-subtitle">Kelola data pengeluaran operasional</p>
    </div>
    <div class="page-actions">
        @if($canManage)
        <button class="btn btn-primary" onclick="openCreateModal()">
            <i data-lucide="plus" style="width:16px;height:16px;"></i>
            Tambah Pengeluaran
        </button>
        @endif
    </div>
</div>
<div class="dash-stat ds-expense" style="margin-bottom:1.25rem;max-width:320px;">
    <div class="dash-stat__row">
        <div>
            <div class="dash-stat__label">Total Pengeluaran</div>
            <div class="dash-stat__value" id="expensesTotalCard">Rp 0</div>
        </div>
        <div class="dash-stat__icon">
            <i data-lucide="receipt" style="width:1.1rem;height:1.1rem;"></i>
        </div>
    </div>
    <div class="dash-stat__trend flat">
        <i data-lucide="clipboard-list"></i> <span id="expensesCountCard">0</span> transaksi
    </div>
</div>

<div class="card">
    <div class="card-toolbar"><div class="dt-search-slot"></div></div>
    <div class="card-content" style="padding:0;">
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

@include('expenses.modals.create')
@include('expenses.modals.edit')
@endsection

@push('scripts')
<script>
let table;
let editId = null;
const canManage   = @json($canManage);
const canDelete   = @json($canDelete);
const createForm  = document.getElementById('createForm');
const editForm    = document.getElementById('editForm');
const createModal = document.getElementById('createModal');
const editModal   = document.getElementById('editModal');

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
    table = $('#expensesTable').DataTable({
        ajax: { url: '{{ route('expenses.data') }}', type: 'GET' },
        columns: [
            { data: 'date' },
            { data: 'description' },
            {
                data: 'category',
                render: (data) => {
                    const cls = categoryBadge[data] || 'badge-info';
                    return `<span class="badge ${cls}">${data}</span>`;
                }
            },
            {
                data: 'nominal',
                render: (data) => Currency.symbol + ' ' + data
            },
            {
                data: 'noted',
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
                                onclick="openEditModal('${row.id}', '${row.date_raw}', '${escHtml(row.description)}', '${escHtml(row.category)}', '${row.nominal_raw}', '${escHtml(row.noted)}')">
                                <i data-lucide="pencil" style="width:14px;height:14px;"></i>
                            </button>` : ''}
                            ${canDelete ? `<button class="icon-btn danger" title="Hapus"
                                onclick="deleteExpense('${row.id}', '${escHtml(row.description)}')">
                                <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                            </button>` : ''}
                        </div>
                    `;
                }
            }
        ],
        order: [[0, 'desc']],
        drawCallback: function () {
            lucide.createIcons();
            updateExpensesTotal(this.api());
        }
    });
});

function updateExpensesTotal(api) {
    const rows = api.rows({ search: 'applied' }).data();
    let total = 0;
    for (let i = 0; i < rows.length; i++) total += parseFloat(rows[i].nominal_raw) || 0;
    document.getElementById('expensesTotalCard').textContent = 'Rp ' + Currency.number(total);
    document.getElementById('expensesCountCard').textContent = rows.length;
}

function escHtml(str) {
    if (str == null) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/'/g, "\\'").replace(/"/g, '&quot;');
}

// ── Create ────────────────────────────────────────────────────────────────────

function openCreateModal() {
    createForm.reset();
    createModal.classList.add('active');
}

function closeCreateModal() {
    createModal.classList.remove('active');
}

function storeExpense() {
    const payload = {
        date:        createForm.date.value,
        category:    createForm.category.value,
        description: createForm.description.value,
        nominal:     getRaw(createForm.nominal),
        noted:       createForm.noted.value,
    };

    axios.post('{{ route('expenses.store') }}', payload)
        .then(res => {
            showSuccess('Berhasil', res.data.message || 'Pengeluaran berhasil ditambahkan');
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

// ── Edit ──────────────────────────────────────────────────────────────────────

function openEditModal(id, date, description, category, nominal, noted) {
    editId = id;
    editForm.date.value        = date;
    editForm.description.value = description;
    editForm.category.value    = category;
    editForm.noted.value       = noted !== '-' ? noted : '';
    setRaw(editForm.nominal, nominal);
    editForm.nominal.value = parseInt(nominal) ? Currency.format(nominal) : '';
    editModal.classList.add('active');
}

function closeEditModal() {
    editModal.classList.remove('active');
    editId = null;
}

function updateExpense() {
    const payload = {
        date:        editForm.date.value,
        category:    editForm.category.value,
        description: editForm.description.value,
        nominal:     getRaw(editForm.nominal),
        noted:       editForm.noted.value,
    };

    axios.put(`/expenses/${editId}`, payload)
        .then(res => {
            showSuccess('Berhasil', res.data.message || 'Pengeluaran berhasil diupdate');
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

// ── Delete ────────────────────────────────────────────────────────────────────

function deleteExpense(id, description) {
    showConfirm({
        title: 'Konfirmasi Hapus',
        message: `Yakin ingin menghapus pengeluaran "${description}"? Tindakan ini tidak dapat dibatalkan.`,
        type: 'danger',
        confirmText: 'Ya, Hapus',
        onConfirm: async () => {
            try {
                const res = await axios.delete(`/expenses/${id}`);
                showSuccess('Berhasil', res.data.message || 'Data berhasil dihapus');
                table.ajax.reload(null, false);
            } catch (err) {
                showError('Gagal', err.response?.data?.message || 'Gagal menghapus data');
            }
        }
    });
}
</script>
@endpush
