@extends('layouts.app')

@section('title', 'Trash — Bank In/Out')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title-text">Trash — Bank In/Out</h1>
        <p class="page-subtitle">Data transaksi bank yang telah dihapus</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('bank.index') }}" class="btn btn-secondary">
            <i data-lucide="arrow-left" style="width:16px;height:16px;"></i>
            Kembali
        </a>
    </div>
</div>

<div class="card">
    <div class="card-toolbar"><div class="dt-search-slot"></div></div>
    <div class="card-content" style="padding:0;">
        <div class="table-wrap">
            <table id="bankTrashTable" class="w-full">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Deskripsi</th>
                        <th>Note</th>
                        <th>Job</th>
                        <th>Type</th>
                        <th>Amount</th>
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
$(document).ready(function () {
    $('#bankTrashTable').DataTable({
        ajax: { url: '{{ route('bank.trash-data') }}', type: 'GET' },
        processing: true,
        columns: [
            { data: 'date' },
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
            { data: 'deleted_by' },
            { data: 'deleted_at' },
            {
                data: null, orderable: false, searchable: false,
                render: (data, type, row) => `
                    <div class="table-actions">
                        ${canRestore ? `<button class="icon-btn success" title="Restore" onclick="restoreTrx('${row.id}')">
                            <i data-lucide="rotate-ccw" style="width:14px;height:14px;"></i>
                        </button>` : ''}
                        ${canDelete ? `<button class="icon-btn danger" title="Hapus Permanen" onclick="forceDeleteTrx('${row.id}')">
                            <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                        </button>` : ''}
                    </div>
                `
            }
        ],
        order: [[7, 'desc']],
        drawCallback: function () { lucide.createIcons(); }
    });
});

function restoreTrx(id) {
    showConfirm({
        title: 'Restore Transaksi', message: 'Yakin ingin me-restore transaksi ini?',
        type: 'success', confirmText: 'Ya, Restore',
        onConfirm: async () => {
            try {
                const res = await axios.post(`/bank/${id}/restore`);
                showSuccess('Berhasil', res.data.message);
                $('#bankTrashTable').DataTable().ajax.reload(null, false);
            } catch (err) { showError('Gagal', err.response?.data?.message || 'Gagal restore'); }
        }
    });
}

function forceDeleteTrx(id) {
    showConfirm({
        title: 'Hapus Permanen', message: 'Yakin ingin menghapus transaksi ini secara permanen?',
        type: 'danger', confirmText: 'Ya, Hapus Permanen',
        onConfirm: async () => {
            try {
                const res = await axios.post(`/bank/${id}/force-delete`);
                showSuccess('Berhasil', res.data.message);
                $('#bankTrashTable').DataTable().ajax.reload(null, false);
            } catch (err) { showError('Gagal', err.response?.data?.message || 'Gagal hapus'); }
        }
    });
}
</script>
@endpush
