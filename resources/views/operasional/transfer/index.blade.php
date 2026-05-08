@extends('layouts.app')

@section('title', 'Transfer Stok')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title-text">Transfer Stok BBM</h1>
        <p class="page-subtitle">Pindah stok antar kapal</p>
    </div>
    <div class="page-actions">
        @if(auth()->user()->canManage())
        <button class="btn btn-primary" onclick="openCreateModal()">
            <i data-lucide="plus" style="width:16px;height:16px;"></i>
            Tambah Transfer
        </button>
        @endif
    </div>
</div>

{{-- Summary Cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1.25rem;margin-bottom:1.5rem;">
    @foreach(['merah'=>['#dc2626','rgba(220,38,38,0.08)'],'biru'=>['#2563eb','rgba(37,99,235,0.08)'],'kuning'=>['#ca8a04','rgba(202,138,4,0.08)']] as $w=>[$color,$bg])
    <div class="dash-stat" style="background:{{$bg}};border-radius:var(--radius-lg,.75rem);padding:1.25rem;position:relative;overflow:hidden;">
        <div class="dash-stat__header">
            <div class="dash-stat__icon" style="background:{{$bg}};color:{{$color}};"><i data-lucide="arrow-right-left" style="width:20px;height:20px;"></i></div>
            <div>
                <div class="dash-stat__label">Transfer {{ ucfirst($w) }}</div>
                <div class="dash-stat__value" id="transferTotal_{{$w}}" style="color:{{$color}};">0 L</div>
            </div>
        </div>
        <div class="dash-stat__bg-icon" style="color:{{$color}};"><i data-lucide="arrow-right-left" style="width:110px;height:110px;"></i></div>
    </div>
    @endforeach
</div>

<div class="card">
    <div class="card-toolbar"><div class="dt-search-slot"></div></div>
    <div class="card-content" style="padding:0;">
        <div class="table-wrap">
            <table id="transferTable" class="w-full">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Dari Kapal</th>
                        <th>Ke Kapal</th>
                        <th>Warna</th>
                        <th>Qty (L)</th>
                        <th>Note</th>
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
            <h3 class="modal-title">Tambah Transfer Stok</h3>
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
                        <label class="form-label">Dari Kapal <span class="text-danger">*</span></label>
                        <select name="from_kapal_id" id="fromKapalSelect" class="form-select" required>
                            <option value="">-- Pilih Kapal Asal --</option>
                        </select>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Ke Kapal <span class="text-danger">*</span></label>
                        <select name="to_kapal_id" id="toKapalSelect" class="form-select" required>
                            <option value="">-- Pilih Kapal Tujuan --</option>
                        </select>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Warna BBM <span class="text-danger">*</span></label>
                        <select name="warna" class="form-select" required>
                            <option value="">-- Pilih Warna --</option>
                            <option value="merah">Merah</option>
                            <option value="biru">Biru</option>
                            <option value="kuning">Kuning</option>
                        </select>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Qty (Liter) <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" class="form-input" placeholder="0" step="0.01" min="0.01" required>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Note</label>
                        <textarea name="note" class="form-textarea" rows="2" placeholder="Catatan transfer"></textarea>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-danger" onclick="closeCreateModal()">
                <i data-lucide="x" style="width:15px;height:15px;"></i> Batal
            </button>
            <button class="btn btn-primary" onclick="storeTransfer()">
                <i data-lucide="save" style="width:15px;height:15px;"></i> Simpan
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let transferTable;
const createForm  = document.getElementById('createForm');
const createModal = document.getElementById('createModal');

const WARNA_BADGE = {
    merah:  '<span class="badge" style="background:#dc2626;color:#fff;">Merah</span>',
    biru:   '<span class="badge" style="background:#2563eb;color:#fff;">Biru</span>',
    kuning: '<span class="badge" style="background:#ca8a04;color:#fff;">Kuning</span>',
};

function escHtml(s) {
    if (!s) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/'/g,"\\'");
}

function loadKapals() {
    axios.get('{{ route('kapal.list') }}').then(res => {
        const opts = res.data.map(k => `<option value="${k.id}">${k.code} — ${k.name}</option>`).join('');
        document.getElementById('fromKapalSelect').innerHTML = '<option value="">-- Pilih Kapal Asal --</option>' + opts;
        document.getElementById('toKapalSelect').innerHTML   = '<option value="">-- Pilih Kapal Tujuan --</option>' + opts;
    });
}

function updateSummary(api) {
    const totals = { merah: 0, biru: 0, kuning: 0 };
    const rows = api.rows({ search: 'applied' }).data();
    for (let i = 0; i < rows.length; i++) {
        const w = rows[i].warna;
        if (totals[w] !== undefined) totals[w] += parseFloat(rows[i].quantity_raw) || 0;
    }
    ['merah','biru','kuning'].forEach(w => {
        document.getElementById(`transferTotal_${w}`).textContent =
            new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2 }).format(totals[w]) + ' L';
    });
}

