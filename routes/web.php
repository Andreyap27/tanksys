<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\CapitalController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\LoriController;
use App\Http\Controllers\LoriExpenseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\KapalController;
use App\Http\Controllers\MobilController;
use App\Http\Controllers\BankTransactionController;
use App\Http\Controllers\HutangController;
use App\Http\Controllers\PiutangController;
use App\Http\Controllers\PettyCashController;
use App\Http\Controllers\PettyCashTransactionController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\StockUsageController;
use App\Http\Controllers\TxPrintController;
use App\Http\Controllers\GajiController;
use App\Http\Controllers\GajiSlipController;
use App\Http\Controllers\KaryawanPinjamanController;
use App\Http\Controllers\UangKoordinasiController;

// Guest routes
Route::middleware('guest.jwt')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Authenticated routes
Route::middleware('auth.jwt')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Reset password wajib jika reset_password = true
    Route::get('/reset-password', [AuthController::class, 'showResetForm'])->name('password.reset.form');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.reset');

    // ── Routes NOT accessible by Finance ────────────────────────────────────────
    Route::middleware('not.finance')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // User Management
    Route::get('/user/next-id', [UserController::class, 'nextId'])->name('user.next-id');
    Route::get('/user/check-username', [UserController::class, 'checkUsername'])->name('user.check-username');
    Route::get('/user/data', [UserController::class, 'data'])->name('user.data');
    Route::post('/user/{user}/reset-password', [UserController::class, 'adminResetPassword'])->name('user.reset-password');
    Route::resource('user', UserController::class)->names('user')->except(['create', 'edit', 'show']);

    // Vendor
    Route::get('/vendor/next-id', [VendorController::class, 'nextId'])->name('vendor.next-id');
    Route::get('/vendor/list', [VendorController::class, 'list'])->name('vendor.list');
    Route::get('/vendor/data', [VendorController::class, 'data'])->name('vendor.data');
    Route::resource('vendor', VendorController::class)->names('vendor')->except(['create', 'edit', 'show']);

    // Customer
    Route::get('/customer/next-id', [CustomerController::class, 'nextId'])->name('customer.next-id');
    Route::get('/customer/data', [CustomerController::class, 'data'])->name('customer.data');
    Route::resource('customer', CustomerController::class)->names('customer')->except(['create', 'edit', 'show']);

    // Purchase
    Route::get('/purchase/data', [PurchaseController::class, 'data'])->name('purchase.data');
    Route::get('/purchase/trash', [PurchaseController::class, 'trash'])->name('purchase.trash');
    Route::get('/purchase/trash-data', [PurchaseController::class, 'trashData'])->name('purchase.trash-data');
    Route::post('/purchase/{id}/restore', [PurchaseController::class, 'restore'])->name('purchase.restore');
    Route::post('/purchase/{id}/force-delete', [PurchaseController::class, 'forceDelete'])->name('purchase.force-delete');
    Route::post('/purchase/{purchase}/approve', [PurchaseController::class, 'approve'])->name('purchase.approve');
    Route::post('/purchase/{purchase}/reject', [PurchaseController::class, 'reject'])->name('purchase.reject');
    Route::post('/purchase/{purchase}/paid', [PurchaseController::class, 'paid'])->name('purchase.paid');
    Route::resource('purchase', PurchaseController::class)->names('purchase')->except(['create', 'edit', 'show']);

    // Kapal
    Route::get('/kapal/list', [KapalController::class, 'list'])->name('kapal.list');
    Route::get('/kapal/data', [KapalController::class, 'data'])->name('kapal.data');
    Route::resource('kapal', KapalController::class)->names('kapal')->except(['create', 'edit', 'show']);

    // Mobil (Master)
    Route::get('/mobil-master/list', [MobilController::class, 'list'])->name('mobil-master.list');
    Route::get('/mobil-master/data', [MobilController::class, 'data'])->name('mobil-master.data');
    Route::resource('mobil-master', MobilController::class)->names('mobil-master')->parameters(['mobil-master' => 'mobil'])->except(['create', 'edit', 'show']);

    // Stock (read only, auto)
    Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
    Route::get('/stock/data', [StockController::class, 'data'])->name('stock.data');
    Route::get('/stock/summary', [StockController::class, 'summary'])->name('stock.summary');

    // Operasional — Transfer Stok
    Route::get('/operasional/transfer', [StockTransferController::class, 'index'])->name('stock-transfer.index');
    Route::get('/operasional/transfer/data', [StockTransferController::class, 'data'])->name('stock-transfer.data');
    Route::post('/operasional/transfer', [StockTransferController::class, 'store'])->name('stock-transfer.store');
    Route::delete('/operasional/transfer/{stockTransfer}', [StockTransferController::class, 'destroy'])->name('stock-transfer.destroy');

    // Operasional — Pemakaian Stok
    Route::get('/operasional/usage', [StockUsageController::class, 'index'])->name('stock-usage.index');
    Route::get('/operasional/usage/data', [StockUsageController::class, 'data'])->name('stock-usage.data');
    Route::post('/operasional/usage', [StockUsageController::class, 'store'])->name('stock-usage.store');
    Route::delete('/operasional/usage/{stockUsage}', [StockUsageController::class, 'destroy'])->name('stock-usage.destroy');

    // Sales
    Route::get('/sales/data', [SaleController::class, 'data'])->name('sales.data');
    Route::get('/sales/trash', [SaleController::class, 'trash'])->name('sales.trash');
    Route::get('/sales/trash-data', [SaleController::class, 'trashData'])->name('sales.trash-data');
    Route::post('/sales/{id}/restore', [SaleController::class, 'restore'])->name('sales.restore');
    Route::post('/sales/{id}/force-delete', [SaleController::class, 'forceDelete'])->name('sales.force-delete');
    Route::get('/sales/next-invoice', [SaleController::class, 'nextInvoice'])->name('sales.next-invoice');
    Route::get('/sales/{sale}/invoice', [SaleController::class, 'invoice'])->name('sales.invoice');
    Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
    Route::post('/sales/{sale}/approve', [SaleController::class, 'approve'])->name('sales.approve');
    Route::post('/sales/{sale}/reject', [SaleController::class, 'reject'])->name('sales.reject');
    Route::post('/sales/{sale}/paid', [SaleController::class, 'paid'])->name('sales.paid');
    Route::resource('sales', SaleController::class)->names('sales')->except(['create', 'edit', 'show']);

    // Capital
    Route::get('/capital/summary', [CapitalController::class, 'summary'])->name('capital.summary');
    Route::get('/capital/data', [CapitalController::class, 'data'])->name('capital.data');
    Route::get('/capital/trash', [CapitalController::class, 'trash'])->name('capital.trash');
    Route::get('/capital/trash-data', [CapitalController::class, 'trashData'])->name('capital.trash-data');
    Route::post('/capital/{id}/restore', [CapitalController::class, 'restore'])->name('capital.restore');
    Route::post('/capital/{id}/force-delete', [CapitalController::class, 'forceDelete'])->name('capital.force-delete');
    Route::post('/capital/{capital}/approve', [CapitalController::class, 'approve'])->name('capital.approve');
    Route::post('/capital/{capital}/reject', [CapitalController::class, 'reject'])->name('capital.reject');
    Route::resource('capital', CapitalController::class)->names('capital')->except(['create', 'edit', 'show']);

    // Expenses
    Route::get('/expenses/capital-total', [ExpenseController::class, 'capitalTotal'])->name('expenses.capital-total');
    Route::get('/expenses/data', [ExpenseController::class, 'data'])->name('expenses.data');
    Route::get('/expenses/trash', [ExpenseController::class, 'trash'])->name('expenses.trash');
    Route::get('/expenses/trash-data', [ExpenseController::class, 'trashData'])->name('expenses.trash-data');
    Route::post('/expenses/{id}/restore', [ExpenseController::class, 'restore'])->name('expenses.restore');
    Route::post('/expenses/{id}/force-delete', [ExpenseController::class, 'forceDelete'])->name('expenses.force-delete');
    Route::post('/expenses/{expense}/approve', [ExpenseController::class, 'approve'])->name('expenses.approve');
    Route::post('/expenses/{expense}/reject', [ExpenseController::class, 'reject'])->name('expenses.reject');
    Route::post('/expenses/{expense}/paid', [ExpenseController::class, 'paid'])->name('expenses.paid');
    Route::resource('expenses', ExpenseController::class)->names('expenses')->except(['create', 'edit', 'show']);

    // Mobil Tangki / Lori Sale
    Route::get('/lori/data', [LoriController::class, 'data'])->name('lori.data');
    Route::get('/lori/trash', [LoriController::class, 'trash'])->name('lori.trash');
    Route::get('/lori/trash-data', [LoriController::class, 'trashData'])->name('lori.trash-data');
    Route::post('/lori/{id}/restore', [LoriController::class, 'restore'])->name('lori.restore');
    Route::post('/lori/{id}/force-delete', [LoriController::class, 'forceDelete'])->name('lori.force-delete');
    Route::resource('lori', LoriController::class)->names('lori')->except(['create', 'edit', 'show']);

    // Mobil Tangki / Lori Expense
    Route::get('/lori-expense/summary', [LoriExpenseController::class, 'summary'])->name('lori-expense.summary');
    Route::get('/lori-expense/data', [LoriExpenseController::class, 'data'])->name('lori-expense.data');
    Route::get('/lori-expense/trash', [LoriExpenseController::class, 'trash'])->name('lori-expense.trash');
    Route::get('/lori-expense/trash-data', [LoriExpenseController::class, 'trashData'])->name('lori-expense.trash-data');
    Route::post('/lori-expense/{id}/restore', [LoriExpenseController::class, 'restore'])->name('lori-expense.restore');
    Route::post('/lori-expense/{id}/force-delete', [LoriExpenseController::class, 'forceDelete'])->name('lori-expense.force-delete');
    Route::resource('lori-expense', LoriExpenseController::class)->names('lori-expense')->except(['create', 'edit', 'show']);

    // Hutang
    Route::get('/hutang/data',                  [HutangController::class, 'data'])->name('hutang.data');
    Route::get('/hutang/trash',                  [HutangController::class, 'trash'])->name('hutang.trash');
    Route::get('/hutang/trash-data',             [HutangController::class, 'trashData'])->name('hutang.trash-data');
    Route::post('/hutang/{id}/restore',          [HutangController::class, 'restore'])->name('hutang.restore');
    Route::post('/hutang/{id}/force-delete',     [HutangController::class, 'forceDelete'])->name('hutang.force-delete');
    Route::post('/hutang/{hutang}/approve',      [HutangController::class, 'approve'])->name('hutang.approve');
    Route::post('/hutang/{hutang}/reject',       [HutangController::class, 'reject'])->name('hutang.reject');
    Route::post('/hutang/{hutang}/paid',         [HutangController::class, 'paid'])->name('hutang.paid');
    Route::resource('hutang', HutangController::class)->names('hutang')->except(['create', 'edit', 'show']);

    // Piutang
    Route::get('/piutang/data',                   [PiutangController::class, 'data'])->name('piutang.data');
    Route::get('/piutang/trash',                   [PiutangController::class, 'trash'])->name('piutang.trash');
    Route::get('/piutang/trash-data',              [PiutangController::class, 'trashData'])->name('piutang.trash-data');
    Route::post('/piutang/{id}/restore',           [PiutangController::class, 'restore'])->name('piutang.restore');
    Route::post('/piutang/{id}/force-delete',      [PiutangController::class, 'forceDelete'])->name('piutang.force-delete');
    Route::post('/piutang/{piutang}/approve',      [PiutangController::class, 'approve'])->name('piutang.approve');
    Route::post('/piutang/{piutang}/reject',       [PiutangController::class, 'reject'])->name('piutang.reject');
    Route::post('/piutang/{piutang}/paid',         [PiutangController::class, 'paid'])->name('piutang.paid');
    Route::resource('piutang', PiutangController::class)->names('piutang')->except(['create', 'edit', 'show']);

    // Petty Cash Master
    Route::get('/petty-cash/list', [PettyCashController::class, 'list'])->name('petty-cash.list');
    Route::get('/petty-cash/data', [PettyCashController::class, 'data'])->name('petty-cash.data');
    Route::resource('petty-cash', PettyCashController::class)->names('petty-cash')->except(['create', 'edit', 'show']);

    // Petty Cash Transactions
    Route::get('/petty-cash-transaction/summary', [PettyCashTransactionController::class, 'summary'])->name('petty-cash-transaction.summary');
    Route::get('/petty-cash-transaction/data', [PettyCashTransactionController::class, 'data'])->name('petty-cash-transaction.data');
    Route::get('/petty-cash-transaction/trash', [PettyCashTransactionController::class, 'trash'])->name('petty-cash-transaction.trash');
    Route::get('/petty-cash-transaction/trash-data', [PettyCashTransactionController::class, 'trashData'])->name('petty-cash-transaction.trash-data');
    Route::post('/petty-cash-transaction/{id}/restore', [PettyCashTransactionController::class, 'restore'])->name('petty-cash-transaction.restore');
    Route::post('/petty-cash-transaction/{id}/force-delete', [PettyCashTransactionController::class, 'forceDelete'])->name('petty-cash-transaction.force-delete');
    Route::resource('petty-cash-transaction', PettyCashTransactionController::class)->names('petty-cash-transaction')->except(['create', 'edit', 'show']);

    // Bank In/Out
    Route::get('/bank/print', [BankTransactionController::class, 'printView'])->name('bank.print');
    Route::get('/bank/data', [BankTransactionController::class, 'data'])->name('bank.data');
    Route::get('/bank/trash', [BankTransactionController::class, 'trash'])->name('bank.trash');
    Route::get('/bank/trash-data', [BankTransactionController::class, 'trashData'])->name('bank.trash-data');
    Route::post('/bank/{id}/restore', [BankTransactionController::class, 'restore'])->name('bank.restore');
    Route::post('/bank/{id}/force-delete', [BankTransactionController::class, 'forceDelete'])->name('bank.force-delete');
    Route::resource('bank', BankTransactionController::class)->names('bank')->except(['create', 'edit', 'show']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');

    // Transaction Print
    Route::get('/tx-print', [TxPrintController::class, 'show'])->name('tx.print');

    }); // end not.finance

    // ── Routes accessible by Finance and Super Admin only ───────────────────────
    Route::middleware('finance.only')->group(function () {

    // Gaji (Karyawan Master)
    Route::get('/gaji/print', [GajiController::class, 'printReport'])->name('gaji.print');
    Route::get('/gaji/data', [GajiController::class, 'data'])->name('gaji.data');
    Route::get('/gaji/summary-stats', [GajiController::class, 'summaryStats'])->name('gaji.summary-stats');
    Route::get('/gaji/trash', [GajiController::class, 'trash'])->name('gaji.trash');
    Route::get('/gaji/trash-data', [GajiController::class, 'trashData'])->name('gaji.trash-data');
    Route::post('/gaji/{id}/restore', [GajiController::class, 'restore'])->name('gaji.restore');
    Route::post('/gaji/{id}/force-delete', [GajiController::class, 'forceDelete'])->name('gaji.force-delete');
    Route::resource('gaji', GajiController::class)->names('gaji')->parameters(['gaji' => 'karyawan']);

    // Pinjaman Karyawan
    Route::post('/gaji/{karyawan}/pinjaman', [KaryawanPinjamanController::class, 'store'])->name('gaji.pinjaman.store');
    Route::put('/gaji/pinjaman/{pinjaman}', [KaryawanPinjamanController::class, 'update'])->name('gaji.pinjaman.update');
    Route::delete('/gaji/pinjaman/{pinjaman}', [KaryawanPinjamanController::class, 'destroy'])->name('gaji.pinjaman.destroy');

    // Gaji Slip
    Route::post('/gaji/{karyawan}/slip', [GajiSlipController::class, 'store'])->name('gaji.slip.store');
    Route::put('/gaji/slip/{slip}', [GajiSlipController::class, 'update'])->name('gaji.slip.update');
    Route::delete('/gaji/slip/{slip}', [GajiSlipController::class, 'destroy'])->name('gaji.slip.destroy');
    Route::post('/gaji/slip/{slip}/approve', [GajiSlipController::class, 'approve'])->name('gaji.slip.approve');
    Route::post('/gaji/slip/{slip}/reject', [GajiSlipController::class, 'reject'])->name('gaji.slip.reject');
    Route::get('/gaji/slip/{slip}/print', [GajiSlipController::class, 'print'])->name('gaji.slip.print');

    // Uang Koordinasi
    Route::get('/uang-koordinasi/print', [UangKoordinasiController::class, 'printReport'])->name('uang-koordinasi.print');
    Route::get('/uang-koordinasi/data', [UangKoordinasiController::class, 'data'])->name('uang-koordinasi.data');
    Route::get('/uang-koordinasi/trash', [UangKoordinasiController::class, 'trash'])->name('uang-koordinasi.trash');
    Route::get('/uang-koordinasi/trash-data', [UangKoordinasiController::class, 'trashData'])->name('uang-koordinasi.trash-data');
    Route::post('/uang-koordinasi/{id}/restore', [UangKoordinasiController::class, 'restore'])->name('uang-koordinasi.restore');
    Route::post('/uang-koordinasi/{id}/force-delete', [UangKoordinasiController::class, 'forceDelete'])->name('uang-koordinasi.force-delete');
    Route::post('/uang-koordinasi/{uangKoordinasi}/approve', [UangKoordinasiController::class, 'approve'])->name('uang-koordinasi.approve');
    Route::post('/uang-koordinasi/{uangKoordinasi}/reject', [UangKoordinasiController::class, 'reject'])->name('uang-koordinasi.reject');
    Route::resource('uang-koordinasi', UangKoordinasiController::class)->names('uang-koordinasi')->except(['create', 'edit']);

    }); // end finance.only

    // ── Report (not.finance) ─────────────────────────────────────────────────────
    Route::middleware('not.finance')->group(function () {

    // Report
    Route::get('/report', fn() => redirect()->route('report.purchase'))->name('report.index');
    Route::get('/report/print',         [ReportController::class, 'printReport'])->name('report.print');
    Route::get('/report/purchase',      [ReportController::class, 'purchase'])->name('report.purchase');
    Route::get('/report/purchase/trash', [ReportController::class, 'purchaseTrash'])->name('report.purchase.trash');
    Route::get('/report/sale',          [ReportController::class, 'sale'])->name('report.sale');
    Route::get('/report/sale/trash',    [ReportController::class, 'saleTrash'])->name('report.sale.trash');
    Route::get('/report/expense',       [ReportController::class, 'expense'])->name('report.expense');
    Route::get('/report/expense/trash', [ReportController::class, 'expenseTrash'])->name('report.expense.trash');
    Route::get('/report/capital',       [ReportController::class, 'capital'])->name('report.capital');
    Route::get('/report/capital/trash', [ReportController::class, 'capitalTrash'])->name('report.capital.trash');
    Route::get('/report/lori-omset',    [ReportController::class, 'loriOmset'])->name('report.lori-omset');
    Route::get('/report/lori-expense',  [ReportController::class, 'loriExpense'])->name('report.lori-expense');
    Route::get('/report/lori-expense/trash', [ReportController::class, 'loriExpenseTrash'])->name('report.lori-expense.trash');
    Route::get('/report/lori',          [ReportController::class, 'lori'])->name('report.lori');
    Route::get('/report/lori/trash',    [ReportController::class, 'loriTrash'])->name('report.lori.trash');
    Route::get('/report/profit-loss',   [ReportController::class, 'profitLoss'])->name('report.profit-loss');
    Route::get('/report/petty-cash',    [ReportController::class, 'pettyCash'])->name('report.petty-cash');

    }); // end not.finance (report)
});
