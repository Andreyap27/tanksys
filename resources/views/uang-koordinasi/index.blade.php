@extends('layouts.app')

@section('title', 'Uang Koordinasi')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title-text">Uang Koordinasi</h1>
        <p class="page-subtitle">Kelola data uang koordinasi</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('uang-koordinasi.trash') }}" class="btn btn-secondary">
            <i data-lucide="trash-2" style="width:16px;height:16px;"></i>
            Trash
        </a>
        @if(auth()->user()->canManagePayroll())
        <button class="btn btn-primary" onclick="openCreateModal()">
            <i data-lucide="plus" style="width:16px;height:16px;"></i>
            Tambah
        </button>
        @endif
    </div>
</div>

{{-- Summary Cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1.25rem;margin-bottom:1.5rem;">
    <div class="dash-stat ds-profit">
        <div class="dash-stat__header">
            <div class="dash-stat__icon"><i data-lucide="check-circle" style="width:20px;height:20px;"></i></div>
            <div>
                <div class="dash-stat__label">Total Approved</div>
                <div class="dash-stat__value" id="cardApproved">Rp 0</div>
                <div style="font-size:0.75rem;color:var(--muted-foreground);" id="cardApprovedCount">0 transaksi</div>
            </div>
        </div>
        <div class="dash-stat__bg-icon"><i data-lucide="check-circle" style="width:110px;height:110px;"></i></div>
    </div>
    <div class="dash-stat ds-purchase">
        <div class="dash-stat__header">
            <div class="dash-stat__icon"><i data-lucide="clock" style="width:20px;height:20px;"></i></div>
            <div>
                <div class="dash-stat__label">Total Pending</div>
                <div class="dash-stat__value" id="cardPending">Rp 0</div>
                <div style="font-size:0.75rem;color:var(--muted-foreground);" id="cardPendingCount">0 transaksi</div>
            </div>
        </div>
        <div class="dash-stat__bg-icon"><i data-lucide="clock" style="width:110px;height:110px;"></i></div>
    </div>
    <div class="dash-stat ds-sale">
        <div class="dash-stat__header">
            <div class="dash-stat__icon"><i data-lucide="handshake" style="width:20px;height:20px;"></i></div>
            <div>
                <div class="dash-stat__label">Grand Total</div>
                <div class="dash-stat__value" id="cardTotal">Rp 0</div>
                <div style="font-size:0.75rem;color:var(--muted-foreground);" id="cardTotalCount">0 transaksi</div>
            </div>
        </div>
        <div class="dash-stat__bg-icon"><i data-lucide="handshake" style="width:110px;height:110px;"></i></div>
    </div>
</div>

<div class="card">
    <div class="card-toolbar">
        <div class="dt-search-slot"></div>
        <select id="statusFilter" class="form-control" style="width:auto;">
            <option value="">Semua Status</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
        </select>
    </div>
    <div class="card-content" style="padding:0;">
        <div class="table-wrap">
            <table id="ukTable" class="w-full">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama</th>
                        <th>Jabatan</th>
                        <th>Nominal</th>
                        <th>Extra</th>
                        <th>Total</th>
                        <th>Catatan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

{{-- Create Modal --}}
<div class="modal-overlay" id="createModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3 class="modal-title">Tambah Uang Koordinasi</h3>
            <button class="modal-close-btn" onclick="closeCreateModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="createForm" onsubmit="return false;">
                <div class="form-grid">
                    <div class="form-group full">
                        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="date" class="form-input" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Nama <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-input" required>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Jabatan</label>
                        <input type="text" name="jabatan" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nominal <span class="text-danger">*</span></label>
                        <input type="text" name="nominal" id="createNominal" class="form-input currency-input" placeholder="0" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Extra</label>
                        <input type="text" name="extra" id="createExtra" class="form-input currency-input" placeholder="0">
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Catatan</label>
                        <textarea name="noted" class="form-textarea" rows="2"></textarea>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-danger" onclick="closeCreateModal()">Batal</button>
            <button class="btn btn-primary" onclick="storeUK()">Simpan</button>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal-overlay" id="editModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3 class="modal-title">Edit Uang Koordinasi</h3>
            <button class="modal-close-btn" onclick="closeEditModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="editForm" onsubmit="return false;">
                <div class="form-grid">
                    <div class="form-group full">
                        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="date" class="form-input" required>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Nama <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-input" required>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Jabatan</label>
                        <input type="text" name="jabatan" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nominal <span class="text-danger">*</span></label>
                        <input type="text" name="nominal" id="editNominal" class="form-input currency-input" placeholder="0" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Extra</label>
                        <input type="text" name="extra" id="editExtra" class="form-input currency-input" placeholder="0">
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Catatan</label>
                        <textarea name="noted" class="form-textarea" rows="2"></textarea>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-danger" onclick="closeEditModal()">Batal</button>
            <button class="btn btn-primary" onclick="updateUK()">Update</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    let table;
    let editId = null;
    const createModal = document.getElementById('createModal');
    const editModal   = document.getElementById('editModal');
    const createForm  = document.getElementById('createForm');
    const editForm    = document.getElementById('editForm');

    function escHtml(str) {
        if (str == null) return '';
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/'/g,"\\'").replace(/"/g,'&quot;');
    }

    document.querySelectorAll('.currency-input').forEach(el => {
        el.addEventListener('input', function() {
            let raw = this.value.replace(/\D/g,'');
            this.value = raw ? parseInt(raw).toLocaleString('id-ID') : '';
        });
    });

    function getRaw(el) {
        return parseFloat(String(el.value).replace(/\./g,'').replace(',','.')) || 0;
    }

    const statusBadge = {
        pending:  '<span class="badge badge-warning">Pending</span>',
        approved: '<span class="badge badge-success">Approved</span>',
        rejected: '<span class="badge badge-danger">Rejected</span>',
    };

    $(document).ready(function() {
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            if (settings.nTable.id !== 'ukTable') return true;
            const filterVal = document.getElementById('statusFilter').value;
            if (!filterVal) return true;
            const rowData = table.row(dataIndex).data();
            return rowData && rowData.status === filterVal;
        });

        table = $('#ukTable').DataTable({
            ajax: { url: '{{ route('uang-koordinasi.data') }}', type: 'GET' },
            processing: true,
            columns: [
                { data: 'date', render: (d, t, r) => t === 'sort' ? r.date_raw : d },
                { data: 'nama' },
                { data: 'jabatan' },
                { data: 'nominal' },
                { data: 'extra' },
                { data: 'total' },
                { data: 'noted' },
                { data: 'status', render: d => statusBadge[d] || d },
                {
                    data: null, orderable: false, searchable: false,
                    render: function(data, type, row) {
                        const editBtn = canManagePayroll ? `<button class="icon-btn primary" title="Edit"
                            onclick="openEditModal('${row.id}','${row.date_raw}','${escHtml(row.nama)}','${row.jabatan === '-' ? '' : escHtml(row.jabatan)}','${row.nominal_raw}','${row.extra_raw}','${escHtml(row.noted === '-' ? '' : row.noted)}')">
                            <i data-lucide="pencil" style="width:14px;height:14px;"></i>
                        </button>` : '';

                        const approveBtn = canManagePayroll && row.status === 'pending' ?
                            `<button class="icon-btn success" title="Approve" onclick="approveUK('${row.id}')">
                                <i data-lucide="check-circle" style="width:14px;height:14px;"></i>
                            </button>
                            <button class="icon-btn danger" title="Reject" onclick="rejectUK('${row.id}')">
                                <i data-lucide="x-circle" style="width:14px;height:14px;"></i>
                            </button>` : '';

                        const deleteBtn = canDelete ? `<button class="icon-btn danger" title="Hapus" onclick="deleteUK('${row.id}','${escHtml(row.nama)}')">
                            <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                        </button>` : '';

                        return `<div class="table-actions">${editBtn}${approveBtn}${deleteBtn}</div>`;
                    }
                }
            ],
            order: [[0, 'desc']],
            drawCallback: function() {
                lucide.createIcons();
                updateSummary(this.api());
            }
        });

        document.getElementById('statusFilter').addEventListener('change', function() {
            table.draw();
        });
    });

    function updateSummary(api) {
        const rows = api.rows({ search: 'applied' }).data();
        let totalApproved = 0, countApproved = 0, totalPending = 0, countPending = 0;
        for (let i = 0; i < rows.length; i++) {
            const tot = parseFloat(rows[i].total_raw) || 0;
            if (rows[i].status === 'approved') { totalApproved += tot; countApproved++; }
            else if (rows[i].status === 'pending') { totalPending += tot; countPending++; }
        }
        document.getElementById('cardApproved').textContent      = 'Rp ' + Currency.number(totalApproved);
        document.getElementById('cardApprovedCount').textContent  = countApproved + ' transaksi';
        document.getElementById('cardPending').textContent        = 'Rp ' + Currency.number(totalPending);
        document.getElementById('cardPendingCount').textContent   = countPending + ' transaksi';
        document.getElementById('cardTotal').textContent          = 'Rp ' + Currency.number(totalApproved + totalPending);
        document.getElementById('cardTotalCount').textContent     = (countApproved + countPending) + ' transaksi';
    }

    // ── Create ──────────────────────────────────────────────────────────────────
    function openCreateModal() {
        createForm.reset();
        createForm.date.value = new Date().toISOString().split('T')[0];
        document.getElementById('createNominal').value = '';
        document.getElementById('createExtra').value = '';
        createModal.classList.add('active');
    }
    function closeCreateModal() { createModal.classList.remove('active'); }

    function storeUK() {
        const payload = {
            date:    createForm.date.value,
            nama:    createForm.nama.value,
            jabatan: createForm.jabatan.value,
            nominal: getRaw(createForm.nominal),
            extra:   getRaw(createForm.extra),
            noted:   createForm.noted.value,
        };
        axios.post('{{ route('uang-koordinasi.store') }}', payload)
            .then(res => {
                showSuccess('Berhasil', res.data.message);
                closeCreateModal();
                table.ajax.reload(null, false);
            })
            .catch(err => {
                const errors = err.response?.data?.errors;
                showError('Gagal', errors ? Object.values(errors).flat().join('\n') : (err.response?.data?.message || 'Terjadi kesalahan'));
            });
    }

    // ── Edit ────────────────────────────────────────────────────────────────────
    function openEditModal(id, date, nama, jabatan, nominal, extra, noted) {
        editId = id;
        editForm.date.value    = date;
        editForm.nama.value    = nama;
        editForm.jabatan.value = jabatan;
        editForm.noted.value   = noted;
        document.getElementById('editNominal').value = nominal ? parseInt(nominal).toLocaleString('id-ID') : '';
        document.getElementById('editExtra').value   = extra   ? parseInt(extra).toLocaleString('id-ID')   : '';
        editModal.classList.add('active');
    }
    function closeEditModal() { editModal.classList.remove('active'); }

    function updateUK() {
        const payload = {
            _method:  'PUT',
            date:     editForm.date.value,
            nama:     editForm.nama.value,
            jabatan:  editForm.jabatan.value,
            nominal:  getRaw(editForm.nominal),
            extra:    getRaw(editForm.extra),
            noted:    editForm.noted.value,
        };
        axios.post(`/uang-koordinasi/${editId}`, payload)
            .then(res => {
                showSuccess('Berhasil', res.data.message);
                closeEditModal();
                table.ajax.reload(null, false);
            })
            .catch(err => {
                const errors = err.response?.data?.errors;
                showError('Gagal', errors ? Object.values(errors).flat().join('\n') : (err.response?.data?.message || 'Terjadi kesalahan'));
            });
    }

    // ── Approve / Reject / Delete ────────────────────────────────────────────────
    function approveUK(id) {
        if (!confirm('Setujui data ini?')) return;
        axios.post(`/uang-koordinasi/${id}/approve`)
            .then(res => { showSuccess('Berhasil', res.data.message); table.ajax.reload(null, false); })
            .catch(err => showError('Gagal', err.response?.data?.message || 'Terjadi kesalahan'));
    }

    function rejectUK(id) {
        if (!confirm('Tolak data ini?')) return;
        axios.post(`/uang-koordinasi/${id}/reject`)
            .then(res => { showSuccess('Berhasil', res.data.message); table.ajax.reload(null, false); })
            .catch(err => showError('Gagal', err.response?.data?.message || 'Terjadi kesalahan'));
    }

    function deleteUK(id, nama) {
        if (!confirm(`Hapus uang koordinasi "${nama}"?`)) return;
        axios.delete(`/uang-koordinasi/${id}`)
            .then(res => { showSuccess('Berhasil', res.data.message); table.ajax.reload(null, false); })
            .catch(err => showError('Gagal', err.response?.data?.message || 'Terjadi kesalahan'));
    }
</script>
@endpush
