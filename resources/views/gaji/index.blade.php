@extends('layouts.app')

@section('title', 'Data Karyawan & Gaji')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title-text">Data Karyawan</h1>
        <p class="page-subtitle">Kelola data karyawan dan slip gaji</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('gaji.trash') }}" class="btn btn-secondary">
            <i data-lucide="trash-2" style="width:16px;height:16px;"></i>
            Trash
        </a>
        @if(auth()->user()->canManage())
        <a href="{{ route('gaji.create') }}" class="btn btn-primary">
            <i data-lucide="plus" style="width:16px;height:16px;"></i>
            Tambah Karyawan
        </a>
        @endif
    </div>
</div>

{{-- Summary Cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1.25rem;margin-bottom:1.5rem;">
    <div class="dash-stat ds-profit">
        <div class="dash-stat__header">
            <div class="dash-stat__icon"><i data-lucide="users" style="width:20px;height:20px;"></i></div>
            <div>
                <div class="dash-stat__label">Total Karyawan</div>
                <div class="dash-stat__value" id="cardTotalKaryawan">0</div>
            </div>
        </div>
        <div class="dash-stat__bg-icon"><i data-lucide="users" style="width:110px;height:110px;"></i></div>
    </div>
    <div class="dash-stat ds-purchase">
        <div class="dash-stat__header">
            <div class="dash-stat__icon"><i data-lucide="banknote" style="width:20px;height:20px;"></i></div>
            <div>
                <div class="dash-stat__label">Total Gaji Pokok</div>
                <div class="dash-stat__value" id="cardTotalGaji">Rp 0</div>
            </div>
        </div>
        <div class="dash-stat__bg-icon"><i data-lucide="banknote" style="width:110px;height:110px;"></i></div>
    </div>
    <div class="dash-stat ds-sale">
        <div class="dash-stat__header">
            <div class="dash-stat__icon"><i data-lucide="wallet" style="width:20px;height:20px;"></i></div>
            <div>
                <div class="dash-stat__label">Total Tunjangan</div>
                <div class="dash-stat__value" id="cardTotalTunjangan">Rp 0</div>
            </div>
        </div>
        <div class="dash-stat__bg-icon"><i data-lucide="wallet" style="width:110px;height:110px;"></i></div>
    </div>
    <div class="dash-stat ds-expense">
        <div class="dash-stat__header">
            <div class="dash-stat__icon"><i data-lucide="credit-card" style="width:20px;height:20px;"></i></div>
            <div>
                <div class="dash-stat__label">Total Pinjaman Aktif</div>
                <div class="dash-stat__value" id="cardTotalPinjaman">Rp 0</div>
            </div>
        </div>
        <div class="dash-stat__bg-icon"><i data-lucide="credit-card" style="width:110px;height:110px;"></i></div>
    </div>
</div>

<div class="card">
    <div class="card-toolbar">
        <div class="dt-search-slot"></div>
    </div>
    <div class="card-content" style="padding:0;">
        <div class="table-wrap">
            <table id="karyawanTable" class="w-full">
                <thead>
                    <tr>
                        <th>Nama Karyawan</th>
                        <th>No KTP</th>
                        <th>No HP</th>
                        <th>Jabatan</th>
                        <th>Gaji Pokok</th>
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

    function escHtml(str) {
        if (str == null) return '';
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/'/g,"\\'").replace(/"/g,'&quot;');
    }

    $(document).ready(function() {
        table = $('#karyawanTable').DataTable({
            ajax: {
                url: '{{ route('gaji.data') }}',
                type: 'GET',
            },
            processing: true,
            columns: [
                { data: 'nama_karyawan' },
                { data: 'no_ktp' },
                { data: 'no_hp' },
                { data: 'jabatan' },
                { data: 'gaji_pokok' },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        const viewBtn = `<a href="/gaji/${row.id}" class="icon-btn info" title="Lihat Detail & Slip Gaji">
                            <i data-lucide="eye" style="width:14px;height:14px;"></i>
                        </a>`;

                        const editBtn = canManage ? `<a href="/gaji/${row.id}/edit" class="icon-btn primary" title="Edit">
                            <i data-lucide="pencil" style="width:14px;height:14px;"></i>
                        </a>` : '';

                        const deleteBtn = canDelete ? `<button class="icon-btn danger" title="Hapus"
                            onclick="deleteKaryawan('${row.id}', '${escHtml(row.nama_karyawan)}')">
                            <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                        </button>` : '';

                        return `<div class="table-actions">${viewBtn}${editBtn}${deleteBtn}</div>`;
                    }
                }
            ],
            order: [[0, 'asc']],
            drawCallback: function() {
                lucide.createIcons();
                updateSummary(this.api());
            }
        });
    });

    function updateSummary(api) {
        const rows = api.rows({ search: 'applied' }).data();
        let totalGaji = 0, totalTunjangan = 0;
        for (let i = 0; i < rows.length; i++) {
            totalGaji      += parseFloat(rows[i].gaji_pokok_raw) || 0;
            totalTunjangan += parseFloat(rows[i].tunjangan_raw)  || 0;
        }
        document.getElementById('cardTotalKaryawan').textContent  = rows.length;
        document.getElementById('cardTotalGaji').textContent      = 'Rp ' + Currency.number(totalGaji);
        document.getElementById('cardTotalTunjangan').textContent = 'Rp ' + Currency.number(totalTunjangan);
    }

    axios.get('{{ route('gaji.summary-stats') }}')
        .then(res => {
            document.getElementById('cardTotalPinjaman').textContent = 'Rp ' + Currency.number(res.data.total_pinjaman_aktif);
        })
        .catch(() => {});

    // ── Delete ──────────────────────────────────────────────────────────────────
    function deleteKaryawan(id, nama) {
        showConfirm({
            type: 'danger',
            title: 'Hapus Karyawan',
            message: `Hapus karyawan "${nama}"?`,
            confirmText: 'Hapus',
            onConfirm: () => {
                axios.delete(`/gaji/${id}`)
                    .then(res => { showSuccess('Berhasil', res.data.message); table.ajax.reload(null, false); })
                    .catch(err => showError('Gagal', err.response?.data?.message || 'Terjadi kesalahan'));
            }
        });
    }
</script>
@endpush
