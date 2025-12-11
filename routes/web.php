<?php

use Illuminate\Support\Facades\Route;

// SPA catch-all route - semua request ke Vue Router
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