$(document).ready(function () {
    loadKapals();

    transferTable = $('#transferTable').DataTable({
        ajax: { url: '{{ route('stock-transfer.data') }}', type: 'GET' },
        columns: [
            { data: 'date' },
            { data: 'from_kapal' },
            { data: 'to_kapal' },
            { data: 'warna', render: d => WARNA_BADGE[d] || d },
            { data: 'quantity' },
            { data: 'note', render: d => d || '<span class="text-muted">-</span>' },
            { data: 'created_by' },
            {
                data: null, orderable: false, searchable: false,
                render: function (data, type, row) {
                    if (!canDelete) return '';
                    return `<div class="table-actions">
                        <button class="icon-btn danger" title="Hapus"
                            onclick="deleteTransfer('${row.id}', '${escHtml(row.from_kapal)} → ${escHtml(row.to_kapal)}')">
                            <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                        </button>
                    </div>`;
                }
            }
        ],
        drawCallback: function () {
            updateSummary(this.api());
        }
    });
});

function openCreateModal() {
    createForm.reset();
    createModal.classList.add('active');
}

function closeCreateModal() {
    createModal.classList.remove('active');
}

function storeTransfer() {
    const fromId = createForm.from_kapal_id.value;
    const toId   = createForm.to_kapal_id.value;

    if (!fromId || !toId)        { showError('Validasi', 'Kapal asal dan tujuan wajib dipilih.'); return; }
    if (fromId === toId)         { showError('Validasi', 'Kapal asal dan tujuan tidak boleh sama.'); return; }
    if (!createForm.warna.value) { showError('Validasi', 'Warna BBM wajib dipilih.'); return; }

    const payload = {
        date:          createForm.date.value,
        from_kapal_id: fromId,
        to_kapal_id:   toId,
        warna:         createForm.warna.value,
        quantity:      parseFloat(createForm.quantity.value),
        note:          createForm.note.value,
    };

    axios.post('{{ route('stock-transfer.store') }}', payload)
        .then(res => {
            showSuccess('Berhasil', res.data.message);
            closeCreateModal();
            transferTable.ajax.reload(null, false);
        })
        .catch(err => {
            const errors = err.response?.data?.errors;
            showError('Gagal', errors ? Object.values(errors).flat().join('\n') : err.response?.data?.message || 'Terjadi kesalahan');
        });
}

function deleteTransfer(id, label) {
    showConfirm({
        title: 'Hapus Transfer',
        message: `Hapus transfer "${label}"? Stok akan dikembalikan.`,
        type: 'danger',
        confirmText: 'Ya, Hapus',
        onConfirm: async () => {
            try {
                const res = await axios.delete(`/operasional/transfer/${id}`);
                showSuccess('Berhasil', res.data.message);
                transferTable.ajax.reload(null, false);
            } catch (err) {
                showError('Gagal', err.response?.data?.message || 'Gagal menghapus');
            }
        }
    });
}
</script>
@endpush
