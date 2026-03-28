<div class="modal-overlay" id="printModal">
    <div class="modal-box" style="max-width:72rem;width:95%;height:90vh;display:flex;flex-direction:column;overflow:hidden;">
        <div class="modal-header">
            <h3 class="modal-title">Preview Print</h3>
            <button class="modal-close-btn" onclick="closePrintModal()">&times;</button>
        </div>
        <div style="flex:1;overflow:hidden;min-height:0;">
            <iframe id="printFrame" src="" style="width:100%;height:100%;border:none;display:block;"></iframe>
        </div>
        <div class="modal-footer">
            <button class="btn" onclick="closePrintModal()">
                <i data-lucide="x" style="width:15px;height:15px;"></i>
                Tutup
            </button>
            <button class="btn btn-primary" onclick="document.getElementById('printFrame').contentWindow.print()">
                <i data-lucide="printer" style="width:15px;height:15px;"></i>
                Print
            </button>
        </div>
    </div>
</div>
