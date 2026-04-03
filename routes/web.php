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
    Route::post('/purchase/{purchase}/restore', [PurchaseController::class, 'restore'])->name('purchase.restore');
    Route::post('/purchase/{id}/force-delete', [PurchaseController::class, 'forceDelete'])->name('purchase.force-delete');
    Route::post('/purchase/{purchase}/approve', [PurchaseController::class, 'approve'])->name('purchase.approve');
    Route::post('/purchase/{purchase}/reject', [PurchaseController::class, 'reject'])->name('purchase.reject');
    Route::resource('purchase', PurchaseController::class)->names('purchase')->except(['create', 'edit', 'show']);

    // Kapal
    Route::get('/kapal/list', [KapalController::class, 'list'])->name('kapal.list');
    Route::get('/kapal/data', [KapalController::class, 'data'])->name('kapal.data');
    Route::resource('kapal', KapalController::class)->names('kapal')->except(['create', 'edit', 'show']);

    // Mobil (Master)
    Route::get('/mobil-master/list', [MobilController::class, 'list'])->name('mobil-master.list');
    Route::get('/mobil-master/data', [MobilController::class, 'data'])->name('mobil-master.data');
    Route::resource('mobil-master', MobilController::class)->names('mobil-master')->except(['create', 'edit', 'show']);

    // Stock (read only, auto)
    Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
    Route::get('/stock/data', [StockController::class, 'data'])->name('stock.data');
    Route::get('/stock/summary', [StockController::class, 'summary'])->name('stock.summary');

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
    Route::resource('expenses', ExpenseController::class)->names('expenses')->except(['create', 'edit', 'show']);

    // Mobil Tangki / Lori Sale
    Route::get('/lori/data', [LoriController::class, 'data'])->name('lori.data');
    Route::get('/lori/trash', [LoriController::class, 'trash'])->name('lori.trash');
    Route::get('/lori/trash-data', [LoriController::class, 'trashData'])->name('lori.trash-data');
    Route::post('/lori/{id}/restore', [LoriController::class, 'restore'])->name('lori.restore');
    Route::post('/lori/{id}/force-delete', [LoriController::class, 'forceDelete'])->name('lori.force-delete');
    Route::resource('lori', LoriController::class)->names('lori')->except(['create', 'edit', 'show']);

    // Mobil Tangki / Lori Expense
    Route::get('/lori-expense/data', [LoriExpenseController::class, 'data'])->name('lori-expense.data');
    Route::get('/lori-expense/trash', [LoriExpenseController::class, 'trash'])->name('lori-expense.trash');
    Route::get('/lori-expense/trash-data', [LoriExpenseController::class, 'trashData'])->name('lori-expense.trash-data');
    Route::post('/lori-expense/{id}/restore', [LoriExpenseController::class, 'restore'])->name('lori-expense.restore');
    Route::post('/lori-expense/{id}/force-delete', [LoriExpenseController::class, 'forceDelete'])->name('lori-expense.force-delete');
    Route::resource('lori-expense', LoriExpenseController::class)->names('lori-expense')->except(['create', 'edit', 'show']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');

    // Report
    Route::get('/report', fn() => redirect()->route('report.purchase'))->name('report.index');
    Route::get('/report/print',         [ReportController::class, 'printReport'])->name('report.print');
    Route::get('/report/purchase',      [ReportController::class, 'purchase'])->name('report.purchase');
    Route::get('/report/sale',          [ReportController::class, 'sale'])->name('report.sale');
    Route::get('/report/expense',       [ReportController::class, 'expense'])->name('report.expense');
    Route::get('/report/capital',       [ReportController::class, 'capital'])->name('report.capital');
    Route::get('/report/lori-omset',    [ReportController::class, 'loriOmset'])->name('report.lori-omset');
    Route::get('/report/lori-expense',  [ReportController::class, 'loriExpense'])->name('report.lori-expense');
    Route::get('/report/lori',          [ReportController::class, 'lori'])->name('report.lori');
    Route::get('/report/profit-loss',   [ReportController::class, 'profitLoss'])->name('report.profit-loss');
});
