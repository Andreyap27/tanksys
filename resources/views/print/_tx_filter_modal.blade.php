{{--
    Required variables from including view:
    $txSection   : string  e.g. 'purchase'
    $txHasKapal  : bool
    $txHasMobil  : bool
    $txHasPc     : bool
--}}

{{-- Filter Modal --}}
<div class="modal-overlay" id="txPrintModal" style="z-index:9999;">
    <div class="modal-box" style="max-width:460px;">
        <div class="modal-header">
            <h3 class="modal-title">Filter Print</h3>
            <button class="modal-close-btn" onclick="closeTxPrintModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="txPrintForm">
                <div class="form-grid">
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Tanggal Dari</label>
                        <input type="date" name="date_from" class="form-input" id="txPrintDateFrom">
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Tanggal Sampai</label>
                        <input type="date" name="date_to" class="form-input" id="txPrintDateTo">
                    </div>

                    @if($txHasKapal ?? false)
                    <div class="form-group full">
                        <label class="form-label">Kapal</label>
                        <select name="kapal_id" class="form-select" id="txPrintKapalSelect">
                            <option value="">-- Semua Kapal --</option>
                            @foreach(\App\Models\Kapal::orderBy('code')->get() as $k)
                            <option value="{{ $k->id }}">{{ $k->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    @if($txHasMobil ?? false)
                    <div class="form-group full">
                        <label class="form-label">Mobil</label>
                        <select name="mobil_id" class="form-select" id="txPrintMobilSelect">
                            <option value="">-- Semua Mobil --</option>
                            @foreach(\App\Models\Mobil::orderBy('name')->get() as $m)
                            <option value="{{ $m->id }}">{{ $m->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    @if($txHasPc ?? false)
                    <div class="form-group full">
                        <label class="form-label">Petty Cash</label>
                        <select name="petty_cash_id" class="form-select" id="txPrintPcSelect">
                            <option value="">-- Semua Petty Cash --</option>
                            @foreach(\App\Models\PettyCash::orderBy('name')->get() as $pc)
                            <option value="{{ $pc->id }}">{{ $pc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    @if($txHasType ?? false)
                    <div class="form-group full">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select" id="txPrintTypeSelect">
                            <option value="">-- Semua Type --</option>
                            <option value="out">Out</option>
                            <option value="in">In</option>
                        </select>
                    </div>
                    @endif
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-danger" onclick="closeTxPrintModal()">
                <i data-lucide="x" style="width:15px;height:15px;"></i> Batal
            </button>
            <button class="btn btn-primary" onclick="doTxPreview('{{ $txSection }}')">
                <i data-lucide="eye" style="width:15px;height:15px;"></i> Preview
            </button>
        </div>
    </div>
</div>

{{-- Preview Modal --}}
<div class="modal-overlay" id="txPreviewModal" style="z-index:10000;">
    <div class="modal-box" style="max-width:72rem;width:95%;height:90vh;display:flex;flex-direction:column;overflow:hidden;">
        <div class="modal-header">
            <h3 class="modal-title">Preview Print</h3>
            <button class="modal-close-btn" onclick="closeTxPreviewModal()">&times;</button>
        </div>
        <div style="flex:1;overflow:hidden;min-height:0;">
            <iframe id="txPrintFrame" src="" style="width:100%;height:100%;border:none;display:block;"></iframe>
        </div>
        <div class="modal-footer">
            <button class="btn" onclick="closeTxPreviewModal()">
                <i data-lucide="x" style="width:15px;height:15px;"></i> Tutup
            </button>
            <button class="btn btn-primary" onclick="document.getElementById('txPrintFrame').contentWindow.print()">
                <i data-lucide="printer" style="width:15px;height:15px;"></i> Print
            </button>
        </div>
    </div>
</div>

<script>
function openTxPrintModal() {
    const now  = new Date();
    const pad  = n => String(n).padStart(2, '0');
    const y    = now.getFullYear(), m = now.getMonth() + 1, d = now.getDate();
    const firstDay = y + '-' + pad(m) + '-01';
    const today    = y + '-' + pad(m) + '-' + pad(d);
    const dfEl = document.getElementById('txPrintDateFrom');
    const dtEl = document.getElementById('txPrintDateTo');
    if (dfEl && !dfEl.value) dfEl.value = firstDay;
    if (dtEl && !dtEl.value) dtEl.value = today;
    document.getElementById('txPrintModal').classList.add('active');
    if (typeof lucide !== 'undefined') lucide.createIcons();
}
function closeTxPrintModal() {
    document.getElementById('txPrintModal').classList.remove('active');
}
function closeTxPreviewModal() {
    document.getElementById('txPreviewModal').classList.remove('active');
    document.getElementById('txPrintFrame').src = '';
}
function doTxPreview(section) {
    const params = new URLSearchParams();
    params.set('section', section);
    const df = document.getElementById('txPrintDateFrom')?.value;
    const dt = document.getElementById('txPrintDateTo')?.value;
    const kp = document.getElementById('txPrintKapalSelect')?.value;
    const mb = document.getElementById('txPrintMobilSelect')?.value;
    const pc = document.getElementById('txPrintPcSelect')?.value;
    if (df) params.set('date_from', df);
    if (dt) params.set('date_to', dt);
    if (kp) params.set('kapal_id', kp);
    if (mb) params.set('mobil_id', mb);
    if (pc) params.set('petty_cash_id', pc);
    const tp = document.getElementById('txPrintTypeSelect')?.value;
    if (tp) params.set('type', tp);
    closeTxPrintModal();
    document.getElementById('txPrintFrame').src = '{{ route('tx.print') }}?' + params.toString();
    document.getElementById('txPreviewModal').classList.add('active');
    if (typeof lucide !== 'undefined') lucide.createIcons();
}
</script>
