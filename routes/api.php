<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

Route::get('/', [ApiController::class, 'index']);

Route::get('getOperadoras', [ApiController::class, 'getOperadorasMovilList']);
Route::get('getComercializadorasLuz', [ApiController::class, 'getComercializadorasLuzList']);
Route::get('getComercializadorasGas', [ApiController::class, 'getComercializadorasGasList']);
Route::get('getOperadorasFibra', [ApiController::class, 'getOperadorasFibraList']);
Route::get('getComercializadorasLuzGas', [ApiController::class, 'getComercializadorasLuzGasList']);
Route::get('getOperadorasFibraMovil', [ApiController::class, 'getOperadorasFibraMovilList']);
Route::get('getOperadorasFibraMovilTv', [ApiController::class, 'getOperadorasFibraMovilTvList']);
/* movil */
Route::get('getTarifasMovil', [ApiController::class, 'getTarifasMovilList']);
Route::get('filterMovil', [ApiController::class, 'getValuesFilterMovilList']);
Route::get('getDetailOffermovil/{id}', [ApiController::class, 'getDetailOfferMovilList']);
Route::get('getExtraOffermovil', [ApiController::class, 'getExtraOfferMovilList']);
/* Luz */
Route::get('getTarifasLuz', [ApiController::class, 'getTarifasLuzList']);
Route::get('getExtraOfferluz', [ApiController::class, 'getExtraOfferLuzList']);
Route::get('getDetailOfferluz/{id}', [ApiController::class, 'getDetailOfferLuzList']);
/* Gas */
Route::get('getTarifasGas', [ApiController::class, 'getTarifasGasList']);
Route::get('getExtraOffergas', [ApiController::class, 'getExtraOfferGasList']);
Route::get('getDetailOffergas/{id}', [ApiController::class, 'getDetailOfferGasList']);
/* Fibra */
Route::get('getTarifasFibra', [ApiController::class, 'getTarifasFibraList']);
Route::get('filterFibra', [ApiController::class, 'getValuesFilterFibraList']);
Route::get('getExtraOfferfibra', [ApiController::class, 'getExtraOfferFibraList']);
Route::get('getDetailOfferfibra/{id}', [ApiController::class, 'getDetailOfferFibraList']);
/* Luz y Gas */
Route::get('getTarifasGasLuz', [ApiController::class, 'getTarifasGasLuzList']);
Route::get('getExtraOfferluz_y_gas', [ApiController::class, 'getExtraOfferGasLuzList']);
Route::get('getDetailOfferluz_y_gas/{id}', [ApiController::class, 'getDetailOfferGasLuzList']);
/* Fibra y Movil */
Route::get('getTarifasFibraMovil', [ApiController::class, 'getTarifasFibraMovilList']);
Route::get('filterMovilFibra', [ApiController::class, 'getValuesFilterFibraMovilList']);
Route::get('getExtraOffermovil_y_fibra', [ApiController::class, 'getExtraOfferFibraMovilList']);
Route::get('getDetailOffermovil_y_fibra/{id}', [ApiController::class, 'getDetailOfferFibraMovilList']);
/* Fibra, Movil y TV */
Route::get('getTarifasfibramovilTv', [ApiController::class, 'getTarifasFibraMovilTvList']);
Route::get('getExtraOfferfibramoviltv', [ApiController::class, 'getExtraOfferFibraMovilTvList']);
Route::get('getDetailOfferfibramoviltv/{id}', [ApiController::class, 'getDetailOfferFibraMovilTvList']);
/* Leads */
Route::post('newLeadMobile', [ApiController::class, 'newLeadMobile']);
