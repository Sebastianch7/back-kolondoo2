<?php

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\MySqlConnection;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\HttpFoundation\Cookie;


/**
 * Obtener la conexión con la BBDD correspondiente cuando vayamos a cargar header y footer
 *
 * @param string $tipo
 * @return ConnectionInterface
 */
function connexionDB(string $tipo): ConnectionInterface
{
    $conn = null;
    $country_prefix = decideCountry();

    switch($tipo)
    {
        case "master":
            $conn = DB::connection($country_prefix.'_master_program');
            break;

        case "blog":
            $conn = DB::connection($country_prefix.'_blog_program');
            break;

        case "leads":
            $conn = DB::connection($country_prefix.'_leads_program');
            break;
    }

    if(is_null($conn))
    {
        registroDeErrores( 3,'Función connexionDB()','No crea conexión con BBDD: '.$tipo.' prefijo: '.$country_prefix);
        abort(503);
    }

    return $conn;
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
    DB::connection('common_event_log')->table('events')->insert(
        array(
            'event_type' => $tipo,
            'source' => $origen,
            'message' => $mensaje,
            'country_code' => $country_code,
            'instance' => $decideCountry,
            'route' =>  !empty($_SERVER["REQUEST_URI"])?(url('/').$_SERVER["REQUEST_URI"]):null,
            'calling_IP' => obtencionIpRealVisitante()
        ));
}

/**
 * Función de registro eventos IVR
 *
 * @param int $event_type
 * @param string|null $source_identifier
 * @param string|null $redirected_to
 * @param int|null $ocm_campaign_id
 * @param string|null $phone
 * @param string $call_status
 * @param string $mensaje
 * @param int|null $ivr_option_selected
 * @param string $data_transferred
 * @param string|null $API_record_id
 * @param string|null $origin_incept_date
 * @return void
 */
function IvrEventRecords(     int $event_type,
                              string|null $source_identifier,
                              string|null $redirected_to,
                              int|null $ocm_campaign_id,
                              string|null $phone,
                              string $call_status,
                              string $mensaje,
                              int|null $ivr_option_selected,
                              string $data_transferred,
                              string|null $API_record_id,
                              string|null $origin_incept_date): void
{
    DB::connection('common_event_log')->table('IVR_events')->insert(
        array(
            'event_type' => $event_type, //Tipo de evento registrado: INFO, ERROR, ETC...
            'source_identifier' => $source_identifier, //Identificador de registro de la campaña IVR
            'redirected_to' => $redirected_to, //Identificador de registro de la campaña IVR
            'ocm_campaign_id' => $ocm_campaign_id, //Identificador numérico del «IVR Dialplan»  de origen en OCM
            'phone' => $phone,
            'call_status' => $call_status,
            'message' => $mensaje,
            'ivr_option_selected' => $ivr_option_selected,
            'data_transferred' => $data_transferred,
            'API_record_id' => $API_record_id,
            'origin_incept_date' => $origin_incept_date,
        ));
}

/**
 * Obtencion de la IP REAL del visitante
 *
 * @return string
 */
function obtencionIpRealVisitante(): string
{
    $return = null;
    if (isset($_SERVER["HTTP_CLIENT_IP"]))
    {
        $return = $_SERVER["HTTP_CLIENT_IP"];
    }
    elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
    {
        $return = $_SERVER["HTTP_X_FORWARDED_FOR"];
    }
    elseif (isset($_SERVER["HTTP_X_FORWARDED"]))
    {
        $return = $_SERVER["HTTP_X_FORWARDED"];
    }
    elseif (isset($_SERVER["HTTP_FORWARDED_FOR"]))
    {
        $return = $_SERVER["HTTP_FORWARDED_FOR"];
    }
    elseif (isset($_SERVER["HTTP_FORWARDED"]))
    {
        $return = $_SERVER["HTTP_FORWARDED"];
    }
    elseif(isset($_SERVER["REMOTE_ADDR"]))
    {
        $return = $_SERVER["REMOTE_ADDR"];
    }
    else
    {
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
function checkingGuestLocationApi(bool $just_country_code, $ip = null): mixed
{
    $visitorIp = empty($ip)?obtencionIpRealVisitante():$ip;
    $ipapi_url = "https://api.ipapi.com/api/";
    $ipapi_key = "213e41b9b546cb54f68186a1d2b6b394";
    $response = null;

    try
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'charset' => 'utf-8'
        ])->acceptJson()
            ->timeout(10)
            ->get($ipapi_url.$visitorIp,
                [
                    'access_key' => $ipapi_key,
                    'language' => 'es',
                    'output' => 'json',
                    'fields' => 'ip,type,continent_code,continent_name,country_code,country_name,region_name,city,zip,latitude,longitude,connection,location',
                ]);
    }
    catch (ConnectionException $e)
    {
        $message = "Fallo de IpAPI no responde. - ERROR: ".$e->getMessage();
        registroDeErrores( 6,'IpAPI',$message);
        return null;
    }

    if(!empty($response) && $response->successful())
    {
        $return = json_decode($response->body());
        if(isset($return->country_code) && is_string($return->country_code))
        {
            if($just_country_code)
            {
                return Str::lower($return->country_code);
            }
            else
            {
                return $return;
            }
        }
        else
        {
            $message = "Fallo IPAPI, responde con mensaje de ERROR: ";
            if(!empty($return->error->code) && !empty($return->error->info))
            {
                $message .= ": ".$return->error->code." -> ".$return->error->info;
            }
            else
            {
                $message .= " SIN INFO";
            }
            //registroDeErrores( 6,'IpAPI',$message);
            return null;
        }
    }
    else
    {
        $message = "Fallo de IpAPI objeto vacío - Objeto response: ".json_encode($response).", Objeto enviado: ".json_encode(['access_key' => $ipapi_key, 'language' => 'es', 'output' => 'json', 'fields' => 'ip,type,continent_code,continent_name,country_code,country_name,region_name,city,zip,latitude,longitude']);
        registroDeErrores( 6,'IpAPI',$message);
        return null;
    }
}

/**
 * Obtener colección de rutas de «blog» para las diferentes instancias función genérica
 *
 * @param string $connection
 * @return mixed
 */
function getRutasBlog(string $connection): mixed
{
    $today = Carbon::now()->format("Y-m-d");
    $return = DB::connection($connection)
        ->table('SEO')
        ->join('blog','SEO.id','=','blog.SEO_id')
        ->join('categorias','blog.categoria_id','=','categorias.id')
        ->select('blog.fecha_modificacion','SEO.url_amigable','SEO.metabots','categorias.url_amigable as categoria_url_amigable','SEO.controlador','SEO.funcion','SEO.vista','blog.titulo')
        ->where('categorias.categoria_activa',true)
        ->where('blog.fecha_publicacion','<=',$today)->where(function ($query) use ($today) {$query->where('blog.fecha_expiracion','>=',$today)->orWhereNull('blog.fecha_expiracion');});

    if(env('APP_ENV') === "production")
    {
        $return->where('SEO.ruta_activa',true) ;
    }

    return $return->orderBy('SEO.id','ASC')->get();
}

/**
 * Obtener colección de rutas de «blog categories» para las diferentes instancias función genérica
 *
 * @param string|null $connection
 * @param MySqlConnection|null $obj_connection
 * @return mixed
 */
function getRutasBlogCategories(string $connection = null, MySqlConnection $obj_connection = null): mixed
{
    $return = (is_null($obj_connection)?DB::connection($connection):$obj_connection)->table('SEO')
        ->join('blog','SEO.id','=','blog.SEO_id')
        ->join('categorias','categorias.id','=','blog.categoria_id')
        ->select(
            'categorias.id',
            'categorias.url_amigable',
            'categorias.metatitulo',
            'categorias.metadescripcion',
            'categorias.categoria',
            'categorias.entradilla',
            'categorias.orden_menu',
            'categorias.fecha_registro'
        )
        ->where('SEO.ruta_activa',true)
        ->where('categorias.categoria_activa',true)
        ->orderBy('categorias.orden_menu','ASC')
        ->groupBy(
            'categorias.id',
            'categorias.url_amigable',
            'categorias.metatitulo',
            'categorias.metadescripcion',
            'categorias.categoria',
            'categorias.entradilla',
            'categorias.orden_menu',
            'categorias.fecha_registro'
        );

    if(env('APP_ENV')==="production")
    {
        $today = Carbon::now()->format("Y-m-d");
        $return->where('blog.fecha_publicacion','<=',$today)->where(function ($query) use ($today) {$query->where('blog.fecha_expiracion','>=',$today)->orWhereNull('blog.fecha_expiracion');});
    }

    return $return->distinct()->get();
}

/**
 * Obtener colección de rutas de comparadores (parrillas) para las diferentes instancias función genérica
 *
 * @param string $connection
 * @return mixed
 */
function getRutasConversion(string $connection): mixed
{
    $getRutasConversionActivas = array();
    $today = Carbon::now()->format("Y-m-d H:i:s");
    $conn = DB::connection($connection);
    foreach ($conn->table('SEO')
                 ->where('ruta_activa',true)
                 ->whereNull('padre_id')
                 ->orderBy('menu_superior','ASC')
                 ->orderBy('id','ASC')
                 ->get() as $item_1)
    {
        $getRutasConversionActivas[] = $item_1; //Primer nivel Mercados. Siempre se van a incluir. Ejemplo internet-telefonia
        foreach ($conn
                     ->table('SEO')
                     ->where('ruta_activa',true)
                     ->where('padre_id',$item_1->id)
                     ->orderBy('menu_superior','ASC')
                     ->orderBy('id','ASC')
                     ->get() as $item_2)
        {
            $semaphore = false;
            foreach ($conn
                         ->table('SEO')
                         ->where('ruta_activa',true)
                         ->where('padre_id',$item_2->id)
                         ->orderBy('id','ASC')
                         ->get() as $item_3)
            {
                //Decidimos la tabla de compañía con la que se va a cruzar tabla_tarifas (según sea operadora, comercializadora o aseguradora)
                $tabla_tarifas = $item_3->tabla_tarifas;
                if(Str::contains($item_3->tabla_tarifas,'TELCO'))
                {
                    $tabla_compania = "1_operadoras";
                    $table_field = "operadora";
                }
                elseif(Str::contains($item_3->tabla_tarifas,'ENERGIA'))
                {
                    $tabla_compania = "1_comercializadoras";
                    $table_field = "comercializadora";
                }
                elseif(Str::contains($item_3->tabla_tarifas,'SEGUROS'))
                {
                    $tabla_compania = "1_aseguradoras";
                    $table_field = "aseguradora";
                }

                //Comprobamos que la ruta tiene ofertas activas que pertenecen a compañías activas.
                if($conn->table($tabla_tarifas)->leftJoin($tabla_compania,$tabla_tarifas.'.'.$table_field,'=',$tabla_compania.'.id')->where('id_producto',$item_3->id)->where('fecha_publicacion','<=',$today)->where(function ($query) use ($today) {$query->where('fecha_expiracion','>=',$today)->orWhereNull('fecha_expiracion');})->where('tarifa_activa',true)->where($tabla_compania.'.'.$table_field.'_activa',true)->exists())
                {
                    //Nivel 3, comparadores de compañía, han de tener ofertas activas y publicadas para aparecer, Ejemplo comparador-solo-fibra/solo-fibra-orange
                    $getRutasConversionActivas[] = $item_3;
                    $semaphore = true;
                }
            }

            if($semaphore)
            {
                $getRutasConversionActivas[] = $item_2; //Nivel 2, comparadores generales, deben de tener algún comparador de compañía con tarifas activas para aparecer. Ejemplo comparador-solo-fibra.
            }
        }
    }

    //dd($getRutasConversionActivas);
    return $getRutasConversionActivas;
}

