@extends('layouts.app')

@section('title', 'Trash — Piutang')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title-text">Trash — Piutang</h1>
        <p class="page-subtitle">Data piutang yang telah dihapus</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('piutang.index') }}" class="btn btn-secondary">
            <i data-lucide="arrow-left" style="width:16px;height:16px;"></i> Kembali
        </a>
    </div>
</div>

<div class="card">
    <div class="card-toolbar"><div class="dt-search-slot"></div></div>
    <div class="card-content" style="padding:0;">
        <div class="table-wrap">
            <table id="piutangTrashTable" class="w-full">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama</th>
                        <th>Deskripsi</th>
                        <th>Nominal</th>
                        <th>Status</th>
                        <th>Dihapus Oleh</th>
                        <th>Dihapus Pada</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function escHtml(str) {
    if (str == null) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/'/g,"\\'").replace(/"/g,'&quot;');
}

$(document).ready(function () {
    $('#piutangTrashTable').DataTable({
        ajax: { url: '{{ route('piutang.trash-data') }}', type: 'GET' },
        columns: [
            { data: 'date', render: (d, t, r) => t === 'sort' ? r.date_raw : d },
            { data: 'nama' },
            { data: 'description' },
            { data: 'nominal', render: d => Currency.symbol + ' ' + d },
            {
                data: 'status',
                render: d => ({
                    pending:  '<span class="badge badge-warning">Pending</span>',
                    approved: '<span class="badge badge-info">Approved</span>',
                    rejected: '<span class="badge badge-danger">Rejected</span>',
                    paid:     '<span class="badge badge-success">Paid</span>',
                })[d] || d
            },
            { data: 'deleted_by' },
            { data: 'deleted_at' },
            {
                data: null, orderable: false, searchable: false,
                render: function (data, type, row) {
                    const restoreBtn = canRestore
                        ? `<button class="icon-btn success" title="Restore" onclick="restorePiutang('${row.id}')">
                               <i data-lucide="rotate-ccw" style="width:14px;height:14px;"></i>
                           </button>` : '';
                    const deleteBtn = canDelete
                        ? `<button class="icon-btn danger" title="Hapus Permanen" onclick="forceDeletePiutang('${row.id}','${escHtml(row.description)}')">
                               <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                           </button>` : '';
                    return `<div class="table-actions">${restoreBtn}${deleteBtn}</div>`;
                }
            }
        ]
    });
});

function restorePiutang(id) {
    showConfirm({
        title: 'Restore Piutang', message: 'Restore piutang ini?',
        type: 'success', confirmText: 'Ya, Restore',
        onConfirm: async () => {
            try {
                await axios.post(`/piutang/${id}/restore`);
                showSuccess('Berhasil', 'Piutang berhasil di-restore.');
                $('#piutangTrashTable').DataTable().ajax.reload(null, false);
            } catch (err) { showError('Gagal', err.response?.data?.message || 'Gagal'); }
        }
    });
}

function forceDeletePiutang(id, description) {
    showConfirm({
        title: 'Hapus Permanen', message: `Hapus permanen piutang "${description}"? Tidak dapat dibatalkan.`,
        type: 'danger', confirmText: 'Ya, Hapus Permanen',
        onConfirm: async () => {
            try {
                await axios.post(`/piutang/${id}/force-delete`);
                showSuccess('Berhasil', 'Piutang berhasil dihapus permanen.');
                $('#piutangTrashTable').DataTable().ajax.reload(null, false);
            } catch (err) { showError('Gagal', err.response?.data?.message || 'Gagal'); }
        }
    });
}
</script>
@endpush
