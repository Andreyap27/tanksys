@extends('layouts.app')

@section('title', 'Piutang')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title-text">Piutang</h1>
        <p class="page-subtitle">Kelola data piutang</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-secondary" onclick="openTxPrintModal()">
            <i data-lucide="printer" style="width:16px;height:16px;"></i> Print
        </button>
        <a href="{{ route('piutang.trash') }}" class="btn btn-secondary">
            <i data-lucide="trash-2" style="width:16px;height:16px;"></i> Trash
        </a>
        @if(auth()->user()->canManage())
        <button class="btn btn-primary" onclick="openCreateModal()">
            <i data-lucide="plus" style="width:16px;height:16px;"></i> Tambah Piutang
        </button>
        @endif
    </div>
</div>

{{-- Summary Cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1.25rem;margin-bottom:1.5rem;">
    <div class="dash-stat ds-purchase">
        <div class="dash-stat__header">
            <div class="dash-stat__icon"><i data-lucide="clock" style="width:20px;height:20px;"></i></div>
            <div>
                <div class="dash-stat__label">Pending</div>
                <div class="dash-stat__value" id="piutangPendingCard">Rp 0</div>
                <div class="dash-stat__trend flat">
                    <i data-lucide="list"></i> <span id="piutangPendingCount">0</span> piutang
                </div>
            </div>
        </div>
        <div class="dash-stat__bg-icon"><i data-lucide="clock" style="width:110px;height:110px;"></i></div>
    </div>
    <div class="dash-stat ds-expense">
        <div class="dash-stat__header">
            <div class="dash-stat__icon"><i data-lucide="check-circle" style="width:20px;height:20px;"></i></div>
            <div>
                <div class="dash-stat__label">Approved (Belum Paid)</div>
                <div class="dash-stat__value" id="piutangApprovedCard">Rp 0</div>
                <div class="dash-stat__trend flat">
                    <i data-lucide="list"></i> <span id="piutangApprovedCount">0</span> piutang
                </div>
            </div>
        </div>
        <div class="dash-stat__bg-icon"><i data-lucide="check-circle" style="width:110px;height:110px;"></i></div>
    </div>
    <div class="dash-stat ds-profit">
        <div class="dash-stat__header">
            <div class="dash-stat__icon"><i data-lucide="banknote" style="width:20px;height:20px;"></i></div>
            <div>
                <div class="dash-stat__label">Total Paid</div>
                <div class="dash-stat__value" id="piutangPaidCard">Rp 0</div>
                <div class="dash-stat__trend flat">
                    <i data-lucide="list"></i> <span id="piutangPaidCount">0</span> piutang
                </div>
            </div>
        </div>
        <div class="dash-stat__bg-icon"><i data-lucide="banknote" style="width:110px;height:110px;"></i></div>
    </div>
</div>

<div class="card">
    <div class="card-toolbar">
        <div class="dt-search-slot"></div>
        <select id="statusFilter" class="form-select" style="width:auto;min-width:130px;">
            <option value="">Semua Status</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="paid">Paid</option>
            <option value="rejected">Rejected</option>
        </select>
    </div>
    <div class="card-content" style="padding:0;">
        <div class="table-wrap">
            <table id="piutangTable" class="w-full">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama</th>
                        <th>Deskripsi</th>
                        <th>Nominal</th>
                        <th>Note</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

@include('piutang.modals.create')
@include('piutang.modals.edit')
@php $txSection='piutang'; $txHasKapal=false; $txHasMobil=false; $txHasPc=false; @endphp
@include('print._tx_filter_modal')
@endsection

@push('scripts')
<script>
let piutangTable;
let piutangEditId = null;
const piutangCreateModal = document.getElementById('piutangCreateModal');
const piutangEditModal   = document.getElementById('piutangEditModal');
const piutangCreateForm  = document.getElementById('piutangCreateForm');
const piutangEditForm    = document.getElementById('piutangEditForm');

function setRaw(el, raw) { el.dataset.raw = raw; }
function getRaw(el)      { return parseFloat(el.dataset.raw) || 0; }

document.querySelectorAll('.fmt-price').forEach(el => {
    el.addEventListener('input', function () {
        const raw = this.value.replace(/[^0-9]/g, '');
        this.value = raw; setRaw(this, raw);
    });
    el.addEventListener('blur', function () {
        const raw = parseInt(this.value.replace(/[^0-9]/g, '')) || 0;
        setRaw(this, raw);
        this.value = raw ? Currency.format(raw) : '';
    });
    el.addEventListener('focus', function () { this.value = this.dataset.raw || ''; });
});

function escHtml(str) {
    if (str == null) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/'/g,"\\'").replace(/"/g,'&quot;');
}

function updateSummary(api) {
    const rows = api.rows({ search: 'applied' }).data();
    let pending = 0, pendingC = 0, approved = 0, approvedC = 0, paid = 0, paidC = 0;
    for (let i = 0; i < rows.length; i++) {
        const amt = parseFloat(rows[i].nominal_raw) || 0;
        if (rows[i].status === 'pending')  { pending  += amt; pendingC++;  }
        if (rows[i].status === 'approved') { approved += amt; approvedC++; }
        if (rows[i].status === 'paid')     { paid     += amt; paidC++;     }
    }
    document.getElementById('piutangPendingCard').textContent   = 'Rp ' + Currency.number(pending);
    document.getElementById('piutangPendingCount').textContent  = pendingC;
    document.getElementById('piutangApprovedCard').textContent  = 'Rp ' + Currency.number(approved);
    document.getElementById('piutangApprovedCount').textContent = approvedC;
    document.getElementById('piutangPaidCard').textContent      = 'Rp ' + Currency.number(paid);
    document.getElementById('piutangPaidCount').textContent     = paidC;
}

$(document).ready(function () {
    piutangTable = $('#piutangTable').DataTable({
        ajax: { url: '{{ route('piutang.data') }}', type: 'GET' },
        columns: [
            { data: 'date', render: (d, t, row) => (t === 'sort' || t === 'type') ? row.date_raw : d },
            { data: 'nama' },
            { data: 'description' },
            { data: 'nominal', render: d => Currency.symbol + ' ' + d },
            { data: 'note', render: d => d && d !== '-' ? d : '<span class="text-muted">-</span>' },
            { data: 'status', render: d => ({
                pending:  '<span class="badge badge-warning">Pending</span>',
                approved: '<span class="badge badge-info">Approved</span>',
                rejected: '<span class="badge badge-danger">Rejected</span>',
                paid:     '<span class="badge badge-success">Paid</span>',
            })[d] || d },
            { data: null, orderable: false, searchable: false,
              render: function (data, type, row) {
                const editBtn = canManage
                    ? `<button class="icon-btn primary" title="Edit"
                           onclick="openEditModal('${row.id}','${row.date_raw}','${escHtml(row.nama)}','${escHtml(row.description)}','${row.nominal_raw}','${escHtml(row.note)}')">
                           <i data-lucide="pencil" style="width:14px;height:14px;"></i>
                       </button>` : '';
                const approveBtn = canApprove && row.status === 'pending'
                    ? `<button class="icon-btn success" title="Approve" onclick="approvePiutang('${row.id}')">
                           <i data-lucide="check-circle" style="width:14px;height:14px;"></i>
                       </button>
                       <button class="icon-btn danger" title="Reject" onclick="rejectPiutang('${row.id}')">
                           <i data-lucide="x-circle" style="width:14px;height:14px;"></i>
                       </button>` : '';
                const payBtn = canApprove && row.status === 'approved'
                    ? `<button class="icon-btn success" title="Tandai Paid" onclick="payPiutang('${row.id}','${escHtml(row.description)}')">
                           <i data-lucide="banknote" style="width:14px;height:14px;"></i>
                       </button>` : '';
                const deleteBtn = canDelete
                    ? `<button class="icon-btn danger" title="Hapus" onclick="deletePiutang('${row.id}','${escHtml(row.description)}')">
                           <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                       </button>` : '';
                return `<div class="table-actions">${editBtn}${approveBtn}${payBtn}${deleteBtn}</div>`;
              }
            }
        ],
        order: [[0, 'desc']],
        drawCallback: function () { updateSummary(this.api()); }
    });

    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'piutangTable') return true;
        const filterVal = document.getElementById('statusFilter').value;
        if (!filterVal) return true;
        return piutangTable.row(dataIndex).data()?.status === filterVal;
    });

    document.getElementById('statusFilter').addEventListener('change', () => piutangTable.draw());
});

