@extends('layouts.app')
@section('title', 'Expenses Mobil Tangki')
@section('content')

@php
    $pageTitle = 'Expenses Mobil Tangki';
    $months = [
        1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April',
        5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
        9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember',
    ];
    $fmt = fn($n) => number_format((float)$n, 0, ',', '.');

    $expMatrix = [];
    foreach ($loriExpensesByCategory as $m => $items) {
        foreach ($items as $item) {
            $expMatrix[(int)$m][$item->category] = (float)$item->total;
        }
    }

    $gCatTotal = array_fill_keys($cats, 0);
    $gTotal    = 0;
    foreach (range(1, 12) as $m) {
        $gTotal += (float)($loriExpensesTotal[$m] ?? 0);
        foreach ($cats as $cat) {
            $gCatTotal[$cat] += $expMatrix[$m][$cat] ?? 0;
        }
    }
@endphp

@include('report._header')

<div class="card">
    <div class="card-header"><div class="card-title">Expenses Mobil Tangki</div></div>
    <div class="card-content" style="padding:0;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Bulan</th>
                        @foreach($cats as $cat)
                            <th class="text-right">{{ $cat }}</th>
                        @endforeach
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($months as $m => $name)
                        @php $rowTotal = (float)($loriExpensesTotal[$m] ?? 0); @endphp
                        <tr>
                            <td>{{ $name }}</td>
                            @foreach($cats as $cat)
                                @php $val = $expMatrix[$m][$cat] ?? 0; @endphp
                                <td class="text-right">{{ $val ? 'Rp '.$fmt($val) : '-' }}</td>
                            @endforeach
                            <td class="text-right">{{ $rowTotal ? 'Rp '.$fmt($rowTotal) : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="report-total-row">
                        <td><strong>Total</strong></td>
                        @foreach($cats as $cat)
                            <td class="text-right"><strong>{{ $gCatTotal[$cat] ? 'Rp '.$fmt($gCatTotal[$cat]) : '-' }}</strong></td>
                        @endforeach
                        <td class="text-right"><strong>Rp {{ $fmt($gTotal) }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

{{-- Category picker modal --}}
<div class="modal-overlay" id="catPickerModal">
    <div class="modal-box" style="max-width:26rem;">
        <div class="modal-header">
            <h3 class="modal-title">Pilih Kategori</h3>
            <button class="modal-close-btn" onclick="closeCatPicker()">&times;</button>
        </div>
        <div class="modal-body">
            <p style="margin-bottom:0.75rem;font-size:0.85rem;color:var(--muted-foreground);">Pilih kategori yang ingin ditampilkan dalam laporan:</p>
            <label style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.6rem;font-weight:600;cursor:pointer;">
                <input type="checkbox" id="catAll" onchange="toggleAllCats(this)" checked>
                Semua Kategori
            </label>
            <hr style="margin-bottom:0.6rem;border-color:var(--border);">
            @foreach($cats as $cat)
            <label style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.4rem;cursor:pointer;">
                <input type="checkbox" class="cat-check" value="{{ $cat }}" checked onchange="syncAllCheck()">
                {{ $cat }}
            </label>
            @endforeach
        </div>
        <div class="modal-footer">
            <button class="btn btn-danger" onclick="closeCatPicker()">
                <i data-lucide="x" style="width:15px;height:15px;"></i>
                Batal
            </button>
            <button class="btn btn-primary" onclick="loadPrintPreview()">
                <i data-lucide="printer" style="width:15px;height:15px;"></i>
                Preview
            </button>
        </div>
    </div>
</div>

@include('report._print_modal')
@endsection

@push('scripts')
<script>
const basePrintUrl = '{{ route('report.print', ['section' => 'lori-expense', 'year' => $year]) }}';

function openPrintModal() {
    document.getElementById('catPickerModal').classList.add('active');
}

function closeCatPicker() {
    document.getElementById('catPickerModal').classList.remove('active');
}

function closePrintModal() {
    document.getElementById('printModal').classList.remove('active');
    document.getElementById('printFrame').src = '';
}

function toggleAllCats(el) {
    document.querySelectorAll('.cat-check').forEach(c => c.checked = el.checked);
}

function syncAllCheck() {
    const checks = document.querySelectorAll('.cat-check');
    const allOn  = Array.from(checks).every(c => c.checked);
    const anyOn  = Array.from(checks).some(c => c.checked);
    const allEl  = document.getElementById('catAll');
    allEl.checked       = allOn;
    allEl.indeterminate = !allOn && anyOn;
}

function loadPrintPreview() {
    const selected = Array.from(document.querySelectorAll('.cat-check:checked')).map(c => c.value);
    if (!selected.length) { alert('Pilih minimal satu kategori.'); return; }

    const params = new URLSearchParams();
    selected.forEach(cat => params.append('categories[]', cat));
    const url = basePrintUrl + '&' + params.toString();

    closeCatPicker();
    document.getElementById('printFrame').src = url;
    document.getElementById('printModal').classList.add('active');
}

lucide.createIcons();
</script>
@endpush
