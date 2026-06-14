<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LCETablesController;
use App\Http\Controllers\LocaleController;

// Language switcher — public
Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

// Auth routes — guests only
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Protected routes
Route::middleware('auth')->group(function () {

    Route::get('/', function () {
        return view('dashboard');
    })->name('dashboard');

    // Oracle / LCE tables browser
    Route::get('/oracle/tables',         [LCETablesController::class, 'index'])->name('oracle.tables');
    Route::get('/oracle/tables/{table}', [LCETablesController::class, 'show'])->name('oracle.table.show');

    // ZATCA — placeholder routes
    Route::get('/invoices',        fn() => abort(404))->name('invoices.index');
    Route::get('/invoices/create', fn() => abort(404))->name('invoices.create');
    Route::get('/vat-categories',  fn() => abort(404))->name('vat-categories.index');
    Route::get('/vat-types',       fn() => abort(404))->name('vat-types.index');

});
