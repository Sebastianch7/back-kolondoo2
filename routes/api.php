<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ExtraOfferController;
use App\Http\Controllers\FilterController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\HelperController;
use App\Http\Controllers\TarifasController;
use App\Http\Controllers\UtilsController;

//Route::get('/', [ApiController::class, 'index']);

Route::get('getOperadoras', [ApiController::class, 'getOperadorasMovilList']);
Route::get('getComercializadorasLuz', [ApiController::class, 'getComercializadorasLuzList']);
Route::get('getComercializadorasGas', [ApiController::class, 'getComercializadorasGasList']);
Route::get('getOperadorasFibra', [ApiController::class, 'getOperadorasFibraList']);
Route::get('getComercializadorasLuzGas', [ApiController::class, 'getComercializadorasLuzGasList']);
Route::get('getOperadorasFibraMovil', [ApiController::class, 'getOperadorasFibraMovilList']);
Route::get('getOperadorasFibraMovilTv', [ApiController::class, 'getOperadorasFibraMovilTvList']);
/* Luz */
Route::get('getTarifasLuz', [TarifasController::class, 'getTarifasLuzList']);
Route::get('getExtraOfferluz', [ExtraOfferController::class, 'getExtraOfferLuzList']);
Route::get('getDetailOffercomparadortarifasluz/{id}', [TarifasController::class, 'getDetailOfferLuzList']);
/* Gas */
Route::get('getTarifasGas', [TarifasController::class, 'getTarifasGasList']);
Route::get('getExtraOffergas', [ExtraOfferController::class, 'getExtraOfferGasList']);
Route::get('getDetailOffercomparadortarifasgas/{id}', [TarifasController::class, 'getDetailOfferGasList']);
/* Luz y Gas */
Route::get('getTarifasGasLuz', [TarifasController::class, 'getTarifasGasLuzList']);
Route::get('getExtraOfferluzygas', [ExtraOfferController::class, 'getExtraOfferGasLuzList']);
Route::get('getDetailOffercomparadortarifasluzygas/{id}', [TarifasController::class, 'getDetailOfferGasLuzList']);
/* movil */
Route::get('getTarifasMovil', [TarifasController::class, 'getTarifasMovilList']);
Route::get('filterMovil', [FilterController::class, 'getValuesFilterMovilList']);
Route::get('getExtraOffercomparadormovil', [ExtraOfferController::class, 'getExtraOfferMovilList']);
Route::get('getDetailOffercomparadormovil/{id}', [TarifasController::class, 'getDetailOfferMovilList']);
/* Fibra */
Route::get('getTarifasFibra', [TarifasController::class, 'getTarifasFibraList']);
Route::get('filterFibra', [FilterController::class, 'getValuesFilterFibraList']);
Route::get('getExtraOffercomparadorfibra', [ExtraOfferController::class, 'getExtraOfferFibraList']);
Route::get('getDetailOffercomparadorfibra/{id}', [TarifasController::class, 'getDetailOfferFibraList']);
/* Fibra y Movil */
Route::get('getTarifasFibraMovil', [TarifasController::class, 'getTarifasFibraMovilList']);
Route::get('filterMovilFibra', [FilterController::class, 'getValuesFilterFibraMovilList']);
Route::get('getExtraOffermovilyfibra', [ExtraOfferController::class, 'getExtraOfferFibraMovilList']);
Route::get('getDetailOffercomparadortarifasfibraymovil/{id}', [TarifasController::class, 'getDetailOfferFibraMovilList']);
/* Fibra, Movil y TV */
Route::get('getTarifasFibraMovilTv', [TarifasController::class, 'getTarifasFibraMovilTvList']);
Route::get('filterMovilFibraTv', [FilterController::class, 'getValuesFilterFibraMovilTvList']);
Route::get('getExtraOffercomparadormovilfibratv', [ExtraOfferController::class, 'getExtraOfferFibraMovilTvList']);
Route::get('getDetailOffercomparadorfibramoviltv/{id}', [TarifasController::class, 'getDetailOfferFibraMovilTvList']);
/* Streaming */
Route::get('getTarifasStreaming', [TarifasController::class, 'getTarifasStreamingList']);
/* blog */
Route::get('getBlog', [BlogController::class, 'getBlogList']);
Route::get('getBlogHome', [BlogController::class, 'getBlogHomeList']);
Route::get('getBlog/{categoria}/{id?}', [BlogController::class, 'getBlogList']);
Route::get('getMenuBlog', [BlogController::class, 'getMenuBlogList']);
/* Suministros */
Route::get('getSuministros', [BlogController::class, 'getSuministrosList']);
Route::get('getSuministrosById/{id}', [BlogController::class, 'getSuministrosList']);
/* Seguros */
Route::get('getSeguros', [BlogController::class, 'getSegurosList']);
Route::get('getSegurosById/{id}', [BlogController::class, 'getSegurosList']);
/* Cobertura movil */
Route::get('getCoberturaMovil', [BlogController::class, 'getCoberturaMovilList']);
Route::get('getCoberturaMovilById/{id}', [BlogController::class, 'getCoberturaMovilList']);
/* Cobertura fibra */
Route::get('getCoberturaFibra', [BlogController::class, 'getCoberturaFibraList']);
Route::get('getCoberturaFibraById/{id}', [BlogController::class, 'getCoberturaFibraList']);
/* optimizacion */
Route::get('getGestion/{funcion}/{id?}', [BlogController::class, 'getGestionList']);
/* Obtener data de localizacion por Ip */
Route::get('getDataLocation', [UtilsController::class, 'checkingGuestLocationApi']);
Route::get('getDataIp', [UtilsController::class, 'obtencionIpRealVisitante']);
/* Leads */
Route::post('LeadRegister', [LeadController::class, 'LeadRegisterInfo']);
Route::post('contactanosRegister', [LeadController::class, 'FormContactanosRegister']);
Route::post('NewsletterRegister', [LeadController::class, 'FormNewsletterRegister']);