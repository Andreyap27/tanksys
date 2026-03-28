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
use App\Http\Controllers\ReportController;

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
    Route::post('/purchase/{purchase}/approve', [PurchaseController::class, 'approve'])->name('purchase.approve');
    Route::post('/purchase/{purchase}/reject', [PurchaseController::class, 'reject'])->name('purchase.reject');
    Route::resource('purchase', PurchaseController::class)->names('purchase')->except(['create', 'edit', 'show']);

    // Stock (read only, auto)
    Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
    Route::get('/stock/data', [StockController::class, 'data'])->name('stock.data');

    // Sales
    Route::get('/sales/data', [SaleController::class, 'data'])->name('sales.data');
    Route::get('/sales/next-invoice', [SaleController::class, 'nextInvoice'])->name('sales.next-invoice');
    Route::get('/sales/{sale}/invoice', [SaleController::class, 'invoice'])->name('sales.invoice');
    Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
    Route::post('/sales/{sale}/approve', [SaleController::class, 'approve'])->name('sales.approve');
    Route::post('/sales/{sale}/reject', [SaleController::class, 'reject'])->name('sales.reject');
    Route::resource('sales', SaleController::class)->names('sales')->except(['create', 'edit', 'show']);

    // Capital
    Route::get('/capital/data', [CapitalController::class, 'data'])->name('capital.data');
    Route::resource('capital', CapitalController::class)->names('capital')->except(['create', 'edit', 'show']);

    // Expenses
    Route::get('/expenses/data', [ExpenseController::class, 'data'])->name('expenses.data');
    Route::resource('expenses', ExpenseController::class)->names('expenses')->except(['create', 'edit', 'show']);

    // Mobil Tangki / Lori
    Route::get('/lori/data', [LoriController::class, 'data'])->name('lori.data');
    Route::resource('lori', LoriController::class)->names('lori')->except(['create', 'edit', 'show']);

    // Report
    Route::get('/report', fn() => redirect()->route('report.purchase'))->name('report.index');
    Route::get('/report/print',       [ReportController::class, 'printReport'])->name('report.print');
    Route::get('/report/purchase',    [ReportController::class, 'purchase'])->name('report.purchase');
    Route::get('/report/sale',        [ReportController::class, 'sale'])->name('report.sale');
    Route::get('/report/expense',     [ReportController::class, 'expense'])->name('report.expense');
    Route::get('/report/profit-loss', [ReportController::class, 'profitLoss'])->name('report.profit-loss');
    Route::get('/report/lori',        [ReportController::class, 'lori'])->name('report.lori');
});
