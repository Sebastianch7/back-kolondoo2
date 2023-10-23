<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Lead;

class ApiController extends Controller
{
    protected $tabla_luz;
    protected $tabla_gas;
    protected $tabla_luz_gas;
    protected $tabla_movil;
    protected $tabla_fibra;
    protected $tabla_tv;
    protected $tabla_movil_fibra;
    protected $tabla_movil_fibra_tv;

    public function __construct()
    {
        $this->tabla_luz = 'WEB_3_TARIFAS_ENERGIA_LUZ';
        $this->tabla_gas = 'WEB_3_TARIFAS_ENERGIA_GAS';
        $this->tabla_luz_gas = 'WEB_3_TARIFAS_ENERGIA_LUZ_GAS';

        $this->tabla_movil = 'WEB_3_TARIFAS_TELCO_MOVIL';
        $this->tabla_fibra = 'WEB_3_TARIFAS_TELCO_FIBRA';
        $this->tabla_tv = 'WEB_3_TARIFAS_TELCO_TV';
        $this->tabla_movil_fibra = 'WEB_3_TARIFAS_TELCO_FIBRA_MOVIL';
        $this->tabla_movil_fibra_tv = 'WEB_3_TARIFAS_TELCO_FIBRA_MOVIL_TV';
    }

    public function index()
    {
        return view('swagger');
    }

    /* consultas de las empresas que tienen servicios activos */
    public function getTarifasMovilList()
    {
        return DB::table($this->tabla_movil)
            ->join('1_operadoras', '1_operadoras.id', '=', $this->tabla_movil . '.operadora')
            ->select($this->tabla_movil . '.*', '1_operadoras.nombre', '1_operadoras.logo')
            ->get();
    }

    public function getTarifasFibraList()
    {
        return DB::table($this->tabla_fibra)
            ->join('1_operadoras', '1_operadoras.id', '=', $this->tabla_fibra . '.operadora')
            ->select($this->tabla_fibra . '.*', '1_operadoras.nombre', '1_operadoras.logo')
            ->get();
    }

    public function getTarifasLuzList()
    {
        return DB::table($this->tabla_luz)
            ->join('1_comercializadoras', '1_comercializadoras.id', '=', $this->tabla_luz . '.comercializadora')
            ->select($this->tabla_luz . '.*', '1_comercializadoras.nombre', '1_comercializadoras.logo')
            ->get();
    }

    public function getTarifasGasList()
    {
        return DB::table($this->tabla_gas)
            ->join('1_comercializadoras', '1_comercializadoras.id', '=', $this->tabla_gas . '.comercializadora')
            ->select($this->tabla_gas . '.*', '1_comercializadoras.nombre', '1_comercializadoras.logo')
            ->get();
    }

    public function getTarifasGasLuzList()
    {
        return DB::table($this->tabla_luz_gas)
            ->join('1_comercializadoras', '1_comercializadoras.id', '=', $this->tabla_luz_gas . '.comercializadora')
            ->select($this->tabla_luz_gas . '.*', '1_comercializadoras.nombre', '1_comercializadoras.logo')
            ->get();
    }

    public function getTarifasFibraMovilList()
    {
        return DB::table($this->tabla_movil_fibra)
            ->join('1_operadoras', '1_operadoras.id', '=', $this->tabla_movil_fibra . '.operadora')
            ->select($this->tabla_movil_fibra . '.*', '1_operadoras.nombre', '1_operadoras.logo')
            ->get();
    }

    public function getTarifasFibraMovilTvList()
    {
        return DB::table($this->tabla_movil_fibra_tv)
            ->join('1_operadoras', '1_operadoras.id', '=', $this->tabla_movil_fibra_tv . '.operadora')
            ->select($this->tabla_movil_fibra_tv . '.*', '1_operadoras.nombre', '1_operadoras.logo')
            ->get();
    }
    /* fin consultas de las empresas que tienen servicios activos */

    /* funciones para llamar las bases para los filtros de precio */
    public function getValuesFilterMovilList()
    {
        return DB::table($this->tabla_movil)
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

    /* consultas por id de cada servicio */
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
            ->select($this->tabla_luz . '.*', '1_comercializadoras.nombre', '1_comercializadoras.logo')
            ->where($this->tabla_luz . '.id', '=', $id)
            ->get();
    }

    public function getDetailOfferGasList($id)
    {
        return DB::table($this->tabla_gas)
            ->join('1_comercializadoras', '1_comercializadoras.id', '=', $this->tabla_gas . '.comercializadora')
            ->select($this->tabla_gas . '.*', '1_comercializadoras.nombre', '1_comercializadoras.logo')
            ->where($this->tabla_gas . '.id', '=', $id)
            ->get();
    }

    public function getDetailOfferGasLuzList($id)
    {
        return DB::table($this->tabla_luz_gas)
            ->join('1_comercializadoras', '1_comercializadoras.id', '=', $this->tabla_luz_gas . '.comercializadora')
            ->select($this->tabla_luz_gas . '.*', '1_comercializadoras.nombre', '1_comercializadoras.logo')
            ->where($this->tabla_luz_gas . '.id', '=', $id)
            ->get();
    }

