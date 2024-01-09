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
    private $utilsController;

    public function __construct(UtilsController $utilsController)
    {
        $this->utilsController = $utilsController;
    }

    public function LeadRegisterInfo(Request $request)
    {
        // Validar los datos del formulario si es necesario
        $request->validate([
            'idOferta' => 'required',
            'phone' => 'required',
            'landing' => 'required',
            'urlOffer' => 'required',
            'company' => 'required',
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
        }
    }

    public function FormContactanosRegister(Request $request)
    {
        /* $request->validate([
                        'nombre' => 'required',
                        'consulta' => 'required',
                        'email' => 'required',
                    ]); */
        /* echo $request->input('consulta');
                    echo $request->input('email');
                    echo $request->input('check'); */
    }


    public function leadLuz($lead, $idLead)
    {
        switch ($lead['company']) {
            case 13:    /*Plenitude*/
                return $this->apiPlenitude($lead, $idLead);
                break;
            case 14:    /*Octopus Energy*/
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
            case 20:    /*Butik*/
                return $this->apiButik($lead, $idLead);
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
            case 22:    /*Másmóvil*/
                break;
            default:
                break;
        }
    }

    // API CPL Lowi
    public function apiLowi($lead, $idLead)
    {
        $visitorIp = $this->utilsController->obtencionIpRealVisitante();;
        try {
            $base_api_url = "https://ws.walmeric.com/provision/wsclient/client_addlead.html";

            $obj = array(
                'format' => 'json',
                'idTag' => '29842f94d414949bf95fb2e6109142cfef1fb2a78114c2c536a36bf5a65b953a2724c2690797eda45de829716997a7ab87bee86aa84414bce8ebd6ca62bdbf093b09fbcdb928d3382a661f74609ff5c0e1a002941ebdbc14932342981ac48d58f4d749b0b5308246a6b0f8135759faee',
                'verifyLeadId' => 'NO',
                'idlead' => $idLead,
                'telefono' => $this->utilsController->formatTelephone($lead['phone']),
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

            $responseObj = json_decode($response);

            if ($responseObj->result === "OK") {
                $message = "ok: Registrado el numero " . $lead['phone'] . " con id = " . $idLead . ", «lead» de *lowi - " . ($lead['company']) . "* en función apiLowi(). - Ip: " . $visitorIp . " - Datos recibidos del «lead» en la función: " . json_encode($responseObj);
                $this->utilsController->registroDeErrores(16, 'Lead saved lowi', $message, $lead['urlOffer'], $visitorIp);
                $responseObj->code = 201;
            } else {
                $message = "Fallo al registrar el numero " . $lead['phone'] . ", «lead» de *lowi - " . ($lead['company']) . "* en función apiLowi(). - Ip: " . $visitorIp . ' - Fallo ocurrido: ' . json_encode($responseObj);
                $this->utilsController->registroDeErrores(10, 'ajaxApiLowi', $message);
            }

            return response()->json([
                'message' => isset($responseObj->message) ? $responseObj->message : $responseObj->result,
                'status' => $responseObj->code
            ], 200);
        } catch (\Exception $e) {
            $message = "Fallo de IpAPI ajaxApiV3 falla al enviar el «lead» desde IP: " . $visitorIp . ' -> ERROR: ' . $e->getMessage();
            $this->utilsController->registroDeErrores(10, 'ajaxApiLowi', $message);

            return response()->json([
                'message' => 'En estos momentos no pudimos procesar tu solicitud, intenta mas tarde.',
                'status' => 502
            ], 502);
        }
    }

    public function apiMasMovil($lead, $idLead)
    {
        $ipReal = $this->utilsController->obtencionIpRealVisitante();
        $apiUrl = 'https://api.byside.com/1.0/call/createCall';
        $authHeader = 'Basic Qzk4NTdFNkIxOTpUZU9ZR0l6eUxVdXlOYW8wRm5wZUlWN0ow';

        $requestData = [
            'phone' => '+34' . $this->utilsController->formatTelephone($lead['phone']),
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
            $this->utilsController->registroDeErrores(16, 'Lead saved mas movil', $message, $lead['urlOffer'], $ipReal);
            $codigo = 201;

            $leadValidation = Lead::find($idLead);
            if ($leadValidation) {
                $leadValidation->idResponse = $data['message']['id'];
                $leadValidation->save();
            } else {
                $message = "-0: Fallo al registrar el numero " . $requestData['phone'] . ", «lead» de *mas movil - " . ($lead['company']) . "* en función apiMasMovil(). - Ip: " . $ipReal . ' - Fallo ocurrido: ' . $data['message']['status_msg'] . " - Datos recibidos del «lead» en la función: " . json_encode($data);
                $this->utilsController->registroDeErrores(11, 'Lead ERROR', $message, $lead['urlOffer'], $ipReal);
            }
        } else {
            switch (isset($data['message']['status'])) {
                case '-5':
                case '-4':
                case '-2':
                case '-3':
                    $message = $data['message']['status'] . ": Fallo al registrar el numero " . $requestData['phone'] . ", «lead» de *mas movil - " . ($lead['company']) . "* en función apiMasMovil(). - Ip: " . $ipReal . ' - Fallo ocurrido: ' . $data['message']['status_msg'] . " - Datos recibidos del «lead» en la función: " . json_encode($data);
                    $this->utilsController->registroDeErrores(11, 'Lead ERROR', $message, $lead['urlOffer'], $ipReal);
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

    public function apiButik($lead, $idLead)
    {
        $visitorIp = $this->utilsController->obtencionIpRealVisitante();;
        try {
            $response = null;
            //$customer_name = (empty($request->dataToSend['nombre_usuario']) || ($request->dataToSend['nombre_usuario'] === "n/d")) ? "N/A" : $request->dataToSend['nombre_usuario'];
            $customer_name = "N/A";
            $base_api_url = "https://app.whatconverts.com/api/v1/leads/";

            //Basic Auth:  user => pass; '4273-1dc57737c98d47b7' => 'aa1a2e99df72fdd82ab7045f8d9fa6ad'
            $obj = array(
                'profile_id' => '97488',
                'send_notification' => 'false',
                'lead_type' => 'web_form',
                'lead_source' => 'kolondoo',
                'lead_medium' => 'affiliate',
                'lead_campaign' => 'telco_kolondoo_comparador_pros',
                'additional_fields[Phone Number]' => $this->utilsController->formatTelephone($lead['phone']),
                'phone_number' => $this->utilsController->formatTelephone($lead['phone']),
                //'additional_fields[Contact_name]' => $customer_name,
                'ip_address' => $visitorIp
            );
            $header = array(
                'Authorization: Basic NDI3My0xZGM1NzczN2M5OGQ0N2I3OmFhMWEyZTk5ZGY3MmZkZDgyYWI3MDQ1ZjhkOWZhNmFk',
                'Cookie: AWSALB=j8227SuD3vMelmJqAqqL38D7Qvm7L09lF8YztrOQPtjw4RK6KcQI3qto1WuSAk3DIOlfRu6vYFZl76LOwBsra2HzeFcwYoLQdNv68GldF1t5q1EXcKbv/iSmJ5wg; AWSALBCORS=j8227SuD3vMelmJqAqqL38D7Qvm7L09lF8YztrOQPtjw4RK6KcQI3qto1WuSAk3DIOlfRu6vYFZl76LOwBsra2HzeFcwYoLQdNv68GldF1t5q1EXcKbv/iSmJ5wg'
            );
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $base_api_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $obj,
                CURLOPT_HTTPHEADER => $header,
            ));

            $response = curl_exec($curl);
            curl_close($curl);
            $responseObj = json_decode($response);

            if ($responseObj->lead_state === 'Completed') {

                $leadValidation = Lead::find($idLead);
                if ($leadValidation) {
                    $leadValidation->idResponse = $responseObj->lead_id;
                    $leadValidation->save();
                } else {
                    $message = "-0: Fallo al registrar el numero " . $lead['phone'] . ", «lead» de *mas butik - " . ($lead['company']) . "* en función butik(). - Ip: " . $visitorIp . ' - Fallo ocurrido: ' . $responseObj->status . " - Datos recibidos del «lead» en la función: " . json_encode($responseObj);
                    $this->utilsController->registroDeErrores(11, 'Lead ERROR', $message, $lead['urlOffer'], $visitorIp);
                }
                $message = "ok: Registrado el numero " . $lead['phone'] . " con id = " . $idLead . ", «lead» de *Butik - " . ($lead['company']) . "* en función apiButik(). - Ip: " . $visitorIp . " - Datos recibidos del «lead» en la función: " . json_encode($responseObj);
                $this->utilsController->registroDeErrores(16, 'Lead saved Butik', $message, $lead['urlOffer'], $visitorIp);
                $responseObj->code = 201;
            } else {
                $message = "Fallo al registrar el numero " . $lead['phone'] . ", «lead» de *Butik - " . ($lead['company']) . "* en función apiButik(). - Ip: " . $visitorIp . ' - Fallo ocurrido: ' . json_encode($responseObj);
                $this->utilsController->registroDeErrores(10, 'ajaxApiButik', $message);
                $responseObj->code = 502;
            }

            return response()->json([
                'message' => $responseObj->lead_state,
                'status' => $responseObj->code
            ], 200);
        } catch (ConnectionException $e) {
            $message = "Fallo de IpAPI ajaxApiAlternaTelco falla al enviar el «lead» desde IP: " . $visitorIp . ' -> ERROR: ' . $e->getMessage();
        }
    }

    public function apiPlenitude($lead, $idLead)
    {
        $visitorIp = $this->utilsController->obtencionIpRealVisitante();;
        try {
            $response = null;
            $base_api_url = "https://hooks.zapier.com/hooks/catch/13049102/bpkbypb/";

            $obj = array(
                'telefono' => $this->utilsController->formatTelephone($lead['phone']),
                'interes' =>  explode('/', $lead['urlOffer'])[4],
                'source' =>  "desar",
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

            $responseObj = json_decode($response);

            if (isset($responseObj->status) && $responseObj->status === "success") {

                $leadValidation = Lead::find($idLead);
                if ($leadValidation) {
                    $leadValidation->idResponse = $responseObj->id;
                    $leadValidation->save();
                } else {
                    $message = "-0: Fallo al registrar el numero " . $lead['phone'] . ", «lead» de *mas plenitude - " . ($lead['company']) . "* en función plenitude(). - Ip: " . $visitorIp . ' - Fallo ocurrido: ' . $responseObj->status . " - Datos recibidos del «lead» en la función: " . json_encode($responseObj);
                    $this->utilsController->registroDeErrores(11, 'Lead ERROR', $message, $lead['urlOffer'], $visitorIp);
                }

                $message = "ok: Registrado el numero " . $lead['phone'] . " con id = " . $idLead . ", «lead» de *Plenitude - " . ($lead['company']) . "* en función apiPlenitude(). - Ip: " . $visitorIp . " - Datos recibidos del «lead» en la función: " . json_encode($responseObj);
                $this->utilsController->registroDeErrores(16, 'Lead saved Plenitude', $message, $lead['urlOffer'], $visitorIp);
                $responseObj->code = 201;
            } else {
                $message = "Fallo al registrar el numero " . $lead['phone'] . ", «lead» de *Plenitude - " . ($lead['company']) . "* en función apiPlenitude(). - Ip: " . $visitorIp . ' - Fallo ocurrido: ' . json_encode($responseObj);
                $this->utilsController->registroDeErrores(10, 'ajaxApiPlenitude', $message);
                $responseObj->code = 502;
            }

            return response()->json([
                'message' => $responseObj->status,
                'status' => $responseObj->code
            ], 200);
        } catch (ConnectionException $e) {
            $fallo_envio_lead = true;
            $message = "Fallo de IpAPI ajaxApiV3 falla al enviar el «lead» desde IP: " . $visitorIp . ' -> ERROR: ' . $e->getMessage();
            registroDeErrores(10, 'ajaxApiV3', $message);
        }
    }
}
