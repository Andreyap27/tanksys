@extends('layouts.app')

@section('title', 'Data Karyawan Terhapus')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title-text">Data Karyawan Terhapus</h1>
        <p class="page-subtitle">Kelola data karyawan yang telah dihapus</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('gaji.index') }}" class="btn btn-secondary">
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
                        <th>Nama Karyawan</th>
                        <th>No KTP</th>
                        <th>No HP</th>
                        <th>Jabatan</th>
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

    $(document).ready(function() {
        table = $('#trashTable').DataTable({
            ajax: { url: '{{ route('gaji.trash-data') }}', type: 'GET' },
            processing: true,
            columns: [
                { data: 'nama_karyawan' },
                { data: 'no_ktp' },
                { data: 'no_hp' },
                { data: 'jabatan' },
                { data: 'deleted_at' },
                { data: 'deleted_by' },
                {
                    data: null, orderable: false, searchable: false,
                    render: function(data, type, row) {
                        let actions = '';
                        if (canRestore) {
                            actions += `<button class="icon-btn success" title="Restore" onclick="restoreKaryawan('${row.id}')">
                                <i data-lucide="undo-2" style="width:14px;height:14px;"></i>
                            </button>`;
                        }
                        if (canDelete) {
                            actions += `<button class="icon-btn danger" title="Hapus Permanen" onclick="forceDeleteKaryawan('${row.id}', '${row.nama_karyawan.replace(/'/g, "\\'")}')">
                                <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                            </button>`;
                        }
                        return `<div class="table-actions">${actions}</div>`;
                    }
                }
            ],
            order: [[0, 'asc']],
            drawCallback: function() { lucide.createIcons(); }
        });
    });

    function restoreKaryawan(id) {
        if (!confirm('Restore karyawan ini?')) return;
        axios.post(`/gaji/${id}/restore`)
            .then(res => { showSuccess('Berhasil', res.data.message); table.ajax.reload(null, false); })
            .catch(err => showError('Gagal', err.response?.data?.message || 'Terjadi kesalahan'));
    }

    function forceDeleteKaryawan(id, nama) {
        if (!confirm(`Hapus karyawan "${nama}" secara PERMANEN? Tindakan ini tidak dapat dibatalkan!`)) return;
        axios.post(`/gaji/${id}/force-delete`)
            .then(res => { showSuccess('Berhasil', res.data.message); table.ajax.reload(null, false); })
            .catch(err => showError('Gagal', err.response?.data?.message || 'Terjadi kesalahan'));
    }
</script>
@endpush
