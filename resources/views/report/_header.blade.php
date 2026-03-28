<div class="page-header">
    <div>
        <h1 class="page-title-text">{{ $pageTitle }}</h1>
        <p class="page-subtitle">Laporan tahun {{ $year }}</p>
    </div>
    <div class="page-actions">
        <form method="GET" action="{{ request()->url() }}" style="display:flex;gap:0.5rem;align-items:center;" id="yearForm">
            <select name="year" class="form-select" style="width:auto;" onchange="document.getElementById('yearForm').submit()">
                @foreach($years as $y)
                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </form>
        <button class="btn btn-primary" onclick="openPrintModal()">
            <i data-lucide="printer" style="width:15px;height:15px;"></i>
            Print
        </button>
    </div>
</div>
