@extends('layouts.app')

@section('title', 'Detail Karyawan - ' . $karyawan->nama_karyawan)

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title-text">{{ $karyawan->nama_karyawan }}</h1>
        <p class="page-subtitle">Detail karyawan & history slip gaji</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('gaji.index') }}" class="btn btn-secondary">
            <i data-lucide="arrow-left" style="width:16px;height:16px;"></i>
            Kembali
        </a>
        @if(auth()->user()->canManage())
        <a href="{{ route('gaji.edit', $karyawan->id) }}" class="btn btn-secondary">
            <i data-lucide="pencil" style="width:16px;height:16px;"></i>
            Edit
        </a>
        <button class="btn btn-primary" onclick="openGenerateModal()">
            <i data-lucide="file-plus" style="width:16px;height:16px;"></i>
            Generate Slip Gaji
        </button>
        @endif
    </div>
</div>

{{-- Two-column info cards --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-bottom:1.5rem;">
    {{-- Left: Data Karyawan & Gaji --}}
    <div class="card" style="margin-bottom:0;">
        <div class="card-header" style="padding:1rem 1.25rem 0.5rem;">
            <h5 style="margin:0;font-weight:600;color:var(--primary);display:flex;align-items:center;gap:6px;">
                <i data-lucide="user" style="width:16px;height:16px;flex-shrink:0;"></i>
                Data Karyawan & Gaji
            </h5>
        </div>
        <div class="card-content" style="padding:1rem 1.25rem;">
            <table style="width:100%;border-collapse:collapse;font-size:0.875rem;">
                <tr>
                    <td style="padding:6px 0;color:var(--muted-foreground);width:45%;">Nama</td>
                    <td style="padding:6px 0;font-weight:600;">{{ $karyawan->nama_karyawan }}</td>
                </tr>
                <tr>
                    <td style="padding:6px 0;color:var(--muted-foreground);">No KTP</td>
                    <td style="padding:6px 0;">{{ $karyawan->no_ktp ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="padding:6px 0;color:var(--muted-foreground);">No HP</td>
                    <td style="padding:6px 0;">{{ $karyawan->no_hp ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="padding:6px 0;color:var(--muted-foreground);">Jabatan</td>
                    <td style="padding:6px 0;">{{ $karyawan->jabatan ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="padding:6px 0;color:var(--muted-foreground);">Gaji Pokok</td>
                    <td style="padding:6px 0;font-weight:600;">Rp {{ number_format($karyawan->gaji_pokok, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="padding:6px 0;color:var(--muted-foreground);">Tunjangan</td>
                    <td style="padding:6px 0;">Rp {{ number_format($karyawan->tunjangan, 0, ',', '.') }}</td>
                </tr>
                @if($karyawan->noted)
                <tr>
                    <td style="padding:6px 0;color:var(--muted-foreground);">Catatan</td>
                    <td style="padding:6px 0;">{{ $karyawan->noted }}</td>
                </tr>
                @endif
            </table>
        </div>
    </div>

    {{-- Right: Pinjaman --}}
    <div class="card" style="margin-bottom:0;">
        <div class="card-header" style="padding:1rem 1.25rem 0.5rem;display:flex;justify-content:space-between;align-items:center;">
            <h5 style="margin:0;font-weight:600;color:var(--destructive);display:flex;align-items:center;gap:6px;">
                <i data-lucide="credit-card" style="width:16px;height:16px;flex-shrink:0;"></i>
                Pinjaman
            </h5>
            @if(auth()->user()->canManage())
            <button class="btn btn-sm btn-primary" onclick="openAddPinjamanModal()" style="font-size:0.75rem;padding:4px 10px;">
                <i data-lucide="plus" style="width:13px;height:13px;"></i> Tambah
            </button>
            @endif
        </div>
        <div class="card-content" style="padding:0;">
            @if($pinjamans->isEmpty())
            <p style="padding:1rem 1.25rem;color:var(--muted-foreground);font-size:0.875rem;">Belum ada pinjaman.</p>
            @else
            <table style="width:100%;border-collapse:collapse;font-size:0.8rem;">
                <thead>
                    <tr style="background:var(--muted);">
                        <th style="padding:7px 12px;text-align:left;font-weight:600;color:var(--muted-foreground);">Pokok</th>
                        <th style="padding:7px 12px;text-align:left;font-weight:600;color:var(--muted-foreground);">Angsuran</th>
                        <th style="padding:7px 12px;text-align:left;font-weight:600;color:var(--muted-foreground);">Sisa</th>
                        <th style="padding:7px 12px;text-align:left;font-weight:600;color:var(--muted-foreground);">Status</th>
                        @if(auth()->user()->canManage())
                        <th style="padding:7px 12px;"></th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($pinjamans as $p)
                    <tr style="border-top:1px solid var(--border);">
                        <td style="padding:7px 12px;">Rp {{ $p['pokok'] }}</td>
                        <td style="padding:7px 12px;">{{ $p['angsuran'] }}x</td>
                        <td style="padding:7px 12px;font-weight:600;color:{{ $p['lunas'] ? 'var(--success)' : 'var(--destructive)' }};">
                            {{ $p['lunas'] ? 'Lunas' : 'Rp '.$p['sisa'] }}
                        </td>
                        <td style="padding:7px 12px;">
                            @if($p['lunas'])
                                <span class="badge badge-success">Lunas</span>
                            @else
                                <span class="badge badge-warning">Aktif</span>
                            @endif
                        </td>
                        @if(auth()->user()->canManage())
                        <td style="padding:7px 12px;">
                            <div class="table-actions">
                                <button class="icon-btn primary" title="Edit"
                                    onclick="openEditPinjamanModal('{{ $p['id'] }}', {{ $p['pokok_raw'] }}, {{ $p['angsuran'] }}, {{ $p['subsidi_raw'] }}, '{{ addslashes($p['noted']) }}')">
                                    <i data-lucide="pencil" style="width:13px;height:13px;"></i>
                                </button>
                                @if(auth()->user()->canDelete())
                                <button class="icon-btn danger" title="Hapus" onclick="deletePinjaman('{{ $p['id'] }}')">
                                    <i data-lucide="trash-2" style="width:13px;height:13px;"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>
</div>

{{-- Slip History --}}
<div class="card">
    <div class="card-header" style="padding:1rem 1.25rem 0.5rem;display:flex;justify-content:space-between;align-items:center;">
        <h5 style="margin:0;font-weight:600;display:flex;align-items:center;gap:6px;">
            <i data-lucide="file-text" style="width:16px;height:16px;flex-shrink:0;"></i>
            History Slip Gaji
        </h5>
    </div>
    <div class="card-content" style="padding:0;">
        <div class="table-wrap">
            <table id="slipTable" class="w-full">
                <thead>
                    <tr>
                        <th>Periode</th>
                        <th>Gaji Pokok</th>
                        <th>Tunjangan</th>
                        <th>Extra</th>
                        <th>Bonus</th>
                        <th>Pot. Pinjaman</th>
                        <th>Pot. Lain</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($slips as $slip)
                    <tr>
                        <td>{{ $slip['period'] }}</td>
                        <td>{{ $slip['gaji_pokok'] }}</td>
                        <td>{{ $slip['tunjangan'] }}</td>
                        <td>{{ $slip['extra'] }}</td>
                        <td>{{ $slip['bonus'] }}</td>
                        <td>{{ $slip['potongan_pinjaman'] }}</td>
                        <td>{{ $slip['potongan_lain'] }}</td>
                        <td style="font-weight:600;">{{ $slip['total'] }}</td>
                        <td>
                            @if($slip['status'] === 'approved')
                                <span class="badge badge-success">Approved</span>
                            @elseif($slip['status'] === 'rejected')
                                <span class="badge badge-danger">Rejected</span>
                            @else
                                <span class="badge badge-warning">Pending</span>
                            @endif
                        </td>
                        <td>
                            <div class="table-actions">
                                <button class="icon-btn secondary" title="Print Slip" onclick="openPrintModal('{{ $slip['id'] }}')">
                                    <i data-lucide="printer" style="width:14px;height:14px;"></i>
                                </button>
                                @if(auth()->user()->canApprove() && $slip['status'] === 'pending')
                                <button class="icon-btn success" title="Approve" onclick="approveSlip('{{ $slip['id'] }}')">
                                    <i data-lucide="check-circle" style="width:14px;height:14px;"></i>
                                </button>
                                <button class="icon-btn danger" title="Reject" onclick="rejectSlip('{{ $slip['id'] }}')">
                                    <i data-lucide="x-circle" style="width:14px;height:14px;"></i>
                                </button>
                                @endif
                                @if(auth()->user()->canManage())
                                <button class="icon-btn primary" title="Edit" onclick="openEditSlipModal('{{ $slip['id'] }}', '{{ $slip['period_raw'] }}', {{ $slip['extra_raw'] }}, {{ $slip['bonus_raw'] }}, {{ $slip['potongan_pinjaman_raw'] }}, {{ $slip['potongan_lain_raw'] }}, '{{ addslashes($slip['noted'] === '-' ? '' : $slip['noted']) }}')">
                                    <i data-lucide="pencil" style="width:14px;height:14px;"></i>
                                </button>
                                @endif
                                @if(auth()->user()->canDelete())
                                <button class="icon-btn danger" title="Hapus" onclick="deleteSlip('{{ $slip['id'] }}')">
                                    <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Pinjaman Modal --}}
<div class="modal-overlay" id="addPinjamanModal">
    <div class="modal-box" style="max-width:460px;">
        <div class="modal-header">
            <h3 class="modal-title">Tambah Pinjaman</h3>
            <button class="modal-close-btn" onclick="closeAddPinjamanModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="addPinjamanForm" onsubmit="return false;">
                <div class="form-grid">
                    <div class="form-group full">
                        <label class="form-label">Pokok Pinjaman <span class="text-danger">*</span></label>
                        <input type="text" id="apPokok" class="form-input currency-input-p" placeholder="0">
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Jumlah Angsuran <span class="text-danger">*</span></label>
                        <input type="text" id="apAngsuran" class="form-input" placeholder="Berapa kali"
                               inputmode="numeric" oninput="this.value=this.value.replace(/\D/g,'')">
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Subsidi</label>
                        <input type="text" id="apSubsidi" class="form-input currency-input-p" placeholder="0">
                        <p style="font-size:0.75rem;color:var(--muted-foreground);margin-top:4px;">Mengurangi sisa pinjaman tanpa potong gaji</p>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Catatan</label>
                        <textarea id="apNoted" class="form-textarea" rows="2"></textarea>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeAddPinjamanModal()">Batal</button>
            <button class="btn btn-primary" onclick="submitAddPinjaman()">Simpan</button>
        </div>
    </div>
</div>

{{-- Edit Pinjaman Modal --}}
<div class="modal-overlay" id="editPinjamanModal">
    <div class="modal-box" style="max-width:460px;">
        <div class="modal-header">
            <h3 class="modal-title">Edit Pinjaman</h3>
            <button class="modal-close-btn" onclick="closeEditPinjamanModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="editPinjamanForm" onsubmit="return false;">
                <div class="form-grid">
                    <div class="form-group full">
                        <label class="form-label">Pokok Pinjaman <span class="text-danger">*</span></label>
                        <input type="text" id="epPokok" class="form-input currency-input-p" placeholder="0">
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Jumlah Angsuran <span class="text-danger">*</span></label>
                        <input type="text" id="epAngsuran" class="form-input" placeholder="Berapa kali"
                               inputmode="numeric" oninput="this.value=this.value.replace(/\D/g,'')">
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Subsidi</label>
                        <input type="text" id="epSubsidi" class="form-input currency-input-p" placeholder="0">
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Catatan</label>
                        <textarea id="epNoted" class="form-textarea" rows="2"></textarea>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeEditPinjamanModal()">Batal</button>
            <button class="btn btn-primary" onclick="submitEditPinjaman()">Update</button>
        </div>
    </div>
</div>

{{-- Print Preview Modal --}}
<div class="modal-overlay" id="printModal">
    <div class="modal-box" style="max-width:780px;width:95%;">
        <div class="modal-header">
            <h3 class="modal-title">Preview Slip Gaji</h3>
            <button class="modal-close-btn" onclick="closePrintModal()">&times;</button>
        </div>
        <div class="modal-body" style="padding:0;overflow:hidden;border-radius:0 0 8px 8px;">
            <iframe id="printFrame" style="width:100%;height:620px;border:none;" src=""></iframe>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closePrintModal()">Tutup</button>
            <button class="btn btn-primary" onclick="document.getElementById('printFrame').contentWindow.print()">
                <i data-lucide="printer" style="width:14px;height:14px;"></i>
                Print / Save PDF
            </button>
        </div>
    </div>
</div>

{{-- Generate / Preview Slip Modal --}}
<div class="modal-overlay" id="generateModal">
    <div class="modal-box" style="max-width:540px;">
        <div class="modal-header">
            <h3 class="modal-title">Generate Slip Gaji</h3>
            <button class="modal-close-btn" onclick="closeGenerateModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="generateForm" onsubmit="return false;">
                <div style="background:var(--muted);border-radius:8px;padding:0.75rem 1rem;margin-bottom:1rem;font-size:0.8rem;color:var(--muted-foreground);">
                    <strong>Preview Slip Gaji</strong> — Gaji Pokok & Tunjangan otomatis dari data karyawan. Isi Extra, Bonus dan Potongan sesuai bulan ini.
                </div>
                <div class="form-grid">
                    <div class="form-group full">
                        <label class="form-label">Periode <span class="text-danger">*</span></label>
                        <input type="month" name="period" id="genPeriod" class="form-input" required>
                    </div>
                    <div class="form-group full" style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Gaji Pokok</label>
                            <input type="text" class="form-input" value="{{ number_format($karyawan->gaji_pokok, 0, ',', '.') }}" readonly style="background:var(--muted);">
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Tunjangan</label>
                            <input type="text" class="form-input" value="{{ number_format($karyawan->tunjangan, 0, ',', '.') }}" readonly style="background:var(--muted);">
                        </div>
                    </div>
                    <div class="form-group full" style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Extra</label>
                            <input type="text" name="extra" id="genExtra" class="form-input currency-input-gen" placeholder="0" value="0">
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Bonus</label>
                            <input type="text" name="bonus" id="genBonus" class="form-input currency-input-gen" placeholder="0" value="0">
                        </div>
                    </div>
                    <div class="form-group full" style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Potongan Pinjaman</label>
                            <input type="text" name="potongan_pinjaman" id="genPotPinjaman" class="form-input"
                                   value="{{ $karyawan->angsuran > 0 ? number_format($karyawan->pinjaman / $karyawan->angsuran, 0, ',', '.') : '0' }}" readonly style="background:var(--muted);">
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Potongan Lain</label>
                            <input type="text" name="potongan_lain" id="genPotLain" class="form-input currency-input-gen" placeholder="0" value="0">
                        </div>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Catatan</label>
                        <textarea name="noted" id="genNoted" class="form-textarea" rows="2"></textarea>
                    </div>
                    <div class="form-group full">
                        <div style="background:var(--primary);color:white;border-radius:8px;padding:0.75rem 1rem;display:flex;justify-content:space-between;align-items:center;">
                            <span style="font-weight:600;">Total Gaji Bersih</span>
                            <span style="font-size:1.15rem;font-weight:700;" id="genTotal">Rp 0</span>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-danger" onclick="closeGenerateModal()">Batal</button>
            <button class="btn btn-primary" onclick="submitGenerate()">
                <i data-lucide="save" style="width:14px;height:14px;"></i>
                Generate & Kirim Approval
            </button>
        </div>
    </div>
</div>

{{-- Edit Slip Modal --}}
<div class="modal-overlay" id="editSlipModal">
    <div class="modal-box" style="max-width:540px;">
        <div class="modal-header">
            <h3 class="modal-title">Edit Slip Gaji</h3>
            <button class="modal-close-btn" onclick="closeEditSlipModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="editSlipForm" onsubmit="return false;">
                <div class="form-grid">
                    <div class="form-group full">
                        <label class="form-label">Periode <span class="text-danger">*</span></label>
                        <input type="month" name="period" id="editSlipPeriod" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Extra</label>
                        <input type="text" name="extra" id="editSlipExtra" class="form-input currency-input-edit" placeholder="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Bonus</label>
                        <input type="text" name="bonus" id="editSlipBonus" class="form-input currency-input-edit" placeholder="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Potongan Pinjaman</label>
                        <input type="text" name="potongan_pinjaman" id="editSlipPotPinjaman" class="form-input currency-input-edit" placeholder="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Potongan Lain</label>
                        <input type="text" name="potongan_lain" id="editSlipPotLain" class="form-input currency-input-edit" placeholder="0">
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Catatan</label>
                        <textarea name="noted" id="editSlipNoted" class="form-textarea" rows="2"></textarea>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-danger" onclick="closeEditSlipModal()">Batal</button>
            <button class="btn btn-primary" onclick="updateSlip()">Update</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const gajiPokok        = {{ (float) $karyawan->gaji_pokok }};
    const tunjangan        = {{ (float) $karyawan->tunjangan }};
    const pinjamanPerBulan = {{ round($totalMonthlyInstallment, 2) }};
    const karyawanId       = '{{ $karyawan->id }}';

    let editSlipId = null;
    const generateModal  = document.getElementById('generateModal');
    const editSlipModal  = document.getElementById('editSlipModal');

    document.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();

        // Currency inputs for pinjaman modals
        document.querySelectorAll('.currency-input-p').forEach(el => {
            el.addEventListener('input', function() {
                let raw = this.value.replace(/\D/g,'');
                this.value = raw ? parseInt(raw).toLocaleString('id-ID') : '';
            });
        });

        // Currency inputs for generate modal
        document.querySelectorAll('.currency-input-gen, .currency-input-edit').forEach(el => {
            el.addEventListener('input', function() {
                let raw = this.value.replace(/\D/g,'');
                this.value = raw ? parseInt(raw).toLocaleString('id-ID') : '';
                if (el.closest('#generateModal')) calcGenTotal();
            });
        });

        // Init DataTable for slips
        $('#slipTable').DataTable({
            searching: false,
            paging: false,
            info: false,
            order: [],
        });
        lucide.createIcons();
    });

    function getCurrRaw(id) {
        return parseFloat(String(document.getElementById(id).value).replace(/\./g,'').replace(',','.')) || 0;
    }

    function calcGenTotal() {
        const extra    = getCurrRaw('genExtra');
        const bonus    = getCurrRaw('genBonus');
        const potP     = getCurrRaw('genPotPinjaman');
        const potL     = getCurrRaw('genPotLain');
        const total    = gajiPokok + tunjangan + extra + bonus - potP - potL;
        document.getElementById('genTotal').textContent = 'Rp ' + Currency.number(total);
    }

    // ── Generate ────────────────────────────────────────────────────────────────
    function openGenerateModal() {
        document.getElementById('generateForm').reset();
        const now = new Date();
        document.getElementById('genPeriod').value = now.getFullYear() + '-' + String(now.getMonth()+1).padStart(2,'0');
        document.getElementById('genExtra').value        = '0';
        document.getElementById('genBonus').value        = '0';
        document.getElementById('genPotPinjaman').value  = pinjamanPerBulan ? parseInt(pinjamanPerBulan).toLocaleString('id-ID') : '0';
        document.getElementById('genPotLain').value      = '0';
        calcGenTotal();
        generateModal.classList.add('active');
    }
    function closeGenerateModal() { generateModal.classList.remove('active'); }

    function submitGenerate() {
        const period = document.getElementById('genPeriod').value;
        if (!period) { showError('Gagal', 'Periode wajib diisi.'); return; }

        const payload = {
            period:            period + '-01',
            extra:             getCurrRaw('genExtra'),
            bonus:             getCurrRaw('genBonus'),
            potongan_pinjaman: getCurrRaw('genPotPinjaman'),
            potongan_lain:     getCurrRaw('genPotLain'),
            noted:             document.getElementById('genNoted').value,
        };

        axios.post(`/gaji/${karyawanId}/slip`, payload)
            .then(res => {
                showSuccess('Berhasil', res.data.message);
                closeGenerateModal();
                setTimeout(() => location.reload(), 800);
            })
            .catch(err => {
                const errors = err.response?.data?.errors;
                showError('Gagal', errors ? Object.values(errors).flat().join('\n') : (err.response?.data?.message || 'Terjadi kesalahan'));
            });
    }

    // ── Edit Slip ────────────────────────────────────────────────────────────────
    function openEditSlipModal(id, period, extra, bonus, potPinjaman, potLain, noted) {
        editSlipId = id;
        document.getElementById('editSlipPeriod').value     = period.substring(0, 7);
        document.getElementById('editSlipExtra').value      = extra    ? parseInt(extra).toLocaleString('id-ID')       : '0';
        document.getElementById('editSlipBonus').value      = bonus    ? parseInt(bonus).toLocaleString('id-ID')       : '0';
        document.getElementById('editSlipPotPinjaman').value = potPinjaman ? parseInt(potPinjaman).toLocaleString('id-ID') : '0';
        document.getElementById('editSlipPotLain').value    = potLain  ? parseInt(potLain).toLocaleString('id-ID')     : '0';
        document.getElementById('editSlipNoted').value      = noted;
        editSlipModal.classList.add('active');
    }
    function closeEditSlipModal() { editSlipModal.classList.remove('active'); }

    function updateSlip() {
        const period = document.getElementById('editSlipPeriod').value;
        if (!period) { showError('Gagal', 'Periode wajib diisi.'); return; }
        const payload = {
            _method:           'PUT',
            period:            period + '-01',
            extra:             parseFloat(String(document.getElementById('editSlipExtra').value).replace(/\./g,'')) || 0,
            bonus:             parseFloat(String(document.getElementById('editSlipBonus').value).replace(/\./g,'')) || 0,
            potongan_pinjaman: parseFloat(String(document.getElementById('editSlipPotPinjaman').value).replace(/\./g,'')) || 0,
            potongan_lain:     parseFloat(String(document.getElementById('editSlipPotLain').value).replace(/\./g,'')) || 0,
            noted:             document.getElementById('editSlipNoted').value,
        };
        axios.post(`/gaji/slip/${editSlipId}`, payload)
            .then(res => {
                showSuccess('Berhasil', res.data.message);
                closeEditSlipModal();
                setTimeout(() => location.reload(), 800);
            })
            .catch(err => {
                const errors = err.response?.data?.errors;
                showError('Gagal', errors ? Object.values(errors).flat().join('\n') : (err.response?.data?.message || 'Terjadi kesalahan'));
            });
    }

    // ── Approve / Reject / Delete Slip ──────────────────────────────────────────
    function approveSlip(id) {
        showConfirm({
            type: 'info',
            title: 'Setujui Slip Gaji',
            message: 'Yakin ingin menyetujui slip gaji ini?',
            confirmText: 'Setujui',
            onConfirm: () => {
                axios.post(`/gaji/slip/${id}/approve`)
                    .then(res => { showSuccess('Berhasil', res.data.message); setTimeout(() => location.reload(), 800); })
                    .catch(err => showError('Gagal', err.response?.data?.message || 'Terjadi kesalahan'));
            }
        });
    }

    function rejectSlip(id) {
        showConfirm({
            type: 'warning',
            title: 'Tolak Slip Gaji',
            message: 'Yakin ingin menolak slip gaji ini?',
            confirmText: 'Tolak',
            onConfirm: () => {
                axios.post(`/gaji/slip/${id}/reject`)
                    .then(res => { showSuccess('Berhasil', res.data.message); setTimeout(() => location.reload(), 800); })
                    .catch(err => showError('Gagal', err.response?.data?.message || 'Terjadi kesalahan'));
            }
        });
    }

    function deleteSlip(id) {
        showConfirm({
            type: 'danger',
            title: 'Hapus Slip Gaji',
            message: 'Slip gaji yang dihapus tidak dapat dikembalikan.',
            confirmText: 'Hapus',
            onConfirm: () => {
                axios.delete(`/gaji/slip/${id}`)
                    .then(res => { showSuccess('Berhasil', res.data.message); setTimeout(() => location.reload(), 800); })
                    .catch(err => showError('Gagal', err.response?.data?.message || 'Terjadi kesalahan'));
            }
        });
    }

    // ── Pinjaman ─────────────────────────────────────────────────────────────────
    function getCurrP(id) {
        return parseFloat(String(document.getElementById(id).value).replace(/\./g,'')) || 0;
    }

    function openAddPinjamanModal() {
        document.getElementById('apPokok').value    = '';
        document.getElementById('apAngsuran').value = '';
        document.getElementById('apSubsidi').value  = '';
        document.getElementById('apNoted').value    = '';
        document.getElementById('addPinjamanModal').classList.add('active');
    }
    function closeAddPinjamanModal() { document.getElementById('addPinjamanModal').classList.remove('active'); }

    function submitAddPinjaman() {
        const pokok    = getCurrP('apPokok');
        const angsuran = parseInt(document.getElementById('apAngsuran').value) || 0;
        if (!pokok)    { showError('Gagal', 'Pokok pinjaman wajib diisi.'); return; }
        if (!angsuran) { showError('Gagal', 'Jumlah angsuran wajib diisi.'); return; }

        axios.post(`/gaji/${karyawanId}/pinjaman`, {
            pokok, angsuran,
            subsidi: getCurrP('apSubsidi'),
            noted:   document.getElementById('apNoted').value,
        })
        .then(res => { showSuccess('Berhasil', res.data.message); closeAddPinjamanModal(); setTimeout(() => location.reload(), 800); })
        .catch(err => showError('Gagal', err.response?.data?.message || 'Terjadi kesalahan'));
    }

    let editPinjamanId = null;
    function openEditPinjamanModal(id, pokok, angsuran, subsidi, noted) {
        editPinjamanId = id;
        document.getElementById('epPokok').value    = pokok    ? parseInt(pokok).toLocaleString('id-ID')    : '';
        document.getElementById('epAngsuran').value = angsuran || '';
        document.getElementById('epSubsidi').value  = subsidi  ? parseInt(subsidi).toLocaleString('id-ID')  : '';
        document.getElementById('epNoted').value    = noted;
        document.getElementById('editPinjamanModal').classList.add('active');
    }
    function closeEditPinjamanModal() { document.getElementById('editPinjamanModal').classList.remove('active'); }

    function submitEditPinjaman() {
        const pokok    = getCurrP('epPokok');
        const angsuran = parseInt(document.getElementById('epAngsuran').value) || 0;
        if (!pokok)    { showError('Gagal', 'Pokok pinjaman wajib diisi.'); return; }
        if (!angsuran) { showError('Gagal', 'Jumlah angsuran wajib diisi.'); return; }

        axios.put(`/gaji/pinjaman/${editPinjamanId}`, {
            pokok, angsuran,
            subsidi: getCurrP('epSubsidi'),
            noted:   document.getElementById('epNoted').value,
        })
        .then(res => { showSuccess('Berhasil', res.data.message); closeEditPinjamanModal(); setTimeout(() => location.reload(), 800); })
        .catch(err => showError('Gagal', err.response?.data?.message || 'Terjadi kesalahan'));
    }

    function deletePinjaman(id) {
        showConfirm({
            type: 'danger',
            title: 'Hapus Pinjaman',
            message: 'Pinjaman yang dihapus tidak dapat dikembalikan.',
            confirmText: 'Hapus',
            onConfirm: () => {
                axios.delete(`/gaji/pinjaman/${id}`)
                    .then(res => { showSuccess('Berhasil', res.data.message); setTimeout(() => location.reload(), 800); })
                    .catch(err => showError('Gagal', err.response?.data?.message || 'Terjadi kesalahan'));
            }
        });
    }

    // ── Print Preview ────────────────────────────────────────────────────────────
    function openPrintModal(slipId) {
        document.getElementById('printFrame').src = `/gaji/slip/${slipId}/print`;
        document.getElementById('printModal').classList.add('active');
    }
    function closePrintModal() {
        document.getElementById('printModal').classList.remove('active');
        document.getElementById('printFrame').src = '';
    }
</script>
@endpush
