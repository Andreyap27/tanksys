<div class="page-header">
    <div>
        <h1 class="page-title-text">{{ $pageTitle }}</h1>
        <p class="page-subtitle">Laporan tahun {{ $year }}</p>
    </div>
    <div class="page-actions">
        <form method="GET" action="{{ request()->url() }}" style="display:flex;gap:0.5rem;align-items:center;" id="yearForm">
            @if(isset($kapalId) && $kapalId)
                <input type="hidden" name="kapal_id" value="{{ $kapalId }}">
            @endif
            @if(isset($mobilId) && $mobilId)
                <input type="hidden" name="mobil_id" value="{{ $mobilId }}">
            @endif
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

@if(isset($kapals) && $kapals->count() > 0)
<div class="tab-bar" style="margin-bottom:1rem;">
    <a href="{{ request()->fullUrlWithQuery(['kapal_id' => '']) }}"
       class="tab {{ !isset($kapalId) || !$kapalId ? 'active' : '' }}">Semua</a>
    @foreach($kapals as $k)
    <a href="{{ request()->fullUrlWithQuery(['kapal_id' => $k->id]) }}"
       class="tab {{ (isset($kapalId) && $kapalId == $k->id) ? 'active' : '' }}">{{ $k->name }}</a>
    @endforeach
</div>
@endif

@if(isset($mobils) && $mobils->count() > 0)
<div class="tab-bar" style="margin-bottom:1rem;">
    <a href="{{ request()->fullUrlWithQuery(['mobil_id' => '']) }}"
       class="tab {{ !isset($mobilId) || !$mobilId ? 'active' : '' }}">Semua</a>
    @foreach($mobils as $m)
    <a href="{{ request()->fullUrlWithQuery(['mobil_id' => $m->id]) }}"
       class="tab {{ (isset($mobilId) && $mobilId == $m->id) ? 'active' : '' }}">{{ $m->plat_nomer ?: $m->name }}</a>
    @endforeach
</div>
@endif
