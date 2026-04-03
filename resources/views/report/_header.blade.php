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
        @php
            $currentRouteName = Route::currentRouteName();
            $trashRouteName = match($currentRouteName) {
                'report.purchase' => 'report.purchase.trash',
                'report.sale' => 'report.sale.trash',
                'report.expense' => 'report.expense.trash',
                'report.capital' => 'report.capital.trash',
                'report.lori' => 'report.lori.trash',
                'report.lori-expense' => 'report.lori-expense.trash',
                default => null,
            };
            $isTrash = str_ends_with($currentRouteName, '.trash');
        @endphp
        @if($trashRouteName && !$isTrash)
        <a href="{{ route($trashRouteName, array_filter(['year' => $year, 'kapal_id' => $kapalId ?? null, 'mobil_id' => $mobilId ?? null])) }}" class="btn btn-secondary">
            <i data-lucide="trash-2" style="width:15px;height:15px;"></i>
            Lihat Trash
        </a>
        @elseif($isTrash)
        <a href="{{ route(str_replace('.trash', '', $currentRouteName), array_filter(['year' => $year, 'kapal_id' => $kapalId ?? null, 'mobil_id' => $mobilId ?? null])) }}" class="btn btn-secondary">
            <i data-lucide="arrow-left" style="width:15px;height:15px;"></i>
            Kembali
        </a>
        @endif
        <button class="btn btn-primary" onclick="openPrintModal()">
            <i data-lucide="printer" style="width:15px;height:15px;"></i>
            Print
        </button>
    </div>
</div>

@if(isset($kapals) && $kapals->count() > 0)
<div class="tab-bar">
    <a href="{{ request()->fullUrlWithQuery(['kapal_id' => '']) }}"
       class="tab {{ !isset($kapalId) || !$kapalId ? 'active' : '' }}"><i data-lucide="list" style="width:16px;height:16px;"></i> Semua</a>
    @foreach($kapals as $k)
    <a href="{{ request()->fullUrlWithQuery(['kapal_id' => $k->id]) }}"
       class="tab {{ (isset($kapalId) && $kapalId == $k->id) ? 'active' : '' }}"><i data-lucide="ship" style="width:16px;height:16px;"></i> {{ $k->name }}</a>
    @endforeach
</div>
@endif

@if(isset($mobils) && $mobils->count() > 0)
<div class="tab-bar">
    <a href="{{ request()->fullUrlWithQuery(['mobil_id' => '']) }}"
       class="tab {{ !isset($mobilId) || !$mobilId ? 'active' : '' }}"><i data-lucide="list" style="width:16px;height:16px;"></i> Semua</a>
    @foreach($mobils as $m)
    <a href="{{ request()->fullUrlWithQuery(['mobil_id' => $m->id]) }}"
       class="tab {{ (isset($mobilId) && $mobilId == $m->id) ? 'active' : '' }}"><i data-lucide="truck" style="width:16px;height:16px;"></i> {{ $m->plat_nomer ?: $m->name }}</a>
    @endforeach
</div>
@endif
