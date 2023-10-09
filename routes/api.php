<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

Route::get('getTarifasMovil', [ApiController::class, 'getTarifasMovilList']);
Route::get('getOperadoras', [ApiController::class, 'getOperadorasList']);
Route::get('filterMovil', [ApiController::class, 'getValuesFilterMovilList']);
Route::get('getDetailOfferMovil/{id}', [ApiController::class, 'getDetailOfferMovilList']);
Route::get('getExtraOfferMovil', [ApiController::class, 'getExtraOfferMovilList']);
Route::get('getExtraOfferLuz', [ApiController::class, 'getExtraOfferLuzList']);
Route::get('getDetailOfferLuz/{id}', [ApiController::class, 'getDetailOfferLuzList']);
Route::post('newLeadMobile', [ApiController::class, 'newLeadMobile']);
//Tarifas Luz
Route::get('getComercializadoras', [ApiController::class, 'getComercializadorasList']);
Route::get('getTarifasLuz', [ApiController::class, 'getTarifasLuzList']);