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
        <button class="btn btn-secondary" onclick="openPrintModal()">
            <i data-lucide="printer" style="width:16px;height:16px;"></i>
            Print
        </button>
        @if(auth()->user()->canManagePayroll())
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

{{-- Print Modal --}}
<div class="modal-overlay" id="printModal">
    <div class="modal-box" style="max-width:460px;">
        <div class="modal-header">
            <h3 class="modal-title">Print Laporan Gaji</h3>
            <button class="modal-close-btn" onclick="closePrintModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Period Dari</label>
                    <input type="month" id="printFrom" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Period Sampai</label>
                    <input type="month" id="printTo" class="form-input">
                </div>
                <div class="form-group full">
                    <label class="form-label">Jabatan (Kategori)</label>
                    <select id="printJabatan" class="form-input">
                        <option value="">-- Semua Jabatan --</option>
                        @foreach($jabatans as $j)
                        <option value="{{ $j }}">{{ $j }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-danger" onclick="closePrintModal()">Batal</button>
            <button class="btn btn-primary" onclick="doPreview()">
                <i data-lucide="eye" style="width:14px;height:14px;"></i>
                Preview
            </button>
        </div>
    </div>
</div>

{{-- Print Preview Modal --}}
<div class="modal-overlay" id="printPreviewModal">
    <div class="modal-box" style="max-width:72rem;width:95%;height:90vh;display:flex;flex-direction:column;overflow:hidden;">
        <div class="modal-header">
            <h3 class="modal-title">Preview Print</h3>
            <button class="modal-close-btn" onclick="closePreviewModal()">&times;</button>
        </div>
        <div style="flex:1;overflow:hidden;min-height:0;">
            <iframe id="gajiPreviewFrame" src="" style="width:100%;height:100%;border:none;display:block;"></iframe>
        </div>
        <div class="modal-footer">
            <button class="btn" onclick="closePreviewModal()">
                <i data-lucide="x" style="width:15px;height:15px;"></i>
                Tutup
            </button>
            <button class="btn btn-primary" onclick="doPrint()">
                <i data-lucide="printer" style="width:15px;height:15px;"></i>
                Print
            </button>
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

                        const editBtn = canManagePayroll ? `<a href="/gaji/${row.id}/edit" class="icon-btn primary" title="Edit">
                            <i data-lucide="pencil" style="width:14px;height:14px;"></i>
                        </a>` : '';

                        const deleteBtn = canManagePayroll ? `<button class="icon-btn danger" title="Hapus"
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

    // ── Print ───────────────────────────────────────────────────────────────────
    const printModal   = document.getElementById('printModal');
    const previewModal = document.getElementById('printPreviewModal');
    const previewFrame = document.getElementById('gajiPreviewFrame');

    function openPrintModal()   { printModal.classList.add('active'); }
    function closePrintModal()  { printModal.classList.remove('active'); }
    function closePreviewModal() {
        previewModal.classList.remove('active');
        previewFrame.src = '';
    }

    function buildPrintUrl() {
        const from    = document.getElementById('printFrom').value;
        const to      = document.getElementById('printTo').value;
        const jabatan = document.getElementById('printJabatan').value;
        const params  = new URLSearchParams();
        if (from)    params.set('from', from);
        if (to)      params.set('to', to);
        if (jabatan) params.set('jabatan', jabatan);
        return '{{ route('gaji.print') }}?' + params.toString();
    }

    function doPreview() {
        closePrintModal();
        previewFrame.src = buildPrintUrl();
        previewModal.classList.add('active');
        lucide.createIcons();
    }

    function doPrint() {
        previewFrame.contentWindow.print();
    }

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
