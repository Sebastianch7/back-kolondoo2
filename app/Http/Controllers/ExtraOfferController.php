<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExtraOfferController extends Controller
{
    protected $tabla_luz = 'WEB_3_TARIFAS_ENERGIA_LUZ';
    protected $tabla_gas = 'WEB_3_TARIFAS_ENERGIA_GAS';
    protected $tabla_luz_gas = 'WEB_3_TARIFAS_ENERGIA_LUZ_GAS';
    protected $tabla_movil = 'WEB_3_TARIFAS_TELCO_MOVIL';
    protected $tabla_fibra = 'WEB_3_TARIFAS_TELCO_FIBRA';
    protected $tabla_tv = 'WEB_3_TARIFAS_TELCO_TV';
    protected $tabla_movil_fibra = 'WEB_3_TARIFAS_TELCO_FIBRA_MOVIL';
    protected $tabla_movil_fibra_tv = 'WEB_3_TARIFAS_TELCO_FIBRA_MOVIL_TV';

    public function getExtraOfferLuzList()
    {
        return DB::table($this->tabla_luz)
            ->join('1_comercializadoras', '1_comercializadoras.id', '=', $this->tabla_luz . '.comercializadora')
            ->select($this->tabla_luz . '.*', '1_comercializadoras.nombre', '1_comercializadoras.logo')
            ->where($this->tabla_luz.'.tarifa_activa','=','1')
            ->where('1_comercializadoras.comercializadora_activa','=','1')
            ->inRandomOrder()
            ->take(3)
            ->get();
    }

    public function getExtraOfferMovilList()
    {
        return DB::table($this->tabla_movil)
            ->join('1_operadoras', '1_operadoras.id', '=', $this->tabla_movil . '.operadora')
            ->select($this->tabla_movil . '.*', '1_operadoras.nombre', '1_operadoras.logo')
            ->where($this->tabla_movil.'.tarifa_activa','=','1')
            ->where('1_operadoras.operadora_activa','=','1')
            ->inRandomOrder()
            ->take(3)
            ->get();
    }

    public function getExtraOfferFibraList()
    {
        return DB::table($this->tabla_fibra)
            ->join('1_operadoras', '1_operadoras.id', '=', $this->tabla_fibra . '.operadora')
            ->select($this->tabla_fibra . '.*', '1_operadoras.nombre', '1_operadoras.logo')
            ->where($this->tabla_fibra.'.tarifa_activa','=','1')
            ->where('1_operadoras.operadora_activa','=','1')
            ->inRandomOrder()
            ->take(3)
            ->get();
    }

    public function getExtraOfferGasList()
    {
        return DB::table($this->tabla_gas)
            ->join('1_comercializadoras', '1_comercializadoras.id', '=', $this->tabla_gas . '.comercializadora')
            ->select($this->tabla_gas . '.*', '1_comercializadoras.nombre', '1_comercializadoras.logo')
            ->where($this->tabla_movil.'.tarifa_activa','=','1')
            ->where('1_comercializadoras.comercializadora_activa','=','1')
            ->inRandomOrder()
            ->take(3)
            ->get();
    }

    public function getExtraOfferGasLuzList()
    {
        return DB::table($this->tabla_luz_gas)
            ->join('1_comercializadoras', '1_comercializadoras.id', '=', $this->tabla_luz_gas . '.comercializadora')
            ->select($this->tabla_luz_gas . '.*', '1_comercializadoras.nombre', '1_comercializadoras.logo')
            ->where($this->tabla_luz_gas.'.tarifa_activa','=','1')
            ->where('1_comercializadoras.comercializadora_activa','=','1')
            ->inRandomOrder()
            ->take(3)
            ->get();
    }

    public function getExtraOfferFibraMovilList()
    {
        return DB::table($this->tabla_movil_fibra)
            ->join('1_operadoras', '1_operadoras.id', '=', $this->tabla_movil_fibra . '.operadora')
            ->select($this->tabla_movil_fibra . '.*', '1_operadoras.nombre', '1_operadoras.logo')
            ->where($this->tabla_movil_fibra.'.tarifa_activa','=','1')
            ->where('1_operadoras.operadora_activa','=','1')
            ->inRandomOrder()
            ->take(3)
            ->get();
    }

    public function getExtraOfferFibraMovilTvList()
    {
        return DB::table($this->tabla_movil_fibra_tv)
            ->join('1_operadoras', '1_operadoras.id', '=', $this->tabla_movil_fibra_tv . '.operadora')
            ->select($this->tabla_movil_fibra_tv . '.*', '1_operadoras.nombre', '1_operadoras.logo')
            ->where($this->tabla_movil_fibra_tv.'.tarifa_activa','=','1')
            ->where('1_operadoras.operadora_activa','=','1')
            ->inRandomOrder()
            ->take(3)
            ->get();
    }
}
