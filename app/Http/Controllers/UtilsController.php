<?php

namespace App\Http\Controllers;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class UtilsController extends Controller
{
    function formatTelephone(string $phone): string
    {
        //Revisión de formato telefónico. Pepephone Api solo acepta numeración normal y con 34 delante. La validación por env de números nacionales incluye +34,34 y 0034 previo al número de 9 cifras.
        return str_replace(array(" ", "+"), "", $phone);
        if (Str::substr($phone, 0, 4) === "0034" && strlen($phone) === 13) {
            $phone = Str::substr($phone, 4);
        } elseif (Str::substr($phone, 0, 2) === "34" && strlen($phone) === 11) {
            $phone = Str::substr($phone, 2);
        }

        return $phone;
    }
    //Para evitar entrar en bucle en funciones y no saturar/consumir las llamadas a IpAPI, tenemos los parámetros $country_code (recogido a partir de checkingGuestLocationApi()) y $decideCountry (que viene de la función homologa) recibidos en la función cuando se quieran registrar. En cualquier caso, guarda laIP.
    /**
     * Función de registro eventos
     *
     * @param int $tipo
     * @param string $origen
     * @param string $mensaje
     * @param string|null $country_code
     * @param string|null $decideCountry
     * @return void
     */
    function registroDeErrores(int $tipo, string $origen, string $mensaje, string|null $country_code = null, string|null $decideCountry = null): void
    {
        DB::table('events')->insert(
            array(
                'event_type' => $tipo,
                'source' => $origen,
                'message' => $mensaje,
                'country_code' => $country_code,
                'instance' => $decideCountry,
                'route' =>  !empty($_SERVER["REQUEST_URI"]) ? (url('/') . $_SERVER["REQUEST_URI"]) : null,
                'calling_IP' => $this->obtencionIpRealVisitante()
            )
        );
    }

    /**
     * Obtencion de la IP REAL del visitante
     *
     * @return string
     */
    function obtencionIpRealVisitante(): string
    {
        $return = null;

        $headers = [
            "HTTP_CLIENT_IP",
            "HTTP_X_FORWARDED_FOR",
            "HTTP_X_FORWARDED",
            "HTTP_FORWARDED_FOR",
            "HTTP_FORWARDED",
            "REMOTE_ADDR"
        ];

        foreach ($headers as $header) {
            if (isset($_SERVER[$header])) {
                $return = $_SERVER[$header];
                break;
            }
        }

        if ($return === null) {
            $return = "no registrado";
        }

        return $return;
    }


    /**
     * Obtención de los datos por IPAPI
     *
     * @param bool $just_country_code
     * @return mixed
     */
    /* IPAPI  Devuelve null, código de país en minúsculas o objeto */
    function checkingGuestLocationApi(bool $just_country_code = null, $ip = null): mixed
    {
        $visitorIp = empty($ip) ? $this->obtencionIpRealVisitante() : $ip;
        $visitorIp = "181.53.96.39";
        $ipapi_url = "https://api.ipapi.com/api/$visitorIp?";
        $ipapi_key = "213e41b9b546cb54f68186a1d2b6b394";
        $response = null;

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'charset' => 'utf-8'
            ])->acceptJson()
                ->timeout(10)
                ->get(
                    $ipapi_url,
                    [
                        'access_key' => $ipapi_key,
                        'language' => 'es',
                        'output' => 'json',
                    ]
                );
            $this->registroDeErrores(15, 'IpAPI', 'Conexion exitosa');
        } catch (ConnectionException $e) {
            $message = "Fallo de IpAPI no responde. - ERROR: " . $e->getMessage();
            //$this->registroDeErrores(6, 'IpAPI', $message);
            return null;
        }

        if (!empty($response) && $response->successful()) {
            $return = json_decode($response->body());
            if (isset($return->country_code) && is_string($return->country_code)) {
                if ($just_country_code) {
                    return Str::lower($return->country_code);
                } else {
                    return $return;
                }
            } else {
                $message = "Fallo IPAPI, responde con mensaje de ERROR: ";
                if (!empty($return->error->code) && !empty($return->error->info)) {
                    $message .= ": " . $return->error->code . " -> " . $return->error->info;
                } else {
                    $message .= " SIN INFO";
                }
                //$this->registroDeErrores(6, 'IpAPI', $message);
                return null;
            }
        } else {
            $message = "Fallo de IpAPI objeto vacío - Objeto response: " . json_encode($response) . ", Objeto enviado: " . json_encode(['access_key' => $ipapi_key, 'language' => 'es', 'output' => 'json', 'fields' => 'ip,type,continent_code,continent_name,country_code,country_name,region_name,city,zip,latitude,longitude']);
            //$this->registroDeErrores(6, 'IpAPI', $message);
            return null;
        }
    }

    /**
     * Comprueba si el telefono está en la lista negra
     *
     * @param string $phone
     */
    function isBannedPhone(mixed $phone): bool
    {
        return DB::connection('common_event_log')->table('banned_phones')->where('phone', preg_replace('/\s+/', '', $phone))->exists();
    }
}
