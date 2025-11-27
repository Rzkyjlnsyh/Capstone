<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ComponentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExchangeRateController;
use App\Http\Controllers\HpeController;
use App\Http\Controllers\ProductComponentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseHistoryController;
use App\Http\Controllers\ReportingController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('auth.login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('me', [AuthController::class, 'me'])->name('auth.me');
    });
});

Route::middleware('auth:sanctum')->group(function () {
    // Dashboard (semua role)
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // Read-only endpoints (semua role)
    Route::get('products', [ProductController::class, 'index'])->name('products.index');
    Route::get('products/{product}', [ProductController::class, 'show'])->name('products.show');

    Route::get('components', [ComponentController::class, 'index'])->name('components.index');
    Route::get('components/{component}', [ComponentController::class, 'show'])->name('components.show');

    Route::get('purchase-histories', [PurchaseHistoryController::class, 'index'])->name('purchase-histories.index');
    Route::get('purchase-histories/{purchaseHistory}', [PurchaseHistoryController::class, 'show'])->name('purchase-histories.show');

    Route::get('exchange-rates', [ExchangeRateController::class, 'index'])->name('exchange-rates.index');
    Route::get('exchange-rates/latest', [ExchangeRateController::class, 'latest'])->name('exchange-rates.latest');
    Route::get('exchange-rates/{exchangeRate}', [ExchangeRateController::class, 'show'])->name('exchange-rates.show');

    Route::get('hpe/results', [HpeController::class, 'index'])->name('hpe.results.index');
    Route::get('hpe/results/{hpeResult}', [HpeController::class, 'show'])->name('hpe.results.show');

    // Admin only operations (produk, komponen, BoM)
    Route::middleware(['role:admin', 'audit'])->group(function () {
        Route::post('products', [ProductController::class, 'store'])->name('products.store');
        Route::put('products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::patch('products/{product}', [ProductController::class, 'update']);
        Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

        Route::post('components', [ComponentController::class, 'store'])->name('components.store');
        Route::put('components/{component}', [ComponentController::class, 'update'])->name('components.update');
        Route::patch('components/{component}', [ComponentController::class, 'update']);
        Route::delete('components/{component}', [ComponentController::class, 'destroy'])->name('components.destroy');

        Route::post('products/{product}/components', [ProductComponentController::class, 'store'])->name('product-components.store');
        Route::patch('products/{product}/components/{productComponent}', [ProductComponentController::class, 'update'])->name('product-components.update');
        Route::delete('products/{product}/components/{productComponent}', [ProductComponentController::class, 'destroy'])->name('product-components.destroy');
    });

    // Admin + Finance for purchase history & HPE calculation & reporting
    Route::middleware(['role:admin,finance', 'audit'])->group(function () {
        Route::post('purchase-histories', [PurchaseHistoryController::class, 'store'])->name('purchase-histories.store');
        Route::put('purchase-histories/{purchaseHistory}', [PurchaseHistoryController::class, 'update'])->name('purchase-histories.update');
        Route::patch('purchase-histories/{purchaseHistory}', [PurchaseHistoryController::class, 'update']);
        Route::delete('purchase-histories/{purchaseHistory}', [PurchaseHistoryController::class, 'destroy'])->name('purchase-histories.destroy');

        Route::post('hpe/calculate', [HpeController::class, 'calculate'])->name('hpe.calculate');

        Route::get('reporting/export-hpe', [ReportingController::class, 'exportHpe'])->name('reporting.export-hpe');
        Route::get('reporting/export-products', [ReportingController::class, 'exportProducts'])->name('reporting.export-products');
    });

    // Admin only: Exchange rate sync & audit logs
    Route::middleware(['role:admin'])->group(function () {
        Route::post('exchange-rates/sync', [ExchangeRateController::class, 'sync'])->name('exchange-rates.sync');

        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('audit-logs.show');
    });
});

