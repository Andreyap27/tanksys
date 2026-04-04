@extends('layouts.app')

@section('title', 'Data Lori Terhapus (Trash)')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title-text">Data Lori Terhapus</h1>
        <p class="page-subtitle">Kelola data lori yang telah dihapus</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('lori.index') }}" class="btn btn-secondary">
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
                <div class="dash-stat__label">Total Harga Dihapus</div>
                <div class="dash-stat__value" id="totalPriceDeleted">Rp 0</div>
            </div>
        </div>
        <div class="dash-stat__bg-icon"><i data-lucide="wallet" style="width:110px;height:110px;"></i></div>
    </div>
</div>

{{-- Mobil Tabs --}}
<div class="tab-bar" id="loriTabs">
    <button class="tab active" data-mobil-id="" onclick="switchTab(this, '')"><i data-lucide="list" style="width:16px;height:16px;"></i> Semua</button>
</div>

<div class="card">
    <div class="card-toolbar">
        <div class="dt-search-slot"></div>
    </div>
    <div class="card-content" style="padding:0;">
        <div class="table-wrap">
            <table id="loriTable" class="w-full">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Customer</th>
                        <th>Dari</th>
                        <th>Ke</th>
                        <th>Harga</th>
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
    let activeLoriMobilId = '';

    function switchTab(btn, mobilId) {
        document.querySelectorAll('#loriTabs .tab').forEach(t => t.classList.remove('active'));
        btn.classList.add('active');
        activeLoriMobilId = mobilId;
        table.ajax.reload(null, false);
    }

    function loadLoriMobils() {
        axios.get('{{ route('
            mobil - master.list ') }}').then(res => {
            const tabBar = document.getElementById('loriTabs');
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
        loadLoriMobils();
        table = $('#loriTable').DataTable({
            ajax: {
                url: '{{ route('lori.trash-data') }}',
                type: 'GET',
                data: function(d) {
                    if (activeLoriMobilId) d.mobil_id = activeLoriMobilId;
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
                    data: 'customer'
                },
                {
                    data: 'from'
                },
                {
                    data: 'to'
                },
                {
                    data: 'price'
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
                            actions += `<button class="icon-btn success" title="Restore" onclick="restoreLori('${row.id}')">
                            <i data-lucide="undo-2" style="width:14px;height:14px;"></i>
                        </button>`;
                        }
                        if (canDelete) {
                            actions += `<button class="icon-btn danger" title="Hapus Permanen" onclick="forceDeleteLori('${row.id}')">
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
        if (!table || typeof table.rows !== 'function') return;
        
        const data = table.rows({order: 'current'}).data();
        let totalPrice = 0;

        data.each(function(row) {
            totalPrice += parseFloat(row.price_raw || 0);
        });

        const totalDeletedEl = document.getElementById('totalDeleted');
        const totalPriceDeletedEl = document.getElementById('totalPriceDeleted');

        if (totalDeletedEl) totalDeletedEl.textContent = data.length;
        if (totalPriceDeletedEl) totalPriceDeletedEl.textContent = 'Rp ' + formatCurrency(totalPrice);
    }

    function formatCurrency(value) {
        return new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(value);
    }

    function restoreLori(id) {
        if (!confirm('Restore data lori ini?')) return;
        axios.post(`/lori/${id}/restore`)
            .then(res => {
                showSuccess('Berhasil', res.data.message);
                table.ajax.reload(null, false);
            })
            .catch(err => {
                showError('Gagal', err.response?.data?.message || 'Terjadi kesalahan');
            });
    }

    function forceDeleteLori(id) {
        if (!confirm('Hapus data lori ini secara PERMANEN? Tindakan ini tidak dapat dibatalkan!')) return;
        axios.post(`/lori/${id}/force-delete`)
            .then(res => {
                showSuccess('Berhasil', res.data.message);
                table.ajax.reload(null, false);
            })
            .catch(err => {
                showError('Gagal', err.response?.data?.message || 'Terjadi kesalahan');
            });
    }
</script>
@endpush