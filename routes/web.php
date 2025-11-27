<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Auth
Route::get('/login', function () {
    return view('pages.auth.login');
})->name('login');

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Products
Route::get('/products', function () {
    return view('pages.products.index');
})->name('products.index');
Route::get('/products/create', function () {
    return view('pages.products.create');
})->name('products.create');
Route::get('/products/{id}', function ($id) {
    return view('pages.products.show', ['id' => $id]);
})->name('products.show');
Route::get('/products/{id}/edit', function ($id) {
    return view('pages.products.edit', ['id' => $id]);
})->name('products.edit');

// Components
Route::get('/components', function () {
    return view('pages.components.index');
})->name('components.index');
Route::get('/components/create', function () {
    return view('pages.components.create');
})->name('components.create');
Route::get('/components/{id}/edit', function ($id) {
    return view('pages.components.edit', ['id' => $id]);
})->name('components.edit');

// Purchase Histories
Route::get('/purchase-histories', function () {
    return view('pages.purchase-histories.index');
})->name('purchase-histories.index');
Route::get('/purchase-histories/create', function () {
    return view('pages.purchase-histories.create');
})->name('purchase-histories.create');
Route::get('/purchase-histories/{id}/edit', function ($id) {
    return view('pages.purchase-histories.edit', ['id' => $id]);
})->name('purchase-histories.edit');

// HPE Results
Route::get('/hpe/results', function () {
    return view('pages.hpe.index');
})->name('hpe.results.index');
Route::get('/hpe/calculate', function () {
    return view('pages.hpe.calculate');
})->name('hpe.calculate');
Route::get('/hpe/results/{id}', function ($id) {
    return view('pages.hpe.show', ['id' => $id]);
})->name('hpe.results.show');

// Exchange Rates
Route::get('/exchange-rates', function () {
    return view('pages.exchange-rates.index');
})->name('exchange-rates.index');

// Audit Logs
Route::get('/audit-logs', function () {
    return view('pages.audit-logs.index');
})->name('audit-logs.index');

// Reporting
Route::get('/reporting', function () {
    return view('pages.reporting.index');
})->name('reporting.index');
