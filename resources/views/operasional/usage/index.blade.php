@extends('layouts.app')

@section('title', 'Pemakaian Stok')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title-text">Pemakaian Stok BBM</h1>
        <p class="page-subtitle">Pencatatan pemakaian stok untuk keperluan operasional</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-secondary" onclick="openTxPrintModal()">
            <i data-lucide="printer" style="width:16px;height:16px;"></i> Print
        </button>
        @if(auth()->user()->canManage())
        <button class="btn btn-primary" onclick="openCreateModal()">
            <i data-lucide="plus" style="width:16px;height:16px;"></i>
            Tambah Pemakaian
        </button>
        @endif
    </div>
</div>

{{-- Summary Cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1.25rem;margin-bottom:1.5rem;">
    <div class="dash-stat ds-stock" style="border-radius:var(--radius-lg,.75rem);padding:1.25rem;position:relative;overflow:hidden;">
        <div class="dash-stat__header">
            <div class="dash-stat__icon"><i data-lucide="flame" style="width:20px;height:20px;"></i></div>
            <div>
                <div class="dash-stat__label">Total Pemakaian</div>
                <div class="dash-stat__value" id="usageTotal_all">0 L</div>
            </div>
        </div>
        <div class="dash-stat__bg-icon"><i data-lucide="flame" style="width:110px;height:110px;"></i></div>
    </div>
</div>

<div class="card">
    <div class="card-toolbar"><div class="dt-search-slot"></div></div>
    <div class="card-content" style="padding:0;">
        <div class="table-wrap">
            <table id="usageTable" class="w-full">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Qty (L)</th>
                        <th>Keperluan</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

{{-- Create Modal --}}
<div class="modal-overlay" id="createModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3 class="modal-title">Tambah Pemakaian Stok</h3>
            <button class="modal-close-btn" onclick="closeCreateModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="createForm" onsubmit="return false;">
                <div class="form-grid">
                    <div class="form-group full">
                        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="date" class="form-input" required>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Qty (Liter) <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" class="form-input" placeholder="0" step="0.01" min="0.01" required>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Keperluan <span class="text-danger">*</span></label>
                        <input type="text" name="keperluan" class="form-input" placeholder="Contoh: Operasional Mesin" required>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-danger" onclick="closeCreateModal()">
                <i data-lucide="x" style="width:15px;height:15px;"></i> Batal
            </button>
            <button class="btn btn-primary" onclick="storeUsage()">
                <i data-lucide="save" style="width:15px;height:15px;"></i> Simpan
            </button>
        </div>
    </div>
</div>
{{-- Edit Modal --}}
<div class="modal-overlay" id="editModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3 class="modal-title">Edit Pemakaian Stok</h3>
            <button class="modal-close-btn" onclick="closeEditModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="editForm" onsubmit="return false;">
                <div class="form-grid">
                    <div class="form-group full">
                        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="date" class="form-input" required>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Qty (Liter) <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" class="form-input" placeholder="0" step="0.01" min="0.01" required>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Keperluan <span class="text-danger">*</span></label>
                        <input type="text" name="keperluan" class="form-input" placeholder="Contoh: Operasional Mesin" required>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-danger" onclick="closeEditModal()">
                <i data-lucide="x" style="width:15px;height:15px;"></i> Batal
            </button>
            <button class="btn btn-primary" onclick="updateUsage()">
                <i data-lucide="save" style="width:15px;height:15px;"></i> Simpan
            </button>
        </div>
    </div>
</div>

@php $txSection='stock-usage'; $txHasKapal=false; $txHasMobil=false; $txHasPc=false; @endphp
@include('print._tx_filter_modal')
@endsection

@push('scripts')
<script>
let usageTable;
let editId = null;
const createForm = document.getElementById('createForm');
const createModal = document.getElementById('createModal');
const editForm = document.getElementById('editForm');
const editModal = document.getElementById('editModal');

function escHtml(s) {
    if (!s) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/'/g,"\\'");
}

function updateSummary(api) {
    const now      = new Date();
    const curYear  = now.getFullYear();
    const curMonth = now.getMonth() + 1;
    const allRows  = api.rows().data();
    let total = 0;
    for (let i = 0; i < allRows.length; i++) {
        const raw = allRows[i].date_raw || '';
        const [rowYear, rowMonth] = raw.split('-').map(Number);
        if (rowYear !== curYear || rowMonth !== curMonth) continue;
        const qty = parseFloat(allRows[i].quantity_raw) || 0;
        total += qty;
    }
    const fmt = n => new Intl.NumberFormat('id-ID').format(Math.round(n));
    document.getElementById('usageTotal_all').textContent = fmt(total) + ' L';
}

$(document).ready(function () {
    usageTable = $('#usageTable').DataTable({
        ajax: { url: '{{ route('stock-usage.data') }}', type: 'GET' },
        columns: [
            { data: 'date', render: (d, t, r) => t === 'sort' ? r.date_raw : d },
            { data: 'quantity' },
            { data: 'keperluan' },
            { data: 'created_by' },
            {
                data: null, orderable: false, searchable: false,
                render: function (data, type, row) {
                    let actions = '';
                    if (canManage) {
                        actions += `<button class="icon-btn primary" title="Edit" onclick="openEditModal('${row.id}', '${row.date_raw}', '${row.quantity_raw}', '${escHtml(row.keperluan)}')">
                            <i data-lucide="pencil" style="width:14px;height:14px;"></i>
                        </button>`;
                    }
                    if (canDelete) {
                        actions += `<button class="icon-btn danger" title="Hapus" onclick="deleteUsage('${row.id}', '${escHtml(row.keperluan)}')">
                            <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                        </button>`;
                    }
                    return actions ? `<div class="table-actions">${actions}</div>` : '';
                }
            }
        ],
        drawCallback: function () {
            updateSummary(this.api());
        }
    });
});

function openCreateModal() { createForm.reset(); createModal.classList.add('active'); }
function closeCreateModal() { createModal.classList.remove('active'); }

function openEditModal(id, dateRaw, qty, keperluan) {
    editId = id;
    editForm.date.value = dateRaw ? dateRaw.split(' ')[0] : '';
    editForm.quantity.value = qty;
    editForm.keperluan.value = keperluan;
    editModal.classList.add('active');
}
function closeEditModal() { editModal.classList.remove('active'); editId = null; }

function updateUsage() {
    if (!editForm.keperluan.value) { showError('Validasi', 'Keperluan wajib diisi.'); return; }

    const payload = {
        date:      editForm.date.value,
        quantity:  parseFloat(editForm.quantity.value),
        keperluan: editForm.keperluan.value,
    };

    axios.put(`/operasional/usage/${editId}`, payload)
        .then(res => {
            showSuccess('Berhasil', res.data.message);
            closeEditModal();
            usageTable.ajax.reload(null, false);
        })
        .catch(err => {
            const errors = err.response?.data?.errors;
            showError('Gagal', errors ? Object.values(errors).flat().join('\n') : err.response?.data?.message || 'Terjadi kesalahan');
        });
}

function storeUsage() {
    if (!createForm.keperluan.value) { showError('Validasi', 'Keperluan wajib diisi.'); return; }

    const payload = {
        date:      createForm.date.value,
        quantity:  parseFloat(createForm.quantity.value),
        keperluan: createForm.keperluan.value,
    };

    axios.post('{{ route('stock-usage.store') }}', payload)
        .then(res => {
            showSuccess('Berhasil', res.data.message);
            closeCreateModal();
            usageTable.ajax.reload(null, false);
        })
        .catch(err => {
            const errors = err.response?.data?.errors;
            showError('Gagal', errors ? Object.values(errors).flat().join('\n') : err.response?.data?.message || 'Terjadi kesalahan');
        });
}

function deleteUsage(id, label) {
    showConfirm({
        title: 'Hapus Pemakaian',
        message: `Hapus pemakaian "${label}"? Stok akan dikembalikan.`,
        type: 'danger',
        confirmText: 'Ya, Hapus',
        onConfirm: async () => {
            try {
                const res = await axios.delete(`/operasional/usage/${id}`);
                showSuccess('Berhasil', res.data.message);
                usageTable.ajax.reload(null, false);
            } catch (err) {
                showError('Gagal', err.response?.data?.message || 'Gagal menghapus');
            }
        }
    });
}
</script>
@endpush
