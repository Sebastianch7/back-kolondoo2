<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

Route::get('getOperadoras', [ApiController::class, 'getOperadorasList']);
Route::get('getComercializadoras', [ApiController::class, 'getComercializadorasList']);
Route::get('getComercializadorasGas', [ApiController::class, 'getComercializadorasGasList']);
Route::get('getOperadorasFibra', [ApiController::class, 'getOperadorasFibraList']);
/* movil */
Route::get('getTarifasMovil', [ApiController::class, 'getTarifasMovilList']);
Route::get('filterMovil', [ApiController::class, 'getValuesFilterMovilList']);
Route::get('getDetailOfferMovil/{id}', [ApiController::class, 'getDetailOfferMovilList']);
Route::get('getExtraOfferMovil', [ApiController::class, 'getExtraOfferMovilList']);
/* Luz */
Route::get('getTarifasLuz', [ApiController::class, 'getTarifasLuzList']);
Route::get('getExtraOfferLuz', [ApiController::class, 'getExtraOfferLuzList']);
Route::get('getDetailOfferLuz/{id}', [ApiController::class, 'getDetailOfferLuzList']);
/* Gas */
Route::get('getTarifasGas', [ApiController::class, 'getTarifasGasList']);
Route::get('getExtraOfferGas', [ApiController::class, 'getExtraOfferGasList']);
Route::get('getDetailOfferGas/{id}', [ApiController::class, 'getDetailOfferGasList']);
/* Fibra */
Route::get('getTarifasFibra', [ApiController::class, 'getTarifasFibraList']);
Route::get('filterFibra', [ApiController::class, 'getValuesFilterFibraList']);
Route::get('getExtraOfferFibra', [ApiController::class, 'getExtraOfferFibraList']);
Route::get('getDetailOfferFibra/{id}', [ApiController::class, 'getDetailOfferFibraList']);
/* Leads */
Route::post('newLeadMobile', [ApiController::class, 'newLeadMobile']);
