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
    public function getMenuBlogList()
    {
        return DB::connection('mysql_second')->table('wp_term_taxonomy')->select('wp_terms.name', 'wp_terms.slug')->join('wp_terms', 'wp_terms.term_id', '=', 'wp_term_taxonomy.term_id')->where('taxonomy', "category")->get();
    }

    public function getBlogList($categoria = null, $id = null)
    {
        $query = DB::connection('mysql_second')->table('wp_posts')
            ->select(
                'wp_yoast_indexable.open_graph_image as imagen',
                'wp_terms.slug as term_slug',
                'wp_terms.name as term_categoria',
                'wp_posts.post_date as fecha_publicacion',
                'wp_posts.post_title as titulo',
                'wp_posts.post_content as contenido',
                'wp_posts.post_excerpt as entradilla',
                'wp_yoast_indexable.title as seo_titulo',
                'wp_yoast_indexable.description as seo_descripcion',
                'wp_yoast_indexable.breadcrumb_title as migapan',
                'wp_yoast_indexable.estimated_reading_time_minutes as tiempo_lectura',
                'wp_posts.post_name as url_amigable',
                'wp_users.display_name as autor',
                'wp_postmeta.meta_value as categoriaPrincipal',
                'principal.slug as categoria_slug',
                'principal.name as categoria',

            )
            ->join('wp_yoast_indexable', 'wp_yoast_indexable.object_id', '=', 'wp_posts.ID')
            ->join('wp_users', 'wp_users.ID', '=', 'wp_posts.post_author')
            ->join('wp_term_relationships', 'wp_term_relationships.object_id', '=', 'wp_posts.ID')
            ->join('wp_term_taxonomy', 'wp_term_taxonomy.term_taxonomy_id', '=', 'wp_term_relationships.term_taxonomy_id')
            ->join('wp_terms', 'wp_terms.term_id', '=', 'wp_term_taxonomy.term_id')
            ->join('wp_postmeta', 'wp_postmeta.post_id', '=', 'wp_posts.ID')
            ->join('wp_terms as principal', 'principal.term_id', '=', 'wp_postmeta.meta_value')

            ->where('wp_postmeta.meta_key', '=', '_yoast_wpseo_primary_category')
            ->where('wp_posts.post_status', '=', 'publish')
            ->where('wp_posts.post_type', '=', 'post')
            ->where('wp_yoast_indexable.object_type', '=', 'post')
            ->where('wp_yoast_indexable.object_sub_type', '=', 'post')
            ->where('wp_yoast_indexable.post_status', '=', 'publish')
            ->where('wp_term_taxonomy.taxonomy', '=', 'category')
            ->orderBy('wp_posts.ID', 'desc');

        // Optional condition based on the variable
        if ($id) {
            $query->where('wp_posts.post_name', '=', $id);
        }
        if ($categoria) {
            $query->where('wp_terms.slug', '=', $categoria);
        }
        if($categoria !== 'destacado'){
            $query->where('wp_terms.slug', '!=', 'destacado');
        }

        return $query->get();
    }

    public function getBlogPreviewList($id)
    {
        $query = DB::connection('mysql_second')->table('wp_posts')
            ->select(
                'wp_yoast_indexable.open_graph_image as imagen',
                'wp_posts.post_date as fecha_publicacion',
                'wp_posts.post_title as titulo',
                'wp_posts.post_content as contenido',
                'wp_posts.post_excerpt as entradilla',
                'wp_yoast_indexable.title as seo_titulo',
                'wp_yoast_indexable.description as seo_descripcion',
                'wp_yoast_indexable.breadcrumb_title as migapan',
                'wp_yoast_indexable.estimated_reading_time_minutes as tiempo_lectura',
                'wp_posts.post_name as url_amigable',
                'wp_users.display_name as autor',
                'wp_postmeta.meta_value as categoriaPrincipal',
                'principal.slug as categoria_slug',
                'principal.name as categoria'
            )
            ->join('wp_yoast_indexable', 'wp_yoast_indexable.object_id', '=', 'wp_posts.ID')
            ->join('wp_users', 'wp_users.ID', '=', 'wp_posts.post_author')
            ->join('wp_term_relationships', 'wp_term_relationships.object_id', '=', 'wp_posts.ID')
            ->join('wp_term_taxonomy', 'wp_term_taxonomy.term_taxonomy_id', '=', 'wp_term_relationships.term_taxonomy_id')
            ->join('wp_terms', 'wp_terms.term_id', '=', 'wp_term_taxonomy.term_id')
            ->join('wp_postmeta', 'wp_postmeta.post_id', '=', 'wp_posts.ID')
            ->join('wp_terms as principal', 'principal.term_id', '=', 'wp_postmeta.meta_value')
            
            ->where('wp_postmeta.meta_key', '=', '_yoast_wpseo_primary_category')
            /* ->where('wp_posts.post_status', '=', 'publish') */
            ->where('wp_posts.post_type', '=', 'post')
            ->where('wp_yoast_indexable.object_type', '=', 'post')
            ->where('wp_yoast_indexable.object_sub_type', '=', 'post')
            /* ->where('wp_yoast_indexable.post_status', '=', 'publish') */
            ->where('wp_term_taxonomy.taxonomy', '=', 'category')
            ->where('wp_posts.ID', '=', $id)
            ->orderBy('wp_posts.ID', 'desc');
        
        return $query->get();
    }

    public function getBlogHomeList()
    {

        return DB::table('blog')->select('categorias.url_amigable as categoria_url', 'blog.id', 'blog.visitas', 'blog.fecha_publicacion', 'blog.categoria_id', 'blog.imagen_principal_escritorio', 'blog.imagen_principal_movil', 'blog.titulo', 'blog.entradilla', 'SEO_BLOG.url_amigable')->join('SEO_BLOG', 'SEO_BLOG.id', 'SEO_id')->join('categorias', 'categorias.id', 'blog.categoria_id')->orderBy('id', 'desc')->limit(3)->get();
    }

    public function getBlogId($id)
    {
        return DB::table('SEO_BLOG')->select('SEO_BLOG.metatitulo as seo_titulo', 'SEO_BLOG.metadescripcion as seo_descripcion', 'blog.*', 'categorias.*', 'blog.entradilla as entrada')->leftJoin('blog', 'blog.SEO_id', 'SEO_BLOG.id')->leftJoin('categorias', 'blog.categoria_id', 'categorias.id')->where('SEO_BLOG.url_amigable', '=', $id)->get();
    }

    public function getGestionList($funcion, $id = null)
    {
        $query = DB::table('SEO_GESTIONES')
            ->select('SEO_GESTIONES.*', 'gestiones.atributo_imagen_principal as alt_img', 'gestiones.titulo as titulo', 'gestiones.imagen_principal_escritorio as imagen', 'gestiones.cuerpo as contenido', 'gestiones.propietario as autor', 'gestiones.fecha_publicacion')
            ->leftJoin('gestiones', 'gestiones.SEO_id', '=', 'SEO_GESTIONES.id')
            ->leftJoin('categorias_gestiones', 'gestiones.categoria_id', '=', 'categorias_gestiones.id')
            ->where('SEO_GESTIONES.funcion', $funcion);

        if ($id !== null) {
            $query->select('SEO_GESTIONES.*', 'gestiones.atributo_imagen_principal as alt_img', 'gestiones.titulo as titulo', 'gestiones.imagen_principal_escritorio as imagen', 'gestiones.cuerpo as contenido', 'gestiones.propietario as autor', 'gestiones.fecha_publicacion');
            $query->where('SEO_GESTIONES.url_amigable', $id);
        }

        return $query->get();
    }
}
