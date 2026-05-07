@extends('layouts.app')

@section('title', 'Trash Transaksi Petty Cash')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title-text">Data Transaksi Petty Cash Terhapus</h1>
        <p class="page-subtitle">Kelola transaksi petty cash yang telah dihapus</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('petty-cash-transaction.index') }}" class="btn btn-secondary">
            <i data-lucide="arrow-left" style="width:16px;height:16px;"></i>
            Kembali
        </a>
    </div>
</div>

<div class="card">
    <div class="card-toolbar"><div class="dt-search-slot"></div></div>
    <div class="card-content" style="padding:0;">
        <div class="table-wrap">
            <table id="pcTrashTable" class="w-full">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Petty Cash</th>
                        <th>Type</th>
                        <th>Deskripsi</th>
                        <th>Amount</th>
                        <th>Catatan</th>
                        <th>Dihapus Oleh</th>
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
let table;

$(document).ready(function () {
    table = $('#pcTrashTable').DataTable({
        ajax: { url: '{{ route('petty-cash-transaction.trash-data') }}', type: 'GET' },
        processing: true,
        columns: [
            {
                data: 'date',
                render: (data, type, row) => (type === 'sort' || type === 'type') ? row.date_raw : data,
            },
            { data: 'petty_cash_name' },
            {
                data: 'type',
                render: (data) => data === 'in'
                    ? '<span class="badge badge-success">In</span>'
                    : '<span class="badge badge-danger">Out</span>',
            },
            { data: 'description' },
            {
                data: 'amount',
                render: (data, type, row) => {
                    const cls = row.type === 'in' ? 'text-success' : 'text-danger';
                    return `<span class="${cls}">${Currency.symbol} ${data}</span>`;
                },
            },
            { data: 'note' },
            { data: 'deleted_by' },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    let actions = '';
                    if (canRestore) {
                        actions += `<button class="icon-btn success" title="Restore" onclick="restoreTrx('${row.id}')">
                            <i data-lucide="undo-2" style="width:14px;height:14px;"></i>
                        </button>`;
                    }
                    if (canDelete) {
                        actions += `<button class="icon-btn danger" title="Hapus Permanen" onclick="forceDeleteTrx('${row.id}')">
                            <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                        </button>`;
                    }
                    return `<div class="table-actions">${actions}</div>`;
                }
            }
        ],
        order: [[0, 'desc']],
        drawCallback: function () { lucide.createIcons(); }
    });
});

function restoreTrx(id) {
    showConfirm({
        title: 'Konfirmasi Restore',
        message: 'Restore transaksi ini?',
        type: 'primary',
        confirmText: 'Ya, Restore',
        onConfirm: async () => {
            try {
                const res = await axios.post(`/petty-cash-transaction/${id}/restore`);
                showSuccess('Berhasil', res.data.message);
                table.ajax.reload(null, false);
            } catch (err) {
                showError('Gagal', err.response?.data?.message || 'Terjadi kesalahan');
            }
        }
    });
}

function forceDeleteTrx(id) {
    showConfirm({
        title: 'Hapus Permanen',
        message: 'Hapus transaksi ini secara PERMANEN? Tindakan ini tidak dapat dibatalkan!',
        type: 'danger',
        confirmText: 'Ya, Hapus Permanen',
        onConfirm: async () => {
            try {
                const res = await axios.post(`/petty-cash-transaction/${id}/force-delete`);
                showSuccess('Berhasil', res.data.message);
                table.ajax.reload(null, false);
            } catch (err) {
                showError('Gagal', err.response?.data?.message || 'Terjadi kesalahan');
            }
        }
    });
}
</script>
@endpush
