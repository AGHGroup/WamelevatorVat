<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LCETablesController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/oracle/tables', [LCETablesController::class, 'index'])->name('oracle.tables');
Route::get('/oracle/tables/{table}', [LCETablesController::class, 'show'])->name('oracle.table.show');