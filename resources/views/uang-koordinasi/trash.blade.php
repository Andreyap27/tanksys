@extends('layouts.app')

@section('title', 'Uang Koordinasi Terhapus')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title-text">Data Uang Koordinasi Terhapus</h1>
        <p class="page-subtitle">Kelola data uang koordinasi yang telah dihapus</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('uang-koordinasi.index') }}" class="btn btn-secondary">
            <i data-lucide="arrow-left" style="width:16px;height:16px;"></i>
            Kembali
        </a>
    </div>
</div>

<div class="card">
    <div class="card-toolbar">
        <div class="dt-search-slot"></div>
    </div>
    <div class="card-content" style="padding:0;">
        <div class="table-wrap">
            <table id="trashTable" class="w-full">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama</th>
                        <th>Jabatan</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Dihapus Pada</th>
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

    const statusBadge = {
        pending:  '<span class="badge badge-warning">Pending</span>',
        approved: '<span class="badge badge-success">Approved</span>',
        rejected: '<span class="badge badge-danger">Rejected</span>',
    };

    $(document).ready(function() {
        table = $('#trashTable').DataTable({
            ajax: { url: '{{ route('uang-koordinasi.trash-data') }}', type: 'GET' },
            processing: true,
            columns: [
                { data: 'date', render: (d, t, r) => t === 'sort' ? r.date_raw : d },
                { data: 'nama' },
                { data: 'jabatan' },
                { data: 'total' },
                { data: 'status', render: d => statusBadge[d] || d },
                { data: 'deleted_at' },
                { data: 'deleted_by' },
                {
                    data: null, orderable: false, searchable: false,
                    render: function(data, type, row) {
                        let actions = '';
                        if (canRestore) {
                            actions += `<button class="icon-btn success" title="Restore" onclick="restoreUK('${row.id}')">
                                <i data-lucide="undo-2" style="width:14px;height:14px;"></i>
                            </button>`;
                        }
                        if (canDelete) {
                            actions += `<button class="icon-btn danger" title="Hapus Permanen" onclick="forceDeleteUK('${row.id}', '${row.nama.replace(/'/g,"\\'")}')">
                                <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                            </button>`;
                        }
                        return `<div class="table-actions">${actions}</div>`;
                    }
                }
            ],
            order: [[0, 'desc']],
            drawCallback: function() { lucide.createIcons(); }
        });
    });

    function restoreUK(id) {
        if (!confirm('Restore data ini?')) return;
        axios.post(`/uang-koordinasi/${id}/restore`)
            .then(res => { showSuccess('Berhasil', res.data.message); table.ajax.reload(null, false); })
            .catch(err => showError('Gagal', err.response?.data?.message || 'Terjadi kesalahan'));
    }

    function forceDeleteUK(id, nama) {
        if (!confirm(`Hapus "${nama}" secara PERMANEN? Tindakan ini tidak dapat dibatalkan!`)) return;
        axios.post(`/uang-koordinasi/${id}/force-delete`)
            .then(res => { showSuccess('Berhasil', res.data.message); table.ajax.reload(null, false); })
            .catch(err => showError('Gagal', err.response?.data?.message || 'Terjadi kesalahan'));
    }
</script>
@endpush
