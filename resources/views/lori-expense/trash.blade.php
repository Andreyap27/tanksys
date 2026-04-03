@extends('layouts.app')

@section('title', 'Data Pengeluaran Lori Terhapus (Trash)')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title-text">Data Pengeluaran Lori Terhapus</h1>
        <p class="page-subtitle">Kelola data pengeluaran lori yang telah dihapus</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('lori-expense.index') }}" class="btn btn-secondary">
            <i data-lucide="arrow-left" style="width:16px;height:16px;"></i>
            Kembali
        </a>
    </div>
</div>

{{-- Mobil Tabs --}}
<div class="tab-bar" id="loriExpenseTabs">
    <button class="tab active" data-mobil-id="" onclick="switchTab(this, '')"><i data-lucide="list" style="width:16px;height:16px;"></i> Semua</button>
</div>

<div class="card">
    <div class="card-toolbar">
        <div class="dt-search-slot"></div>
    </div>
    <div class="card-content" style="padding:0;">
        <div class="table-wrap">
            <table id="loriExpenseTable" class="w-full">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Deskripsi</th>
                        <th>Kategori</th>
                        <th>Nominal</th>
                        <th>Catatan</th>
                        <th>Dihapus</th>
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
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });

    let table;
    let activeLoriExpenseMobilId = '';
    const canRestore = @json($canRestore);
    const canDelete = @json($canDelete);

    const categoryBadge = {
        'BBM': 'badge-danger',
        'Gaji': 'badge-info',
        'Maintenance': 'badge-primary',
        'Umum': 'badge-success',
    };

    function switchTab(btn, mobilId) {
        document.querySelectorAll('#loriExpenseTabs .tab').forEach(t => t.classList.remove('active'));
        btn.classList.add('active');
        activeLoriExpenseMobilId = mobilId;
        table.ajax.reload(null, false);
    }

    function loadLoriExpenseMobils() {
        axios.get('{{ route('mobil-master.list') }}').then(res => {
            const tabBar = document.getElementById('loriExpenseTabs');
            res.data.forEach(m => {
                const btn = document.createElement('button');
                btn.className = 'tab';
                btn.dataset.mobilId = m.id;
                btn.innerHTML = '<i data-lucide="truck" style="width:16px;height:16px;"></i> ' + m.plate_number;
                btn.onclick = function() {
                    switchTab(this, m.id);
                };
                tabBar.appendChild(btn);
            });
            lucide.createIcons();
        });
    }

    $(document).ready(function() {
        loadLoriExpenseMobils();
        table = $('#loriExpenseTable').DataTable({
            ajax: {
                url: '{{ route('lori-expense.trash-data') }}',
                type: 'GET',
                data: function(d) {
                    if (activeLoriExpenseMobilId) d.mobil_id = activeLoriExpenseMobilId;
                }
            },
            processing: true,
            columns: [{
                    data: 'date',
                    render: function(data, type, row) {
                        return (type === 'sort' || type === 'type') ? row.date_raw : data;
                    }
                },
                {
                    data: 'description'
                },
                {
                    data: 'category',
                    render: (data) => {
                        const cls = categoryBadge[data] || 'badge-info';
                        return `<span class="badge ${cls}">${data}</span>`;
                    }
                },
                {
                    data: 'nominal'
                },
                {
                    data: 'noted'
                },
                {
                    data: 'deleted_at'
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        let actions = '';
                        if (canRestore) {
                            actions += `<button class="icon-btn success" title="Restore" onclick="restoreLoriExpense('${row.id}')">
                            <i data-lucide="undo-2" style="width:14px;height:14px;"></i>
                        </button>`;
                        }
                        if (canDelete) {
                            actions += `<button class="icon-btn danger" title="Hapus Permanen" onclick="forceDeleteLoriExpense('${row.id}')">
                            <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                        </button>`;
                        }
                        return `<div class="table-actions">${actions}</div>`;
                    }
                }
            ],
            order: [
                [0, 'desc']
            ],
            drawCallback: function() {
                lucide.createIcons();
            }
        });
    });

    function restoreLoriExpense(id) {
        if (!confirm('Restore pengeluaran lori ini?')) return;
        axios.post(`{{ route('lori-expense.restore', '') }}/${id}`)
            .then(res => {
                showSuccess('Berhasil', res.data.message);
                table.ajax.reload(null, false);
            })
            .catch(err => {
                showError('Gagal', err.response?.data?.message || 'Terjadi kesalahan');
            });
    }

    function forceDeleteLoriExpense(id) {
        if (!confirm('Hapus pengeluaran lori ini secara PERMANEN? Tindakan ini tidak dapat dibatalkan!')) return;
        axios.post(`{{ route('lori-expense.force-delete', '') }}/${id}`)
            .then(res => {
                showSuccess('Berhasil', res.data.message);
                table.ajax.reload(null, false);
            })
            .catch(err => {
                showError('Gagal', err.response?.data?.message || 'Terjadi kesalahan');
            });
    }
</script>

<style>
    .icon-btn {
        width: 2rem;
        height: 2rem;
        border-radius: 0.375rem;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }

    .icon-btn.success {
        background: rgba(22, 163, 74, 0.1);
        color: #16a34a;
    }

    .icon-btn.success:hover {
        background: rgba(22, 163, 74, 0.2);
    }

    .icon-btn.danger {
        background: rgba(220, 38, 38, 0.1);
        color: #dc2626;
    }

    .icon-btn.danger:hover {
        background: rgba(220, 38, 38, 0.2);
    }

    .table-actions {
        display: flex;
        gap: 0.5rem;
    }
</style>
@endpush