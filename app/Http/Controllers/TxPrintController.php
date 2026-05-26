<?php

namespace App\Http\Controllers;

use App\Models\Capital;
use App\Models\Hutang;
use App\Models\Piutang;
use App\Models\Kapal;
use App\Models\Lori;
use App\Models\LoriExpense;
use App\Models\Mobil;
use App\Models\PettyCash;
use App\Models\PettyCashTransaction;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Expense;
use App\Models\Stock;
use App\Models\StockTransfer;
use App\Models\StockUsage;
use Illuminate\Http\Request;

class TxPrintController extends Controller
{
    public function show(Request $request)
    {
        $section   = $request->input('section');
        $dateFrom  = $request->input('date_from') ?: null;
        $dateTo    = $request->input('date_to')   ?: null;
        $kapalId   = $request->input('kapal_id')  ?: null;
        $mobilId   = $request->input('mobil_id')  ?: null;
        $pcId      = $request->input('petty_cash_id') ?: null;

        $kapalName = $kapalId ? optional(Kapal::find($kapalId))->name : null;
        $mobilName = $mobilId ? optional(Mobil::find($mobilId))->name : null;
        $pcName    = $pcId    ? optional(PettyCash::find($pcId))->name : null;

        $data = compact('section', 'dateFrom', 'dateTo', 'kapalId', 'mobilId', 'pcId',
                        'kapalName', 'mobilName', 'pcName');

        $fmt    = fn($n) => number_format((float) $n, 0, ',', '.');
        $fmtQty = fn($n) => number_format((float) $n, 0, ',', '.');
        $data['fmt']    = $fmt;
        $data['fmtQty'] = $fmtQty;

        switch ($section) {
            case 'stock':
                $q = Stock::orderBy('date')->orderBy('created_at');
                if ($kapalId)  $q->where('kapal_id', $kapalId);
                if ($dateFrom) $q->whereDate('date', '>=', $dateFrom);
                if ($dateTo)   $q->whereDate('date', '<=', $dateTo);
                $running = 0;
                $data['rows'] = $q->get()->map(function ($s) use (&$running, $fmtQty) {
                    $running += (float) $s->qty_in - (float) $s->qty_out;
                    return [
                        'date'    => $this->resolveDate($s)->translatedFormat('d M Y H:i'),
                        'party'   => $s->party,
                        'type'    => match($s->type) {
                            'purchase'     => 'Pembelian',
                            'sale'         => 'Penjualan',
                            'transfer_in'  => 'Transfer Masuk',
                            'transfer_out' => 'Transfer Keluar',
                            'usage'        => 'Pemakaian',
                            default        => ucfirst($s->type),
                        },
                        'warna'   => ucfirst($s->warna ?? '-'),
                        'qty_in'  => $s->qty_in  > 0 ? $fmtQty($s->qty_in)  : '-',
                        'qty_out' => $s->qty_out > 0 ? $fmtQty($s->qty_out) : '-',
                        'balance' => $fmtQty($running),
                    ];
                });
                $data['title'] = 'Riwayat Stok BBM';
                break;

            case 'purchase':
                $q = Purchase::whereIn('status', ['approved', 'paid'])->with('kapal')->orderBy('date')->orderBy('created_at');
                if ($kapalId)  $q->where('kapal_id', $kapalId);
                if ($dateFrom) $q->whereDate('date', '>=', $dateFrom);
                if ($dateTo)   $q->whereDate('date', '<=', $dateTo);
                $data['rows'] = $q->get();
                $data['title'] = 'Data Purchase';
                break;

            case 'sales':
                $q = Sale::whereIn('status', ['approved', 'paid'])->with('customer', 'kapal')->orderBy('date')->orderBy('created_at');
                if ($kapalId)  $q->where('kapal_id', $kapalId);
                if ($dateFrom) $q->whereDate('date', '>=', $dateFrom);
                if ($dateTo)   $q->whereDate('date', '<=', $dateTo);
                $data['rows'] = $q->get();
                $data['title'] = 'Data Penjualan';
                break;

            case 'expenses':
                $q = Expense::orderBy('date')->orderBy('created_at');
                if ($kapalId)  $q->where('kapal_id', $kapalId);
                if ($dateFrom) $q->whereDate('date', '>=', $dateFrom);
                if ($dateTo)   $q->whereDate('date', '<=', $dateTo);
                $data['rows'] = $q->get();
                $data['title'] = 'Data Expenses Ship';
                break;

            case 'petty-cash':
                $q = PettyCashTransaction::with('pettyCash')->orderBy('date')->orderBy('created_at');
                if ($pcId)     $q->where('petty_cash_id', $pcId);
                if ($dateFrom) $q->whereDate('date', '>=', $dateFrom);
                if ($dateTo)   $q->whereDate('date', '<=', $dateTo);
                $data['rows'] = $q->get();
                $data['title'] = 'Data Petty Cash';
                break;

            case 'capital':
                $q = Capital::where('status', 'approved')->orderBy('date')->orderBy('created_at');
                if ($kapalId)  $q->where('kapal_id', $kapalId);
                if ($dateFrom) $q->whereDate('date', '>=', $dateFrom);
                if ($dateTo)   $q->whereDate('date', '<=', $dateTo);
                $data['rows'] = $q->get();
                $data['title'] = 'Data Capital';
                break;

            case 'stock-transfer':
                $q = StockTransfer::with('fromKapal', 'toKapal')->orderBy('date')->orderBy('created_at');
                if ($kapalId)  $q->where(fn($qq) => $qq->where('from_kapal_id', $kapalId)->orWhere('to_kapal_id', $kapalId));
                if ($dateFrom) $q->whereDate('date', '>=', $dateFrom);
                if ($dateTo)   $q->whereDate('date', '<=', $dateTo);
                $data['rows'] = $q->get();
                $data['title'] = 'Data Transfer Stok';
                break;

            case 'stock-usage':
                $q = StockUsage::with('kapal')->orderBy('date')->orderBy('created_at');
                if ($kapalId)  $q->where('kapal_id', $kapalId);
                if ($dateFrom) $q->whereDate('date', '>=', $dateFrom);
                if ($dateTo)   $q->whereDate('date', '<=', $dateTo);
                $data['rows'] = $q->get();
                $data['title'] = 'Data Pemakaian Stok';
                break;

            case 'lori':
                $q = Lori::with('mobil', 'customer')->orderBy('date')->orderBy('created_at');
                if ($mobilId)  $q->where('mobil_id', $mobilId);
                if ($dateFrom) $q->whereDate('date', '>=', $dateFrom);
                if ($dateTo)   $q->whereDate('date', '<=', $dateTo);
                $data['rows'] = $q->get();
                $data['title'] = 'Data Sale Mobil Tangki';
                break;

            case 'lori-expense':
                $q = LoriExpense::with('mobil')->orderBy('date')->orderBy('created_at');
                if ($mobilId)  $q->where('mobil_id', $mobilId);
                if ($dateFrom) $q->whereDate('date', '>=', $dateFrom);
                if ($dateTo)   $q->whereDate('date', '<=', $dateTo);
                $data['rows'] = $q->get();
                $data['title'] = 'Data Expenses Mobil Tangki';
                break;

            case 'hutang':
                $q = Hutang::orderBy('date')->orderBy('created_at');
                if ($dateFrom) $q->whereDate('date', '>=', $dateFrom);
                if ($dateTo)   $q->whereDate('date', '<=', $dateTo);
                $data['rows'] = $q->get();
                $data['title'] = 'Data Hutang';
                break;

            case 'piutang':
                $q = Piutang::orderBy('date')->orderBy('created_at');
                if ($dateFrom) $q->whereDate('date', '>=', $dateFrom);
                if ($dateTo)   $q->whereDate('date', '<=', $dateTo);
                $data['rows'] = $q->get();
                $data['title'] = 'Data Piutang';
                break;

            default:
                abort(404);
        }

        return view('print.transaction', $data);
    }
}
