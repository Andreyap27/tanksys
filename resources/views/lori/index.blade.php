@extends('layouts.app')

@section('title', 'Data Lori')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@23.1.0/build/css/intlTelInput.css">
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title-text">Mobil Tangki (Lori)</h1>
        <p class="page-subtitle">Kelola data pengiriman mobil tangki</p>
    </div>
    <div class="page-actions">
        @if($canManage)
        <button class="btn btn-primary" onclick="openCreateModal()">
            <i data-lucide="plus" style="width:16px;height:16px;"></i>
            Tambah Lori
        </button>
        @endif
    </div>
</div>
{{-- Tabs --}}
<div class="tab-bar" id="loriMobilTabs">
    <button class="tab active" onclick="switchLoriTab(this, '')">Semua</button>
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
                        <th>Rute</th>
                        <th>Harga</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

@include('lori.modals.create')
@include('lori.modals.edit')
@include('customer.modals.create', [
'modalId' => 'quickCustomerModal',
'formId' => 'quickCustomerForm',
'contactId' => 'quickCustomerContact',
'onClose' => 'closeQuickCustomerModal()',
'onSave' => 'storeQuickCustomer()',
'hideCode' => true,
'modalStyle'=> 'z-index:1100;',
])
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@23.1.0/build/js/intlTelInput.min.js"></script>
<script>
    const canManage = @json($canManage);
    const canDelete = @json($canDelete);
    let table;
    let editId = null;
    let activeLoriMobilId = '';
    let quickCustomerContext = 'create';
    const createForm = document.getElementById('createForm');
    const editForm = document.getElementById('editForm');
    const quickCustomerForm = document.getElementById('quickCustomerForm');
    const createModal = document.getElementById('createModal');
    const editModal = document.getElementById('editModal');
    const quickCustomerModal = document.getElementById('quickCustomerModal');

    const itiQuickCustomer = window.intlTelInput(document.getElementById('quickCustomerContact'), {
        initialCountry: 'id',
        separateDialCode: true,
        utilsScript: 'https://cdn.jsdelivr.net/npm/intl-tel-input@23.1.0/build/js/utils.js',
    });

    // ── Mobil Tabs ────────────────────────────────────────────────────────────────
    function loadLoriMobils() {
        axios.get('{{ route('mobil-master.list') }}').then(res => {
            const tabBar = document.getElementById('loriMobilTabs');
            const opts = res.data.map(m => `<option value="${m.id}">${m.name}${m.plat_nomer ? ' — '+m.plat_nomer : ''}</option>`).join('');
            
            // Add "Semua" (All) tab
            const allBtn = document.createElement('button');
            allBtn.className = 'tab active';
            allBtn.innerHTML = '<i data-lucide="list" style="width:16px;height:16px;"></i> Semua';
            allBtn.onclick = function() {
                switchLoriTab(this, null);
            };
            tabBar.appendChild(allBtn);
            
            res.data.forEach(m => {
                const btn = document.createElement('button');
                btn.className = 'tab';
                btn.dataset.mobilId = m.id;
                btn.innerHTML = `<i data-lucide="truck" style="width:16px;height:16px;"></i> ${m.plat_nomer || m.name}`;
                btn.onclick = function() {
                    switchLoriTab(this, m.id);
                };
                tabBar.appendChild(btn);
            });
            lucide.createIcons();
            
            document.getElementById('createLoriMobilSelect').innerHTML = '<option value="">-- Pilih Mobil --</option>' + opts;
            document.getElementById('editLoriMobilSelect').innerHTML = '<option value="">-- Pilih Mobil --</option>' + opts;
        });
    }

    function switchLoriTab(btn, mobilId) {
        document.querySelectorAll('#loriMobilTabs .tab').forEach(t => t.classList.remove('active'));
        btn.classList.add('active');
        activeLoriMobilId = mobilId;
        table.ajax.reload(null, false);
    }

    // ── Select2 ───────────────────────────────────────────────────────────────────
    function initSelect2() {
        $('#createCustomerSelect').select2({
            placeholder: '-- Pilih Customer --',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#createModal .modal-box'),
        });
        $('#editCustomerSelect').select2({
            placeholder: '-- Pilih Customer --',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#editModal .modal-box'),
        });
    }

    // ── Load customer options ─────────────────────────────────────────────────────
    function loadCustomerOptions(selectedId = '', callback = null) {
        axios.get('{{ route('
            customer.data ') }}').then(res => {
            const customers = res.data.data || res.data;
            const options = customers.map(c =>
                `<option value="${c.id}">${c.customer_id ? c.customer_id + ' — ' : ''}${c.name}</option>`
            ).join('');

            document.getElementById('createCustomerSelect').innerHTML = '<option value=""></option>' + options;
            document.getElementById('editCustomerSelect').innerHTML = '<option value=""></option>' + options;

            if (selectedId) {
                $('#createCustomerSelect').val(selectedId).trigger('change');
                $('#editCustomerSelect').val(selectedId).trigger('change');
            } else {
                $('#createCustomerSelect').val(null).trigger('change');
                $('#editCustomerSelect').val(null).trigger('change');
            }

            if (callback) callback();
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
        loadLoriMobils();
        initSelect2();

        table = $('#loriTable').DataTable({
            ajax: {
                url: '{{ route('
                lori.data ') }}',
                type: 'GET',
                data: function(d) {
                    if (activeLoriMobilId) d.mobil_id = activeLoriMobilId;
                }
            },
            columns: [{
                    data: 'date'
                },
                {
                    data: 'customer_name'
                },
                {
                    data: null,
                    render: (data, type, row) => `${escHtml(row.from)} &rarr; ${escHtml(row.to)}`
                },
                {
                    data: 'price',
                    render: (data) => Currency.symbol + ' ' + data
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return `
                        <div class="table-actions">
                            ${canManage ? `<button class="icon-btn primary" title="Edit"
                                onclick="openEditModal('${row.id}', '${row.date_raw}', '${row.customer_id}', '${escHtml(row.from)}', '${escHtml(row.to)}', '${row.price_raw}', '${row.mobil_id || ''}')">
                                <i data-lucide="pencil" style="width:14px;height:14px;"></i>
                            </button>` : ''}
                            ${canDelete ? `<button class="icon-btn danger" title="Hapus"
                                onclick="deleteLori('${row.id}', '${escHtml(row.customer_name)}')">
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
        loadCustomerOptions();
        if (activeLoriMobilId) document.getElementById('createLoriMobilSelect').value = activeLoriMobilId;
        createModal.classList.add('active');
    }

    function closeCreateModal() {
        createModal.classList.remove('active');
    }

    function storeLori() {
        const customer = $('#createCustomerSelect').val();
        if (!customer) {
            showError('Validasi', 'Customer wajib dipilih.');
            return;
        }

        const payload = {
            mobil_id: document.getElementById('createLoriMobilSelect').value || null,
            date: createForm.date.value,
            customer_id: customer,
            from: createForm.from.value,
            to: createForm.to.value,
            price: getRaw(createForm.price),
        };

        axios.post('{{ route('
                lori.store ') }}', payload)
            .then(res => {
                showSuccess('Berhasil', res.data.message || 'Data berhasil disimpan');
                closeCreateModal();
                table.ajax.reload(null, false);
            })
            .catch(err => {
                const errors = err.response?.data?.errors;
                if (errors) {
                    showError('Gagal', Object.values(errors).flat().join('\n'));
                } else {
                    showError('Gagal', err.response?.data?.message || 'Terjadi kesalahan');
                }
            });
    }

    // ── Edit ──────────────────────────────────────────────────────────────────────

    function openEditModal(id, date, customer_id, from, to, price, mobilId) {
        editId = id;
        editForm.date.value = date;
        editForm.from.value = from;
        editForm.to.value = to;
        setRaw(editForm.price, price);
        editForm.price.value = parseInt(price) ? Currency.format(price) : '';
        loadCustomerOptions(customer_id);
        document.getElementById('editLoriMobilSelect').value = mobilId || '';
        editModal.classList.add('active');
    }

    function closeEditModal() {
        editModal.classList.remove('active');
        editId = null;
    }

    function updateLori() {
        const customer = $('#editCustomerSelect').val();
        if (!customer) {
            showError('Validasi', 'Customer wajib dipilih.');
            return;
        }

        const payload = {
            mobil_id: document.getElementById('editLoriMobilSelect').value || null,
            date: editForm.date.value,
            customer_id: customer,
            from: editForm.from.value,
            to: editForm.to.value,
            price: getRaw(editForm.price),
        };

        axios.put(`/lori/${editId}`, payload)
            .then(res => {
                showSuccess('Berhasil', res.data.message || 'Data berhasil diupdate');
                closeEditModal();
                table.ajax.reload(null, false);
            })
            .catch(err => {
                const errors = err.response?.data?.errors;
                if (errors) {
                    showError('Gagal', Object.values(errors).flat().join('\n'));
                } else {
                    showError('Gagal', err.response?.data?.message || 'Terjadi kesalahan');
                }
            });
    }

    // ── Delete ────────────────────────────────────────────────────────────────────

    function deleteLori(id, customerName) {
        showConfirm({
            title: 'Konfirmasi Hapus',
            message: `Yakin ingin menghapus data lori untuk "${customerName}"? Tindakan ini tidak dapat dibatalkan.`,
            type: 'danger',
            confirmText: 'Ya, Hapus',
            onConfirm: async () => {
                try {
                    const res = await axios.delete(`/lori/${id}`);
                    showSuccess('Berhasil', res.data.message || 'Data berhasil dihapus');
                    table.ajax.reload(null, false);
                } catch (err) {
                    showError('Gagal', err.response?.data?.message || 'Gagal menghapus data');
                }
            }
        });
    }

    // ── Quick Add Customer ────────────────────────────────────────────────────────

    function openQuickCustomerModal(context) {
        quickCustomerContext = context;
        quickCustomerForm.reset();
        itiQuickCustomer.setNumber('');
        quickCustomerModal.classList.add('active');
    }

    function closeQuickCustomerModal() {
        quickCustomerModal.classList.remove('active');
    }

    function storeQuickCustomer() {
        const payload = {
            name: quickCustomerForm.name.value,
            address: quickCustomerForm.address.value,
            pic_name: quickCustomerForm.pic_name.value,
            contact: itiQuickCustomer.getNumber(),
        };

        axios.post('{{ route('
                customer.store ') }}', payload)
            .then(res => {
                const newId = res.data.id;
                showSuccess('Berhasil', res.data.message || 'Customer berhasil ditambahkan');
                closeQuickCustomerModal();
                loadCustomerOptions(newId, () => {
                    if (quickCustomerContext === 'create') {
                        $('#createCustomerSelect').val(newId).trigger('change');
                    } else {
                        $('#editCustomerSelect').val(newId).trigger('change');
                    }
                });
            })
            .catch(err => {
                const errors = err.response?.data?.errors;
                if (errors) {
                    showError('Gagal', Object.values(errors).flat().join('\n'));
                } else {
                    showError('Gagal', err.response?.data?.message || 'Terjadi kesalahan');
                }
            });
    }
</script>
@endpush