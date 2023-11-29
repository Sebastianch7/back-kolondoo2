<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use JetBrains\PhpStorm\NoReturn;
use Mockery\Exception;

class BlogController extends Controller
{
    public function getBlogList()
    {
        return DB::table('blog')->select('blog.id', 'blog.visitas', 'blog.fecha_publicacion', 'blog.categoria_id', 'blog.imagen_principal_escritorio', 'blog.imagen_principal_movil', 'blog.titulo', 'blog.entradilla', 'SEO_BLOG.url_amigable')->join('SEO_BLOG', 'SEO_BLOG.id', 'SEO_id')->orderBy('id', 'desc')->get();
    }

    public function getBlogId($id)
    {
        return DB::table('SEO_BLOG')->select('blog.*', 'categorias.*', 'blog.entradilla as entrada')->leftJoin('blog', 'blog.SEO_id', 'SEO_BLOG.id')->leftJoin('categorias', 'blog.categoria_id', 'categorias.id')->where('SEO_BLOG.url_amigable', '=', $id)->get();
    }
    /* Genera datos para vista de precios de la Luz (Vertical de Gestiones) en instancia Española */
    /* #[NoReturn]  */
    public function preciosLuzREEApi()
    {
        header("Access-Control-Allow-Origin: https://dev.vuskoo.com");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type");
        $today = now();
        $GLOBALS["country_instance"] = "es";

        $apiURL = 'https://apidatos.ree.es/es/datos/mercados/precios-mercados-tiempo-real?start_date=' . $today->format('Y-m-d') . 'T00:00&end_date=' . $today->format('Y-m-d') . 'T23:00&time_trunc=hour';

        // Make the HTTP request
        $response = file_get_contents($apiURL);

        if ($response === false) {
            // Handle error
            die('Error fetching data from API.');
        }

        // Decode JSON response
        $data = json_decode($response, true);

        if ($data === null) {
            // Handle JSON decoding error
            die('Error decoding JSON response.');
        }

        $jsonData = json_encode($data, true);

        // Set the appropriate headers to indicate JSON content
        header('Content-Type: application/json');

        // Return JSON response
        echo $jsonData;
    }
}
