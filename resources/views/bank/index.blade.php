@extends('layouts.app')

@section('title', 'Bank In/Out')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title-text">Bank In / Out</h1>
        <p class="page-subtitle">Kelola transaksi kredit dan debit bank</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('bank.trash') }}" class="btn btn-secondary">
            <i data-lucide="trash-2" style="width:16px;height:16px;"></i>
            Trash
        </a>
        <button class="btn btn-secondary" onclick="openDateFilterModal()">
            <i data-lucide="printer" style="width:16px;height:16px;"></i>
            Print
        </button>
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
                <div class="dash-stat__label">Total Income (Kredit)</div>
                <div class="dash-stat__value" id="bankIncomeCard">Rp 0</div>
                <div class="dash-stat__trend flat">
                    <i data-lucide="list"></i> <span id="bankIncomeCountCard">0</span> transaksi
                </div>
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
                <div class="dash-stat__label">Total Expenses (Debit)</div>
                <div class="dash-stat__value" id="bankExpenseCard">Rp 0</div>
                <div class="dash-stat__trend flat">
                    <i data-lucide="list"></i> <span id="bankExpenseCountCard">0</span> transaksi
                </div>
            </div>
        </div>
        <div class="dash-stat__bg-icon">
            <i data-lucide="arrow-down-circle" style="width:110px;height:110px;"></i>
        </div>
    </div>
    <div class="dash-stat" id="bankBalanceCardWrapper">
        <div class="dash-stat__header">
            <div class="dash-stat__icon">
                <i data-lucide="scale" style="width:20px;height:20px;"></i>
            </div>
            <div>
                <div class="dash-stat__label">Balance</div>
                <div class="dash-stat__value" id="bankBalanceCard">Rp 0</div>
            </div>
        </div>
        <div class="dash-stat__bg-icon">
            <i data-lucide="scale" style="width:110px;height:110px;"></i>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-toolbar"><div class="dt-search-slot"></div></div>
    <div class="card-content" style="padding:0;">
        <div class="table-wrap">
            <table id="bankTable" class="w-full">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Deskripsi</th>
                        <th>Note</th>
                        <th>Job</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

@include('bank.modals.create')
@include('bank.modals.edit')

{{-- Date Filter Modal --}}
<div class="modal-overlay" id="dateFilterModal">
    <div class="modal-box" style="max-width:26rem;">
        <div class="modal-header">
            <h3 class="modal-title">Filter Print</h3>
            <button class="modal-close-btn" onclick="closeDateFilterModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group" style="margin-bottom:1rem;">
                <label class="form-label">Tanggal Dari <span class="text-danger">*</span></label>
                <input type="date" id="printDateFrom" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">Tanggal Sampai <span class="text-danger">*</span></label>
                <input type="date" id="printDateTo" class="form-input">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-danger" onclick="closeDateFilterModal()">
                <i data-lucide="x" style="width:15px;height:15px;"></i>
                Batal
            </button>
            <button class="btn btn-primary" onclick="loadBankPrintPreview()">
                <i data-lucide="printer" style="width:15px;height:15px;"></i>
                Preview
            </button>
        </div>
    </div>
</div>

{{-- Print Preview Modal --}}
<div class="modal-overlay" id="printModal">
    <div class="modal-box" style="max-width:72rem;width:95%;height:90vh;display:flex;flex-direction:column;overflow:hidden;">
        <div class="modal-header">
            <h3 class="modal-title">Preview Print — Bank In/Out</h3>
            <button class="modal-close-btn" onclick="closePrintModal()">&times;</button>
        </div>
        <div style="flex:1;overflow:hidden;min-height:0;">
            <iframe id="printFrame" src="" style="width:100%;height:100%;border:none;display:block;"></iframe>
        </div>
        <div class="modal-footer">
            <button class="btn" onclick="closePrintModal()">
                <i data-lucide="x" style="width:15px;height:15px;"></i>
                Tutup
            </button>
            <button class="btn btn-primary" onclick="document.getElementById('printFrame').contentWindow.print()">
                <i data-lucide="printer" style="width:15px;height:15px;"></i>
                Print
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (typeof lucide !== 'undefined') lucide.createIcons();
});

let table;
let editId = null;
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

// ── Summary ───────────────────────────────────────────────────────────────────
function updateBankSummary(api) {
    const rows = api.rows({ search: 'applied' }).data();
    let income = 0, incomeCount = 0, expense = 0, expenseCount = 0;
    for (let i = 0; i < rows.length; i++) {
        const amt = parseFloat(rows[i].amount_raw) || 0;
        if (rows[i].type === 'in') {
            income += amt;
            incomeCount++;
        } else {
            expense += amt;
            expenseCount++;
        }
    }
    const balance = income - expense;

    document.getElementById('bankIncomeCard').textContent       = 'Rp ' + Currency.number(income);
    document.getElementById('bankIncomeCountCard').textContent  = incomeCount;
    document.getElementById('bankExpenseCard').textContent      = 'Rp ' + Currency.number(expense);
    document.getElementById('bankExpenseCountCard').textContent = expenseCount;
    document.getElementById('bankBalanceCard').textContent      = 'Rp ' + Currency.number(balance);

    const wrapper = document.getElementById('bankBalanceCardWrapper');
    wrapper.classList.remove('ds-profit', 'ds-loss');
    wrapper.classList.add(balance >= 0 ? 'ds-profit' : 'ds-loss');
}