    public function getDetailOfferFibraList($id)
    {
        return DB::table($this->tabla_fibra)
            ->join('1_comercializadoras', '1_comercializadoras.id', '=', $this->tabla_fibra . '.comercializadora')
            ->select($this->tabla_fibra . '.*', '1_comercializadoras.nombre', '1_comercializadoras.logo')
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
    /* fin funciones consultas por id  */

    /* funciones para llamar los 3 items adicionales para la página de agradecimiento */
    public function getExtraOfferLuzList()
    {
        return DB::table($this->tabla_luz)
            ->join('1_comercializadoras', '1_comercializadoras.id', '=', $this->tabla_luz . '.comercializadora')
            ->select($this->tabla_luz . '.*', '1_comercializadoras.nombre', '1_comercializadoras.logo')
            ->inRandomOrder()
            ->take(3)
            ->get();
    }

    public function getExtraOfferMovilList()
    {
        return DB::table($this->tabla_movil)
            ->join('1_operadoras', '1_operadoras.id', '=', $this->tabla_movil . '.operadora')
            ->select($this->tabla_movil . '.*', '1_operadoras.nombre', '1_operadoras.logo')
            ->inRandomOrder()
            ->take(3)
            ->get();
    }

    public function getExtraOfferGasList()
    {
        return DB::table($this->tabla_gas)
            ->join('1_comercializadoras', '1_comercializadoras.id', '=', $this->tabla_gas . '.comercializadora')
            ->select($this->tabla_gas . '.*', '1_comercializadoras.nombre', '1_comercializadoras.logo')
            ->inRandomOrder()
            ->take(3)
            ->get();
    }

    public function getExtraOfferGasLuzList()
    {
        return DB::table($this->tabla_luz_gas)
            ->join('1_comercializadoras', '1_comercializadoras.id', '=', $this->tabla_luz_gas . '.comercializadora')
            ->select($this->tabla_luz_gas . '.*', '1_comercializadoras.nombre', '1_comercializadoras.logo')
            ->inRandomOrder()
            ->take(3)
            ->get();
    }

    public function getExtraOfferFibraMovilList()
    {
        return DB::table($this->tabla_movil_fibra)
            ->join('1_operadoras', '1_operadoras.id', '=', $this->tabla_movil_fibra . '.operadora')
            ->select($this->tabla_movil_fibra . '.*', '1_operadoras.nombre', '1_operadoras.logo')
            ->inRandomOrder()
            ->take(3)
            ->get();
    }

    public function getExtraOfferFibraMovilTvList()
    {
        return DB::table($this->tabla_movil_fibra_tv)
            ->join('1_operadoras', '1_operadoras.id', '=', $this->tabla_movil_fibra_tv . '.operadora')
            ->select($this->tabla_movil_fibra_tv . '.*', '1_operadoras.nombre', '1_operadoras.logo')
            ->inRandomOrder()
            ->take(3)
            ->get();
    }
    /* fin funciones para llamar 3 items adicionales */

    /* funciones para consultar las ofertas comerciales */
    public function getComercializadorasLuzList()
    {
        return DB::table($this->tabla_luz)
            ->join('1_comercializadoras', '1_comercializadoras.id', '=', $this->tabla_luz . '.comercializadora')
            ->select('1_comercializadoras.id', '1_comercializadoras.nombre', '1_comercializadoras.logo')
            ->groupBy($this->tabla_luz . '.comercializadora')
            ->get();
    }

    public function getComercializadorasGasList()
    {
        return DB::table($this->tabla_gas)
            ->join('1_comercializadoras', '1_comercializadoras.id', '=', $this->tabla_gas . '.comercializadora')
            ->select('1_comercializadoras.id', '1_comercializadoras.nombre', '1_comercializadoras.logo')
            ->groupBy($this->tabla_gas . '.comercializadora')
            ->get();
    }

    public function getOperadorasMovilList()
    {
        return DB::table($this->tabla_movil)
            ->join('1_operadoras', '1_operadoras.id', '=', $this->tabla_movil . '.operadora')
            ->select('1_operadoras.id', '1_operadoras.nombre', '1_operadoras.logo')
            ->groupBy('operadora')
            ->get();
    }

    public function getOperadorasFibraList()
    {
        return DB::table($this->tabla_fibra)
            ->join('1_operadoras', '1_operadoras.id', '=', $this->tabla_fibra . '.operadora')
            ->select('1_operadoras.id', '1_operadoras.nombre', '1_operadoras.logo')
            ->groupBy('operadora')
            ->get();
    }

    public function getComercializadorasLuzGasList()
    {
        return DB::table($this->tabla_luz_gas)
            ->join('1_comercializadoras', '1_comercializadoras.id', '=', $this->tabla_luz_gas . '.comercializadora')
            ->select('1_comercializadoras.id', '1_comercializadoras.nombre', '1_comercializadoras.logo')
            ->groupBy('comercializadora')
            ->get();
    }

    public function getOperadorasFibraMovilList()
    {
        return DB::table($this->tabla_movil_fibra)
            ->join('1_operadoras', '1_operadoras.id', '=', $this->tabla_movil_fibra . '.operadora')
            ->select('1_operadoras.id', '1_operadoras.nombre', '1_operadoras.logo')
            ->groupBy('operadora')
            ->get();
    }

    public function getOperadorasFibraMovilTvList()
    {
        return DB::table($this->tabla_movil_fibra_tv)
            ->join('1_operadoras', '1_operadoras.id', '=', $this->tabla_movil_fibra_tv . '.operadora')
            ->select('1_operadoras.id', '1_operadoras.nombre', '1_operadoras.logo')
            ->groupBy('operadora')
            ->get();
    }

    public function newLeadMobile(Request $request)
    {
        // Validar los datos del formulario si es necesario
        /* $request->validate([
            'idOferta' => 'required',
            'phone' => 'required',
            'landing' => 'required',
        ]);
     */
        // Crear una nueva instancia del modelo Lead con los datos del formulario
        $lead = new Lead([
            'idOferta' => $request->input('idPlan'),
            'phone' => $request->input('phoneNumber'),
            'landing' => $request->input('landing'),
        ]);

        // Guardar el nuevo registro en la base de datos
        $lead->save();

        // Puedes devolver una respuesta de éxito o redireccionar a otra página
        return response()->json(['message' => 'Registro de Lead exitoso'], 201);
    }
}