/**
 * Obtener colección de rutas de tarifas para las diferentes instancias función genérica y por vertical
 *
 * @param string $connection
 * @param string $tarifa_table
 * @return mixed
 */
function getRutastarifas(string $connection, string $tarifa_table): mixed
{
    $today = Carbon::now()->format("Y-m-d H:i:s");
    return DB::connection($connection)->table($tarifa_table)
        ->leftJoin('SEO',$tarifa_table.'.id_producto','=','SEO.id')
        ->select($tarifa_table.'.id as id_tarifa',$tarifa_table.'.controlador',$tarifa_table.'.funcion',$tarifa_table.'.vista','SEO.url_amigable')
        ->where($tarifa_table.'.tarifa_activa',true)
        ->where($tarifa_table.'.fecha_publicacion','<=',$today)->where(function ($query) use ($tarifa_table,$today) {$query->where($tarifa_table.'.fecha_expiracion','>=',$today)->orWhereNull($tarifa_table.'.fecha_expiracion');})
        ->where('SEO.ruta_activa',true)
        ->get();
}

/**
 * Obtener tablas disponibles de tarifas en BBDD
 *
 * @param string $connection
 * @param string $table_patter
 * @return mixed
 */
function getAvailableTables(string $connection, string $table_patter): mixed
{
    $tables = DB::connection($connection)->select("SHOW TABLES");
    $items = array();
    foreach($tables as $obj)
    {
        foreach($obj as $index => $table)
        {
            if(Str::match("/".$table_patter."/",$table))
            {
                $items[] = $table;
            }
        }
    }

    return $items;
}

/**
 * Obtener colección de rutas de «blog» para las diferentes instancias función genérica
 *
 * @param string $connection
 * @return mixed
 */
function getRutasGestiones(string $connection): mixed
{
    return DB::connection($connection)
        ->table('WEB_3_GESTIONES')
        ->where('active',true)
        ->WhereNotNull('father_id')
        ->orderBy('menu_order','ASC')
        ->get();
}

/**
 * Obtener colección de rutas de las categorias de «gestiones» para las diferentes instancias función genérica
 *
 * @param string $connection
 * @return mixed
 */
function getRutasGestionesCategories(string $connection): mixed
{
    return DB::connection($connection)
        ->table('categorias_gestiones')
        ->where('categoria_activa',true)
        ->whereNull('padre_id')
        ->orderBy('orden_menu','ASC')
        ->get();
}

/**
 * Obtener colección de rutas de las categorias de «gestiones» para las diferentes instancias función genérica
 *
 * @param string $connection
 * @return mixed
 */
function getRutasContenidosGestiones(string $connection): mixed
{
    $today = \Carbon\Carbon::now()->format("Y-m-d");
    $routes = DB::connection($connection)
        ->table('SEO_GESTIONES')
        ->leftJoin('gestiones','gestiones.SEO_id','=','SEO_GESTIONES.id')
        ->leftJoin('categorias_gestiones','gestiones.categoria_id','=','categorias_gestiones.id')
        ->select('SEO_GESTIONES.id','SEO_GESTIONES.url_amigable','SEO_GESTIONES.controlador','SEO_GESTIONES.funcion','SEO_GESTIONES.vista','SEO_GESTIONES.metabots','gestiones.titulo')
        ->where('gestiones.fecha_publicacion','<=',$today)->where(function ($query) use ($today) {$query->where('gestiones.fecha_expiracion','>=',$today)->orWhereNull('gestiones.fecha_expiracion');})
        ->where('categorias_gestiones.categoria_activa',true);
    if(env('APP_ENV') === "production")
    {
        $routes->where('SEO_GESTIONES.ruta_activa',true);
    }

    return $routes->orderBy('SEO_GESTIONES.id','ASC')->get();
}

/**
 * Obtener colección de redirecciones definidas en la respectiva BBDD
 *
 * @param string $type
 * @param string $instance
 * @return mixed
 */
function getRedirecciones(string $type, string $instance): array
{
    return DB::connection('common_event_log')->table('Z1_GLOBAL_REDIRECTIONS')->where('active',1)->where('content_type',$type)->where('instance',$instance)->get()->pluck('url_from')->toArray();
}

/**
 * Obtener el valor de una cookie
 *
 * @param string $cookie_name
 * @return string|null
 */
function getCookie(string $cookie_name): string|null
{
    return  empty($_COOKIE[$cookie_name])?null:$_COOKIE[$cookie_name];
}

/**
 * Generar el etiquetado dinámico hreflang por idiomas para la web
 * Si una página no es indexable, no serán necesarias etiquetas hreflang
 * Se mostrarán las etiquetas en los diferentes idiomas disponibles si la url tiene un homólogo, un contenido relacionado con el mismo nivel.
 * Para rutas del blog general mandamos un hreflang plano, resto depende de los idiomas disponibles
 *
 * @return array
 */