// ── DataTable ─────────────────────────────────────────────────────────────────
$(document).ready(function () {
    table = $('#bankTable').DataTable({
        ajax: {
            url: '{{ route('bank.data') }}',
            type: 'GET',
        },
        processing: true,
        columns: [
            {
                data: 'date',
                render: (data, type, row) => (type === 'sort' || type === 'type') ? row.date_raw : data,
            },
            { data: 'description' },
            { data: 'note' },
            { data: 'job' },
            {
                data: 'type',
                render: (data) => data === 'in'
                    ? '<span class="badge badge-success">In (Kredit)</span>'
                    : '<span class="badge badge-danger">Out (Debit)</span>',
            },
            {
                data: 'amount',
                render: (data, type, row) => {
                    const color = row.type === 'in' ? 'color:#16a34a;font-weight:600;' : 'color:#dc2626;font-weight:600;';
                    return `<span style="${color}">${Currency.symbol} ${data}</span>`;
                },
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    return `
                        <div class="table-actions">
                            ${canManage ? `<button class="icon-btn primary" title="Edit"
                                onclick="openEditModal('${row.id}','${row.date_raw}','${row.type}','${row.amount_raw}','${escHtml(row.description)}','${row.note}','${escHtml(row.job)}')">
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
        drawCallback: function () {
            lucide.createIcons();
            updateBankSummary(this.api());
        }
    });
});

// ── Create ────────────────────────────────────────────────────────────────────
function openCreateModal() {
    createForm.reset();
    setRaw(createForm.amount, 0);
    createModal.classList.add('active');
}
function closeCreateModal() { createModal.classList.remove('active'); }

function storeTrx() {
    const payload = {
        date:        createForm.date.value,
        type:        createForm.type.value,
        amount:      getRaw(createForm.amount),
        description: createForm.description.value,
        note:        createForm.note.value,
        job:         createForm.job.value,
    };
    axios.post('{{ route('bank.store') }}', payload)
        .then(res => {
            showSuccess('Berhasil', res.data.message);
            closeCreateModal();
            table.ajax.reload(null, false);
        })
        .catch(err => {
            const errors = err.response?.data?.errors;
            showError('Gagal', errors ? Object.values(errors).flat().join('\n') : err.response?.data?.message || 'Terjadi kesalahan');
        });
}

// ── Edit ──────────────────────────────────────────────────────────────────────
function openEditModal(id, date, type, amount, description, note, job) {
    editId = id;
    editForm.date.value        = date;
    editForm.type.value        = type;
    editForm.description.value = description;
    editForm.note.value        = note;
    editForm.job.value         = job;
    setRaw(editForm.amount, amount);
    editForm.amount.value = parseInt(amount) ? Currency.format(amount) : '';
    editModal.classList.add('active');
}
function closeEditModal() { editModal.classList.remove('active'); editId = null; }

function updateTrx() {
    const payload = {
        date:        editForm.date.value,
        type:        editForm.type.value,
        amount:      getRaw(editForm.amount),
        description: editForm.description.value,
        note:        editForm.note.value,
        job:         editForm.job.value,
    };
    axios.put(`/bank/${editId}`, payload)
        .then(res => {
            showSuccess('Berhasil', res.data.message);
            closeEditModal();
            table.ajax.reload(null, false);
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
        message: `Yakin ingin menghapus transaksi "${description}"? Tindakan ini tidak dapat dibatalkan.`,
        type: 'danger',
        confirmText: 'Ya, Hapus',
        onConfirm: async () => {
            try {
                const res = await axios.delete(`/bank/${id}`);
                showSuccess('Berhasil', res.data.message);
                table.ajax.reload(null, false);
            } catch (err) {
                showError('Gagal', err.response?.data?.message || 'Gagal menghapus data');
            }
        }
    });
}

// ── Print ─────────────────────────────────────────────────────────────────────
function openDateFilterModal() {
    const today = new Date().toISOString().slice(0, 10);
    const firstOfMonth = today.slice(0, 7) + '-01';
    document.getElementById('printDateFrom').value = firstOfMonth;
    document.getElementById('printDateTo').value   = today;
    document.getElementById('dateFilterModal').classList.add('active');
}
function closeDateFilterModal() {
    document.getElementById('dateFilterModal').classList.remove('active');
}
function closePrintModal() {
    document.getElementById('printModal').classList.remove('active');
    document.getElementById('printFrame').src = '';
}
function loadBankPrintPreview() {
    const dateFrom = document.getElementById('printDateFrom').value;
    const dateTo   = document.getElementById('printDateTo').value;
    if (!dateFrom || !dateTo) { showError('Gagal', 'Harap isi tanggal dari dan sampai.'); return; }
    if (dateFrom > dateTo)    { showError('Gagal', 'Tanggal dari tidak boleh lebih besar dari tanggal sampai.'); return; }
    closeDateFilterModal();
    const url = '{{ route('bank.print') }}?date_from=' + dateFrom + '&date_to=' + dateTo;
    document.getElementById('printFrame').src = url;
    document.getElementById('printModal').classList.add('active');
}
</script>
@endpush
