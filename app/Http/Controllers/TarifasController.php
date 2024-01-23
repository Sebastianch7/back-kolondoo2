<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TarifasController extends Controller
{
    protected $tabla_luz = 'WEB_3_TARIFAS_ENERGIA_LUZ';
    protected $tabla_gas = 'WEB_3_TARIFAS_ENERGIA_GAS';
    protected $tabla_luz_gas = 'WEB_3_TARIFAS_ENERGIA_LUZ_GAS';
    protected $tabla_movil = 'WEB_3_TARIFAS_TELCO_MOVIL';
    protected $tabla_fibra = 'WEB_3_TARIFAS_TELCO_FIBRA';
    protected $tabla_tv = 'WEB_3_TARIFAS_TELCO_TV';
    protected $tabla_movil_fibra = 'WEB_3_TARIFAS_TELCO_FIBRA_MOVIL';
    protected $tabla_movil_fibra_tv = 'WEB_3_TARIFAS_TELCO_FIBRA_MOVIL_TV';
    protected $tabla_streaming = 'WEB_3_TARIFAS_TELCO_STREAMING'; 

    public function getTarifasMovilList()
    {
        return DB::table($this->tabla_movil)
            ->join('1_operadoras', '1_operadoras.id', '=', $this->tabla_movil . '.operadora')
            ->select($this->tabla_movil . '.*', '1_operadoras.nombre', '1_operadoras.logo')
            ->where($this->tabla_movil.'.tarifa_activa','=','1')
            ->where('1_operadoras.operadora_activa','=','1')
            ->orderBy('precio', 'asc')
            ->get();
    }

    public function getTarifasFibraList($id = null)
    {
        return DB::table($this->tabla_fibra)
            ->join('1_operadoras', '1_operadoras.id', '=', $this->tabla_fibra . '.operadora')
            ->select($this->tabla_fibra . '.*', '1_operadoras.nombre', '1_operadoras.logo')
            ->where($this->tabla_fibra.'.tarifa_activa','=','1')
            ->where('1_operadoras.operadora_activa','=','1')
            ->orderBy('precio', 'asc')
            ->get();
    }

    public function getTarifasLuzList()
    {
        return DB::table($this->tabla_luz)
            ->join('1_comercializadoras', '1_comercializadoras.id', '=', $this->tabla_luz . '.comercializadora')
            ->select($this->tabla_luz . '.*', '1_comercializadoras.nombre', '1_comercializadoras.logo')
            ->where($this->tabla_luz.'.tarifa_activa','=','1')
            ->where('1_comercializadoras.comercializadora_activa','=','1')
            ->orderBy('precio', 'asc')
            ->get();
    }

    public function getTarifasGasList()
    {
        return DB::table($this->tabla_gas)
            ->join('1_comercializadoras', '1_comercializadoras.id', '=', $this->tabla_gas . '.comercializadora')
            ->select($this->tabla_gas . '.*', '1_comercializadoras.nombre', '1_comercializadoras.logo')
            ->where($this->tabla_gas.'.tarifa_activa','=','1')
            ->where('1_comercializadoras.comercializadora_activa','=','1')
            ->orderBy('precio', 'asc')
            ->get();
    }

    public function getTarifasGasLuzList()
    {
        return DB::table($this->tabla_luz_gas)
            ->join('1_comercializadoras', '1_comercializadoras.id', '=', $this->tabla_luz_gas . '.comercializadora')
            ->select($this->tabla_luz_gas . '.*', '1_comercializadoras.nombre', '1_comercializadoras.logo')
            ->where($this->tabla_luz_gas.'.tarifa_activa','=','1')
            ->where('1_comercializadoras.comercializadora_activa','=','1')
            ->orderBy('precio', 'asc')
            ->get();
    }

    public function getTarifasFibraMovilList()
    {
        return DB::table($this->tabla_movil_fibra)
            ->join('1_operadoras', '1_operadoras.id', '=', $this->tabla_movil_fibra . '.operadora')
            ->select($this->tabla_movil_fibra . '.*', '1_operadoras.nombre', '1_operadoras.logo')
            ->where($this->tabla_movil_fibra.'.tarifa_activa','=','1')
            ->where('1_operadoras.operadora_activa','=','1')
            ->orderBy('precio', 'asc')
            ->get();
    }

    public function getTarifasFibraMovilTvList($id = null)
    {
        $query = DB::table($this->tabla_movil_fibra_tv)
            ->join('1_operadoras', '1_operadoras.id', '=', $this->tabla_movil_fibra_tv . '.operadora')
            ->select($this->tabla_movil_fibra_tv . '.*', '1_operadoras.nombre', '1_operadoras.logo')
            ->where($this->tabla_movil_fibra_tv.'.tarifa_activa','=','1')
            ->where('1_operadoras.operadora_activa','=','1')
            ->orderBy('precio', 'asc');

        if (!empty($id)) {
            $query->where($this->tabla_movil_fibra_tv . '.id', '=', $id);
        }

        return $query->get();
    }

    public function getTarifasStreamingList($id = null)
    {
        $query = DB::table($this->tabla_movil_fibra_tv)
            ->join('1_operadoras', '1_operadoras.id', '=', $this->tabla_movil_fibra_tv . '.operadora')
            ->select($this->tabla_movil_fibra_tv . '.*', '1_operadoras.nombre', '1_operadoras.logo')
            ->orderBy('precio', 'asc');

        if (!empty($id)) {
            $query->where($this->tabla_movil_fibra_tv . '.id', '=', $id);
        }

        return $query->get();
    }

    public function getDetailOfferMovilList($id)
    {
        return DB::table($this->tabla_movil)
            ->join('1_operadoras', '1_operadoras.id', '=', $this->tabla_movil . '.operadora')
            ->select($this->tabla_movil . '.*', '1_operadoras.nombre', '1_operadoras.logo')
            ->where($this->tabla_movil . '.id', '=', $id)
            ->get();
    }

    public function getDetailOfferLuzList($id)
    {
        return DB::table($this->tabla_luz)
            ->join('1_comercializadoras', '1_comercializadoras.id', '=', $this->tabla_luz . '.comercializadora')
            ->select($this->tabla_luz . '.*', '1_comercializadoras.nombre', '1_comercializadoras.logo',$this->tabla_luz.'.comercializadora as operadora')
            ->where($this->tabla_luz . '.id', '=', $id)
            ->get();
    }

    public function getDetailOfferGasList($id)
    {
        return DB::table($this->tabla_gas)
            ->join('1_comercializadoras', '1_comercializadoras.id', '=', $this->tabla_gas . '.comercializadora')
            ->select($this->tabla_gas . '.*', '1_comercializadoras.nombre', '1_comercializadoras.logo',$this->tabla_gas.'.comercializadora as operadora')
            ->where($this->tabla_gas . '.id', '=', $id)
            ->get();
    }

    public function getDetailOfferGasLuzList($id)
    {
        return DB::table($this->tabla_luz_gas)
            ->join('1_comercializadoras', '1_comercializadoras.id', '=', $this->tabla_luz_gas . '.comercializadora')
            ->select($this->tabla_luz_gas . '.*', '1_comercializadoras.nombre', '1_comercializadoras.logo',$this->tabla_luz_gas.'.comercializadora as operadora')
            ->where($this->tabla_luz_gas . '.id', '=', $id)
            ->get();
    }

    public function getDetailOfferFibraList($id)
    {
        return DB::table($this->tabla_fibra)
            ->join('1_operadoras', '1_operadoras.id', '=', $this->tabla_fibra . '.operadora')
            ->select($this->tabla_fibra . '.*', '1_operadoras.nombre', '1_operadoras.logo','1_operadoras.politica_privacidad')
            ->where($this->tabla_fibra . '.id', '=', $id)
            ->get();
    }

    public function getDetailOfferFibraMovilList($id)
    {
        return DB::table($this->tabla_movil_fibra)
            ->join('1_operadoras', '1_operadoras.id', '=', $this->tabla_movil_fibra . '.operadora')
            ->select($this->tabla_movil_fibra . '.*', '1_operadoras.nombre', '1_operadoras.logo')
            ->where($this->tabla_movil_fibra . '.id', '=', $id)
            ->get();
    }

    public function getDetailOfferFibraMovilTvList($id)
    {
        return DB::table($this->tabla_movil_fibra_tv)
            ->join('1_operadoras', '1_operadoras.id', '=', $this->tabla_movil_fibra_tv . '.operadora')
            ->select($this->tabla_movil_fibra_tv . '.*', '1_operadoras.nombre', '1_operadoras.logo')
            ->where($this->tabla_movil_fibra_tv . '.id', '=', $id)
            ->get();
    }

}
