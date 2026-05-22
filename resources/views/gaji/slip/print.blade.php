<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji - {{ $slip->karyawan->nama_karyawan }} - {{ $slip->period->translatedFormat('M Y') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 13px;
            color: #1a1a1a;
            background: #f5f5f5;
        }
        .page {
            max-width: 720px;
            margin: 2rem auto;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0,0,0,0.10);
        }
        .header {
            background: #dbeafe;
            color: #1e3a6e;
            padding: 1.25rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid #93c5fd;
        }
        .header-logo { display: flex; align-items: center; flex: 1; }
        .header-logo img { height: 50px; }
        .header-center { flex: 2; text-align: center; }
        .header-center h1 { font-size: 1.1rem; font-weight: 700; }
        .header-center p { font-size: 0.75rem; color: #3b5ea6; margin-top: 3px; }
        .header-right { flex: 1; text-align: right; }
        .header-right .slip-title { font-size: 1.4rem; font-weight: 700; letter-spacing: 0.05em; }
        .header-right .period { font-size: 0.9rem; color: #3b5ea6; margin-top: 4px; }
        .section { padding: 1.25rem 2rem; }
        .section-title {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #1a5cb8;
            border-bottom: 2px solid #1a5cb8;
            padding-bottom: 4px;
            margin-bottom: 0.75rem;
        }
        .emp-info { display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem 1.5rem; }
        .info-row { display: flex; gap: 0.5rem; }
        .info-label { color: #666; min-width: 100px; }
        .info-value { font-weight: 600; }

        /* Two-column pay layout */
        .pay-columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            overflow: hidden;
        }
        .pay-col { padding: 0; }
        .pay-col:first-child { border-right: 1px solid #e0e0e0; }
        .pay-col-header {
            padding: 8px 14px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            border-bottom: 1px solid #e0e0e0;
        }
        .pay-col-header.income  { background: #f0fdf4; color: #16a34a; }
        .pay-col-header.deduct  { background: #fff1f2; color: #dc2626; }
        .pay-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 14px;
            font-size: 0.8rem;
            border-bottom: 1px solid #f3f4f6;
        }
        .pay-row:last-child { border-bottom: none; }
        .pay-row.subtotal {
            font-weight: 700;
            background: #f9fafb;
            border-top: 1px solid #e0e0e0;
            border-bottom: none;
        }
        .pay-row.subtotal.income { color: #16a34a; }
        .pay-row.subtotal.deduct { color: #dc2626; }
        .pay-row .amount { font-weight: 500; }

        /* Total bersih */
        .total-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 14px;
            margin-top: 12px;
            font-size: 1rem;
            font-weight: 700;
            color: #1a5cb8;
        }
        .terbilang-box {
            margin-top: 8px;
            background: #f8faff;
            border: 1px solid #c7d9f5;
            border-radius: 6px;
            padding: 8px 14px;
            font-size: 0.8rem;
            color: #1a5cb8;
        }
        .terbilang-box span { font-weight: 600; text-transform: capitalize; }

        .footer { text-align: center; padding: 1rem 2rem; font-size: 0.75rem; color: #888; border-top: 1px solid #eee; margin-top: 1rem; }
        .badge-approved { background: #dcfce7; color: #16a34a; padding: 3px 10px; border-radius: 999px; font-size: 0.75rem; font-weight: 600; }
        .badge-pending  { background: #fef9c3; color: #ca8a04; padding: 3px 10px; border-radius: 999px; font-size: 0.75rem; font-weight: 600; }
        .badge-rejected { background: #fee2e2; color: #dc2626; padding: 3px 10px; border-radius: 999px; font-size: 0.75rem; font-weight: 600; }
        @media print {
            body { background: white; }
            .page { margin: 0; box-shadow: none; border-radius: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
<?php
function terbilang($angka) {
    $angka = abs(intval($angka));
    $satuan = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan',
               'sepuluh', 'sebelas', 'dua belas', 'tiga belas', 'empat belas', 'lima belas',
               'enam belas', 'tujuh belas', 'delapan belas', 'sembilan belas'];
    if ($angka === 0) return 'nol';
    if ($angka < 20) return $satuan[$angka];
    if ($angka < 100) {
        $s = $satuan[intval($angka / 10)] . ' puluh';
        if ($angka % 10) $s .= ' ' . $satuan[$angka % 10];
        return $s;
    }
    if ($angka < 200) {
        $s = 'seratus';
        if ($angka % 100) $s .= ' ' . terbilang($angka % 100);
        return $s;
    }
    if ($angka < 1000) {
        $s = $satuan[intval($angka / 100)] . ' ratus';
        if ($angka % 100) $s .= ' ' . terbilang($angka % 100);
        return $s;
    }
    if ($angka < 2000) {
        $s = 'seribu';
        if ($angka % 1000) $s .= ' ' . terbilang($angka % 1000);
        return $s;
    }
    if ($angka < 1000000) {
        $s = terbilang(intval($angka / 1000)) . ' ribu';
        if ($angka % 1000) $s .= ' ' . terbilang($angka % 1000);
        return $s;
    }
    if ($angka < 1000000000) {
        $s = terbilang(intval($angka / 1000000)) . ' juta';
        if ($angka % 1000000) $s .= ' ' . terbilang($angka % 1000000);
        return $s;
    }
    if ($angka < 1000000000000) {
        $s = terbilang(intval($angka / 1000000000)) . ' miliar';
        if ($angka % 1000000000) $s .= ' ' . terbilang($angka % 1000000000);
        return $s;
    }
    return 'angka terlalu besar';
}

$gajiPokok      = (float) $slip->gaji_pokok;
$tunjangan      = (float) $slip->tunjangan;
$extra          = (float) $slip->extra;
$bonus          = (float) $slip->bonus;
$potPinjaman    = (float) $slip->potongan_pinjaman;
$potLain        = (float) $slip->potongan_lain;
$totalPemasukan = $gajiPokok + $tunjangan + $extra + $bonus;
$totalPotongan  = $potPinjaman + $potLain;
$totalBersih    = (float) $slip->total;
?>
<div class="page">
    <div class="header">
        <div class="header-logo">
            <img src="/images/logo.png" alt="Logo" onerror="this.style.display='none'">
        </div>
        <div class="header-center">
            <h1>PT. ANUGRAH ENERGI PETROLUM</h1>
            <p>Retail B.R-03 Baloi Apartment, Jl. Permata Baloi, Batam</p>
        </div>
        <div class="header-right">
            <div class="slip-title">SLIP GAJI</div>
            <div class="period">{{ $slip->period->translatedFormat('F Y') }}</div>
            <div style="margin-top:8px;">
                @if($slip->status === 'approved')
                    <span class="badge-approved">Approved</span>
                @elseif($slip->status === 'rejected')
                    <span class="badge-rejected">Rejected</span>
                @else
                    <span class="badge-pending">Pending</span>
                @endif
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Data Karyawan</div>
        <div class="emp-info">
            <div class="info-row">
                <span class="info-label">Nama</span>
                <span class="info-value">{{ $slip->karyawan->nama_karyawan }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">No KTP</span>
                <span class="info-value">{{ $slip->karyawan->no_ktp ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Jabatan</span>
                <span class="info-value">{{ $slip->karyawan->jabatan ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">No HP</span>
                <span class="info-value">{{ $slip->karyawan->no_hp ?? '-' }}</span>
            </div>
        </div>
    </div>

    <div class="section" style="padding-top:0;">
        <div class="section-title">Rincian Gaji</div>

        <div class="pay-columns">
            {{-- Pemasukan --}}
            <div class="pay-col">
                <div class="pay-col-header income">Pemasukan</div>
                <div class="pay-row">
                    <span>Gaji Pokok</span>
                    <span class="amount">Rp {{ number_format($gajiPokok, 0, ',', '.') }}</span>
                </div>
                <div class="pay-row">
                    <span>Tunjangan</span>
                    <span class="amount">Rp {{ number_format($tunjangan, 0, ',', '.') }}</span>
                </div>
                <div class="pay-row">
                    <span>Extra</span>
                    <span class="amount">Rp {{ number_format($extra, 0, ',', '.') }}</span>
                </div>
                <div class="pay-row">
                    <span>Bonus</span>
                    <span class="amount">Rp {{ number_format($bonus, 0, ',', '.') }}</span>
                </div>
                <div class="pay-row subtotal income">
                    <span>Total Pemasukan</span>
                    <span>Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</span>
                </div>
            </div>

            {{-- Potongan --}}
            <div class="pay-col">
                <div class="pay-col-header deduct">Potongan</div>
                <div class="pay-row">
                    <span>Potongan Pinjaman</span>
                    <span class="amount">Rp {{ number_format($potPinjaman, 0, ',', '.') }}</span>
                </div>
                <div class="pay-row">
                    <span>Potongan Lain</span>
                    <span class="amount">Rp {{ number_format($potLain, 0, ',', '.') }}</span>
                </div>
                <div class="pay-row subtotal deduct">
                    <span>Total Potongan</span>
                    <span>Rp {{ number_format($totalPotongan, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <div class="terbilang-box">
            Terbilang: <span>{{ terbilang($totalBersih) }} rupiah</span>
        </div>
        <div class="total-bar">
            <span>Total Gaji Bersih</span>
            <span>Rp {{ number_format($totalBersih, 0, ',', '.') }}</span>
        </div>
    </div>

    @if($slip->noted)
    <div class="section" style="padding-top:0;">
        <div class="section-title">Catatan</div>
        <p style="color:#444;">{{ $slip->noted }}</p>
    </div>
    @endif

    @if($slip->status === 'approved' && $slip->approver)
    <div class="section" style="padding-top:0;display:flex;justify-content:flex-end;">
        <div style="text-align:center;min-width:160px;">
            <p style="font-size:0.75rem;color:#666;">Disetujui oleh</p>
            <div style="margin:2.5rem 0 0.25rem;border-top:1px solid #333;padding-top:4px;font-weight:600;">
                {{ $slip->approver->name }}
            </div>
            <p style="font-size:0.75rem;color:#666;">{{ $slip->approved_at?->translatedFormat('d M Y') }}</p>
        </div>
    </div>
    @endif

    <div class="footer">
        Dokumen ini diterbitkan oleh sistem. &mdash; PT. Anugrah Energi Petrolum
    </div>
</div>
</body>
</html>
