<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoicesController;
use App\Http\Controllers\VatCategoriesController;
use App\Http\Controllers\VatTypesController;
use App\Http\Controllers\LCETablesController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\CompanySettingController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\SystemSelectController;

// Public — language switcher
Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

// Guest only
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Protected
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // VAT Invoices
    Route::get('/invoices',                [InvoicesController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/export',         [InvoicesController::class, 'export'])->name('invoices.export');
    Route::get('/invoices/create',         fn() => abort(404))->name('invoices.create');
    Route::get('/invoices/{serial}/print', [InvoicesController::class, 'show'])->name('invoices.print');

    // VAT Categories
    Route::get('/vat-categories', [VatCategoriesController::class, 'index'])->name('vat-categories.index');

    // VAT Types
    Route::get('/vat-types', [VatTypesController::class, 'index'])->name('vat-types.index');

    // Locations
    Route::get('/locations/regions',              [LocationController::class, 'regions'])->name('locations.regions');
    Route::post('/locations/regions',             [LocationController::class, 'storeRegion'])->name('locations.regions.store');
    Route::delete('/locations/regions/{id}',      [LocationController::class, 'destroyRegion'])->name('locations.regions.destroy');

    Route::get('/locations/cities',               [LocationController::class, 'cities'])->name('locations.cities');
    Route::post('/locations/cities',              [LocationController::class, 'storeCity'])->name('locations.cities.store');
    Route::delete('/locations/cities/{id}',       [LocationController::class, 'destroyCity'])->name('locations.cities.destroy');

    Route::get('/locations/districts',            [LocationController::class, 'districts'])->name('locations.districts');
    Route::post('/locations/districts',           [LocationController::class, 'storeDistrict'])->name('locations.districts.store');
    Route::delete('/locations/districts/{id}',    [LocationController::class, 'destroyDistrict'])->name('locations.districts.destroy');

    // Customers
    Route::get('/customers',             [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/{id}/edit',   [CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('/customers/{id}',        [CustomerController::class, 'update'])->name('customers.update');

    // Company Settings
    Route::get('/company/settings',              [CompanySettingController::class, 'edit'])->name('company.settings.edit');
    Route::put('/company/settings',              [CompanySettingController::class, 'update'])->name('company.settings.update');
    Route::get('/company/districts/{cityId}',    [CompanySettingController::class, 'districts'])->name('company.districts');

    // System switcher
    Route::get('/switch-system/{system}', function ($system) {
        abort_if(! in_array($system, ['zatca', 'wamelevator']), 404);
        $user = auth()->user();
        if ($system === 'wamelevator' && ! $user->hasWamelevatorAccess()) abort(403);
        if ($system === 'zatca'       && ! $user->hasZatcaAccess())       abort(403);
        session(['active_system' => $system]);
        return redirect()->back()->with('success', 'تم تغيير النظام');
    })->name('switch.system');

    // Oracle table browser
    Route::get('/oracle/tables',         [LCETablesController::class, 'index'])->name('oracle.tables');
    Route::get('/oracle/tables/{table}', [LCETablesController::class, 'show'])->name('oracle.table.show');

});
