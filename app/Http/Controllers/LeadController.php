<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function LeadRegisterInfo(Request $request)
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
