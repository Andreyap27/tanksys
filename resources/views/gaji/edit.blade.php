@extends('layouts.app')

@section('title', 'Edit Karyawan')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title-text">Edit Karyawan</h1>
        <p class="page-subtitle">{{ $karyawan->nama_karyawan }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('gaji.show', $karyawan->id) }}" class="btn btn-secondary">
            <i data-lucide="arrow-left" style="width:16px;height:16px;"></i>
            Kembali
        </a>
    </div>
</div>

<form id="editKaryawanForm" onsubmit="return false;">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;align-items:start;">

        {{-- Left: Data Karyawan --}}
        <div class="card" style="margin-bottom:0;">
            <div class="card-header" style="padding:1rem 1.25rem 0.5rem;">
                <h5 style="margin:0;font-weight:600;color:var(--primary);display:flex;align-items:center;gap:6px;">
                    <i data-lucide="user" style="width:16px;height:16px;flex-shrink:0;"></i>
                    Data Karyawan
                </h5>
            </div>
            <div class="card-content" style="padding:1.25rem;">
                <div class="form-group" style="margin-bottom:1rem;">
                    <label class="form-label">Nama Karyawan <span class="text-danger">*</span></label>
                    <input type="text" name="nama_karyawan" id="nama_karyawan" class="form-input"
                           value="{{ $karyawan->nama_karyawan }}" placeholder="Masukkan nama lengkap" required>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group">
                        <label class="form-label">No KTP</label>
                        <input type="text" name="no_ktp" id="no_ktp" class="form-input" maxlength="16"
                               value="{{ $karyawan->no_ktp }}" placeholder="16 digit NIK" inputmode="numeric"
                               oninput="this.value=this.value.replace(/\D/g,'')">
                    </div>
                    <div class="form-group">
                        <label class="form-label">No HP</label>
                        <input type="text" name="no_hp" id="no_hp" class="form-input" maxlength="20"
                               value="{{ $karyawan->no_hp }}" placeholder="08xxxxxxxxxx">
                    </div>
                </div>
                <div class="form-group" style="margin-top:1rem;">
                    <label class="form-label">Jabatan</label>
                    <input type="text" name="jabatan" id="jabatan" class="form-input"
                           value="{{ $karyawan->jabatan }}" placeholder="Contoh: Operator, Supir, Admin">
                </div>
                <div class="form-group" style="margin-top:1rem;">
                    <label class="form-label">Catatan</label>
                    <textarea name="noted" id="noted" class="form-textarea" rows="3"
                              placeholder="Catatan tambahan (opsional)">{{ $karyawan->noted }}</textarea>
                </div>
            </div>
        </div>

        {{-- Right: Gaji --}}
        <div class="card" style="margin-bottom:0;">
            <div class="card-header" style="padding:1rem 1.25rem 0.5rem;">
                <h5 style="margin:0;font-weight:600;color:var(--success);display:flex;align-items:center;gap:6px;">
                    <i data-lucide="banknote" style="width:16px;height:16px;flex-shrink:0;"></i>
                    Gaji
                </h5>
            </div>
            <div class="card-content" style="padding:1.25rem;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group">
                        <label class="form-label">Gaji Pokok <span class="text-danger">*</span></label>
                        <input type="text" name="gaji_pokok" id="gaji_pokok" class="form-input currency-input"
                               value="{{ number_format($karyawan->gaji_pokok, 0, ',', '.') }}" placeholder="0" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tunjangan</label>
                        <input type="text" name="tunjangan" id="tunjangan" class="form-input currency-input"
                               value="{{ number_format($karyawan->tunjangan, 0, ',', '.') }}" placeholder="0">
                    </div>
                </div>
                <div style="margin-top:1rem;background:var(--muted);border-radius:8px;padding:0.75rem 1rem;display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:0.875rem;color:var(--muted-foreground);">Base Gaji (Pokok + Tunjangan)</span>
                    <span style="font-weight:700;color:var(--foreground);" id="previewBase">Rp 0</span>
                </div>
                <div style="display:flex;justify-content:flex-end;gap:0.75rem;margin-top:1rem;">
                    <a href="{{ route('gaji.show', $karyawan->id) }}" class="btn btn-secondary">
                        <i data-lucide="x" style="width:16px;height:16px;"></i>
                        Batal
                    </a>
                    <button type="button" class="btn btn-primary" onclick="submitEdit()">
                        <i data-lucide="save" style="width:16px;height:16px;"></i>
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>

    </div>
</form>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();
        updatePreview();
    });

    document.querySelectorAll('.currency-input').forEach(el => {
        el.addEventListener('input', function () {
            let raw = this.value.replace(/\D/g, '');
            this.value = raw ? parseInt(raw).toLocaleString('id-ID') : '';
            updatePreview();
        });
    });

    function getRaw(id) {
        return parseFloat(String(document.getElementById(id).value).replace(/\./g, '')) || 0;
    }

    function updatePreview() {
        const base = getRaw('gaji_pokok') + getRaw('tunjangan');
        document.getElementById('previewBase').textContent = 'Rp ' + Currency.number(base);
    }

    const hasSlips = {{ $hasSlips ? 'true' : 'false' }};

    function submitEdit() {
        const gajiPokok = getRaw('gaji_pokok');
        if (!document.getElementById('nama_karyawan').value.trim()) {
            showError('Gagal', 'Nama karyawan wajib diisi.');
            return;
        }
        if (!gajiPokok) {
            showError('Gagal', 'Gaji pokok wajib diisi.');
            return;
        }

        if (hasSlips) {
            showConfirm({
                type: 'warning',
                title: 'Perhatian',
                message: 'Karyawan ini sudah memiliki slip gaji. Perubahan gaji pokok/tunjangan hanya akan berlaku pada slip bulan berikutnya.',
                confirmText: 'Tetap Simpan',
                onConfirm: () => doSave(),
            });
        } else {
            doSave();
        }
    }

    function doSave() {
        const payload = {
            _method:       'PUT',
            nama_karyawan: document.getElementById('nama_karyawan').value,
            no_ktp:        document.getElementById('no_ktp').value,
            no_hp:         document.getElementById('no_hp').value,
            jabatan:       document.getElementById('jabatan').value,
            gaji_pokok:    getRaw('gaji_pokok'),
            tunjangan:     getRaw('tunjangan'),
            noted:         document.getElementById('noted').value,
        };

        const btn = document.querySelector('[onclick="submitEdit()"]');
        btn.disabled = true;
        btn.innerHTML = '<i data-lucide="loader-2" style="width:16px;height:16px;"></i> Menyimpan...';
        lucide.createIcons();

        axios.post('{{ route('gaji.update', $karyawan->id) }}', payload)
            .then(res => {
                showSuccess('Berhasil', res.data.message);
                setTimeout(() => window.location.href = '{{ route('gaji.show', $karyawan->id) }}', 800);
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '<i data-lucide="save" style="width:16px;height:16px;"></i> Simpan Perubahan';
                lucide.createIcons();
                const errors = err.response?.data?.errors;
                showError('Gagal', errors ? Object.values(errors).flat().join('\n') : (err.response?.data?.message || 'Terjadi kesalahan'));
            });
    }
</script>
@endpush
