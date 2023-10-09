<?php

use App\Http\Controllers\ComercializadorasController;
use App\Http\Controllers\OperadorasController;
use App\Models\Operadoras;
use Illuminate\Support\Facades\Route;

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

Route::get('/operadoras', [OperadorasController::class, 'index']);
Route::get('/comercializadoras', [ComercializadorasController::class, 'index']);