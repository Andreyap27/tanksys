@extends('layouts.app')

@section('title', 'Data Modal')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title-text">Data Modal Usaha</h1>
        <p class="page-subtitle">Kelola data modal usaha</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('capital.trash') }}" class="btn btn-secondary">
            <i data-lucide="trash-2" style="width:16px;height:16px;"></i>
            Trash
        </a>
        @if(auth()->user()->canManage())
        <button class="btn btn-primary" onclick="openCreateModal()">
            <i data-lucide="plus" style="width:16px;height:16px;"></i>
            Tambah Modal
        </button>
        @endif
    </div>
</div>

{{-- Summary Cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1.25rem;margin-bottom:1.5rem;">
    <div class="dash-stat ds-capital">
        <div class="dash-stat__header">
            <div class="dash-stat__icon"><i data-lucide="building-2" style="width:20px;height:20px;"></i></div>
            <div>
                <div class="dash-stat__label">PT Aldive</div>
                <div class="dash-stat__value" id="capitalAldive">Rp 0</div>
            </div>
        </div>
        <div class="dash-stat__bg-icon"><i data-lucide="building-2" style="width:110px;height:110px;"></i></div>
    </div>
    <div class="dash-stat ds-capital">
        <div class="dash-stat__header">
            <div class="dash-stat__icon"><i data-lucide="user" style="width:20px;height:20px;"></i></div>
            <div>
                <div class="dash-stat__label">Rudi Hartono</div>
                <div class="dash-stat__value" id="capitalRudi">Rp 0</div>
            </div>
        </div>
        <div class="dash-stat__bg-icon"><i data-lucide="user" style="width:110px;height:110px;"></i></div>
    </div>
    <div class="dash-stat ds-purchase">
        <div class="dash-stat__header">
            <div class="dash-stat__icon"><i data-lucide="wallet" style="width:20px;height:20px;"></i></div>
            <div>
                <div class="dash-stat__label">Total Capital (Approved)</div>
                <div class="dash-stat__value" id="capitalTotal">Rp 0</div>
            </div>
        </div>
        <div class="dash-stat__bg-icon"><i data-lucide="wallet" style="width:110px;height:110px;"></i></div>
    </div>
</div>

{{-- Kapal Tabs --}}
<div class="tab-bar" id="capitalTabs">
    <button class="tab active" onclick="switchCapitalTab(this, '')"><i data-lucide="list" style="width:16px;height:16px;"></i> Semua</button>
</div>

<div class="card">
    <div class="card-toolbar">
        <div class="dt-search-slot"></div>
        <select id="capitalStatusFilter" class="form-select" style="width:auto;min-width:130px;">
            <option value="">Semua Status</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
        </select>
    </div>
    <div class="card-content" style="padding:0;">
        <div class="table-wrap">
            <table id="capitalTable" class="w-full">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama</th>
                        <th>Nominal</th>
                        <th>Catatan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

@include('capital.modals.create')
@include('capital.modals.edit')
@endsection

@push('scripts')
<script>
    // @ts-nocheck
    // Initialize Lucide icons for page load
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });

    let table;
    let editId = null;
    let activeCapitalKapalId = '';
    const createForm = document.getElementById('createForm');
    const editForm = document.getElementById('editForm');
    const createModal = document.getElementById('createModal');
    const editModal = document.getElementById('editModal');

    function loadCapitalKapals() {
        axios.get('{{ route('kapal.list') }}').then(res => {
            const tabBar = document.getElementById('capitalTabs');
            const opts = res.data.map(k => `<option value="${k.id}">${k.code} — ${k.name}</option>`).join('');
            res.data.forEach(k => {
                const btn = document.createElement('button');
                btn.className = 'tab';
                btn.dataset.kapalId = k.id;
                btn.innerHTML = `<i data-lucide="ship" style="width:16px;height:16px;"></i> ${k.name}`;
                btn.onclick = function() {
                    switchCapitalTab(this, k.id);
                };
                tabBar.appendChild(btn);
            });
            lucide.createIcons();
            document.getElementById('createCapitalKapalSelect').innerHTML = '<option value="">-- Pilih Kapal --</option>' + opts;
            document.getElementById('editCapitalKapalSelect').innerHTML = '<option value="">-- Pilih Kapal --</option>' + opts;
        });
    }

    function switchCapitalTab(btn, kapalId) {
        document.querySelectorAll('#capitalTabs .tab').forEach(t => t.classList.remove('active'));
        btn.classList.add('active');
        activeCapitalKapalId = kapalId;
        refreshCapitalSummary(kapalId);
        table.ajax.reload(null, false);
    }

    function refreshCapitalSummary(kapalId) {
        const params = {};
        if (kapalId) params.kapal_id = kapalId;
        axios.get('{{ route('capital.summary') }}', { params }).then(res => {
            document.getElementById('capitalAldive').textContent = 'Rp ' + Currency.number(res.data.pt_aldive);
            document.getElementById('capitalRudi').textContent = 'Rp ' + Currency.number(res.data.rudi_hartono);
            document.getElementById('capitalTotal').textContent = 'Rp ' + Currency.number(res.data.total);
        });
    }

    // ── Input formatter ───────────────────────────────────────────────────────────
    function setRaw(el, raw) {
        el.dataset.raw = raw;
    }

    function getRaw(el) {
        return parseFloat(el.dataset.raw) || 0;
    }

    document.querySelectorAll('.fmt-price').forEach(el => {
        el.addEventListener('input', function() {
            const raw = this.value.replace(/[^0-9]/g, '');
            this.value = raw;
            setRaw(this, raw);
        });
        el.addEventListener('blur', function() {
            const raw = parseInt(this.value.replace(/[^0-9]/g, '')) || 0;
            setRaw(this, raw);
            this.value = raw ? Currency.format(raw) : '';
        });
        el.addEventListener('focus', function() {
            this.value = this.dataset.raw || '';
        });
    });

    // ── DataTable ─────────────────────────────────────────────────────────────────
    $(document).ready(function() {
        loadCapitalKapals();
        refreshCapitalSummary('');

        table = $('#capitalTable').DataTable({
            ajax: {
                url: '{{ route('capital.data') }}',
                type: 'GET',
                data: function(d) {
                    if (activeCapitalKapalId) d.kapal_id = activeCapitalKapalId;
                }
            },
            processing: true,
            columns: [{
                    data: 'date'
                },
                {
                    data: 'name'
                },
                {
                    data: 'nominal',
                    render: (data) => Currency.symbol + ' ' + data
                },
                {
                    data: 'note',
                    render: (data) => data ? escHtml(data) : '-'
                },
                {
                    data: 'status',
                    render: function(data) {
                        const map = {
                            pending: '<span class="badge badge-warning">Pending</span>',
                            approved: '<span class="badge badge-success">Approved</span>',
                            rejected: '<span class="badge badge-danger">Rejected</span>',
                        };
                        return map[data] || data;
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        const editBtn = canManage ?
                            `<button class="icon-btn primary" title="Edit" onclick="openEditModal('${row.id}', '${row.date_raw}', '${escHtml(row.name)}', '${row.nominal_raw}', '${escHtml(row.note)}', '${row.kapal_id || ''}')">
                               <i data-lucide="pencil" style="width:14px;height:14px;"></i>
                           </button>` :
                            '';

                        const approveBtn = canApprove && row.status === 'pending' ?
                            `<button class="icon-btn success" title="Approve"
                               onclick="approveCapital('${row.id}')">
                               <i data-lucide="check-circle" style="width:14px;height:14px;"></i>
                           </button>
                           <button class="icon-btn danger" title="Reject"
                               onclick="rejectCapital('${row.id}')">
                               <i data-lucide="x-circle" style="width:14px;height:14px;"></i>
                           </button>` :
                            '';

                        const deleteBtn = canDelete ?
                            `<button class="icon-btn danger" title="Hapus"
                               onclick="deleteCapital('${row.id}','${escHtml(row.name)}')">
                               <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                           </button>` :
                            '';

                        return `<div class="table-actions">${editBtn}${approveBtn}${deleteBtn}</div>`;
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

        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            if (settings.nTable.id !== 'capitalTable') return true;
            const filterVal = document.getElementById('capitalStatusFilter').value;
            if (!filterVal) return true;
            const rowData = table.row(dataIndex).data();
            return rowData && rowData.status === filterVal;
        });

        document.getElementById('capitalStatusFilter').addEventListener('change', function() {
            table.draw();
        });
    });

    function escHtml(str) {
        if (str == null) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/'/g, "\\'").replace(/"/g, '&quot;');
    }

    // ── Create ────────────────────────────────────────────────────────────────────
    function openCreateModal() {
        createForm.reset();
        if (activeCapitalKapalId) document.getElementById('createCapitalKapalSelect').value = activeCapitalKapalId;
        createModal.classList.add('active');
    }

    function closeCreateModal() {
        createModal.classList.remove('active');
    }

    function storeCapital() {
        const payload = {
            kapal_id: document.getElementById('createCapitalKapalSelect').value || null,
            date: createForm.date.value,
            name: createForm.name.value,
            nominal: getRaw(createForm.nominal),
            note: createForm.note.value,
        };
        axios.post('{{ route('capital.store') }}', payload)
            .then(res => {
                showSuccess('Berhasil', res.data.message);
                closeCreateModal();
                table.ajax.reload(null, false);
            })
            .catch(err => {
                const errors = err.response?.data?.errors;
                showError('Gagal', errors ? Object.values(errors).flat().join('\n') : err.response?.data?.message || 'Terjadi kesalahan');
            });
    }

    // ── Edit ──────────────────────────────────────────────────────────────────────
    function openEditModal(id, date, name, nominal, note, kapalId) {
        editId = id;
        editForm.date.value = date;
        editForm.name.value = name;
        setRaw(editForm.nominal, nominal);
        editForm.nominal.value = parseInt(nominal) ? Currency.format(nominal) : '';
        editForm.note.value = note || '';
        document.getElementById('editCapitalKapalSelect').value = kapalId || '';
        editModal.classList.add('active');
    }

    function closeEditModal() {
        editModal.classList.remove('active');
        editId = null;
    }

    function updateCapital() {
        const payload = {
            kapal_id: document.getElementById('editCapitalKapalSelect').value || null,
            date: editForm.date.value,
            name: editForm.name.value,
            nominal: getRaw(editForm.nominal),
            note: editForm.note.value,
        };
        axios.put(`/capital/${editId}`, payload)
            .then(res => {
                showSuccess('Berhasil', res.data.message);
                closeEditModal();
                table.ajax.reload(null, false);
            })
            .catch(err => {
                const errors = err.response?.data?.errors;
                showError('Gagal', errors ? Object.values(errors).flat().join('\n') : err.response?.data?.message || 'Terjadi kesalahan');
            });
    }

    // ── Approve / Reject ──────────────────────────────────────────────────────────
    function approveCapital(id) {
        showConfirm({
            title: 'Konfirmasi Approve',
            message: 'Yakin ingin menyetujui data modal ini?',
            type: 'success',
            confirmText: 'Ya, Setujui',
            onConfirm: async () => {
                try {
                    const res = await axios.post(`/capital/${id}/approve`);
                    showSuccess('Berhasil', res.data.message);
                    table.ajax.reload(null, false);
                } catch (err) {
                    showError('Gagal', err.response?.data?.message || 'Gagal menyetujui data');
                }
            }
        });
    }

    function rejectCapital(id) {
        showConfirm({
            title: 'Konfirmasi Reject',
            message: 'Yakin ingin menolak data modal ini?',
            type: 'danger',
            confirmText: 'Ya, Tolak',
            onConfirm: async () => {
                try {
                    const res = await axios.post(`/capital/${id}/reject`);
                    showSuccess('Berhasil', res.data.message);
                    table.ajax.reload(null, false);
                } catch (err) {
                    showError('Gagal', err.response?.data?.message || 'Gagal menolak data');
                }
            }
        });
    }

    // ── Delete ────────────────────────────────────────────────────────────────────
    function deleteCapital(id, name) {
        showConfirm({
            title: 'Konfirmasi Hapus',
            message: `Yakin ingin menghapus data modal "${name}"? Tindakan ini tidak dapat dibatalkan.`,
            type: 'danger',
            confirmText: 'Ya, Hapus',
            onConfirm: async () => {
                try {
                    const res = await axios.delete(`/capital/${id}`);
                    showSuccess('Berhasil', res.data.message);
                    table.ajax.reload(null, false);
                } catch (err) {
                    showError('Gagal', err.response?.data?.message || 'Gagal menghapus data');
                }
            }
        });
    }
</script>
@endpush