// ── Create ────────────────────────────────────────────────────────────────────
function openCreateModal() { piutangCreateForm.reset(); piutangCreateModal.classList.add('active'); }
function closePiutangCreateModal() { piutangCreateModal.classList.remove('active'); }

function storePiutang() {
    axios.post('{{ route('piutang.store') }}', {
        date:        piutangCreateForm.date.value,
        nama:        piutangCreateForm.nama.value,
        description: piutangCreateForm.description.value,
        nominal:     getRaw(piutangCreateForm.nominal),
        note:        piutangCreateForm.note.value,
    }).then(res => {
        showSuccess('Berhasil', res.data.message);
        closePiutangCreateModal();
        piutangTable.ajax.reload(null, false);
    }).catch(err => {
        const errors = err.response?.data?.errors;
        showError('Gagal', errors ? Object.values(errors).flat().join('\n') : err.response?.data?.message || 'Terjadi kesalahan');
    });
}

// ── Edit ──────────────────────────────────────────────────────────────────────
function openEditModal(id, date, nama, description, nominal, note) {
    piutangEditId = id;
    piutangEditForm.date.value        = date;
    piutangEditForm.nama.value        = nama;
    piutangEditForm.description.value = description;
    piutangEditForm.note.value        = note !== '-' ? note : '';
    setRaw(piutangEditForm.nominal, nominal);
    piutangEditForm.nominal.value = parseInt(nominal) ? Currency.format(nominal) : '';
    piutangEditModal.classList.add('active');
}
function closePiutangEditModal() { piutangEditModal.classList.remove('active'); piutangEditId = null; }

