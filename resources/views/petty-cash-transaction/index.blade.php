@extends('layouts.app')

@section('title', 'Transaksi Petty Cash')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title-text">Transaksi Petty Cash</h1>
        <p class="page-subtitle">Kelola transaksi kredit dan debit petty cash</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-secondary" onclick="openTxPrintModal()">
            <i data-lucide="printer" style="width:16px;height:16px;"></i> Print
        </button>
        <a href="{{ route('petty-cash-transaction.trash') }}" class="btn btn-secondary">
            <i data-lucide="trash-2" style="width:16px;height:16px;"></i>
            Trash
        </a>
        @if(auth()->user()->canManage())
        <button class="btn btn-primary" onclick="openCreateModal()">
            <i data-lucide="plus" style="width:16px;height:16px;"></i>
            Tambah Transaksi
        </button>
        @endif
    </div>
</div>

{{-- Summary Cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1.25rem;margin-bottom:1.5rem;">
    <div class="dash-stat ds-profit">
        <div class="dash-stat__header">
            <div class="dash-stat__icon">
                <i data-lucide="arrow-up-circle" style="width:20px;height:20px;"></i>
            </div>
            <div>
                <div class="dash-stat__label">Total In (Kredit)</div>
                <div class="dash-stat__value" id="pcKreditCard">Rp 0</div>
            </div>
        </div>
        <div class="dash-stat__bg-icon">
            <i data-lucide="arrow-up-circle" style="width:110px;height:110px;"></i>
        </div>
    </div>
    <div class="dash-stat ds-expense">
        <div class="dash-stat__header">
            <div class="dash-stat__icon">
                <i data-lucide="arrow-down-circle" style="width:20px;height:20px;"></i>
            </div>
            <div>
                <div class="dash-stat__label">Total Out (Debit)</div>
                <div class="dash-stat__value" id="pcDebitCard">Rp 0</div>
            </div>
        </div>
        <div class="dash-stat__bg-icon">
            <i data-lucide="arrow-down-circle" style="width:110px;height:110px;"></i>
        </div>
    </div>
    <div class="dash-stat" id="pcBalanceWrapper">
        <div class="dash-stat__header">
            <div class="dash-stat__icon">
                <i data-lucide="scale" style="width:20px;height:20px;"></i>
            </div>
            <div>
                <div class="dash-stat__label">Balance</div>
                <div class="dash-stat__value" id="pcBalanceCard">Rp 0</div>
            </div>
        </div>
        <div class="dash-stat__bg-icon">
            <i data-lucide="scale" style="width:110px;height:110px;"></i>
        </div>
    </div>
</div>

{{-- Tabs --}}
<div class="tab-bar" id="pettyCashTabs"></div>

<div class="card">
    <div class="card-toolbar"><div class="dt-search-slot"></div></div>
    <div class="card-content" style="padding:0;">
        <div class="table-wrap">
            <table id="pettyCashTrxTable" class="w-full">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Petty Cash</th>
                        <th>Deskripsi</th>
                        <th>Amount</th>
                        <th>Catatan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

@include('petty-cash-transaction.modals.create')
@include('petty-cash-transaction.modals.edit')
@php $txSection='petty-cash'; $txHasKapal=false; $txHasMobil=false; $txHasPc=true; @endphp
@include('print._tx_filter_modal')
@endsection

@push('scripts')
<script>
let table;
let editId = null;
let activePcId = '';
const createForm  = document.getElementById('createForm');
const editForm    = document.getElementById('editForm');
const createModal = document.getElementById('createModal');
const editModal   = document.getElementById('editModal');

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

function escHtml(str) {
    if (str == null) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/'/g,"\\'").replace(/"/g,'&quot;');
}

// ── Tabs ──────────────────────────────────────────────────────────────────────
function loadPettyCashTabs() {
    axios.get('{{ route('petty-cash.list') }}').then(res => {
        const tabBar = document.getElementById('pettyCashTabs');
        const opts   = res.data.map(p => `<option value="${p.id}">${p.name}</option>`).join('');

        const allBtn = document.createElement('button');
        allBtn.className = 'tab active';
        allBtn.innerHTML = '<i data-lucide="list" style="width:16px;height:16px;"></i> Semua';
        allBtn.onclick = function () { switchPcTab(this, null); };
        tabBar.appendChild(allBtn);

        res.data.forEach(p => {
            const btn = document.createElement('button');
            btn.className = 'tab';
            btn.dataset.pcId = p.id;
            btn.innerHTML = `<i data-lucide="wallet" style="width:16px;height:16px;"></i> ${p.name}`;
            btn.onclick = function () { switchPcTab(this, p.id); };
            tabBar.appendChild(btn);
        });
        lucide.createIcons();

        document.getElementById('createPettyCashSelect').innerHTML = '<option value="">-- Pilih Petty Cash --</option>' + opts;
        document.getElementById('editPettyCashSelect').innerHTML   = '<option value="">-- Pilih Petty Cash --</option>' + opts;
    });
}

function switchPcTab(btn, pcId) {
    document.querySelectorAll('#pettyCashTabs .tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    activePcId = pcId;
    refreshSummary(pcId);
    table.ajax.reload(null, false);
}

function refreshSummary(pcId) {
    const _n = new Date();
    const params = { month: _n.getMonth() + 1, year: _n.getFullYear() };
    if (pcId) params.petty_cash_id = pcId;
    axios.get('{{ route('petty-cash-transaction.summary') }}', { params }).then(res => {
        document.getElementById('pcKreditCard').textContent  = 'Rp ' + Currency.number(res.data.kredit  || 0);
        document.getElementById('pcDebitCard').textContent   = 'Rp ' + Currency.number(res.data.debit   || 0);
        document.getElementById('pcBalanceCard').textContent = 'Rp ' + Currency.number(res.data.balance || 0);
        const wrapper = document.getElementById('pcBalanceWrapper');
        wrapper.classList.remove('ds-profit', 'ds-loss');
        wrapper.classList.add((res.data.balance || 0) >= 0 ? 'ds-profit' : 'ds-loss');
    });
}

// ── DataTable ─────────────────────────────────────────────────────────────────
$(document).ready(function () {
    loadPettyCashTabs();
    refreshSummary(null);

    table = $('#pettyCashTrxTable').DataTable({
        ajax: {
            url: '{{ route('petty-cash-transaction.data') }}',
            type: 'GET',
            data: function (d) {
                if (activePcId) d.petty_cash_id = activePcId;
            }
        },
        processing: true,
        columns: [
            {
                data: 'date',
                render: (data, type, row) => (type === 'sort' || type === 'type') ? row.date_raw : data,
            },
            { data: 'petty_cash_name' },
            { data: 'description' },
            {
                data: 'amount',
                render: (data, type, row) => {
                    const color = row.type === 'in' ? 'color:#16a34a;font-weight:600;' : 'color:#dc2626;font-weight:600;';
                    return `<span style="${color}">${Currency.symbol} ${data}</span>`;
                },
            },
            {
                data: 'note',
                render: (data) => data && data !== '-' ? data : '<span class="text-muted">-</span>',
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    return `
                        <div class="table-actions">
                            ${canManage ? `<button class="icon-btn primary" title="Edit"
                                onclick="openEditModal('${row.id}','${row.date_raw}','${row.type}','${row.amount_raw}','${escHtml(row.description)}','${escHtml(row.note)}','${row.petty_cash_id || ''}')">
                                <i data-lucide="pencil" style="width:14px;height:14px;"></i>
                            </button>` : ''}
                            ${canDelete ? `<button class="icon-btn danger" title="Hapus"
                                onclick="deleteTrx('${row.id}','${escHtml(row.description)}')">
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

// ── Create ────────────────────────────────────────────────────────────────────
function openCreateModal() {
    createForm.reset();
    setRaw(createForm.amount, 0);
    if (activePcId) document.getElementById('createPettyCashSelect').value = activePcId;
    createModal.classList.add('active');
}
function closeCreateModal() { createModal.classList.remove('active'); }

function storeTrx() {
    const payload = {
        petty_cash_id: document.getElementById('createPettyCashSelect').value || null,
        type:          createForm.type.value,
        date:          createForm.date.value,
        description:   createForm.description.value,
        amount:        getRaw(createForm.amount),
        note:          createForm.note.value,
    };
    axios.post('{{ route('petty-cash-transaction.store') }}', payload)
        .then(res => {
            showSuccess('Berhasil', res.data.message);
            closeCreateModal();
            table.ajax.reload(null, false);
            refreshSummary(activePcId);
        })
        .catch(err => {
            const errors = err.response?.data?.errors;
            showError('Gagal', errors ? Object.values(errors).flat().join('\n') : err.response?.data?.message || 'Terjadi kesalahan');
        });
}

// ── Edit ──────────────────────────────────────────────────────────────────────
function openEditModal(id, date, type, amount, description, note, pcId) {
    editId = id;
    editForm.date.value        = date;
    editForm.type.value        = type;
    editForm.description.value = description;
    editForm.note.value        = note !== '-' ? note : '';
    setRaw(editForm.amount, amount);
    editForm.amount.value = parseInt(amount) ? Currency.format(amount) : '';
    document.getElementById('editPettyCashSelect').value = pcId || '';
    editModal.classList.add('active');
}
function closeEditModal() { editModal.classList.remove('active'); editId = null; }

function updateTrx() {
    const payload = {
        petty_cash_id: document.getElementById('editPettyCashSelect').value || null,
        type:          editForm.type.value,
        date:          editForm.date.value,
        description:   editForm.description.value,
        amount:        getRaw(editForm.amount),
        note:          editForm.note.value,
    };
    axios.put(`/petty-cash-transaction/${editId}`, payload)
        .then(res => {
            showSuccess('Berhasil', res.data.message);
            closeEditModal();
            table.ajax.reload(null, false);
            refreshSummary(activePcId);
        })
        .catch(err => {
            const errors = err.response?.data?.errors;
            showError('Gagal', errors ? Object.values(errors).flat().join('\n') : err.response?.data?.message || 'Terjadi kesalahan');
        });
}

// ── Delete ────────────────────────────────────────────────────────────────────
function deleteTrx(id, description) {
    showConfirm({
        title: 'Konfirmasi Hapus',
        message: `Yakin ingin menghapus transaksi "${description}"?`,
        type: 'danger',
        confirmText: 'Ya, Hapus',
        onConfirm: async () => {
            try {
                const res = await axios.delete(`/petty-cash-transaction/${id}`);
                showSuccess('Berhasil', res.data.message);
                table.ajax.reload(null, false);
                refreshSummary(activePcId);
            } catch (err) {
                showError('Gagal', err.response?.data?.message || 'Gagal menghapus data');
            }
        }
    });
}
</script>
@endpush
