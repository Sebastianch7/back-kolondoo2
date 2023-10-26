<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FilterController extends Controller
{
    protected $tabla_luz = 'WEB_3_TARIFAS_ENERGIA_LUZ';
    protected $tabla_gas = 'WEB_3_TARIFAS_ENERGIA_GAS';
    protected $tabla_luz_gas = 'WEB_3_TARIFAS_ENERGIA_LUZ_GAS';
    protected $tabla_movil = 'WEB_3_TARIFAS_TELCO_MOVIL';
    protected $tabla_fibra = 'WEB_3_TARIFAS_TELCO_FIBRA';
    protected $tabla_tv = 'WEB_3_TARIFAS_TELCO_TV';
    protected $tabla_movil_fibra = 'WEB_3_TARIFAS_TELCO_FIBRA_MOVIL';
    protected $tabla_movil_fibra_tv = 'WEB_3_TARIFAS_TELCO_FIBRA_MOVIL_TV';

    /* funciones para llamar las bases para los filtros de precio */
    public function getValuesFilterMovilList()
    {
        return DB::table($this->tabla_movil)
            ->selectRaw('ROUND(MAX(GB)+5) as max_gb, ROUND(MAX(precio)+5) as max_precio, ROUND(MIN(GB)-5) as min_gb, ROUND(MIN(precio)-5) as min_precio')
            ->get();
    }

    public function getValuesFilterFibraMovilList()
    {
        return DB::table($this->tabla_movil_fibra)
            ->selectRaw('ROUND(MAX(GB)+5) as max_gb, ROUND(MAX(precio)+5) as max_precio, ROUND(MIN(GB)-5) as min_gb, ROUND(MIN(precio)-5) as min_precio')
            ->get();
    }

    public function getValuesFilterFibraMovilTvList()
    {
        return DB::table($this->tabla_movil_fibra_tv)
            ->selectRaw('ROUND(MAX(GB)+5) as max_gb, ROUND(MAX(precio)+5) as max_precio, ROUND(MIN(GB)-5) as min_gb, ROUND(MIN(precio)-5) as min_precio')
            ->get();
    }

    public function getValuesFilterFibraList()
    {
        return DB::table($this->tabla_fibra)
            ->selectRaw('ROUND(MAX(precio)+5) as max_precio, ROUND(MIN(precio)-5) as min_precio')
            ->get();
    }
    /* fin funciones para filtros */
}
