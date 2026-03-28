<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $sale->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 13px;
            color: #1a1a1a;
            background: #f5f5f5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
        }
        .page {
            max-width: 800px;
            margin: 2rem auto;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0,0,0,0.10);
        }

        /* Header */
        .inv-header {
            background: #fff;
            padding: 1.25rem 2rem 1rem;
            border-bottom: 3px solid #1a5cb8;
        }
        .inv-logo {
            height: 60px;
            width: auto;
            object-fit: contain;
        }
        .inv-company-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1a5cb8;
            text-decoration: underline;
            text-underline-offset: 5px;
            text-decoration-thickness: 2px;
            letter-spacing: 0.03em;
            line-height: 1.2;
            margin-bottom: 0.3rem;
        }
        .inv-company-address {
            font-size: 0.75rem;
            color: #555;
            line-height: 1.55;
        }

        /* Invoice label strip */
        .inv-label {
            background: #f0f4fb;
            padding: 0.75rem 2rem;
            border-bottom: 1px solid #dbeafe;
            display: flex;
            align-items: baseline;
            justify-content: space-between;
        }
        .inv-label-title {
            font-size: 1.1rem;
            font-weight: 800;
            color: #1a5cb8;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }
        .inv-label-number {
            font-size: 0.85rem;
            color: #374151;
            font-weight: 600;
        }

        /* Body */
        .inv-body { padding: 2rem 2.5rem; }

        /* Meta row */
        .inv-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        .inv-meta-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #888;
            margin-bottom: 0.2rem;
        }
        .inv-meta-value {
            font-size: 0.95rem;
            font-weight: 600;
            color: #1a1a1a;
        }
        .inv-meta-value.large {
            font-size: 1.1rem;
            color: #1a5cb8;
        }

        /* Divider */
        .inv-divider {
            border: none;
            border-top: 1px solid #e5e7eb;
            margin: 1.5rem 0;
        }

        /* Table */
        .inv-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.5rem;
        }
        .inv-table thead tr {
            background: #f0f4fb;
        }
        .inv-table th {
            padding: 0.65rem 1rem;
            text-align: left;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #1a5cb8;
            border-bottom: 2px solid #1a5cb8;
        }
        .inv-table th.right, .inv-table td.right { text-align: right; }
        .inv-table td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #f0f0f0;
            color: #333;
        }
        .inv-table tbody tr:last-child td { border-bottom: none; }

        /* Total block */
        .inv-total-block {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 2rem;
        }
        .inv-total-table { width: 280px; }
        .inv-total-table td {
            padding: 0.4rem 0.75rem;
            font-size: 0.9rem;
            color: #444;
        }
        .inv-total-table td.label { color: #888; }
        .inv-total-table tr.grand td {
            padding-top: 0.65rem;
            border-top: 2px solid #1a5cb8;
            font-size: 1.05rem;
            font-weight: 700;
            color: #1a5cb8;
        }

        /* Noted */
        .inv-noted {
            background: #f8f9fc;
            border-left: 3px solid #1a5cb8;
            padding: 0.75rem 1rem;
            border-radius: 0 4px 4px 0;
            font-size: 0.85rem;
            color: #555;
            margin-bottom: 2rem;
        }
        .inv-noted-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #1a5cb8;
            font-weight: 600;
            margin-bottom: 0.2rem;
        }

        /* Print button */
        .print-bar {
            max-width: 800px;
            margin: 0 auto 1rem;
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
        }
        .print-bar button {
            padding: 0.5rem 1.25rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.875rem;
            font-weight: 600;
        }
        .btn-print { background: #1a5cb8; color: #fff; }
        .btn-close { background: #e5e7eb; color: #333; }

        /* In-iframe mode (paper preview) */
        body.in-modal {
            background: #d1d5db;
            min-height: 100%;
            padding: 1.5rem 1rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
        }
        body.in-modal .print-bar { display: none; }
        body.in-modal .page {
            margin: 0 auto;
            width: 100%;
            max-width: 800px;
            border-radius: 4px;
            box-shadow: 0 6px 32px rgba(0,0,0,0.22);
        }

        @media print {
            body, body.in-modal {
                background: #fff !important;
                display: block !important;
                padding: 0 !important;
                min-height: unset !important;
                align-items: unset !important;
            }
            .print-bar { display: none !important; }
            .page, body.in-modal .page {
                margin: 0 !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
            }
        }
    </style>
</head>
<body class="{{ isset($embedded) ? 'in-modal' : '' }}">
<script>
    // Auto-detect if inside iframe
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

<div class="page">
    {{-- Header --}}
    <div class="inv-header">
        <table style="width:100%;border-collapse:collapse;">
            <tr>
                <td style="width:20%;vertical-align:middle;">
                    @if(file_exists(public_path('images/logo.png')))
                        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="inv-logo">
                    @endif
                </td>
                <td style="text-align:center;vertical-align:middle;">
                    <div class="inv-company-name">PT. ANUGRAH ENERGI PETROLUM</div>
                    <div class="inv-company-address">
                        Komplek The Residence Blok A 22 RT 05/RW 07,<br>
                        Kel. Patam Lestari, Kec. Sekupang, Kota Batam, Kepulauan Riau 29427
                    </div>
                </td>
                <td style="width:20%;"></td>
            </tr>
        </table>
    </div>

    {{-- Invoice Label --}}
    <div class="inv-label">
        <span class="inv-label-title">Invoice</span>
        <span class="inv-label-number">{{ $sale->invoice_number }}</span>
    </div>

    {{-- Body --}}
    <div class="inv-body">

        {{-- Meta --}}
        <div class="inv-meta">
            <div>
                <div class="inv-meta-label">Tagihan Kepada</div>
                <div class="inv-meta-value large">{{ $sale->customer->name }}</div>
                @if($sale->customer->address)
                <div style="font-size:0.82rem;color:#666;margin-top:0.2rem;">{{ $sale->customer->address }}</div>
                @endif
                @if($sale->customer->contact)
                <div style="font-size:0.82rem;color:#666;">{{ $sale->customer->contact }}</div>
                @endif
            </div>
            <div style="text-align:right;">
                <div style="margin-bottom:0.75rem;">
                    <div class="inv-meta-label">Tanggal</div>
                    <div class="inv-meta-value">{{ $sale->date->translatedFormat('d F Y') }}</div>
                </div>
                <div>
                    <div class="inv-meta-label">No. Invoice</div>
                    <div class="inv-meta-value" style="color:#1a5cb8;">{{ $sale->invoice_number }}</div>
                </div>
            </div>
        </div>

        <hr class="inv-divider">

        {{-- Item table --}}
        <table class="inv-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Deskripsi</th>
                    <th class="right">Qty (L)</th>
                    <th class="right">Harga / Liter</th>
                    <th class="right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>{{ $sale->description ?: 'Penjualan Bahan Bakar Minyak' }}</td>
                    <td class="right">{{ number_format($sale->quantity, 2, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($sale->price, 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($sale->amount, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        {{-- Total --}}
        <div class="inv-total-block">
            <table class="inv-total-table">
                <tr>
                    <td class="label">Subtotal</td>
                    <td class="right">Rp {{ number_format($sale->amount, 0, ',', '.') }}</td>
                </tr>
                <tr class="grand">
                    <td>Total</td>
                    <td class="right">Rp {{ number_format($sale->amount, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        {{-- Noted --}}
        @if($sale->noted)
        <div class="inv-noted">
            <div class="inv-noted-label">Catatan</div>
            {{ $sale->noted }}
        </div>
        @endif

        {{-- Signature --}}
        <div style="display:flex;justify-content:flex-end;margin-top:1rem;">
            <div style="text-align:center;width:180px;">
                <div style="font-size:0.8rem;color:#888;margin-bottom:4rem;">Hormat Kami,</div>
                <div style="border-top:1px solid #333;padding-top:0.4rem;font-size:0.85rem;font-weight:600;">
                    PT. ANUGRAH ENERGI PETROLUM
                </div>
            </div>
        </div>

    </div>

</div>

</body>
</html>
