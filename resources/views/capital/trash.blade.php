@extends('layouts.app')

@section('title', 'Data Modal Terhapus (Trash)')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title-text">Data Modal Terhapus</h1>
        <p class="page-subtitle">Kelola data modal yang telah dihapus</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('capital.index') }}" class="btn btn-secondary">
            <i data-lucide="arrow-left" style="width:16px;height:16px;"></i>
            Kembali
        </a>
    </div>
</div>

{{-- Summary Cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1.25rem;margin-bottom:1.5rem;">
    <div class="dash-stat ds-purchase">
        <div class="dash-stat__header">
            <div class="dash-stat__icon"><i data-lucide="trash-2" style="width:20px;height:20px;"></i></div>
            <div>
                <div class="dash-stat__label">Total Dihapus</div>
                <div class="dash-stat__value" id="totalDeleted">0</div>
            </div>
        </div>
        <div class="dash-stat__bg-icon"><i data-lucide="trash-2" style="width:110px;height:110px;"></i></div>
    </div>
    <div class="dash-stat ds-purchase">
        <div class="dash-stat__header">
            <div class="dash-stat__icon"><i data-lucide="wallet" style="width:20px;height:20px;"></i></div>
            <div>
                <div class="dash-stat__label">Total Modal Dihapus</div>
                <div class="dash-stat__value" id="totalNominalDeleted">Rp 0</div>
            </div>
        </div>
        <div class="dash-stat__bg-icon"><i data-lucide="wallet" style="width:110px;height:110px;"></i></div>
    </div>
</div>

{{-- Kapal Tabs --}}
<div class="tab-bar" id="capitalTabs">
    <button class="tab active" data-kapal-id="" onclick="switchTab(this, '')"><i data-lucide="list" style="width:16px;height:16px;"></i> Semua</button>
</div>

<div class="card">
    <div class="card-toolbar">
        <div class="dt-search-slot"></div>
    </div>
    <div class="card-content" style="padding:0;">
        <div class="table-wrap">
            <table id="capitalTable" class="w-full">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama</th>
                        <th>Nominal</th>
                        <th>Status</th>
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
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });

    let table;
    let activeCapitalKapalId = '';
    const canRestore = @json($canRestore);
    const canDelete = @json($canDelete);

    function switchTab(btn, kapalId) {
        document.querySelectorAll('#capitalTabs .tab').forEach(t => t.classList.remove('active'));
        btn.classList.add('active');
        activeCapitalKapalId = kapalId;
        table.ajax.reload(null, false);
    }

    function loadCapitalKapals() {
        axios.get('{{ route('
            kapal.list ') }}').then(res => {
            const tabBar = document.getElementById('capitalTabs');
            res.data.forEach(k => {
                const btn = document.createElement('button');
                btn.className = 'tab';
                btn.dataset.kapalId = k.id;
                btn.innerHTML = '<i data-lucide="ship" style="width:16px;height:16px;"></i> ' + k.name;
                btn.onclick = function() {
                    switchTab(this, k.id);
                };
                tabBar.appendChild(btn);
            });
            lucide.createIcons();
        });
    }

    $(document).ready(function() {
        loadCapitalKapals();
        table = $('#capitalTable').DataTable({
            ajax: {
                url: '{{ route('capital.trash-data') }}',
                type: 'GET',
                data: function(d) {
                    if (activeCapitalKapalId) d.kapal_id = activeCapitalKapalId;
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
                    data: 'name'
                },
                {
                    data: 'nominal'
                },
                {
                    data: 'status',
                    render: function(data) {
                        const colors = {
                            'pending': 'badge-warning',
                            'approved': 'badge-success',
                            'rejected': 'badge-danger'
                        };
                        return `<span class="badge ${colors[data] || 'badge-info'}">${data}</span>`;
                    }
                },
                {
                    data: 'note'
                },
                {
                    data: 'deleted_by'
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        let actions = '';
                        if (canRestore) {
                            actions += `<button class="icon-btn success" title="Restore" onclick="restoreCapital('${row.id}')">
                            <i data-lucide="undo-2" style="width:14px;height:14px;"></i>
                        </button>`;
                        }
                        if (canDelete) {
                            actions += `<button class="icon-btn danger" title="Hapus Permanen" onclick="forceDeleteCapital('${row.id}')">
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
                updateSummaryCards();
            }
        });
    });

    function updateSummaryCards() {
        const data = table.rows({order: 'current'}).data();
        let totalNominal = 0;

        data.each(function(row) {
            totalNominal += parseFloat(row.nominal_raw || 0);
        });

        document.getElementById('totalDeleted').textContent = data.length;
        document.getElementById('totalNominalDeleted').textContent = 'Rp ' + formatCurrency(totalNominal);
    }

    function formatCurrency(value) {
        return new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(value);
    }

    function restoreCapital(id) {
        if (!confirm('Restore modal ini?')) return;
        axios.post(`/capital/${id}/restore`)
            .then(res => {
                showSuccess('Berhasil', res.data.message);
                table.ajax.reload(null, false);
            })
            .catch(err => {
                showError('Gagal', err.response?.data?.message || 'Terjadi kesalahan');
            });
    }

    function forceDeleteCapital(id) {
        if (!confirm('Hapus modal ini secara PERMANEN? Tindakan ini tidak dapat dibatalkan!')) return;
        axios.post(`/capital/${id}/force-delete`)
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