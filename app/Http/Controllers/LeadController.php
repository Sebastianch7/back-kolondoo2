<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\UtilsController;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class LeadController extends Controller
{

    public function LeadRegisterInfo(Request $request)
    {
        // Validar los datos del formulario si es necesario
        $request->validate([
            'idOferta' => 'required',
            'phone' => 'required',
            'landing' => 'required',
            'urlOffer' => 'required'
        ]);

        // Crear una nueva instancia del modelo Lead con los datos del formulario
        $lead = new Lead([
            'idOferta' => $request->input('idOferta'),
            'phone' => $request->input('phone'),
            'landing' => $request->input('landing'),
            'urlOffer' => $request->input('urlOffer'),
            'company' => $request->input('company'),
        ]);

        // Guardar el nuevo registro en la base de datos
        $data = $lead->save();
        if ($lead->save()) {
            switch ($request->input('landing')) {
                case 'comparador-fibra':
                    return $this->leadFibra($lead, $lead->id);
                    break;
                case 'comparador-tarifas-luz':
                    return $this->leadLuz($lead, $lead->id);
                    break;
                case 'comparador-tarifas-fibra-y-movil':
                    return $this->leadFibraMovil($lead, $lead->id);
                    break;
            }
        } else {
            //funcion de guardar log con error
            //return response()->json(['message' => 'Registro de Lead no exitoso'], 400);
        }
    }

    public function FormContactanosRegister(Request $request)
    {
        /* $request->validate([
                        'nombre' => 'required',
                        'consulta' => 'required',
                        'email' => 'required',
                    ]); */
        echo $request;/* 
                    echo $request->input('consulta');
                    echo $request->input('email');
                    echo $request->input('check'); */
    }


    public function leadLuz($lead)
    {
        switch ($lead['company']) {
            case 1:    /*Iberdrola*/
                break;
            case 2:    /*Endesa*/
                break;
            case 3:    /*Naturgy*/
                break;
            case 4:    /*Repsol*/
                break;
            case 5:    /*Holaluz*/
                break;
            case 6:    /*Lucera*/
                break;
            case 7:    /*Alterna*/
                break;
            case 8:    /*Sweno*/
                break;
            case 9:    /*PepeEnergy*/
                break;
            case 10:    /*Wombbat*/
                break;
            case 11:    /*Gana Energía*/
                break;
            case 12:    /*Factor Energía*/
                break;
            case 13:    /*Plenitude*/
                break;
            case 14:    /*Octopus Energy*/
                break;
            case 15:    /*Holaluz By*/
                break;
            case 16:    /*Naturgy By*/
                break;
            case 17:    /*Imagina Energía*/
                break;
            case 18:    /*Prosegur*/
                break;
            default:
                break;
        }
    }

    public function leadFibraMovil($lead, $idLead)
    {
        switch ($lead['company']) {
            case 11:    /*Lowi*/
                return $this->apiLowi($lead, $idLead);
                break;

                break;
            case 20:    /*Butik*/

                break;

                break;
            case 10:    /*Másmóvil*/
            case 22:    /*Másmóvil*/
                return $this->apiMasMovil($lead, $idLead);
                break;
            default:

                break;
        }
    }

    public function leadFibra($lead)
    {
        switch ($lead['company']) {
            case 1:    /*Vodafone*/

                break;
            case 2:    /*Orange*/

                break;
            case 3:    /*Movistar*/

                break;
            case 4:    /*Yoigo*/

                break;
            case 5:    /*Simyo*/

                break;
            case 6:    /*Amena*/

                break;
            case 7:    /*Pepephone*/

                break;
            case 8:    /*Jazztel*/

                break;
            case 9:    /*Adamo*/

                break;
            case 10:    /*Másmóvil*/

                break;
            case 11:    /*Lowi*/

                break;
            case 12:    /*O2*/

                break;
            case 13:    /*Finetwork*/

                break;
            case 14:    /*Alterna*/

                break;
            case 15:    /*Eurona*/

                break;
            case 16:    /*Excom*/

                break;
            case 17:    /*Lemmon*/

                break;
            case 18:    /*Virgin*/

                break;
            case 19:    /*Llamaya*/

                break;
            case 20:    /*Butik*/

                break;
            case 21:    /*Vodafoneyu*/

                break;
            case 22:    /*Másmóvil*/

                break;
            default:

                break;
        }
    }


    // API CPL Lowi
    public function apiLowi($lead, $idLead)
    {
        $utilsController = new UtilsController();
        $visitorIp = $utilsController->obtencionIpRealVisitante();
        try {
            $base_api_url = "https://ws.walmeric.com/provision/wsclient/client_addlead.html";

            $obj = array(
                'format' => 'json',
                'idTag' => '29842f94d414949bf95fb2e6109142cfef1fb2a78114c2c536a36bf5a65b953a2724c2690797eda45de829716997a7ab87bee86aa84414bce8ebd6ca62bdbf093b09fbcdb928d3382a661f74609ff5c0e1a002941ebdbc14932342981ac48d58f4d749b0b5308246a6b0f8135759faee',
                'verifyLeadId' => 'NO',
                'idlead' => $idLead,
                'telefono' => $utilsController->formatTelephone($lead['phone']),
            );

            $query_string = http_build_query($obj);
            $full_api_url = $base_api_url . '?' . $query_string;

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'charset' => 'utf-8'
            ])->acceptJson()
                ->timeout(20)
                ->get($full_api_url);

            /* {
                "result": "KO",
                "code": 485,
                "leadId": "",
                "message": "Lead en proceso de registro"
            } */

            /* {
                "result": "OK",
                "code": 200,
                "leadId": "9",
                "new": true
            } */

            $responseObj = json_decode($response);

            if ($responseObj->result === "OK") {
                $message = "ok: Registrado el numero " . $lead['phone'] . " con id = " . $idLead . ", «lead» de *lowi - " . ($lead['company']) . "* en función apiLowi(). - Ip: " . $visitorIp . " - Datos recibidos del «lead» en la función: " . json_encode($responseObj);
                $utilsController->registroDeErrores(16, 'Lead saved lowi', $message, $lead['urlOffer'], $visitorIp);
                $responseObj->code = 201;
            } else {
                $message = "Fallo al registrar el numero " . $lead['phone'] . ", «lead» de *lowi - " . ($lead['company']) . "* en función apiLowi(). - Ip: " . $visitorIp . ' - Fallo ocurrido: ' . json_encode($responseObj);
                $utilsController->registroDeErrores(10, 'ajaxApiLowi', $message);
            }

            return response()->json([
                'message' => isset($responseObj->message) ? $responseObj->message : $responseObj->result,
                'status' => $responseObj->code
            ], 200);

        } catch (\Exception $e) {
            $message = "Fallo de IpAPI ajaxApiV3 falla al enviar el «lead» desde IP: " . $visitorIp . ' -> ERROR: ' . $e->getMessage();
            $utilsController->registroDeErrores(10, 'ajaxApiLowi', $message);

            return response()->json([
                'message' => 'En estos momentos no pudimos procesar tu solicitud, intenta mas tarde.',
                'status' => 502
            ], 502);
        }
    }

    public function apiMasMovil($lead, $idLead)
    {
        $utilsController = new UtilsController();
        $ipReal = $utilsController->obtencionIpRealVisitante();
        $apiUrl = 'https://api.byside.com/1.0/call/createCall';
        $authHeader = 'Basic Qzk4NTdFNkIxOTpUZU9ZR0l6eUxVdXlOYW8wRm5wZUlWN0ow';

        $requestData = [
            'phone' => $utilsController->formatTelephone($lead['phone']),
            'schedule_datetime' => 'NOW',
            'channel' => 'proveedores',
            'branch_id' => '26970',
            'lang' => 'es',
            'uuid' => 84125734612783612387162,
            'is_uid_authenticated' => false,
            'user_ip' => $ipReal,
            'url' => $lead['urlOffer'],
            'info' => [
                'mm_external_campaign_900' => '900696243',
                'proveedor_id' => 'HMG',
            ],
        ];


        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'charset' => 'utf-8',
            'Authorization' => $authHeader,
        ])->post($apiUrl, $requestData);

        $data = $response->json();

        if (isset($data['message']['id'])) {
            $message = "ok: Registrado el numero " . $requestData['phone'] . " con id = " . $data['message']['id'] . ", «lead» de *mas movil - " . ($lead['company']) . "* en función apiMasMovil(). - Ip: " . $ipReal . " - Datos recibidos del «lead» en la función: " . json_encode($data);
            $utilsController->registroDeErrores(16, 'Lead saved mas movil', $message, $lead['urlOffer'], $ipReal);
            $codigo = 201;

            $lead = Lead::find($idLead);

            if ($lead) {
                $lead->idResponse = $data['message']['id'];
                $lead->save();
            } else {
                $message = "-0: Fallo al registrar el numero " . $requestData['phone'] . ", «lead» de *mas movil - " . ($lead['company']) . "* en función apiMasMovil(). - Ip: " . $ipReal . ' - Fallo ocurrido: ' . $data['message']['status_msg'] . " - Datos recibidos del «lead» en la función: " . json_encode($data);
                $utilsController->registroDeErrores(11, 'Lead ERROR', $message, $lead['urlOffer'], $ipReal);
            }
        } else {
            switch (isset($data['message']['status'])) {
                case '-5':
                case '-4':
                case '-2':
                case '-3':
                    $message = $data['message']['status'] . ": Fallo al registrar el numero " . $requestData['phone'] . ", «lead» de *mas movil - " . ($lead['company']) . "* en función apiMasMovil(). - Ip: " . $ipReal . ' - Fallo ocurrido: ' . $data['message']['status_msg'] . " - Datos recibidos del «lead» en la función: " . json_encode($data);
                    $utilsController->registroDeErrores(11, 'Lead ERROR', $message, $lead['urlOffer'], $ipReal);
                    $codigo = 502;
                    break;
            }
        }
        //respuesta del api
        return response()->json([
            'message' => isset($data['message']['status_msg']) ? $data['message']['status_msg'] : $data['message']['id'],
            'status' => $codigo
        ], 200);
    }

    /**
     * Registro en nuestra BBDD de los «leads» recogidos de las diferentes compañías

     */
    function leadRecordTelco($lead): ?string
    {
        $data_lead = $lead;
        $lead_id = null;
        $IP_data = checkingGuestLocationApi(false);
        $visitorIp = !empty($IP_data) ? $IP_data->ip : "Sin datos desde IPAPI";
        $user_id = null;
        $country_code = null;
        //$decideCountry = decideCountry();
        $decideCountry = 'N/A';

        try {
            $registro_nuevo = false;
            $es_movil = true;
            $phone = str_replace(" ", "", $lead['phone']);
            if (in_array(Str::substr($phone, 0, 1), array(8, 9), true)) {
                $es_movil = false;
                $registro_nuevo = true;
            } elseif (!$conn->table('usuarios')->where('tlf_movil', $phone)->exists()) {
                $registro_nuevo = true;
            }

            if ($registro_nuevo) {
                $user = array();
                if ($es_movil) {
                    $user['tlf_movil'] = $phone;
                } else {
                    $user['tlf_fijo'] = $phone;
                }

                $user['nombre'] = (empty($lead['nombre_usuario']) || ($lead['nombre_usuario'] === "n/d")) ? null : $lead['nombre_usuario'];
                $user['email'] = (empty($lead['email'])) ? null : $lead['email'];
                $user['direccion'] = empty($lead['direccion_usuario']) ? null : $lead['direccion_usuario'];
                $user['poblacion'] = empty($lead['poblacion_usuario']) ? null : $lead['poblacion_usuario'];
                $user['provincia'] = empty($lead['provincia_usuario']) ? null : $lead['provincia_usuario'];
                $user['codigo_postal'] = empty($lead['cp_usuario']) ? null : $lead['cp_usuario'];
                if (isset($lead['acepta_comunicaciones_comerciales_kolondoo'])) {
                    if (intval($lead['acepta_comunicaciones_comerciales_kolondoo']) === 1) {
                        $user['acepta_comunicaciones_comerciales'] = true;
                        $user['fecha_aceptacion_comunicaciones_comerciales'] = Carbon::now()->format("Y-m-d H:i:s");
                    }
                }

                if (!empty($IP_data)) {
                    $country_code = !empty($IP_data->country_code) ? $IP_data->country_code : null;
                    $user['ip'] = !empty($IP_data->ip) ? $IP_data->ip : null;
                    $user['ip_type'] = !empty($IP_data->type) ? $IP_data->type : null;
                    $user['ip_nombre_continente'] = !empty($IP_data->continent_name) ? $IP_data->continent_name : null;
                    $user['ip_codigo_pais'] = !empty($IP_data->country_code) ? $IP_data->country_code : null;
                    $user['ip_nombre_pais'] = !empty($IP_data->country_name) ? $IP_data->country_name : null;
                    $user['ip_region'] = !empty($IP_data->region_name) ? $IP_data->region_name : null;
                    $user['ip_ciudad'] = !empty($IP_data->city) ? $IP_data->city : null;
                    $user['ip_codigo_postal'] = !empty($IP_data->zip) ? $IP_data->zip : null;
                    $user['ip_latitud'] = !empty($IP_data->longitude) ? $IP_data->longitude : null;
                    $user['ip_longitud'] = !empty($IP_data->latitude) ? $IP_data->latitude : null;
                }

                //$user_id = $conn->table('usuarios')->insertGetId($user);
                //agregar funcion para guardar la data anterior
            } else {
                $user_id = $conn->table('usuarios')->where('tlf_movil', $phone)->orderby('fecha_registro', 'DESC')->pluck('id')->first();
                if (isset($data_lead['acepta_comunicaciones_comerciales_kolondoo'])) {
                    if (intval($data_lead['acepta_comunicaciones_comerciales_kolondoo']) === 1) {
                        $conn->table('usuarios')->where('id', $user_id)->update([
                            'acepta_comunicaciones_comerciales' => true,
                            'fecha_aceptacion_comunicaciones_comerciales' => Carbon::now()->format("Y-m-d H:i:s")
                        ]);
                    }
                }
            }

            //Inserción del lead
            $lead = array();
            $lead['usuario_id'] = $user_id;
            $lead['producto'] = $data_lead['producto'];
            $lead['tipo_conversion'] = $data_lead['tipo_conversion'];
            $lead['tarifa'] = empty($data_lead['tarifa']) ? "S/T" : $data_lead['tarifa'];
            $lead['compania'] = $data_lead['compania'];
            $lead['tipo_formulario'] = $data_lead['tipo_formulario'];
            $lead['precio'] = empty($data_lead['precio']) ? null : $data_lead['precio'];
            $lead['tv'] = empty($data_lead['tv']) ? null : $data_lead['tv'];
            $lead['fijo'] = empty($data_lead['fijo']) ? null : $data_lead['fijo'];
            $lead['fibra'] = empty($data_lead['fibra']) ? null : $data_lead['fibra'];
            $lead['movil'] = empty($data_lead['movil']) ? null : $data_lead['movil'];
            $lead['lineas_adicionales'] = empty($data_lead['lineas_adicionales']) ? null : intval($data_lead['lineas_adicionales']);
            $lead['dato1'] = empty($data_lead['dato1']) ? null : trim($data_lead['dato1']);
            $lead['dato2'] = empty($data_lead['dato2']) ? null : trim($data_lead['dato2']);
            $lead['dato3'] = empty($data_lead['dato3']) ? null : trim($data_lead['dato3']);
            $lead['dato4'] = empty($data_lead['dato4']) ? null : trim($data_lead['dato4']);
            if ($data_lead['tipo_conversion'] === "cpl") {
                $lead['acepta_cesion_datos_a_proveedor'] = intval($data_lead['acepta_cesion_datos_a_proveedor']) === 1;
                //Con «leads» de procedencia «Facebook» se aceptan las dos políticas implícitamente
                if ($lead['producto'] === "FACEBOOK") {
                    $lead['acepta_politica_privacidad_kolondoo'] = intval($data_lead['acepta_politica_privacidad_kolondoo']) === 1;
                }
            } elseif ($data_lead['tipo_conversion'] === "cpa") {
                $lead['acepta_politica_privacidad_kolondoo'] = intval($data_lead['acepta_politica_privacidad_kolondoo']) === 1;
            } else {
                throw new Exception("ERROR en tipo de conversión en función leadRecordTelco(), tipo_conversion no es «cpl» ni «cpa» revisar tabla operadoras (" . $data_lead['compania'] . ")");
            }

            //utm params
            $lead['utm_source'] = empty($request->utm_source) ? null : $request->utm_source;
            $lead['utm_medium'] = empty($request->utm_medium) ? null : $request->utm_medium;
            $lead['utm_campaign'] = empty($request->utm_campaign) ? null : $request->utm_campaign;
            $lead['utm_content'] = empty($request->utm_content) ? null : $request->utm_content;
            $lead['utm_term'] = empty($request->utm_term) ? null : $request->utm_term;

            /*
            Reescribimos la procedencia del registro en caso de que estemos en campañas Emailing de Arkeero
            Deberán venir los valores utm_campaign=ark, utm_medium=email y utm_source={definición de campaña, por ejemplo naturgy101022} para que se reconozca como campaña de emailing de arkeero.
        */
            if (!empty($request->utm_source) && !empty($request->utm_medium) && !empty($request->utm_campaign) && Str::lower($request->utm_source) === "ark" && Str::lower($request->utm_medium) === "email") {
                $lead['producto'] = "EMAILING_RECORD_" . Str::upper($request->utm_campaign);
            }

            $lead_id = $conn->table('telco')->insertGetId($lead);

            $conn->commit();
        } catch (\PDOException | \Exception $e) {
            $conn->rollback();
            $lead_id = null;
            $message = "Fallo al registrar el «lead» de *" . ($data_lead['compania']) . "* en función leadRecordTelco(). - Ip: " . $visitorIp . ' - Fallo ocurrido: ' . $e->getMessage() . ", Línea: " . $e->getLine() . " - Datos recibidos del «lead» en la función: " . json_encode($data_lead, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            registroDeErrores(11, 'Lead ERROR', $message, $country_code, $decideCountry);
        }

        return 'telco_' . (empty($lead_id) ? 'not_registered' : $lead_id);
    }
}
