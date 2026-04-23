@extends('layouts.app')

@section('title', 'Expenses Mobil Tangki')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title-text">Expenses Mobil Tangki</h1>
        <p class="page-subtitle">Kelola pengeluaran operasional mobil tangki</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('lori-expense.trash') }}" class="btn btn-secondary">
            <i data-lucide="trash-2" style="width:16px;height:16px;"></i>
            Trash
        </a>
        @if(auth()->user()->canManage())
        <button class="btn btn-primary" onclick="openCreateModal()">
            <i data-lucide="plus" style="width:16px;height:16px;"></i>
            Tambah Expense
        </button>
        @endif
    </div>
</div>

{{-- Summary Cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1.25rem;margin-bottom:1.5rem;">
    <div class="dash-stat ds-profit">
        <div class="dash-stat__header">
            <div class="dash-stat__icon">
                <i data-lucide="arrow-up-circle" style="width:20px;height:20px;"></i>
            </div>
            <div>
                <div class="dash-stat__label">Total In (Kredit)</div>
                <div class="dash-stat__value" id="loriExpKreditCard">Rp 0</div>
            </div>
        </div>
        <div class="dash-stat__bg-icon">
            <i data-lucide="arrow-up-circle" style="width:110px;height:110px;"></i>
        </div>
    </div>
    <div class="dash-stat ds-expense">
        <div class="dash-stat__header">
            <div class="dash-stat__icon">
                <i data-lucide="arrow-down-circle" style="width:20px;height:20px;"></i>
            </div>
            <div>
                <div class="dash-stat__label">Total Out (Debit)</div>
                <div class="dash-stat__value" id="loriExpDebitCard">Rp 0</div>
            </div>
        </div>
        <div class="dash-stat__bg-icon">
            <i data-lucide="arrow-down-circle" style="width:110px;height:110px;"></i>
        </div>
    </div>
    <div class="dash-stat" id="loriExpBalanceWrapper">
        <div class="dash-stat__header">
            <div class="dash-stat__icon">
                <i data-lucide="scale" style="width:20px;height:20px;"></i>
            </div>
            <div>
                <div class="dash-stat__label">Balance</div>
                <div class="dash-stat__value" id="loriExpBalanceCard">Rp 0</div>
            </div>
        </div>
        <div class="dash-stat__bg-icon">
            <i data-lucide="scale" style="width:110px;height:110px;"></i>
        </div>
    </div>
</div>

{{-- Tabs --}}
<div class="tab-bar" id="loriExpenseMobilTabs">
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
                        <th>Type</th>
                        <th>Deskripsi</th>
                        <th>Kategori</th>
                        <th>Catatan</th>
                        <th>Nominal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

@include('lori-expense.modals.create')
@include('lori-expense.modals.edit')
@endsection

@push('scripts')
<script>
    let table;
    let editId = null;
    let activeLoriExpenseMobilId = '';
    const createForm = document.getElementById('createForm');
    const editForm = document.getElementById('editForm');
    const createModal = document.getElementById('createModal');
    const editModal = document.getElementById('editModal');

    const categoryBadge = {
        'BBM': 'badge-danger',
        'Gaji': 'badge-info',
        'Maintenance': 'badge-warning',
        'Umum': 'badge-success',
    };

    // ── Mobil Tabs ────────────────────────────────────────────────────────────────
    function loadLoriExpenseMobils() {
        axios.get('{{ route('mobil-master.list') }}').then(res => {
            const tabBar = document.getElementById('loriExpenseMobilTabs');
            const opts = res.data.map(m => `<option value="${m.id}">${m.name}${m.plat_nomer ? ' — '+m.plat_nomer : ''}</option>`).join('');

            const allBtn = document.createElement('button');
            allBtn.className = 'tab active';
            allBtn.innerHTML = '<i data-lucide="list" style="width:16px;height:16px;"></i> Semua';
            allBtn.onclick = function() {
                switchLoriExpenseTab(this, null);
            };
            tabBar.appendChild(allBtn);

            res.data.forEach(m => {
                const btn = document.createElement('button');
                btn.className = 'tab';
                btn.dataset.mobilId = m.id;
                btn.innerHTML = `<i data-lucide="truck" style="width:16px;height:16px;"></i> ${m.plat_nomer || m.name}`;
                btn.onclick = function() {
                    switchLoriExpenseTab(this, m.id);
                };
                tabBar.appendChild(btn);
            });
            lucide.createIcons();

            document.getElementById('createLoriExpenseMobilSelect').innerHTML = '<option value="">-- Pilih Mobil --</option>' + opts;
            document.getElementById('editLoriExpenseMobilSelect').innerHTML = '<option value="">-- Pilih Mobil --</option>' + opts;
        });
    }

    function switchLoriExpenseTab(btn, mobilId) {
        document.querySelectorAll('#loriExpenseMobilTabs .tab').forEach(t => t.classList.remove('active'));
        btn.classList.add('active');
        activeLoriExpenseMobilId = mobilId;
        refreshLoriExpSummary(mobilId);
        table.ajax.reload(null, false);
    }

    function refreshLoriExpSummary(mobilId) {
        const params = {};
        if (mobilId) params.mobil_id = mobilId;
        axios.get('{{ route('lori-expense.summary') }}', {
                params
            }).then(res => {
            document.getElementById('loriExpKreditCard').textContent = 'Rp ' + Currency.number(res.data.kredit || 0);
            document.getElementById('loriExpDebitCard').textContent = 'Rp ' + Currency.number(res.data.debit || 0);
            document.getElementById('loriExpBalanceCard').textContent = 'Rp ' + Currency.number(res.data.balance || 0);
            const wrapper = document.getElementById('loriExpBalanceWrapper');
            wrapper.classList.remove('ds-profit', 'ds-loss');
            wrapper.classList.add((res.data.balance || 0) >= 0 ? 'ds-profit' : 'ds-loss');
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
        loadLoriExpenseMobils();
        refreshLoriExpSummary(null);

        table = $('#loriExpenseTable').DataTable({
            ajax: {
                url: '{{ route('lori-expense.data') }}',
                type: 'GET',
                data: function(d) {
                    if (activeLoriExpenseMobilId) d.mobil_id = activeLoriExpenseMobilId;
                }
            },
            processing: true,
            columns: [{
                    data: 'date'
                },
                {
                    data: 'type',
                    render: (data) => data === 'in' ?
                        '<span class="badge badge-success">In</span>' : '<span class="badge badge-danger">Out</span>'
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
                    data: 'noted',
                    render: (data) => data && data !== '-' ? data : '<span class="text-muted">-</span>'
                },
                {
                    data: 'nominal',
                    render: (data, type, row) => {
                        const cls = row.type === 'in' ? 'text-success' : 'text-danger';
                        return `<span class="${cls}">${Currency.symbol} ${data}</span>`;
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return `
                        <div class="table-actions">
                            ${canManage ? `<button class="icon-btn primary" title="Edit"
                                onclick="openEditModal('${row.id}','${row.date_raw}','${row.type}','${escHtml(row.description)}','${escHtml(row.category)}','${row.nominal_raw}','${escHtml(row.noted)}','${row.mobil_id || ''}')">
                                <i data-lucide="pencil" style="width:14px;height:14px;"></i>
                            </button>` : ''}
                            ${canDelete ? `<button class="icon-btn danger" title="Hapus"
                                onclick="deleteExpense('${row.id}','${escHtml(row.description)}')">
                                <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                            </button>` : ''}
                        </div>
                    `;
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

    function escHtml(str) {
        if (str == null) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/'/g, "\\'").replace(/"/g, '&quot;');
    }

    // ── Create ────────────────────────────────────────────────────────────────────
    function openCreateModal() {
        createForm.reset();
        setRaw(createForm.nominal, 0);
        if (activeLoriExpenseMobilId) document.getElementById('createLoriExpenseMobilSelect').value = activeLoriExpenseMobilId;
        createModal.classList.add('active');
    }

    function closeCreateModal() {
        createModal.classList.remove('active');
    }

    function storeExpense() {
        const payload = {
            mobil_id: document.getElementById('createLoriExpenseMobilSelect').value || null,
            type: createForm.type.value,
            date: createForm.date.value,
            description: createForm.description.value,
            category: createForm.category.value,
            nominal: getRaw(createForm.nominal),
            noted: createForm.noted.value,
        };
        axios.post('{{ route('lori-expense.store') }}', payload)
            .then(res => {
                showSuccess('Berhasil', res.data.message);
                closeCreateModal();
                table.ajax.reload(null, false);
                refreshLoriExpSummary(activeLoriExpenseMobilId);
            })
            .catch(err => {
                const errors = err.response?.data?.errors;
                showError('Gagal', errors ? Object.values(errors).flat().join('\n') : err.response?.data?.message || 'Terjadi kesalahan');
            });
    }

    // ── Edit ──────────────────────────────────────────────────────────────────────
    function openEditModal(id, date, type, description, category, nominal, noted, mobilId) {
        editId = id;
        editForm.date.value = date;
        editForm.type.value = type;
        editForm.description.value = description;
        editForm.category.value = category;
        editForm.noted.value = noted !== '-' ? noted : '';
        setRaw(editForm.nominal, nominal);
        editForm.nominal.value = parseInt(nominal) ? Currency.format(nominal) : '';
        document.getElementById('editLoriExpenseMobilSelect').value = mobilId || '';
        editModal.classList.add('active');
    }

    function closeEditModal() {
        editModal.classList.remove('active');
        editId = null;
    }

    function updateExpense() {
        const payload = {
            mobil_id: document.getElementById('editLoriExpenseMobilSelect').value || null,
            type: editForm.type.value,
            date: editForm.date.value,
            description: editForm.description.value,
            category: editForm.category.value,
            nominal: getRaw(editForm.nominal),
            noted: editForm.noted.value,
        };
        axios.put(`/lori-expense/${editId}`, payload)
            .then(res => {
                showSuccess('Berhasil', res.data.message);
                closeEditModal();
                table.ajax.reload(null, false);
                refreshLoriExpSummary(activeLoriExpenseMobilId);
            })
            .catch(err => {
                const errors = err.response?.data?.errors;
                showError('Gagal', errors ? Object.values(errors).flat().join('\n') : err.response?.data?.message || 'Terjadi kesalahan');
            });
    }

    // ── Delete ────────────────────────────────────────────────────────────────────
    function deleteExpense(id, description) {
        showConfirm({
            title: 'Konfirmasi Hapus',
            message: `Yakin ingin menghapus "${description}"? Tindakan ini tidak dapat dibatalkan.`,
            type: 'danger',
            confirmText: 'Ya, Hapus',
            onConfirm: async () => {
                try {
                    const res = await axios.delete(`/lori-expense/${id}`);
                    showSuccess('Berhasil', res.data.message);
                    table.ajax.reload(null, false);
                    refreshLoriExpSummary(activeLoriExpenseMobilId);
                } catch (err) {
                    showError('Gagal', err.response?.data?.message || 'Gagal menghapus data');
                }
            }
        });
    }
</script>
@endpush
