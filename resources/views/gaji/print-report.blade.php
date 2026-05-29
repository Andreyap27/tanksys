<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Gaji</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 12px;
            color: #1a1a1a;
            background: #f5f5f5;
            min-height: 100vh;
        }
        .page {
            max-width: 960px;
            margin: 1.5rem auto;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0,0,0,0.10);
        }

        /* ── Company Header ──────────────────────────────────────── */
        .rpt-header {
            background: #fff;
            padding: 1.25rem 2rem 1rem;
            border-bottom: 3px solid #1a5cb8;
        }
        .rpt-logo { height: 60px; width: auto; object-fit: contain; }
        .rpt-company-name {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1a5cb8;
            text-decoration: underline;
            text-underline-offset: 5px;
            text-decoration-thickness: 2px;
            letter-spacing: 0.03em;
            line-height: 1.2;
            margin-bottom: 0.3rem;
        }
        .rpt-company-address { font-size: 0.75rem; color: #555; line-height: 1.55; }

        /* ── Report label ────────────────────────────────────────── */
        .rpt-label {
            background: #f0f4fb;
            padding: 0.75rem 2.25rem;
            border-bottom: 1px solid #dbeafe;
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .rpt-label-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: #1a5cb8;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .rpt-label-sub { font-size: 0.8rem; color: #64748b; font-weight: 500; }

        /* ── Body ────────────────────────────────────────────────── */
        .rpt-body { padding: 1.75rem 2.25rem; }

        /* ── Table ───────────────────────────────────────────────── */
        .rpt-table { width: 100%; border-collapse: collapse; font-size: 11.5px; }
        .rpt-table thead tr { background: #f0f4fb; }
        .rpt-table th {
            padding: 0.6rem 0.85rem;
            text-align: left;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #1a5cb8;
            border-bottom: 2px solid #1a5cb8;
            font-weight: 700;
            white-space: nowrap;
        }
        .rpt-table th.r, .rpt-table td.r { text-align: right; }
        .rpt-table tbody tr:nth-child(even) { background: #f8fafc; }
        .rpt-table tbody tr:hover { background: #eef3fb; }
        .rpt-table td { padding: 0.55rem 0.85rem; border-bottom: 1px solid #f0f4f8; color: #374151; }
        .rpt-table tfoot td {
            padding: 0.65rem 0.85rem;
            background: #eef2fb;
            border-top: 2px solid #1a5cb8;
            font-weight: 700;
            font-size: 12px;
            color: #1a1a1a;
        }
        .badge-approved { background: #dcfce7; color: #16a34a; padding: 2px 8px; border-radius: 999px; font-size: 0.68rem; font-weight: 600; }
        .badge-pending  { background: #fef9c3; color: #ca8a04; padding: 2px 8px; border-radius: 999px; font-size: 0.68rem; font-weight: 600; }
        .badge-rejected { background: #fee2e2; color: #dc2626; padding: 2px 8px; border-radius: 999px; font-size: 0.68rem; font-weight: 600; }

        /* ── Print bar ───────────────────────────────────────────── */
        .print-bar {
            max-width: 960px;
            margin: 0.75rem auto 0;
            padding: 0 1rem;
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
        }
        .print-bar button {
            padding: 0.45rem 1.1rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .btn-print { background: #1a5cb8; color: #fff; }
        .btn-close  { background: #e5e7eb; color: #333; }

        /* ── In-iframe mode ──────────────────────────────────────── */
        body.in-modal {
            background: #d1d5db;
            min-height: 100%;
            padding: 1.5rem 1rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        body.in-modal .print-bar { display: none; }
        body.in-modal .page { margin: 0 auto; width: 100%; max-width: 960px; border-radius: 4px; box-shadow: 0 6px 32px rgba(0,0,0,0.22); }

        /* ── Print media ─────────────────────────────────────────── */
        @media print {
            body, body.in-modal {
                background: #fff !important;
                display: block !important;
                padding: 0 !important;
                min-height: unset !important;
                align-items: unset !important;
            }
            .print-bar { display: none !important; }
            .page, body.in-modal .page { margin: 0 !important; box-shadow: none !important; border-radius: 0 !important; max-width: 100% !important; width: 100% !important; }
            .rpt-table { font-size: 10px; }
            .rpt-table th, .rpt-table td, .rpt-table tfoot td { padding: 0.4rem 0.6rem; }
            .rpt-table tbody tr:hover { background: transparent !important; }
        }
    </style>
</head>
<body>
<script>
    if (window.self !== window.top) {
        document.addEventListener('DOMContentLoaded', function () {
            document.body.classList.add('in-modal');
        });
    }
</script>

<div class="print-bar">
    <button class="btn-close" onclick="window.close()">Tutup</button>
    <button class="btn-print" onclick="window.print()">&#128438; Cetak</button>
</div>

@php
    $fmt = fn($n) => number_format((float)$n, 0, ',', '.');
    $totalGajiPokok = 0; $totalTunjangan = 0; $totalExtra = 0;
    $totalBonus = 0; $totalPotongan = 0; $totalBersih = 0;
    foreach ($slips as $s) {
        $totalGajiPokok += (float)$s->gaji_pokok;
        $totalTunjangan += (float)$s->tunjangan;
        $totalExtra     += (float)$s->extra;
        $totalBonus     += (float)$s->bonus;
        $totalPotongan  += (float)$s->potongan_pinjaman + (float)$s->potongan_lain;
        $totalBersih    += (float)$s->total;
    }
    $fromLabel = $from ? \Carbon\Carbon::parse($from.'-01')->translatedFormat('M Y') : '-';
    $toLabel   = $to   ? \Carbon\Carbon::parse($to.'-01')->translatedFormat('M Y')   : '-';
@endphp

<div class="page">

    <div class="rpt-header">
        <table style="width:100%;border-collapse:collapse;">
            <tr>
                <td style="width:20%;vertical-align:middle;">
                    @if(file_exists(public_path('images/logo.png')))
                        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="rpt-logo">
                    @endif
                </td>
                <td style="text-align:center;vertical-align:middle;">
                    <div class="rpt-company-name">PT. ANUGRAH ENERGI PETROLUM</div>
                    <div class="rpt-company-address">
                        Retail B.R-03 Baloi Apartment, Jl. Permata Baloi, RT/RW 004/008,<br>
                        Kel. Baloi Indah, Kec. Lubuk Baja, Batam, 29444
                    </div>
                </td>
                <td style="width:20%;"></td>
            </tr>
        </table>
    </div>

    <div class="rpt-label">
        <span class="rpt-label-title">Laporan Gaji</span>
        <span class="rpt-label-sub">
            Period: {{ $fromLabel }} — {{ $toLabel }}
            @if($jabatan) &nbsp;|&nbsp; Jabatan: {{ $jabatan }} @endif
            &nbsp;|&nbsp; {{ $slips->count() }} slip
        </span>
    </div>

    <div class="rpt-body">
        <table class="rpt-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Karyawan</th>
                    <th>Jabatan</th>
                    <th>Period</th>
                    <th class="r">Gaji Pokok</th>
                    <th class="r">Tunjangan</th>
                    <th class="r">Extra</th>
                    <th class="r">Bonus</th>
                    <th class="r">Potongan</th>
                    <th class="r">Total Bersih</th>
                </tr>
            </thead>
            <tbody>
                @forelse($slips as $i => $slip)
                @php $pot = (float)$slip->potongan_pinjaman + (float)$slip->potongan_lain; @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $slip->karyawan->nama_karyawan ?? '-' }}</td>
                    <td>{{ $slip->karyawan->jabatan ?? '-' }}</td>
                    <td>{{ $slip->period->translatedFormat('M Y') }}</td>
                    <td class="r">Rp {{ $fmt($slip->gaji_pokok) }}</td>
                    <td class="r">Rp {{ $fmt($slip->tunjangan) }}</td>
                    <td class="r">{{ $slip->extra > 0 ? 'Rp '.$fmt($slip->extra) : '-' }}</td>
                    <td class="r">{{ $slip->bonus > 0 ? 'Rp '.$fmt($slip->bonus) : '-' }}</td>
                    <td class="r">{{ $pot > 0 ? 'Rp '.$fmt($pot) : '-' }}</td>
                    <td class="r">Rp {{ $fmt($slip->total) }}</td>
                </tr>
                @empty
                <tr><td colspan="11" style="text-align:center;padding:2rem;color:#888;font-style:italic;">Tidak ada data</td></tr>
                @endforelse
            </tbody>
            @if($slips->count() > 0)
            <tfoot>
                <tr>
                    <td colspan="4">TOTAL ({{ $slips->count() }} slip)</td>
                    <td class="r">Rp {{ $fmt($totalGajiPokok) }}</td>
                    <td class="r">Rp {{ $fmt($totalTunjangan) }}</td>
                    <td class="r">Rp {{ $fmt($totalExtra) }}</td>
                    <td class="r">Rp {{ $fmt($totalBonus) }}</td>
                    <td class="r">Rp {{ $fmt($totalPotongan) }}</td>
                    <td class="r">Rp {{ $fmt($totalBersih) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

</div>
</body>
</html>
