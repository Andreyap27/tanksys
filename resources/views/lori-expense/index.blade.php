@extends('layouts.app')

@section('title', 'Expenses Mobil Tangki')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title-text">Expenses Mobil Tangki</h1>
        <p class="page-subtitle">Kelola pengeluaran operasional mobil tangki</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('lori-expense.trash') }}" class="btn btn-secondary">
            <i data-lucide="trash-2" style="width:16px;height:16px;"></i>
            Trash
        </a>
        @if(auth()->user()->canManage())
        <button class="btn btn-primary" onclick="openCreateModal()">
            <i data-lucide="plus" style="width:16px;height:16px;"></i>
            Tambah Expense
        </button>
        @endif
    </div>
</div>
{{-- Tabs --}}
<div class="tab-bar" id="loriExpenseMobilTabs">
</div>

<div class="card">
    <div class="card-toolbar"><div class="dt-search-slot"></div></div>
    <div class="card-content" style="padding:0;">
        <div class="table-wrap">
            <table id="loriExpenseTable" class="w-full">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Deskripsi</th>
                        <th>Kategori</th>
                        <th>Nominal</th>
                        <th>Catatan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

@include('lori-expense.modals.create')
@include('lori-expense.modals.edit')
@endsection

@push('scripts')
<script>
let table;
let editId = null;
let activeLoriExpenseMobilId = '';
const createForm  = document.getElementById('createForm');
const editForm    = document.getElementById('editForm');
const createModal = document.getElementById('createModal');
const editModal   = document.getElementById('editModal');

const categoryBadge = {
    'BBM':         'badge-danger',
    'Gaji':        'badge-info',
    'Maintenance': 'badge-warning',
    'Umum':        'badge-success',
};

// ── Mobil Tabs ────────────────────────────────────────────────────────────────
function loadLoriExpenseMobils() {
    axios.get('{{ route('mobil-master.list') }}').then(res => {
        const tabBar = document.getElementById('loriExpenseMobilTabs');
        const opts = res.data.map(m => `<option value="${m.id}">${m.name}${m.plat_nomer ? ' — '+m.plat_nomer : ''}</option>`).join('');
        
        // Add "Semua" (All) tab
        const allBtn = document.createElement('button');
        allBtn.className = 'tab active';
        allBtn.innerHTML = '<i data-lucide="list" style="width:16px;height:16px;"></i> Semua';
        allBtn.onclick = function() { switchLoriExpenseTab(this, null); };
        tabBar.appendChild(allBtn);
        
        res.data.forEach(m => {
            const btn = document.createElement('button');
            btn.className = 'tab';
            btn.dataset.mobilId = m.id;
            btn.innerHTML = `<i data-lucide="truck" style="width:16px;height:16px;"></i> ${m.plat_nomer || m.name}`;
            btn.onclick = function() { switchLoriExpenseTab(this, m.id); };
            tabBar.appendChild(btn);
        });
        lucide.createIcons();
        
        document.getElementById('createLoriExpenseMobilSelect').innerHTML = '<option value="">-- Pilih Mobil --</option>' + opts;
        document.getElementById('editLoriExpenseMobilSelect').innerHTML   = '<option value="">-- Pilih Mobil --</option>' + opts;
    });
}

function switchLoriExpenseTab(btn, mobilId) {
    document.querySelectorAll('#loriExpenseMobilTabs .tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    activeLoriExpenseMobilId = mobilId;
    table.ajax.reload(null, false);
}

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
    loadLoriExpenseMobils();

    table = $('#loriExpenseTable').DataTable({
        ajax: {
            url: '{{ route('lori-expense.data') }}',
            type: 'GET',
            data: function(d) { if (activeLoriExpenseMobilId) d.mobil_id = activeLoriExpenseMobilId; }
        },
        processing: true,
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
                                onclick="openEditModal('${row.id}','${row.date_raw}','${escHtml(row.description)}','${escHtml(row.category)}','${row.nominal_raw}','${escHtml(row.noted)}','${row.mobil_id || ''}')">
                                <i data-lucide="pencil" style="width:14px;height:14px;"></i>
                            </button>` : ''}
                            ${canDelete ? `<button class="icon-btn danger" title="Hapus"
                                onclick="deleteExpense('${row.id}','${escHtml(row.description)}')">
                                <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                            </button>` : ''}
                        </div>
                    `;
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
    setRaw(createForm.nominal, 0);
    if (activeLoriExpenseMobilId) document.getElementById('createLoriExpenseMobilSelect').value = activeLoriExpenseMobilId;
    createModal.classList.add('active');
}
function closeCreateModal() { createModal.classList.remove('active'); }

function storeExpense() {
    const payload = {
        mobil_id:    document.getElementById('createLoriExpenseMobilSelect').value || null,
        date:        createForm.date.value,
        description: createForm.description.value,
        category:    createForm.category.value,
        nominal:     getRaw(createForm.nominal),
        noted:       createForm.noted.value,
    };
    axios.post('{{ route('lori-expense.store') }}', payload)
        .then(res => { showSuccess('Berhasil', res.data.message); closeCreateModal(); table.ajax.reload(null, false); })
        .catch(err => {
            const errors = err.response?.data?.errors;
            showError('Gagal', errors ? Object.values(errors).flat().join('\n') : err.response?.data?.message || 'Terjadi kesalahan');
        });
}

// ── Edit ──────────────────────────────────────────────────────────────────────
function openEditModal(id, date, description, category, nominal, noted, mobilId) {
    editId = id;
    editForm.date.value        = date;
    editForm.description.value = description;
    editForm.category.value    = category;
    editForm.noted.value       = noted !== '-' ? noted : '';
    setRaw(editForm.nominal, nominal);
    editForm.nominal.value = parseInt(nominal) ? Currency.format(nominal) : '';
    document.getElementById('editLoriExpenseMobilSelect').value = mobilId || '';
    editModal.classList.add('active');
}
function closeEditModal() { editModal.classList.remove('active'); editId = null; }

function updateExpense() {
    const payload = {
        mobil_id:    document.getElementById('editLoriExpenseMobilSelect').value || null,
        date:        editForm.date.value,
        description: editForm.description.value,
        category:    editForm.category.value,
        nominal:     getRaw(editForm.nominal),
        noted:       editForm.noted.value,
    };
    axios.put(`/lori-expense/${editId}`, payload)
        .then(res => { showSuccess('Berhasil', res.data.message); closeEditModal(); table.ajax.reload(null, false); })
        .catch(err => {
            const errors = err.response?.data?.errors;
            showError('Gagal', errors ? Object.values(errors).flat().join('\n') : err.response?.data?.message || 'Terjadi kesalahan');
        });
}

// ── Delete ────────────────────────────────────────────────────────────────────
function deleteExpense(id, description) {
    showConfirm({
        title: 'Konfirmasi Hapus',
        message: `Yakin ingin menghapus "${description}"? Tindakan ini tidak dapat dibatalkan.`,
        type: 'danger',
        confirmText: 'Ya, Hapus',
        onConfirm: async () => {
            try {
                const res = await axios.delete(`/lori-expense/${id}`);
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
