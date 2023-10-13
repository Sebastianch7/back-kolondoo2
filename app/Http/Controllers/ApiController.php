<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Comercializadoras;
use App\Models\Operadoras;
use App\Models\Lead;

class ApiController extends Controller
{
    /* consultas de las empresas que tienen servicios activos */
    public function getTarifasMovilList()
    {
        return DB::table('tarifasMovil')
            ->join('operadoras', 'operadoras.id', '=', 'tarifasMovil.operadora')
            ->select('tarifasMovil.*', 'operadoras.nombre', 'operadoras.logo')
            ->get();
    }
    
    public function getTarifasFibraList()
    {
        return DB::table('tarifasFibra')
            ->join('operadoras', 'operadoras.id', '=', 'tarifasFibra.operadora')
            ->select('tarifasFibra.*', 'operadoras.nombre', 'operadoras.logo')
            ->get();
    }

    public function getTarifasLuzList()
    {
        return DB::table('tarifasLuz')
            ->join('comercializadoras', 'comercializadoras.id', '=', 'tarifasLuz.comercializadora')
            ->select('tarifasLuz.*', 'comercializadoras.nombre', 'comercializadoras.logo')
            ->get();
    }

    public function getTarifasGasList()
    {
        return DB::table('tarifasGas')
            ->join('comercializadoras', 'comercializadoras.id', '=', 'tarifasGas.comercializadora')
            ->select('tarifasGas.*', 'comercializadoras.nombre', 'comercializadoras.logo')
            ->get();
    }
    /* fin consultas de las empresas que tienen servicios activos */

    /* funciones para llamar las bases para los filtros de precio */
    public function getValuesFilterMovilList()
    {
        return DB::table('tarifasMovil')
            ->selectRaw('ROUND(MAX(GB)+5) as max_gb, ROUND(MAX(precio)+5) as max_precio, ROUND(MIN(GB)-5) as min_gb, ROUND(MIN(precio)-5) as min_precio')
            ->get();
    }

    public function getValuesFilterFibraList()
    {
        return DB::table('tarifasFibra')
            ->selectRaw('ROUND(MAX(precio)+5) as max_precio, ROUND(MIN(precio)-5) as min_precio')
            ->get();
    }
    /* fin funciones para filtros */

    /* consultas por id de cada servicio */
    public function getDetailOfferMovilList($id)
    {
        return DB::table('tarifasMovil')
            ->join('operadoras', 'operadoras.id', '=', 'tarifasMovil.operadora')
            ->select('tarifasMovil.*', 'operadoras.nombre', 'operadoras.logo')
            ->where('tarifasMovil.id', '=', $id)
            ->get();
    }

    public function getDetailOfferLuzList($id)
    {
        return DB::table('tarifasLuz')
            ->join('comercializadoras', 'comercializadoras.id', '=', 'tarifasLuz.comercializadora')
            ->select('tarifasLuz.*', 'comercializadoras.nombre', 'comercializadoras.logo')
            ->where('tarifasLuz.id', '=', $id)
            ->get();
    }
    /* fin funciones cosultas por id  */

    /* funciones para llamar los 3 items adicionales para a page de thank */
    public function getExtraOfferLuzList()
    {
        return DB::table('tarifasLuz')
            ->join('comercializadoras', 'comercializadoras.id', '=', 'tarifasLuz.comercializadora')
            ->select('tarifasLuz.*', 'comercializadoras.nombre', 'comercializadoras.logo')
            ->inRandomOrder()
            ->take(3)
            ->get();
    }

    public function getExtraOfferMovilList()
    {
        return DB::table('tarifasMovil')
            ->join('operadoras', 'operadoras.id', '=', 'tarifasMovil.operadora')
            ->select('tarifasMovil.*', 'operadoras.nombre', 'operadoras.logo')
            ->inRandomOrder()
            ->take(3)
            ->get();
    }
    /* fin funciones para llamar 3 items adiconales */

    /* funciones para consultar las ofertas comerciales */
    public function getComercializadorasList()
    {
        return DB::table('tarifasLuz')
            ->join('comercializadoras', 'comercializadoras.id', '=', 'tarifasLuz.comercializadora')
            ->select('comercializadoras.id', 'comercializadoras.nombre', 'comercializadoras.logo')
            ->groupBy('comercializadora')
            ->get();
    }

    public function getComercializadorasGasList()
    {
        return DB::table('tarifasGas')
            ->join('comercializadoras', 'comercializadoras.id', '=', 'tarifasGas.comercializadora')
            ->select('comercializadoras.id', 'comercializadoras.nombre', 'comercializadoras.logo')
            ->groupBy('comercializadora')
            ->get();
    }

    public function getOperadorasList()
    {
        return DB::table('tarifasMovil')
            ->join('operadoras', 'operadoras.id', '=', 'tarifasMovil.operadora')
            ->select('operadoras.id', 'operadoras.nombre', 'operadoras.logo')
            ->groupBy('operadora')
            ->get();
    }

    public function getOperadorasFibraList()
    {
        return DB::table('tarifasFibra')
            ->join('operadoras', 'operadoras.id', '=', 'tarifasFibra.operadora')
            ->select('operadoras.id', 'operadoras.nombre', 'operadoras.logo')
            ->groupBy('operadora')
            ->get();
    }
    /* fin funciones para consultar ofertas comerciales */



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
