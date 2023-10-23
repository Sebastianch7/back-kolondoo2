<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

Route::get('/', [ApiController::class, 'index']);

Route::get('getOperadoras', [ApiController::class, 'getOperadorasMovilList']);
Route::get('getComercializadoras', [ApiController::class, 'getComercializadorasLuzList']);
Route::get('getComercializadorasGas', [ApiController::class, 'getComercializadorasGasList']);
Route::get('getOperadorasFibra', [ApiController::class, 'getOperadorasFibraList']);
Route::get('getComercializadorasLuzGas', [ApiController::class, 'getComercializadorasLuzGasList']);
Route::get('getOperadorasFibraMovil', [ApiController::class, 'getOperadorasFibraMovilList']);
Route::get('getOperadorasFibraMovilTv', [ApiController::class, 'getOperadorasFibraMovilTvList']);
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
/* Luz y Gas */
Route::get('getTarifasGasLuz', [ApiController::class, 'getTarifasGasLuzList']);
Route::get('getExtraOfferGasLuz', [ApiController::class, 'getExtraOfferGasLuzList']);
Route::get('getDetailOfferGasLuz/{id}', [ApiController::class, 'getDetailOfferGasLuzList']);
/* Fibra y Movil */
Route::get('getTarifasFibraMovil', [ApiController::class, 'getTarifasFibraMovilList']);
Route::get('getExtraOfferFibraMovil', [ApiController::class, 'getExtraOfferFibraMovilList']);
Route::get('getDetailOfferFibraMovil/{id}', [ApiController::class, 'getDetailOfferFibraMovilList']);
/* Fibra, Movil y TV */
Route::get('getTarifasFibraMovilTv', [ApiController::class, 'getTarifasFibraMovilTvList']);
Route::get('getExtraOfferFibraMovilTv', [ApiController::class, 'getExtraOfferFibraMovilTvList']);
Route::get('getDetailOfferFibraMovilTv/{id}', [ApiController::class, 'getDetailOfferFibraMovilTvList']);
/* Leads */
Route::post('newLeadMobile', [ApiController::class, 'newLeadMobile']);