function updatePiutang() {
    axios.put(`/piutang/${piutangEditId}`, {
        date:        piutangEditForm.date.value,
        nama:        piutangEditForm.nama.value,
        description: piutangEditForm.description.value,
        nominal:     getRaw(piutangEditForm.nominal),
        note:        piutangEditForm.note.value,
    }).then(res => {
        showSuccess('Berhasil', res.data.message);
        closePiutangEditModal();
        piutangTable.ajax.reload(null, false);
    }).catch(err => {
        const errors = err.response?.data?.errors;
        showError('Gagal', errors ? Object.values(errors).flat().join('\n') : err.response?.data?.message || 'Terjadi kesalahan');
    });
}

// ── Approve / Reject / Pay / Delete ──────────────────────────────────────────
function approvePiutang(id) {
    showConfirm({ title: 'Konfirmasi Approve', message: 'Yakin ingin menyetujui piutang ini?',
        type: 'success', confirmText: 'Ya, Setujui',
        onConfirm: async () => {
            try { const res = await axios.post(`/piutang/${id}/approve`); showSuccess('Berhasil', res.data.message); piutangTable.ajax.reload(null, false); }
            catch (err) { showError('Gagal', err.response?.data?.message || 'Gagal'); }
        }
    });
}
function rejectPiutang(id) {
    showConfirm({ title: 'Konfirmasi Reject', message: 'Yakin ingin menolak piutang ini?',
        type: 'danger', confirmText: 'Ya, Tolak',
        onConfirm: async () => {
            try { const res = await axios.post(`/piutang/${id}/reject`); showSuccess('Berhasil', res.data.message); piutangTable.ajax.reload(null, false); }
            catch (err) { showError('Gagal', err.response?.data?.message || 'Gagal'); }
        }
    });
}
function payPiutang(id, description) {
    showConfirm({ title: 'Tandai Paid', message: `Tandai piutang "${description}" sebagai sudah dibayar?`,
        type: 'success', confirmText: 'Ya, Paid',
        onConfirm: async () => {
            try { const res = await axios.post(`/piutang/${id}/paid`); showSuccess('Berhasil', res.data.message); piutangTable.ajax.reload(null, false); }
            catch (err) { showError('Gagal', err.response?.data?.message || 'Gagal'); }
        }
    });
}
function deletePiutang(id, description) {
    showConfirm({ title: 'Konfirmasi Hapus', message: `Hapus piutang "${description}"?`,
        type: 'danger', confirmText: 'Ya, Hapus',
        onConfirm: async () => {
            try { const res = await axios.delete(`/piutang/${id}`); showSuccess('Berhasil', res.data.message); piutangTable.ajax.reload(null, false); }
            catch (err) { showError('Gagal', err.response?.data?.message || 'Gagal'); }
        }
    });
}
</script>
@endpush
