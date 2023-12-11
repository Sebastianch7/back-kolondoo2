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
    public function getBlogList($categoria)
    {
        $categoria = strtolower($categoria);
        $categorias = [
            'internet' => 1,
            'movil' => 2,
            'television' => 3,
            'energia' => 4,
            'hogar' => 5,
            'mejores-ofertas' => 6,
            'seguros' => 7
        ];

        if ($categoria == 'null') {
            return DB::table('blog')->select('blog.id', 'blog.visitas', 'blog.fecha_publicacion', 'blog.categoria_id', 'blog.imagen_principal_escritorio', 'blog.imagen_principal_movil', 'blog.titulo', 'blog.entradilla', 'SEO_BLOG.url_amigable')->join('SEO_BLOG', 'SEO_BLOG.id', 'SEO_id')->orderBy('id', 'desc')->get();
        } else if ($categoria == 'mas-visitadas') {
            return DB::table('blog')->select('blog.id', 'blog.visitas', 'blog.fecha_publicacion', 'blog.categoria_id', 'blog.imagen_principal_escritorio', 'blog.imagen_principal_movil', 'blog.titulo', 'blog.entradilla', 'SEO_BLOG.url_amigable')->join('SEO_BLOG', 'SEO_BLOG.id', 'SEO_id')->orderBy('blog.visitas', 'desc')->get();
        } else {
            return DB::table('blog')->select('blog.id', 'categoria_id', 'blog.visitas', 'blog.fecha_publicacion', 'blog.categoria_id', 'blog.imagen_principal_escritorio', 'blog.imagen_principal_movil', 'blog.titulo', 'blog.entradilla', 'SEO_BLOG.url_amigable')->join('SEO_BLOG', 'SEO_BLOG.id', 'SEO_id')->where('categoria_id', $categorias[$categoria])->orderBy('id', 'desc')->get();
        }
    }

    public function getBlogHomeList()
    {

        return DB::table('blog')->select('blog.id', 'blog.visitas', 'blog.fecha_publicacion', 'blog.categoria_id', 'blog.imagen_principal_escritorio', 'blog.imagen_principal_movil', 'blog.titulo', 'blog.entradilla', 'SEO_BLOG.url_amigable')->join('SEO_BLOG', 'SEO_BLOG.id', 'SEO_id')->orderBy('id', 'desc')->limit(3)->get();
    }

    public function getBlogId($id)
    {
        return DB::table('SEO_BLOG')->select('blog.*', 'categorias.*', 'blog.entradilla as entrada')->leftJoin('blog', 'blog.SEO_id', 'SEO_BLOG.id')->leftJoin('categorias', 'blog.categoria_id', 'categorias.id')->where('SEO_BLOG.url_amigable', '=', $id)->get();
    }

    public function getBlogDescatados()
    {
        $today = Carbon::now()->format("Y-m-d");
        $destacados = DB::table('SEO_BLOG')
            ->leftJoin('blog', 'SEO_BLOG.id', '=', 'blog.SEO_id')
            ->leftJoin('categorias', 'categorias.id', '=', 'blog.categoria_id')
            ->select(
                'SEO_BLOG.url_amigable as blog_item_url_amigable',
                'SEO_BLOG.controlador',
                'SEO_BLOG.funcion',
                'SEO_BLOG.vista',
                'SEO_BLOG.ruta_activa',
                'blog.categoria_id',
                'blog.fecha_publicacion',
                'blog.imagen_principal_movil',
                'blog.atributo_imagen_principal',
                'blog.titulo',
                'categorias.url_amigable as cat_url_amigable',
                'categorias.categoria as cat_categoria'
            );
        if (env('APP_ENV') === "production") {
            $destacados->where('SEO_BLOG.ruta_activa', true);
        }
        return $destacados->where('categorias.categoria_activa', true)
            ->where('categorias.id', 6)
            ->where('blog.fecha_publicacion', '<=', $today)
            ->where(function ($query) use ($today) {
                $query->where('blog.fecha_expiracion', '>=', $today)->orWhereNull('blog.fecha_expiracion');
            })
            ->limit(4)
            ->orderBy('blog.fecha_publicacion', 'DESC')
            ->get();
    }
}
