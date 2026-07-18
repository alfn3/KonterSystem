<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ApiTransactionController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\QrisPaymentController;
use App\Http\Controllers\DailyCashController;
use App\Http\Controllers\DailyDepositController;

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Authenticated web portal routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/inventoris/stok', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventoris/analitik', [InventoryController::class, 'analytics'])->name('inventory.analytics');
    Route::post('/inventoris/stok', [InventoryController::class, 'store'])->name('inventory.store');
    Route::put('/inventoris/stok/{id}', [InventoryController::class, 'update'])->name('inventory.update');
    Route::delete('/inventoris/stok/{id}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
    Route::get('/inventoris/riwayat', [InventoryController::class, 'history'])->name('inventory.history');
    Route::post('/inventoris/restok', [InventoryController::class, 'restock'])->name('inventory.restock');

    Route::get('/operasional/cabang', [BranchController::class, 'index'])->name('branch.index');
    Route::post('/operasional/cabang', [BranchController::class, 'store'])->name('branch.store');
    Route::delete('/operasional/cabang/{id}', [BranchController::class, 'destroy'])->name('branch.destroy');
    Route::get('/operasional/cabang/{id}', [BranchController::class, 'show'])->name('branch.show');
    Route::put('/operasional/cabang/{id}', [BranchController::class, 'update'])->name('branch.update');
    Route::get('/operasional/cabang/{id}/aktivitas', [BranchController::class, 'activities'])->name('branch.activities');

    Route::get('/operasional/audit', [AuditController::class, 'index'])->name('audit.index');
    Route::post('/operasional/audit', [AuditController::class, 'store'])->name('audit.store');

    Route::resource('/operasional/karyawan', EmployeeController::class)->names([
        'index' => 'employee.index',
        'store' => 'employee.store',
        'update' => 'employee.update',
        'destroy' => 'employee.destroy',
    ]);

    Route::resource('/operasional/pelanggan', CustomerController::class)->names([
        'index' => 'customer.index',
        'store' => 'customer.store',
        'update' => 'customer.update',
        'destroy' => 'customer.destroy',
    ]);

    Route::get('/laporan/bulanan', [ReportController::class, 'monthly'])->name('report.monthly');

    // User Management
    Route::resource('/sistem/user', UserController::class)->names([
        'index' => 'user.index',
        'store' => 'user.store',
        'update' => 'user.update',
        'destroy' => 'user.destroy',
    ]);

    // Device Management
    Route::resource('/sistem/device', DeviceController::class)->names([
        'index' => 'device.index',
        'store' => 'device.store',
        'update' => 'device.update',
        'destroy' => 'device.destroy',
    ]);

    // QRIS Payment Confirmation
    Route::get('/keuangan/qris', [QrisPaymentController::class, 'index'])->name('qris.index');
    Route::put('/keuangan/qris/{id}/confirm', [QrisPaymentController::class, 'confirm'])->name('qris.confirm');

    // Setoran Cabang (Rincian Setoran)
    Route::get('/keuangan/setoran-cabang', [DailyDepositController::class, 'index'])->name('daily-deposits.index');
    Route::post('/keuangan/setoran-cabang', [DailyDepositController::class, 'store'])->name('daily-deposits.store');

    // Kas Harian
    Route::get('/keuangan/kas-harian', [DailyCashController::class, 'index'])->name('daily-cash.index');
});

// Unprotected API routes for mobile integration
Route::post('/api/login', [ApiTransactionController::class, 'mobileLogin']);
Route::post('/api/login/request-otp', [ApiTransactionController::class, 'requestOtp']);
Route::post('/api/login/verify-otp', [ApiTransactionController::class, 'verifyOtp']);
Route::get('/api/login/check-session', [ApiTransactionController::class, 'checkSession']);
Route::post('/api/devices', [ApiTransactionController::class, 'registerDevice']);
Route::post('/api/transaksi', [ApiTransactionController::class, 'store']);
Route::post('/api/transaksi/qris', [ApiTransactionController::class, 'generateQris']);
Route::get('/api/transaksi/{id}/status', [ApiTransactionController::class, 'getStatus']);
Route::match(['get', 'post'], '/api/qrcode', [ApiTransactionController::class, 'generateQrCode']);
Route::post('/api/callback/payment', [ApiTransactionController::class, 'handlePaymentCallback']);
Route::get('/api/transaksi', [ApiTransactionController::class, 'index']);
Route::get('/api/produk', [ApiTransactionController::class, 'products']);
Route::get('/api/pengeluaran', [ApiTransactionController::class, 'getExpenses']);
Route::post('/api/pengeluaran', [ApiTransactionController::class, 'storeExpense']);
Route::put('/api/pengeluaran/{id}', [ApiTransactionController::class, 'updateExpense']);
Route::delete('/api/pengeluaran/{id}', [ApiTransactionController::class, 'deleteExpense']);
Route::post('/api/closing', [ApiTransactionController::class, 'storeClosing']);
Route::get('/api/closing', [ApiTransactionController::class, 'getClosing']);
Route::get('/api/saldo-elektrik', [ApiTransactionController::class, 'getSaldoElektrik']);
Route::post('/api/attendance', [ApiTransactionController::class, 'storeAttendance']);
Route::get('/api/attendance', [ApiTransactionController::class, 'getAttendance']);
Route::get('/api/pelanggan', [ApiTransactionController::class, 'getCustomers']);
Route::post('/api/pelanggan', [ApiTransactionController::class, 'storeCustomer']);
Route::put('/api/pelanggan/{id}', [ApiTransactionController::class, 'updateCustomer']);
Route::delete('/api/pelanggan/{id}', [ApiTransactionController::class, 'deleteCustomer']);
Route::get('/api/cabang/status', [ApiTransactionController::class, 'getBranchStatuses']);


