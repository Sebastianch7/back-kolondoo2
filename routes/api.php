<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ExtraOfferController;
use App\Http\Controllers\FilterController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\TarifasController;

Route::get('/', [ApiController::class, 'index']);

Route::get('getOperadoras', [ApiController::class, 'getOperadorasMovilList']);
Route::get('getComercializadorasLuz', [ApiController::class, 'getComercializadorasLuzList']);
Route::get('getComercializadorasGas', [ApiController::class, 'getComercializadorasGasList']);
Route::get('getOperadorasFibra', [ApiController::class, 'getOperadorasFibraList']);
Route::get('getComercializadorasLuzGas', [ApiController::class, 'getComercializadorasLuzGasList']);
Route::get('getOperadorasFibraMovil', [ApiController::class, 'getOperadorasFibraMovilList']);
Route::get('getOperadorasFibraMovilTv', [ApiController::class, 'getOperadorasFibraMovilTvList']);
/* movil */
Route::get('getTarifasMovil', [TarifasController::class, 'getTarifasMovilList']);
Route::get('filterMovil', [FilterController::class, 'getValuesFilterMovilList']);
Route::get('getExtraOffermovil', [ExtraOfferController::class, 'getTarifasMovilList']);
Route::get('getDetailOffermovil/{id}', [TarifasController::class, 'getDetailOfferMovilList']);
/* Luz */
Route::get('getTarifasLuz', [TarifasController::class, 'getTarifasLuzList']);
Route::get('getExtraOfferluz', [ExtraOfferController::class, 'getExtraOfferLuzList']);
Route::get('getDetailOfferluz/{id}', [TarifasController::class, 'getTarifasLuzList']);
/* Gas */
Route::get('getTarifasGas', [TarifasController::class, 'getTarifasGasList']);
Route::get('getExtraOffergas', [ExtraOfferController::class, 'getExtraOfferGasList']);
Route::get('getDetailOffergas/{id}', [TarifasController::class, 'getTarifasGasList']);
/* Fibra */
Route::get('getTarifasFibra', [TarifasController::class, 'getTarifasFibraList']);
Route::get('filterFibra', [FilterController::class, 'getValuesFilterFibraList']);
Route::get('getExtraOfferfibra', [ExtraOfferController::class, 'getExtraOfferFibraList']);
Route::get('getDetailOfferfibra/{id}', [TarifasController::class, 'getTarifasFibraList']);
/* Luz y Gas */
Route::get('getTarifasGasLuz', [TarifasController::class, 'getTarifasGasLuzList']);
Route::get('getExtraOfferluz_y_gas', [ExtraOfferController::class, 'getExtraOfferGasLuzList']);
Route::get('getDetailOfferluz_y_gas/{id}', [TarifasController::class, 'getTarifasGasLuzList']);
/* Fibra y Movil */
Route::get('getTarifasFibraMovil', [TarifasController::class, 'getTarifasFibraMovilList']);
Route::get('filterMovilFibra', [FilterController::class, 'getValuesFilterFibraMovilList']);
Route::get('getExtraOffermovil_y_fibra', [ExtraOfferController::class, 'getExtraOfferFibraMovilList']);
Route::get('getDetailOffermovil_y_fibra/{id}', [TarifasController::class, 'getTarifasFibraMovilList']);
/* Fibra, Movil y TV */
Route::get('getTarifasFibraMovilTv', [TarifasController::class, 'getTarifasFibraMovilTvList']);
Route::get('filterMovilFibraTv', [FilterController::class, 'getValuesFilterFibraMovilTvList']);
Route::get('getExtraOffermovil_fibra_tv', [ExtraOfferController::class, 'getExtraOfferFibraMovilTvList']);
Route::get('getDetailOffermovil_fibra_tv/{id}', [TarifasController::class, 'getTarifasFibraMovilTvList']);
/* Streaming */
Route::get('getTarifasStreaming', [TarifasController::class, 'getTarifasStreamingList']);
/* Leads */
Route::post('LeadRegister', [LeadController::class, 'LeadRegisterInfo']);


/* blog */
Route::get('getBlog', [BlogController::class, 'getBlogList']);
Route::get('getBlogHome', [BlogController::class, 'getBlogHomeList']);
Route::get('getBlog/{categoria}', [BlogController::class, 'getBlogList']);
Route::get('getBlogById/{id}', [BlogController::class, 'getBlogId']);
Route::get('getBlogDestacados', [BlogController::class, 'getBlogDescatados']);