function getHreflangTags(): array
{
    $return = array();
    $route_prefix = Route::getCurrentRoute()->getPrefix();
    $current_domain_route = url()->current();
    $route_name = Route::currentRouteName();
    $available_prefixes = json_decode(env('AVAILABLE_PREFIXES'));
    $country_prefixes = json_decode(env('COUNTRIES_PREFIXES'));

    /*
        dd(
            $route_prefix,
            $current_domain_route,
            $route_name,
            $available_prefixes,
            $country_prefixes
        );
    */

    if(!empty($route_prefix) && is_string($route_prefix) && in_array('/'.$route_prefix.'/', $available_prefixes))
    {
        //Caso Home del Blog
        if ($route_name === "blog-home")
        {
            foreach($country_prefixes as $country_code)
            {
                $return[] ="<link rel='alternate' hreflang='es-".$country_code."' href='".url('/').'/'.$country_code."/ahorro/' />";
            }
        }
        //Rutas del «blog» a categorías
        elseif(str_contains($current_domain_route."/", '/ahorro/'))
        {
            /*
                Contemplamos dos casos:
                    1) Rutas en el ámbito de categoría
                    2) Rutas de contenidos/noticias del «blog»
            */
            //1) Rutas en el ámbito de categoría
            if(str_starts_with($route_name, 'ahorro/'))
            {
                $cat = explode("/", $current_domain_route);
                $cat = end($cat);

                //Definición de excepciones
                $top_visitas = array();
                foreach($country_prefixes as $country_code)
                {
                    $top_visitas[] = "<link rel='alternate' hreflang='es-".$country_code."' href='".url('/').'/'.$country_code."/ahorro/top-visitas/' />";
                }
                $mejores_ofertas = array(
                    "<link rel='alternate' hreflang='es-es' href='".url('/')."/es/ahorro/mejores-ofertas/' />",
                    "<link rel='alternate' hreflang='es-mx' href='".url('/')."/mx/ahorro/mejores-planes/' />"
                );
                $movil = array(
                    "<link rel='alternate' hreflang='es-es' href='".url('/')."/es/ahorro/movil/' />",
                    "<link rel='alternate' hreflang='es-mx' href='".url('/')."/mx/ahorro/celular/' />",
                    "<link rel='alternate' hreflang='es-pe' href='".url('/')."/pe/ahorro/movil/' />"
                );
                $tv = array(
                    "<link rel='alternate' hreflang='es-es' href='".url('/')."/es/ahorro/television/' />",
                    "<link rel='alternate' hreflang='es-mx' href='".url('/')."/mx/ahorro/television/' />",
                    "<link rel='alternate' hreflang='es-pe' href='".url('/')."/pe/ahorro/tv/' />"
                );
                $exception_cats = array(
                    "top-visitas" => $top_visitas,
                    "mejores-ofertas" => $mejores_ofertas,
                    "mejores-planes" => $mejores_ofertas,
                    "movil" => $movil,
                    "celular" => $movil,
                    "television" => $tv,
                    "tv" => $tv,
                );

                //Manejamos primero las excepciones definidas en el array de excepciones, motivado principalmente por la diferencia de nomenclatura de los nombres de las categorías en los diferentes países.
                if(in_array($cat, array_keys($exception_cats)))
                {
                    $return = $exception_cats[$cat];
                }
                else
                {
                    $cat_found = array();
                    foreach($country_prefixes as $country_code)
                    {
                        //Si estamos aquí es porque existen contenidos en esta categoría para esta instancia. Buscamos en las otras instancias a ver si tb existe.
                        if($country_code !== $route_prefix)
                        {
                            foreach (getRutasBlogCategories($country_code."_blog_program", null) as $cat_foreign)
                            {
                                if($cat_foreign->url_amigable === $cat)
                                {
                                    $cat_found[] = "<link rel='alternate' hreflang='es-".$country_code."' href='".url('/').'/'.$country_code."/ahorro/".$cat."/' />";
                                }
                            }
                        }
                    }
                    if(!empty($cat_found))
                    {
                        array_unshift($cat_found, "<link rel='alternate' hreflang='es-".$route_prefix."' href='".url('/').'/'.$route_prefix."/ahorro/".$cat."/' />");
                        $return = $cat_found;
                    }
                }
            }
            //2) Rutas de contenidos/noticias del «blog»
            else
            {
                $pretty_url = explode("/", $current_domain_route);
                $pretty_url = end($pretty_url);

                //Definición de excepciones
                $mejor_cobertura_movil =  array(
                    "<link rel='alternate' hreflang='es-es' href='".url('/')."/es/ahorro/movil/como-conseguir-la-mejor-cobertura-para-tu-movil/' />",
                    "<link rel='alternate' hreflang='es-pe' href='".url('/')."/pe/ahorro/movil/tips-para-mejorar-la-cobertura-de-tu-celular/' />"
                );
                $internet_lento = array(
                    "<link rel='alternate' hreflang='es-es' href='".url('/')."/es/ahorro/internet/internet-va-lento-causas-y-como-solucionarlo/' />",
                    "<link rel='alternate' hreflang='es-pe' href='".url('/')."/pe/ahorro/internet/por-que-mi-internet-va-lento/' />"
                );
                $velocidad_home_office = array(
                    "<link rel='alternate' hreflang='es-es' href='".url('/')."/es/ahorro/internet/mejor-velocidad-de-fibra-para-teletrabajar/' />",
                    "<link rel='alternate' hreflang='es-mx' href='".url('/')."/mx/ahorro/internet/mejor-velocidad-fibra-optica-home-office/' />"
                );
                $pretty_exceptions = array(
                    'como-conseguir-la-mejor-cobertura-para-tu-movil' => $mejor_cobertura_movil,
                    'tips-para-mejorar-la-cobertura-de-tu-celular' => $mejor_cobertura_movil,
                    'internet-va-lento-causas-y-como-solucionarlo' => $internet_lento,
                    'por-que-mi-internet-va-lento' => $internet_lento,
                    'mejor-velocidad-de-fibra-para-teletrabajar' => $velocidad_home_office,
                    'mejor-velocidad-fibra-optica-home-office' => $velocidad_home_office,
                );

                //Manejamos primero las excepciones definidas en el array de excepciones, motivado principalmente por la diferencia de nomenclatura de los nombres de las categorías en los diferentes países.
                if(in_array($pretty_url, array_keys($pretty_exceptions)))
                {
                    $return = $pretty_exceptions[$pretty_url];
                }
            }
        }
        //Rutas de la vertical de Gestiones
        elseif($route_name === "managements-home")
        {
            //Por desarrollar en un futuro si procede
        }
        elseif(str_contains($current_domain_route."/", '/gestiones/'))
        {
            //Por desarrollar en un futuro si procede
        }
        elseif(!empty($route_name) && !empty($country_prefixes) && $route_name==="home") //Rutas base de las instancias
        {
            foreach($country_prefixes as $country_code)
            {
                $return[] ="<link rel='alternate' hreflang='es-".$country_code."' href='".url('/').'/'.$country_code."/' />";
            }
            $return[] ="<link rel='alternate' hreflang ='x-default' href='".url('/')."/' />";
        }
        elseif(!empty($country_prefixes) && is_array($country_prefixes) && !empty($route_name) && in_array($route_prefix, $country_prefixes)) //Rutas de las diversas páginas existentes del comparador y noticias
        {
            //Definición de excepciones
            $internet_telefonia = array(
                "<link rel='alternate' hreflang='es-es' href='".url('/')."/es/internet-telefonia/' />",
                "<link rel='alternate' hreflang='es-mx' href='".url('/')."/mx/planes-celular-telefonia-internet-tv/' />",
            );
            $web_routes_exceptions = array(
                'internet-telefonia'  => $internet_telefonia,
                'planes-celular-telefonia-internet-tv'  => $internet_telefonia,
            );

            //Excepciones
            if(in_array($route_name, array_keys($web_routes_exceptions)))
            {
                $return = $web_routes_exceptions[$route_name];
            }
            //Resto de casos
            else
            {
                /* Comprobamos si existe alguna ruta homóloga de conversión. Creamos array con todas las rutas disponibles por países */
                $all_conversion_routes = array();
                foreach($country_prefixes as $country_code)
                {
                    if($country_code !== $route_prefix)
                    {
                        //Todas las rutas web de conversión
                        foreach(DB::connection($country_code.'_master_program')
                                    ->table('SEO')
                                    ->where('ruta_activa',true)
                                    ->orderBy('SEO.id','ASC')
                                    ->get()
                                    ->pluck('url_amigable') as $route)
                        {
                            $all_conversion_routes[] = Str::lower($country_code.'/'.$route);
                        }

                        //Todas las rutas de gestiones
                        foreach(DB::connection($country_code.'_master_program')
                                    ->table('WEB_3_GESTIONES')
                                    ->where('active',true)
                                    ->where('is_visible_in_gestiones',true)
                                    ->whereNotNull('father_id')
                                    ->orderBy('WEB_3_GESTIONES.id','ASC')
                                    ->get()
                                    ->pluck('url_amigable') as $route)
                        {
                            $all_conversion_routes[] = Str::lower($country_code.'/'.$route);
                        }
                    }
                }

                /* Guardamos las coincidencias */
                foreach ($country_prefixes as $country_code)
                {
                    if(in_array(Str::lower($country_code.'/'.$route_name), $all_conversion_routes))
                    {
                        $return[] = "<link rel='alternate' hreflang='es-".$country_code."' href='".url('/').'/'.Str::lower($country_code.'/'.$route_name)."/' />";
                    }
                }

                //Si existen coincidencias añadimos el «current»
                if(!empty($return))
                {
                    array_unshift($return, "<link rel='alternate' hreflang='es-".$route_prefix."' href='".url('/').'/'.$route_prefix.'/'.$route_name."/' />");
                }
            }
        }
    }

    //dd($return);
    return $return;
}

/**
 * Obtiene la correspondiente etiqueta «canonical» si está definida en las tablas SEO y 3_GESTIONES de master_program y blog_program
 * @param $conn
 * @param $instance
 * @param $route_id
 * @return mixed
 */
function getCanonica($conn, $instance, $route_id): mixed
{
    $url = $conn->table("SEO")->select('url_amigable','canonical','padre_id')->where('id',$route_id)->first();
    $base = implode("/", array(
        url('/'),
        $instance,
        ""
    ));

    switch ($url->canonical)
    {
        case null:
        case "":
            $return = null;
            break;

        case "this":
            $return = $base.$url->url_amigable."/";
            break;

        case "father":
            $return = $base.($conn->table("SEO")->where('id',$url->padre_id)->pluck('url_amigable')->first())."/";
            break;

        default:
            $return = $base.$url->canonical."/";
            break;
    }

    return $return;
}

/**
 * Calcular el código del país cuando estamos bajo «blog» comun /ahorro (sin prefijo definido por routing)
 * Intentamos obtener la preferencia de país dada por el prefijo de ruta. Si no existe o es diferente a un código de país (caso de prefijo de ruta /ahorro «blog» común), intentamos leer la cookie de preferencia de país.
 * Si la cookie no existe, se obtiene el código de país dado por Ip. Si falla o no está dentro de las instancias definidas y permitidas, se redirecciona a Web por defecto /es (España)
 * La variable global se define en el «MiddleWare» InstanceMiddleware
 *
 * @return string
 */
function decideCountry(): string
{
    $return = "es";
    if(isset($GLOBALS["country_instance"]))
    {
        $return = $GLOBALS["country_instance"];
    }
    else
    {
        registroDeErrores(3,"Funcion decideCountry()","No está definida la variable Global de País «GLOBALS['country_instance']». Se establece *es* por defecto");
    }
    return $return;
}

/**
 * Registro en nuestra BBDD de los «leads» recogidos de las diferentes compañías
 *
 * @param Request $request
 * @return string|null
 */
function leadRecordTelco(Request $request): ?string
{
    $data_lead = $request->dataToSend;
    $lead_id = null;
    $IP_data = checkingGuestLocationApi(false);
    $visitorIp = !empty($IP_data)?$IP_data->ip:"Sin datos desde IPAPI";
    $user_id = null;
    $country_code = null;
    $decideCountry = decideCountry();

    $conn = connexionDB("leads");
    $conn->beginTransaction();
    try
    {
        $registro_nuevo = false;
        $es_movil = true;
        $tel_usuario = str_replace(" ", "", $data_lead['tel_usuario']);
        if(in_array(Str::substr($tel_usuario, 0, 1), array(8,9), true))
        {
            $es_movil = false;
            $registro_nuevo = true;
        }
        elseif(!$conn->table('usuarios')->where('tlf_movil', $tel_usuario)->exists())
        {
            $registro_nuevo = true;
        }

        if($registro_nuevo) {
            $user = array();
            if ($es_movil) {
                $user['tlf_movil'] = $tel_usuario;
            } else {
                $user['tlf_fijo'] = $tel_usuario;
            }

            $user['nombre'] = (empty($data_lead['nombre_usuario']) || ($data_lead['nombre_usuario'] === "n/d")) ? null : $data_lead['nombre_usuario'];
            $user['email'] = (empty($data_lead['email'])) ? null : $data_lead['email'];
            $user['direccion'] = empty($data_lead['direccion_usuario']) ? null : $data_lead['direccion_usuario'];
            $user['poblacion'] = empty($data_lead['poblacion_usuario']) ? null : $data_lead['poblacion_usuario'];
            $user['provincia'] = empty($data_lead['provincia_usuario']) ? null : $data_lead['provincia_usuario'];
            $user['codigo_postal'] = empty($data_lead['cp_usuario']) ? null : $data_lead['cp_usuario'];
            if (isset($data_lead['acepta_comunicaciones_comerciales_kolondoo'])) {
                if (intval($data_lead['acepta_comunicaciones_comerciales_kolondoo']) === 1) {
                    $user['acepta_comunicaciones_comerciales'] = true;
                    $user['fecha_aceptacion_comunicaciones_comerciales'] = Carbon::now()->format("Y-m-d H:i:s");
                }
            }

            if(!empty($IP_data))
            {
                $country_code = !empty($IP_data->country_code)?$IP_data->country_code:null;
                $user['ip'] = !empty($IP_data->ip)?$IP_data->ip:null;
                $user['ip_type'] = !empty($IP_data->type)?$IP_data->type:null;
                $user['ip_nombre_continente'] = !empty($IP_data->continent_name)?$IP_data->continent_name:null;
                $user['ip_codigo_pais'] = !empty($IP_data->country_code)?$IP_data->country_code:null;
                $user['ip_nombre_pais'] = !empty($IP_data->country_name)?$IP_data->country_name:null;
                $user['ip_region'] = !empty($IP_data->region_name)?$IP_data->region_name:null;
                $user['ip_ciudad'] = !empty($IP_data->city)?$IP_data->city:null;
                $user['ip_codigo_postal'] = !empty($IP_data->zip)?$IP_data->zip:null;
                $user['ip_latitud'] = !empty($IP_data->longitude)?$IP_data->longitude:null;
                $user['ip_longitud'] = !empty($IP_data->latitude)?$IP_data->latitude:null;
            }

            $user_id = $conn->table('usuarios')->insertGetId($user);
        }
        else
        {
            $user_id = $conn->table('usuarios')->where('tlf_movil', $tel_usuario)->orderby('fecha_registro','DESC')->pluck('id')->first();
            if (isset($data_lead['acepta_comunicaciones_comerciales_kolondoo'])) {
                if(intval($data_lead['acepta_comunicaciones_comerciales_kolondoo']) === 1)
                {
                    $conn->table('usuarios')->where('id',$user_id)->update([
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
        $lead['tarifa'] = empty($data_lead['tarifa'])?"S/T":$data_lead['tarifa'];
        $lead['compania'] = $data_lead['compania'];
        $lead['tipo_formulario'] = $data_lead['tipo_formulario'];
        $lead['precio'] = empty($data_lead['precio'])?null:$data_lead['precio'];
        $lead['tv'] = empty($data_lead['tv'])?null:$data_lead['tv'];
        $lead['fijo'] = empty($data_lead['fijo'])?null:$data_lead['fijo'];
        $lead['fibra'] = empty($data_lead['fibra'])?null:$data_lead['fibra'];
        $lead['movil'] = empty($data_lead['movil'])?null:$data_lead['movil'];
        $lead['lineas_adicionales'] = empty($data_lead['lineas_adicionales'])?null:intval($data_lead['lineas_adicionales']);
        $lead['dato1'] = empty($data_lead['dato1'])?null:trim($data_lead['dato1']);
        $lead['dato2'] = empty($data_lead['dato2'])?null:trim($data_lead['dato2']);
        $lead['dato3'] = empty($data_lead['dato3'])?null:trim($data_lead['dato3']);
        $lead['dato4'] = empty($data_lead['dato4'])?null:trim($data_lead['dato4']);
        if($data_lead['tipo_conversion'] === "cpl")
        {
            $lead['acepta_cesion_datos_a_proveedor'] = intval($data_lead['acepta_cesion_datos_a_proveedor']) === 1;
            //Con «leads» de procedencia «Facebook» se aceptan las dos políticas implícitamente
            if($lead['producto'] === "FACEBOOK")
            {
                $lead['acepta_politica_privacidad_kolondoo'] = intval($data_lead['acepta_politica_privacidad_kolondoo']) === 1;
            }
        }
        elseif($data_lead['tipo_conversion'] === "cpa")
        {
            $lead['acepta_politica_privacidad_kolondoo'] = intval($data_lead['acepta_politica_privacidad_kolondoo']) === 1;
        }
        else
        {
            throw new Exception("ERROR en tipo de conversión en función leadRecordTelco(), tipo_conversion no es «cpl» ni «cpa» revisar tabla operadoras (".$data_lead['compania'].")");
        }

        //utm params
        $lead['utm_source'] = empty($request->utm_source)?null:$request->utm_source;
        $lead['utm_medium'] = empty($request->utm_medium)?null:$request->utm_medium;
        $lead['utm_campaign'] = empty($request->utm_campaign)?null:$request->utm_campaign;
        $lead['utm_content'] = empty($request->utm_content)?null:$request->utm_content;
        $lead['utm_term'] = empty($request->utm_term)?null:$request->utm_term;

        /*
            Reescribimos la procedencia del registro en caso de que estemos en campañas Emailing de Arkeero
            Deberán venir los valores utm_campaign=ark, utm_medium=email y utm_source={definición de campaña, por ejemplo naturgy101022} para que se reconozca como campaña de emailing de arkeero.
        */
        if(!empty($request->utm_source) && !empty($request->utm_medium) && !empty($request->utm_campaign) && Str::lower($request->utm_source) === "ark" && Str::lower($request->utm_medium) === "email")
        {
            $lead['producto'] = "EMAILING_RECORD_".Str::upper($request->utm_campaign);
        }

        $lead_id = $conn->table('telco')->insertGetId($lead);

        $conn->commit();
    }
    catch (\PDOException | \Exception $e)
    {
        $conn->rollback();
        $lead_id = null;
        $message = "Fallo al registrar el «lead» de *".($data_lead['compania'])."* en función leadRecordTelco(). - Ip: " . $visitorIp . ' - Fallo ocurrido: ' . $e->getMessage().", Línea: ".$e->getLine()." - Datos recibidos del «lead» en la función: ".json_encode($data_lead, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        registroDeErrores(11, 'Lead ERROR', $message,$country_code,$decideCountry);
    }

    return 'telco_'.(empty($lead_id)?'not_registered':$lead_id);
}

/**
 * Registro en nuestra BBDD de los «leads» recogidos de las diferentes compañías
 *
 * @param Request $request
 * @return string|null
 */
function leadRecordTelcoPeru(Request $request): ?string
{
    $data_lead = $request->dataToSend;
    $lead_id = null;
    $IP_data = checkingGuestLocationApi(false);
    $visitorIp = !empty($IP_data)?$IP_data->ip:"Sin datos desde IPAPI";
    $user_id = null;
    $country_code = null;
    $decideCountry = decideCountry();

    $conn = connexionDB("leads");
    $conn->beginTransaction();
    try
    {
        $registro_nuevo = false;
        $tel_usuario = str_replace(" ", "", $data_lead['tel_usuario']);
        if(!$conn->table('usuarios')->where('tel_usuario', $tel_usuario)->exists())
        {
            $registro_nuevo = true;
        }

        if($registro_nuevo)
        {
            $user = array();
            $user['tel_usuario'] = $tel_usuario;
            $user['nombre'] = (empty($data_lead['nombre_usuario']) || ($data_lead['nombre_usuario'] === "n/d"))?null:$data_lead['nombre_usuario'];
            $user['email'] = (empty($data_lead['email']))?null:$data_lead['email'];
            $user['direccion'] = empty($data_lead['direccion_usuario'])?null:$data_lead['direccion_usuario'];
            $user['poblacion'] = empty($data_lead['poblacion_usuario'])?null:$data_lead['poblacion_usuario'];
            $user['provincia'] = empty($data_lead['provincia_usuario'])?null:$data_lead['provincia_usuario'];
            $user['codigo_postal'] = empty($data_lead['cp_usuario'])?null:$data_lead['cp_usuario'];
            if(intval($data_lead['acepta_comunicaciones_comerciales_kolondoo']) === 1)
            {
                $user['acepta_comunicaciones_comerciales'] = true;
                $user['fecha_aceptacion_comunicaciones_comerciales'] = Carbon::now()->format("Y-m-d H:i:s");
            }

            if(!empty($IP_data))
            {
                $country_code = !empty($IP_data->country_code)?$IP_data->country_code:null;
                $user['ip'] = !empty($IP_data->ip)?$IP_data->ip:null;
                $user['ip_type'] = !empty($IP_data->type)?$IP_data->type:null;
                $user['ip_nombre_continente'] = !empty($IP_data->continent_name)?$IP_data->continent_name:null;
                $user['ip_codigo_pais'] = !empty($IP_data->country_code)?$IP_data->country_code:null;
                $user['ip_nombre_pais'] = !empty($IP_data->country_name)?$IP_data->country_name:null;
                $user['ip_region'] = !empty($IP_data->region_name)?$IP_data->region_name:null;
                $user['ip_ciudad'] = !empty($IP_data->city)?$IP_data->city:null;
                $user['ip_codigo_postal'] = !empty($IP_data->zip)?$IP_data->zip:null;
                $user['ip_latitud'] = !empty($IP_data->longitude)?$IP_data->longitude:null;
                $user['ip_longitud'] = !empty($IP_data->latitude)?$IP_data->latitude:null;
            }

            $user_id = $conn->table('usuarios')->insertGetId($user);
        }
        else
        {
            $user_id = $conn->table('usuarios')->where('tel_usuario', $tel_usuario)->orderby('fecha_registro','DESC')->pluck('id')->first();
            if(intval($data_lead['acepta_comunicaciones_comerciales_kolondoo']) === 1)
            {
                $conn->table('usuarios')->where('id',$user_id)->update([
                    'acepta_comunicaciones_comerciales' => true,
                    'fecha_aceptacion_comunicaciones_comerciales' => Carbon::now()->format("Y-m-d H:i:s")
                ]);
            }
        }

        //Inserción del lead
        $lead = array();
        $lead['usuario_id'] = $user_id;
        $lead['producto'] = $data_lead['producto'];
        $lead['tipo_conversion'] = $data_lead['tipo_conversion'];
        $lead['tarifa'] = empty($data_lead['tarifa'])?null:$data_lead['tarifa'];
        $lead['compania'] = empty($data_lead['compania'])?null:$data_lead['compania'];
        $lead['tipo_formulario'] = empty($data_lead['tipo_formulario'])?null:$data_lead['tipo_formulario'];
        $lead['precio'] = empty($data_lead['precio'])?null:$data_lead['precio'];
        $lead['tv'] = empty($data_lead['tv'])?null:$data_lead['tv'];
        $lead['fijo'] = empty($data_lead['fijo'])?null:$data_lead['fijo'];
        $lead['fibra'] = empty($data_lead['fibra'])?null:$data_lead['fibra'];
        $lead['movil'] = empty($data_lead['movil'])?null:$data_lead['movil'];
        $lead['dato1'] = empty($data_lead['dato1'])?null:trim($data_lead['dato1']);
        $lead['dato2'] = empty($data_lead['dato2'])?null:trim($data_lead['dato2']);
        $lead['dato3'] = empty($data_lead['dato3'])?null:trim($data_lead['dato3']);
        $lead['dato4'] = empty($data_lead['dato4'])?null:trim($data_lead['dato4']);

        //Fecha de Lima/Perú
        date_default_timezone_set('America/Lima');
        $lead['fecha_lead'] = (new \DateTime())->format('Y-m-d H:i:s');

        if($data_lead['tipo_conversion'] === "cpl")
        {
            $lead['acepta_cesion_datos_a_proveedor'] = intval($data_lead['acepta_cesion_datos_a_proveedor']) === 1;
            //Con «leads» de procedencia «Facebook» se aceptan las dos políticas implícitamente
            if($lead['producto'] === "FACEBOOK")
            {
                $lead['acepta_politica_privacidad_kolondoo'] = intval($data_lead['acepta_politica_privacidad_kolondoo']) === 1;
            }
        }
        elseif($data_lead['tipo_conversion'] === "cpa")
        {
            $lead['acepta_politica_privacidad_kolondoo'] = intval($data_lead['acepta_politica_privacidad_kolondoo']) === 1;
        }
        else
        {
            throw new Exception("ERROR en tipo de conversión en función leadRecordTelcoPeru(), tipo_conversion no es «cpl» ni «cpa» revisar tabla operadoras (".$data_lead['compania'].")");
        }

        //utm params
        $lead['utm_source'] = empty($request->utm_source)?null:$request->utm_source;
        $lead['utm_medium'] = empty($request->utm_medium)?null:$request->utm_medium;
        $lead['utm_campaign'] = empty($request->utm_campaign)?null:$request->utm_campaign;
        $lead['utm_content'] = empty($request->utm_content)?null:$request->utm_content;
        $lead['utm_term'] = empty($request->utm_term)?null:$request->utm_term;

        $lead_id = $conn->table('telco')->insertGetId($lead);

        $conn->commit();
    }
    catch (\PDOException | \Exception $e)
    {
        $conn->rollback();
        $lead_id = null;
        $message = "Fallo al registrar el «lead» de *".($data_lead['compania'])."* en función leadRecordTelcoPeru(). - Ip: " . $visitorIp . ' - Fallo ocurrido: ' . $e->getMessage()." - Datos recibidos del «lead» en la función: ".json_encode($data_lead, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        registroDeErrores(11, 'Lead ERROR', $message,$country_code,$decideCountry);
    }

    return 'telco_'.(empty($lead_id)?'not_registered':$lead_id);
}

/**
 * Registro en nuestra BBDD de los «leads» recogidos de las diferentes compañías
 *
 * @param Request $request
 * @return string|null
 */
function leadRecordTelcoEcuador(Request $request): ?string
{
    $data_lead = $request->dataToSend;
    $lead_id = null;
    $IP_data = checkingGuestLocationApi(false);
    $visitorIp = !empty($IP_data)?$IP_data->ip:"Sin datos desde IPAPI";
    $user_id = null;
    $country_code = null;
    $decideCountry = decideCountry();

    $conn = connexionDB("leads");
    $conn->beginTransaction();
    try
    {
        $registro_nuevo = false;
        $tel_usuario = str_replace(" ", "", $data_lead['tel_usuario']);
        if(!$conn->table('usuarios')->where('tel_usuario', $tel_usuario)->exists())
        {
            $registro_nuevo = true;
        }

        if($registro_nuevo)
        {
            $user = array();
            $user['tel_usuario'] = $tel_usuario;
            $user['nombre'] = (empty($data_lead['nombre_usuario']) || ($data_lead['nombre_usuario'] === "n/d"))?null:$data_lead['nombre_usuario'];
            $user['email'] = (empty($data_lead['email']))?null:$data_lead['email'];
            $user['direccion_principal'] = empty($data_lead['direccion_principal'])?null:$data_lead['direccion_principal'];
            $user['direccion_secundaria'] = empty($data_lead['direccion_secundaria'])?null:$data_lead['direccion_secundaria'];
            $user['provincia'] = empty($data_lead['provincia'])?null:$data_lead['provincia'];
            $user['canton'] = empty($data_lead['canton'])?null:$data_lead['canton'];
            $user['parroquia'] = empty($data_lead['parroquia'])?null:$data_lead['parroquia'];
            $user['sector'] = empty($data_lead['sector'])?null:$data_lead['sector'];
            $user['codigo_postal'] = empty($data_lead['codigo_postal'])?null:$data_lead['codigo_postal'];
            if(intval($data_lead['acepta_comunicaciones_comerciales_kolondoo']) === 1)
            {
                $user['acepta_comunicaciones_comerciales'] = true;
                $user['fecha_aceptacion_comunicaciones_comerciales'] = Carbon::now()->format("Y-m-d H:i:s");
            }

            if(!empty($IP_data))
            {
                $country_code = !empty($IP_data->country_code)?$IP_data->country_code:null;
                $user['ip'] = !empty($IP_data->ip)?$IP_data->ip:null;
                $user['ip_type'] = !empty($IP_data->type)?$IP_data->type:null;
                $user['ip_nombre_continente'] = !empty($IP_data->continent_name)?$IP_data->continent_name:null;
                $user['ip_codigo_pais'] = !empty($IP_data->country_code)?$IP_data->country_code:null;
                $user['ip_nombre_pais'] = !empty($IP_data->country_name)?$IP_data->country_name:null;
                $user['ip_region'] = !empty($IP_data->region_name)?$IP_data->region_name:null;
                $user['ip_ciudad'] = !empty($IP_data->city)?$IP_data->city:null;
                $user['ip_codigo_postal'] = !empty($IP_data->zip)?$IP_data->zip:null;
                $user['ip_latitud'] = !empty($IP_data->longitude)?$IP_data->longitude:null;
                $user['ip_longitud'] = !empty($IP_data->latitude)?$IP_data->latitude:null;
            }

            $user_id = $conn->table('usuarios')->insertGetId($user);
        }
        else
        {
            $user_id = $conn->table('usuarios')->where('tel_usuario', $tel_usuario)->orderby('fecha_registro','DESC')->pluck('id')->first();
            if(intval($data_lead['acepta_comunicaciones_comerciales_kolondoo']) === 1)
            {
                $conn->table('usuarios')->where('id',$user_id)->update([
                    'acepta_comunicaciones_comerciales' => true,
                    'fecha_aceptacion_comunicaciones_comerciales' => Carbon::now()->format("Y-m-d H:i:s")
                ]);
            }
        }

        //Inserción del lead
        $lead = array();
        $lead['usuario_id'] = $user_id;
        $lead['producto'] = $data_lead['producto'];
        $lead['tipo_conversion'] = $data_lead['tipo_conversion'];
        $lead['plan'] = $data_lead['plan'];
        $lead['compania'] = $data_lead['compania'];
        $lead['tipo_formulario'] = $data_lead['tipo_formulario'];
        $lead['precio'] = empty($data_lead['precio'])?null:$data_lead['precio'];
        $lead['tv'] = empty($data_lead['tv'])?null:$data_lead['tv'];
        $lead['fijo'] = empty($data_lead['fijo'])?null:$data_lead['fijo'];
        $lead['fibra'] = empty($data_lead['fibra'])?null:$data_lead['fibra'];
        $lead['movil'] = empty($data_lead['movil'])?null:$data_lead['movil'];
        $lead['dato1'] = empty($data_lead['dato1'])?null:trim($data_lead['dato1']);
        $lead['dato2'] = empty($data_lead['dato2'])?null:trim($data_lead['dato2']);
        $lead['dato3'] = empty($data_lead['dato3'])?null:trim($data_lead['dato3']);
        $lead['dato4'] = empty($data_lead['dato4'])?null:trim($data_lead['dato4']);

        //Fecha de Ecuador GTM -5
        date_default_timezone_set('America/Guayaquil');
        $lead['fecha_lead'] = (new \DateTime())->format('Y-m-d H:i:s');

        if($data_lead['tipo_conversion'] === "cpl")
        {
            $lead['acepta_cesion_datos_a_proveedor'] = intval($data_lead['acepta_cesion_datos_a_proveedor']) === 1;
        }
        elseif($data_lead['tipo_conversion'] === "cpa")
        {
            $lead['acepta_politica_privacidad_kolondoo'] = intval($data_lead['acepta_politica_privacidad_kolondoo']) === 1;
        }
        else
        {
            throw new Exception("ERROR en tipo de conversión en función leadRecordTelcoEcuador(), tipo_conversion no es «cpl» ni «cpa» revisar tabla operadoras (".$data_lead['compania'].")");
        }

        //utm params
        $lead['utm_source'] = empty($request->utm_source)?null:$request->utm_source;
        $lead['utm_medium'] = empty($request->utm_medium)?null:$request->utm_medium;
        $lead['utm_campaign'] = empty($request->utm_campaign)?null:$request->utm_campaign;
        $lead['utm_content'] = empty($request->utm_content)?null:$request->utm_content;
        $lead['utm_term'] = empty($request->utm_term)?null:$request->utm_term;

        $lead_id = $conn->table('telco')->insertGetId($lead);

        $conn->commit();
    }
    catch (\PDOException | \Exception $e)
    {
        $conn->rollback();
        $lead_id = null;
        $message = "Fallo al registrar el «lead» de *".($data_lead['compania'])."* en función leadRecordTelcoEcuador(). - Ip: " . $visitorIp . ' - Fallo ocurrido: ' . $e->getMessage()." - Datos recibidos del «lead» en la función: ".json_encode($data_lead, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        registroDeErrores(11, 'Lead ERROR', $message,$country_code,$decideCountry);
    }

    return 'telco_'.(empty($lead_id)?'not_registered':$lead_id);
}

/**
 * Registro en nuestra BBDD de los «leads» recogidos de las diferentes compañías
 *
 * @param Request $request
 * @return string|null
 */
function leadRecordEnergy(Request $request): ?string
{
    $data_lead = $request->dataToSend;
    $lead_id = null;
    $IP_data = checkingGuestLocationApi(false);
    $visitorIp = !empty($IP_data)?$IP_data->ip:"Sin datos desde IPAPI";
    $user_id = null;
    $country_code = null;
    $decideCountry = decideCountry();

    $conn = connexionDB("leads");
    $conn->beginTransaction();
    try
    {
        $registro_nuevo = false;
        $es_movil = true;
        $tel_usuario = str_replace(" ", "", $data_lead['tel_usuario']);
        if(in_array(Str::substr($data_lead['tel_usuario'], 0, 1), array(8,9), true))
        {
            $es_movil = false;
            $registro_nuevo = true;
        }
        elseif(!$conn->table('usuarios')->where('tlf_movil', $tel_usuario)->exists())
        {
            $registro_nuevo = true;
        }

        if($registro_nuevo)
        {
            $user = array();
            if($es_movil)
            {
                $user['tlf_movil'] = $tel_usuario;
            }
            else
            {
                $user['tlf_fijo'] = $tel_usuario;
            }
            $user['nombre'] = (empty($data_lead['nombre_usuario']) || ($data_lead['nombre_usuario'] === "n/d"))?null:$data_lead['nombre_usuario'];
            $user['email'] = (empty($data_lead['email']))?null:$data_lead['email'];
            $user['direccion'] = empty($data_lead['direccion_usuario'])?null:$data_lead['direccion_usuario'];
            $user['poblacion'] = empty($data_lead['poblacion_usuario'])?null:$data_lead['poblacion_usuario'];
            $user['provincia'] = empty($data_lead['provincia_usuario'])?null:$data_lead['provincia_usuario'];
            $user['codigo_postal'] = empty($data_lead['cp_usuario'])?null:$data_lead['cp_usuario'];
            if(intval($data_lead['acepta_comunicaciones_comerciales_kolondoo']) === 1)
            {
                $user['acepta_comunicaciones_comerciales'] = true;
                $user['fecha_aceptacion_comunicaciones_comerciales'] = Carbon::now()->format("Y-m-d H:i:s");
            }

            if(!empty($IP_data))
            {
                $country_code = !empty($IP_data->country_code)?$IP_data->country_code:null;
                $user['ip'] = !empty($IP_data->ip)?$IP_data->ip:null;
                $user['ip_type'] = !empty($IP_data->type)?$IP_data->type:null;
                $user['ip_nombre_continente'] = !empty($IP_data->continent_name)?$IP_data->continent_name:null;
                $user['ip_codigo_pais'] = !empty($IP_data->country_code)?$IP_data->country_code:null;
                $user['ip_nombre_pais'] = !empty($IP_data->country_name)?$IP_data->country_name:null;
                $user['ip_region'] = !empty($IP_data->region_name)?$IP_data->region_name:null;
                $user['ip_ciudad'] = !empty($IP_data->city)?$IP_data->city:null;
                $user['ip_codigo_postal'] = !empty($IP_data->zip)?$IP_data->zip:null;
                $user['ip_latitud'] = !empty($IP_data->longitude)?$IP_data->longitude:null;
                $user['ip_longitud'] = !empty($IP_data->latitude)?$IP_data->latitude:null;
            }

            $user_id = $conn->table('usuarios')->insertGetId($user);
        }
        else
        {
            $user_id = $conn->table('usuarios')->where('tlf_movil', $tel_usuario)->orderby('fecha_registro','DESC')->pluck('id')->first();
            if(intval($data_lead['acepta_comunicaciones_comerciales_kolondoo']) === 1)
            {
                $conn->table('usuarios')->where('id',$user_id)->update([
                    'acepta_comunicaciones_comerciales' => true,
                    'fecha_aceptacion_comunicaciones_comerciales' => Carbon::now()->format("Y-m-d H:i:s")
                ]);
            }
        }

        //Inserción del lead
        $lead = array();
        $lead['usuario_id'] = $user_id;
        $lead['producto'] = $data_lead['producto'];
        $lead['tipo_conversion'] = $data_lead['tipo_conversion'];
        $lead['tarifa'] = $data_lead['tarifa'];
        $lead['compania'] = $data_lead['compania'];
        $lead['tipo_formulario'] = $data_lead['tipo_formulario'];
        $lead['precio'] = empty($data_lead['precio'])?null:$data_lead['precio'];
        $lead['preferencia_de_consumo'] = empty($data_lead['consumo'])?null:$data_lead['consumo'];
        $lead['preferencia_de_pago_luz'] = empty($data_lead['pagar_luz'])?null:$data_lead['pagar_luz'];
        $lead['energia_verde'] = null;
        if(isset($data_lead['luz_verde']))
        {
            $lead['energia_verde'] = $data_lead['luz_verde'];
        }
        $lead['maximo_ahorro'] = null;
        if(isset($data_lead['maximo_ahorro']))
        {
            $lead['maximo_ahorro'] = $data_lead['maximo_ahorro'];
        }
        $lead['tengo_gas'] = null;
        if(isset($data_lead['tengo_gas']))
        {
            $lead['tengo_gas'] = $data_lead['tengo_gas'];
        }
        $lead['tengo_luz'] = null;
        if(isset($data_lead['tengo_luz']))
        {
            $lead['tengo_luz'] = $data_lead['tengo_luz'];
        }
        $lead['dato1'] = empty($data_lead['dato1'])?null:trim($data_lead['dato1']);
        $lead['dato2'] = empty($data_lead['dato2'])?null:trim($data_lead['dato2']);
        $lead['dato3'] = empty($data_lead['dato3'])?null:trim($data_lead['dato3']);
        $lead['dato4'] = empty($data_lead['dato4'])?null:trim($data_lead['dato4']);
        if($data_lead['tipo_conversion'] === "cpl")
        {
            $lead['acepta_cesion_datos_a_proveedor'] = intval($data_lead['acepta_cesion_datos_a_proveedor']) === 1;
        }
        elseif($data_lead['tipo_conversion'] === "cpa")
        {
            $lead['acepta_politica_privacidad_kolondoo'] = intval($data_lead['acepta_politica_privacidad_kolondoo']) === 1;
        }
        else
        {
            throw new Exception("ERROR en tipo de conversión en función leadRecordEnergy(), tipo_conversion no es «cpl» ni «cpa» revisar tabla operadoras (".$data_lead['compania'].")");
        }

        //utm params
        $lead['utm_source'] = empty($request->utm_source)?null:$request->utm_source;
        $lead['utm_medium'] = empty($request->utm_medium)?null:$request->utm_medium;
        $lead['utm_campaign'] = empty($request->utm_campaign)?null:$request->utm_campaign;
        $lead['utm_content'] = empty($request->utm_content)?null:$request->utm_content;
        $lead['utm_term'] = empty($request->utm_term)?null:$request->utm_term;

        /*
            Reescribimos la procedencia del registro en caso de que estemos en campañas Emailing de Arkeero
            Deberán venir los valores utm_campaign=ark, utm_medium=email y utm_source={definición de campaña, por ejemplo naturgy101022} para que se reconozca como campaña de emailing de arkeero.
        */
        if(!empty($request->utm_source) && !empty($request->utm_medium) && !empty($request->utm_campaign) && Str::lower($request->utm_source) === "ark" && Str::lower($request->utm_medium) === "email")
        {
            $lead['producto'] = "EMAILING_RECORD_".Str::upper($request->utm_campaign);
        }

        $lead_id = $conn->table('energia')->insertGetId($lead);

        $conn->commit();
    }
    catch (\PDOException | \Exception $e)
    {
        $conn->rollback();
        $lead_id = null;
        $message = "Fallo al registrar el «lead» de *".($data_lead['compania'])."* en función leadRecordEnergy(). - Ip: " . $visitorIp . ' - Fallo ocurrido: ' . $e->getMessage()." - Datos recibidos del «lead» en la función: ".json_encode($data_lead, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        registroDeErrores(11, 'Lead ERROR', $message,$country_code,$decideCountry);
    }

    return 'energia_'.(empty($lead_id)?'not_registered':$lead_id);
}

/**
 * Registro en nuestra BBDD de los «leads» recogidos de alertas
 *
 * @param Request $request
 * @return string|null
 */
function leadRecordAlarm(Request $request): ?string
{
    $data_lead = $request->dataToSend;
    $lead_id = null;
    $IP_data = checkingGuestLocationApi(false);
    $visitorIp = !empty($IP_data)?$IP_data->ip:"Sin datos desde IPAPI";
    $user_id = null;
    $country_code = null;
    $decideCountry = decideCountry();

    $conn = connexionDB("leads");
    $conn->beginTransaction();
    try
    {
        $registro_nuevo = false;
        $es_movil = true;
        $tel_usuario = str_replace(" ", "", $data_lead['tel_usuario']);
        if(in_array(Str::substr($data_lead['tel_usuario'], 0, 1), array(8,9), true))
        {
            $es_movil = false;
            $registro_nuevo = true;
        }
        elseif(!$conn->table('usuarios')->where('tlf_movil', $tel_usuario)->exists())
        {
            $registro_nuevo = true;
        }

        if($registro_nuevo)
        {
            $user = array();
            if($es_movil)
            {
                $user['tlf_movil'] = $tel_usuario;
            }
            else
            {
                $user['tlf_fijo'] = $tel_usuario;
            }
            $user['nombre'] = (empty($data_lead['nombre'])) ? null : $data_lead['nombre'];
            $user['apellidos'] = ((empty($data_lead['apellidos']))) ? null : $data_lead['apellidos'];
            $user['email'] = (empty($data_lead['email']))?null:$data_lead['email'];
            $user['direccion'] = empty($data_lead['direccion_usuario'])?null:$data_lead['direccion_usuario'];
            $user['poblacion'] = empty($data_lead['poblacion_usuario'])?null:$data_lead['poblacion_usuario'];
            $user['provincia'] = empty($data_lead['provincia_usuario'])?null:$data_lead['provincia_usuario'];
            $user['codigo_postal'] = empty($data_lead['cp_usuario'])?null:$data_lead['cp_usuario'];
            if(intval($data_lead['acepta_comunicaciones_comerciales_kolondoo']) === 1)
            {
                $user['acepta_comunicaciones_comerciales'] = true;
                $user['fecha_aceptacion_comunicaciones_comerciales'] = Carbon::now()->format("Y-m-d H:i:s");
            }

            if(!empty($IP_data))
            {
                $country_code = !empty($IP_data->country_code)?$IP_data->country_code:null;
                $user['ip'] = !empty($IP_data->ip)?$IP_data->ip:null;
                $user['ip_type'] = !empty($IP_data->type)?$IP_data->type:null;
                $user['ip_nombre_continente'] = !empty($IP_data->continent_name)?$IP_data->continent_name:null;
                $user['ip_codigo_pais'] = !empty($IP_data->country_code)?$IP_data->country_code:null;
                $user['ip_nombre_pais'] = !empty($IP_data->country_name)?$IP_data->country_name:null;
                $user['ip_region'] = !empty($IP_data->region_name)?$IP_data->region_name:null;
                $user['ip_ciudad'] = !empty($IP_data->city)?$IP_data->city:null;
                $user['ip_codigo_postal'] = !empty($IP_data->zip)?$IP_data->zip:null;
                $user['ip_latitud'] = !empty($IP_data->longitude)?$IP_data->longitude:null;
                $user['ip_longitud'] = !empty($IP_data->latitude)?$IP_data->latitude:null;
            }

            $user_id = $conn->table('usuarios')->insertGetId($user);
        }
        else
        {
            $user_id = $conn->table('usuarios')->where('tlf_movil', $tel_usuario)->orderby('fecha_registro','DESC')->pluck('id')->first();
            if(intval($data_lead['acepta_comunicaciones_comerciales_kolondoo']) === 1)
            {
                $conn->table('usuarios')->where('id',$user_id)->update([
                    'acepta_comunicaciones_comerciales' => true,
                    'fecha_aceptacion_comunicaciones_comerciales' => Carbon::now()->format("Y-m-d H:i:s")
                ]);
            }
        }

        //Inserción del lead
        $lead = array();
        $lead['usuario_id'] = $user_id;
        $lead['producto'] = $data_lead['producto'];
        $lead['tipo_conversion'] = $data_lead['tipo_conversion'];
        $lead['tarifa'] = $data_lead['tarifa'];
        $lead['compania'] = $data_lead['compania'];
        $lead['tipo_formulario'] = $data_lead['tipo_formulario'];
        $lead['precio'] = empty($data_lead['precio'])?null:$data_lead['precio'];
        /* $lead['preferencia_de_consumo'] = empty($data_lead['consumo'])?null:$data_lead['consumo'];
        $lead['preferencia_de_pago_luz'] = empty($data_lead['pagar_luz'])?null:$data_lead['pagar_luz'];
        $lead['energia_verde'] = null;
        if(isset($data_lead['luz_verde']))
        {
            $lead['energia_verde'] = $data_lead['luz_verde'];
        }
        $lead['maximo_ahorro'] = null;
        if(isset($data_lead['maximo_ahorro']))
        {
            $lead['maximo_ahorro'] = $data_lead['maximo_ahorro'];
        }
        $lead['tengo_gas'] = null;
        if(isset($data_lead['tengo_gas']))
        {
            $lead['tengo_gas'] = $data_lead['tengo_gas'];
        }
        $lead['tengo_luz'] = null;
        if(isset($data_lead['tengo_luz']))
        {
            $lead['tengo_luz'] = $data_lead['tengo_luz'];
        }*/
        $lead['dato1'] = empty($data_lead['dato1'])?null:trim($data_lead['dato1']);
        $lead['dato2'] = empty($data_lead['dato2'])?null:trim($data_lead['dato2']);
        $lead['dato3'] = empty($data_lead['dato3'])?null:trim($data_lead['dato3']);
        $lead['dato4'] = empty($data_lead['dato4'])?null:trim($data_lead['dato4']);
        if($data_lead['tipo_conversion'] === "cpl")
        {
            $lead['acepta_cesion_datos_a_proveedor'] = intval($data_lead['acepta_cesion_datos_a_proveedor']) === 1;
        }
        elseif($data_lead['tipo_conversion'] === "cpa")
        {
            $lead['acepta_politica_privacidad_kolondoo'] = intval($data_lead['acepta_politica_privacidad_kolondoo']) === 1;
        }
        else
        {
            throw new Exception("ERROR en tipo de conversión en función leadRecordAlarm(), tipo_conversion no es «cpl» ni «cpa» revisar tabla operadoras (".$data_lead['compania'].")");
        }

        //utm params
        $lead['utm_source'] = empty($request->utm_source)?null:$request->utm_source;
        $lead['utm_medium'] = empty($request->utm_medium)?null:$request->utm_medium;
        $lead['utm_campaign'] = empty($request->utm_campaign)?null:$request->utm_campaign;
        $lead['utm_content'] = empty($request->utm_content)?null:$request->utm_content;
        $lead['utm_term'] = empty($request->utm_term)?null:$request->utm_term;

        /*
            Reescribimos la procedencia del registro en caso de que estemos en campañas Emailing de Arkeero
            Deberán venir los valores utm_campaign=ark, utm_medium=email y utm_source={definición de campaña, por ejemplo naturgy101022} para que se reconozca como campaña de emailing de arkeero.
        */
        if(!empty($request->utm_source) && !empty($request->utm_medium) && !empty($request->utm_campaign) && Str::lower($request->utm_source) === "ark" && Str::lower($request->utm_medium) === "email")
        {
            $lead['producto'] = "EMAILING_RECORD_".Str::upper($request->utm_campaign);
        }

        $lead_id = $conn->table('alarmas')->insertGetId($lead);

        $conn->commit();
    }
    catch (\PDOException | \Exception $e)
    {
        $conn->rollback();
        $lead_id = null;
        $message = "Fallo al registrar el «lead» de *".($data_lead['compania'])."* en función leadRecordAlarm(). - Ip: " . $visitorIp . ' - Fallo ocurrido: ' . $e->getMessage()." - Datos recibidos del «lead» en la función: ".json_encode($data_lead, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        registroDeErrores(11, 'Lead ERROR', $message,$country_code,$decideCountry);
    }

    return 'alarma_'.(empty($lead_id)?'not_registered':$lead_id);
}

/**
 * Registro en nuestra BBDD de los «leads» recogidos para seguros de Decesos.
 *
 * @param Request $request
 * @return string|null
 */
function leadRecordDecesos(Request $request): ?string
{
    $data_lead = $request->dataToSend;
    $lead_id = null;
    $IP_data = checkingGuestLocationApi(false);
    $visitorIp = !empty($IP_data)?$IP_data->ip:"Sin datos desde IPAPI";
    $user_id = null;
    $country_code = null;
    $decideCountry = decideCountry();

    $conn = connexionDB("leads");
    $conn->beginTransaction();
    try
    {
        $registro_nuevo = false;
        $es_movil = true;
        $tel_usuario = str_replace(" ", "", $data_lead['tel_usuario']);
        if(in_array(Str::substr($data_lead['tel_usuario'], 0, 1), array(8,9), true))
        {
            $es_movil = false;
            $registro_nuevo = true;
        }
        elseif(!$conn->table('usuarios')->where('tlf_movil', $tel_usuario)->exists())
        {
            $registro_nuevo = true;
        }

        if($registro_nuevo)
        {
            $user = array();
            if($es_movil)
            {
                $user['tlf_movil'] = $tel_usuario;
            }
            else
            {
                $user['tlf_fijo'] = $tel_usuario;
            }
            $user['nombre'] = (empty($data_lead['nombre_usuario']) || ($data_lead['nombre_usuario'] === "n/d"))?null:$data_lead['nombre_usuario'];
            $user['email'] = (empty($data_lead['email']))?null:$data_lead['email'];
            $user['direccion'] = empty($data_lead['direccion_usuario'])?null:$data_lead['direccion_usuario'];
            $user['poblacion'] = empty($data_lead['poblacion_usuario'])?null:$data_lead['poblacion_usuario'];
            $user['provincia'] = empty($data_lead['provincia_usuario'])?null:$data_lead['provincia_usuario'];
            $user['codigo_postal'] = empty($data_lead['cp_usuario'])?null:$data_lead['cp_usuario'];
            if(intval($data_lead['acepta_comunicaciones_comerciales_kolondoo']) === 1)
            {
                $user['acepta_comunicaciones_comerciales'] = true;
                $user['fecha_aceptacion_comunicaciones_comerciales'] = Carbon::now()->format("Y-m-d H:i:s");
            }

            if(!empty($IP_data))
            {
                $country_code = !empty($IP_data->country_code)?$IP_data->country_code:null;
                $user['ip'] = !empty($IP_data->ip)?$IP_data->ip:null;
                $user['ip_type'] = !empty($IP_data->type)?$IP_data->type:null;
                $user['ip_nombre_continente'] = !empty($IP_data->continent_name)?$IP_data->continent_name:null;
                $user['ip_codigo_pais'] = !empty($IP_data->country_code)?$IP_data->country_code:null;
                $user['ip_nombre_pais'] = !empty($IP_data->country_name)?$IP_data->country_name:null;
                $user['ip_region'] = !empty($IP_data->region_name)?$IP_data->region_name:null;
                $user['ip_ciudad'] = !empty($IP_data->city)?$IP_data->city:null;
                $user['ip_codigo_postal'] = !empty($IP_data->zip)?$IP_data->zip:null;
                $user['ip_latitud'] = !empty($IP_data->longitude)?$IP_data->longitude:null;
                $user['ip_longitud'] = !empty($IP_data->latitude)?$IP_data->latitude:null;
            }

            $user_id = $conn->table('usuarios')->insertGetId($user);
        }
        else
        {
            $user_id = $conn->table('usuarios')->where('tlf_movil', $tel_usuario)->orderby('fecha_registro','DESC')->pluck('id')->first();
            if(intval($data_lead['acepta_comunicaciones_comerciales_kolondoo']) === 1)
            {
                $conn->table('usuarios')->where('id',$user_id)->update([
                    'acepta_comunicaciones_comerciales' => true,
                    'fecha_aceptacion_comunicaciones_comerciales' => Carbon::now()->format("Y-m-d H:i:s")
                ]);
            }
        }

        //Inserción del lead
        $lead = array();
        $lead['usuario_id'] = $user_id;
        $lead['producto'] = $data_lead['producto'];
        $lead['tipo_conversion'] = $data_lead['tipo_conversion'];
        $lead['tarifa'] = $data_lead['tarifa'];
        $lead['compania'] = $data_lead['compania'];
        $lead['tipo_formulario'] = $data_lead['tipo_formulario'];
        $lead['precio'] = empty($data_lead['precio'])?null:$data_lead['precio'];
        $lead['fechas_nacimiento_asegurados'] = empty($data_lead['fechas_nacimiento_asegurados'])?null:$data_lead['fechas_nacimiento_asegurados'];
        $lead['dato1'] = empty($data_lead['dato1'])?null:trim($data_lead['dato1']);
        $lead['dato2'] = empty($data_lead['dato2'])?null:trim($data_lead['dato2']);
        $lead['dato3'] = empty($data_lead['dato3'])?null:trim($data_lead['dato3']);
        $lead['dato4'] = empty($data_lead['dato4'])?null:trim($data_lead['dato4']);
        if($data_lead['tipo_conversion'] === "cpl")
        {
            $lead['acepta_cesion_datos_a_proveedor'] = intval($data_lead['acepta_cesion_datos_a_proveedor']) === 1;
        }
        elseif($data_lead['tipo_conversion'] === "cpa")
        {
            $lead['acepta_politica_privacidad_kolondoo'] = intval($data_lead['acepta_politica_privacidad_kolondoo']) === 1;
        }
        else
        {
            throw new Exception("ERROR en tipo de conversión en función leadRecordDecesos(), tipo_conversion no es «cpl» ni «cpa» revisar tabla aseguradoras (".$data_lead['compania'].")");
        }

        //utm params
        $lead['utm_source'] = empty($request->utm_source)?null:$request->utm_source;
        $lead['utm_medium'] = empty($request->utm_medium)?null:$request->utm_medium;
        $lead['utm_campaign'] = empty($request->utm_campaign)?null:$request->utm_campaign;
        $lead['utm_content'] = empty($request->utm_content)?null:$request->utm_content;
        $lead['utm_term'] = empty($request->utm_term)?null:$request->utm_term;

        $lead_id = $conn->table('decesos')->insertGetId($lead);

        $conn->commit();
    }
    catch (\PDOException | \Exception $e)
    {
        $conn->rollback();
        $lead_id = null;
        $message = "Fallo al registrar el «lead» de *".($data_lead['compania'])."* en función leadRecordDecesos(). - Ip: " . $visitorIp . ' - Fallo ocurrido: ' . $e->getMessage()." - Datos recibidos del «lead» en la función: ".json_encode($data_lead, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        registroDeErrores(11, 'Lead ERROR', $message,$country_code,$decideCountry);
    }

    return 'decesos_'.(empty($lead_id)?'not_registered':$lead_id);
}


function buildUrl(int $common, string $url,string|null $additional = null): string
{
    $country_code = $common?(empty($url)?"":"/"):"/".decideCountry().(empty($url)?"":"/");
    $persistent_GET_params = array();

    foreach(array(
                'utm_source',
                'utm_medium',
                'utm_campaign',
                'utm_content',
                'utm_term',
                'gclid',
                'msclkid',
            ) as $param)
    {
        if(!empty($_GET[$param]))
        {
            $persistent_GET_params[] = $param."=".$_GET[$param];
        }
    }

    //Añadir parámetros adicionales por GET
    if(!empty($additional))
    {
        $persistent_GET_params[] = $additional;
    }

    return env('APP_URL').$country_code.$url."/".(!empty($persistent_GET_params)?("?".implode("&",$persistent_GET_params)):"");
}

function getJsUtmParams(): string
{
    $persistent_params = "";
    foreach(array(
                'utm_source',
                'utm_medium',
                'utm_campaign',
                'utm_content',
                'utm_term',
            ) as $param)
    {
        if(!empty($_GET[$param]))
        {
            $persistent_params .= "var ".$param." = '".$_GET[$param]."';";
        }
    }

    return $persistent_params;
}

/**
 * Devuelve el Market según la tabla de referencia
 *
 * @param string $table
 * @return string
 */
#[Pure] function getMarket(string $table): string
{
    $market = "";
    if(Str::contains($table,"TELCO"))
    {
        $market = "telco";
    }
    elseif(Str::contains($table,"ENERGIA"))
    {
        $market = "energia";
    }
    elseif(Str::contains($table,"SEGUROS"))
    {
        $market = "seguros";
    }

    return $market;
}

/**
 * Función para comprobar que los «leads» se generan desde una Ip del país para control de calidad de «leads»
 *
 * @param string $api
 * @return bool
 */
function checkingLeadComesFromOurCountryInstance(string $api): bool
{
    //Quitamos el bloqueo el 25/08/2022, si no se vuelve a activar en un tiempo por necesidades, eliminamos definitivamente la función
    return  true;

    $return = false;
    $ip_country_code = checkingGuestLocationApi(true);
    $instance_country_code = decideCountry();
    $error = "Fallo al llamar a IPAPI en función checkingLeadComesFromOurCountryInstance()";

    if(!is_null($ip_country_code))
    {
        //02-03-2022, quitamos bloqueo por pais de origen al generar un «lead» y solo informamos
        //$return = ($ip_country_code === $instance_country_code);
        //$error = "Se intenta generar un «lead» desde país no autorizado: *".$ip_country_code."* en instancia *".$instance_country_code."* desde la API ".$api." en uso de la función helpers_generales/checkingLeadComesFromOurCountryInstance()";
        if(($ip_country_code !== $instance_country_code))
        {
            registroDeErrores(12, '«Lead» desde fuera del país', "País de origen del «lead»: *".$ip_country_code."*"." en instancia: *".$instance_country_code."*", $ip_country_code,$instance_country_code);
        }
        $return =  true;
    }
    else
    {
        //Si $ip_country_code viene a null, existe algún tipo de error en la llamada a IPAPI por lo que NO bloqueamos el «lead» en ningún caso por no saber el origen del llamante y registramos un error que se va a informar
        $error = "No se comprueba que país origen del «lead» coincida con la instancia intentada en checkingLeadComesFromOurCountryInstance()";
        $return = true;
        registroDeErrores(12, 'Fallo IPAPI no responde con valor del país', $error, $ip_country_code,$instance_country_code);
    }

    if(!$return)
    {
        registroDeErrores(12, 'Bloqueo de «lead»', $error, $ip_country_code,$instance_country_code);
    }

    return $return;
}

/**
 * Obtiene el Listado de Iconos de BBDD
 *
 * @return array
 */
function getIconos(): array
{
    $iconos = array();
    foreach (connexionDB('master')->table("4_ICONOS")->select('id','icono_html')->get() as $item)
    {
        $iconos[$item->id] = $item->icono_html;
    }

    return $iconos;
}

/**
 * Obtiene el Listado de Iconos de BBDD
 *
 * @param string $conn
 * @param string $filename
 * @param string $line
 */
function createFileLog(string $conn, string $filename, string $line): void
{
    Storage::disk($conn)->append($filename.(Carbon::now()->format("_Ymd")).".log", $line.PHP_EOL);
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

/**
 * Comprobación de publicación para ofertas
 *
 * @param mixed $collection
 * @param string $timezone
 * @return mixed
 */
function cleanOffersByDayHour(mixed $collection, string $timezone): mixed
{
    /*
        //Definición del parámetro  do_not_list_in_dates  de la oferta en BBDD para restringir su visibilidad. Intervalo define cuando NO se muestra en la parrilla. En el ejemplo, desde miércoles  a las 09:31 hasta el viernes a las 10:52.
        dd(
            json_encode(array(
                0 => array(
                    'from' => array(
                        'day' => 3,
                        'hours' => 9,
                        'minutes' => 51
                    ),
                    'to' => array(
                        'day' => 5,
                        'hours' => 10,
                        'minutes' => 52
                    ),
                ),
            ))
        );
    */
    $available_timezones = array(
        'es' => 'Europe/Madrid',
        'pe' => 'America/Lima',
        'ec' => 'America/Guayaquil',
        'mx' => 'America/Mexico_City',
        'co' => 'America/Bogota',
    );

    try
    {
        date_default_timezone_set($available_timezones[$timezone]);
        $todayMinutesElapsed = (intval(Carbon::now()->format("H"))*60)+intval(Carbon::now()->format("i"));
        $weekDay = intval(Carbon::now()->format("N")); //Lunes es 1, Domingo es 7

        foreach ($collection as $index => $offer)
        {
            $show = true;
            if(isset($offer->do_not_list_in_dates) && !empty($offer->do_not_list_in_dates))
            {
                $notAllowed = json_decode($offer->do_not_list_in_dates, true);
                if(!empty($notAllowed))
                {
                    foreach ($notAllowed as $timeSlot)
                    {
                        $minutesFlagFrom = ($timeSlot['from']['hours']*60)+$timeSlot['from']['minutes'];
                        $minutesFlagTo = ($timeSlot['to']['hours']*60)+$timeSlot['to']['minutes'];
                        $dayFrom = $timeSlot['from']['day'];
                        $dayTo = $timeSlot['to']['day'];

                        if($dayFrom < $dayTo && $dayFrom < $weekDay && $dayTo > $weekDay) //Ej. Martes(2) hasta jueves(4) Desconecta el miércoles
                        {
                            $show = false;
                        }
                        elseif($dayFrom > $dayTo && ($dayFrom < $weekDay || $dayTo > $weekDay)) //Ej. Jueves(4) hasta siguiente lunes(1) Desconecta el viernes, sábado, domingo
                        {
                            $show = false;
                        }
                        elseif($dayFrom === $dayTo && $dayFrom === $weekDay && $todayMinutesElapsed >= $minutesFlagFrom && $todayMinutesElapsed < $minutesFlagTo) //Ej. Desconecta mismo dia entre horas
                        {
                            $show = false;
                        }
                        elseif($dayFrom !== $dayTo && $dayFrom === $weekDay && $minutesFlagFrom <= $todayMinutesElapsed) //Ej. Desconecta a la hora establecida el dia desde
                        {
                            $show = false;
                        }
                        elseif($dayFrom !== $dayTo && $dayTo === $weekDay && $minutesFlagTo > $todayMinutesElapsed) //Ej. Desconecta hasta la hora establecida el dia hasta
                        {
                            $show = false;
                        }
                    }
                }
            }

            if(!$show)
            {
                unset($collection[$index]);
            }
        }
    }
    catch (\Exception $e)
    {
        registroDeErrores(3, "cleanOffersByDayHour() in helpers_generales.php",$e->getMessage().PHP_EOL.$e->getTraceAsString(),$timezone,$timezone);
        return $collection;
    }

    return $collection;
}

/**
 * Control sobre parrilla vacía
 *
 * @param mixed $collection
 * @return mixed
 */
function checkCollectionLength(mixed $collection): mixed
{
    $return = null;
    if(count($collection) === 0)
    {
        $url_components = explode("/", str_replace("https://","",url()->current()));
        array_pop($url_components);

        $return = "https://".implode("/", $url_components)."/";
    }

    return $return;
}
