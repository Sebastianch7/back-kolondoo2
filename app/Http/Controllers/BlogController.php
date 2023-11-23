<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BlogController extends Controller
{
    public function getBlogList(){
        return DB::table('blog')->select('id','visitas','fecha_publicacion','categoria_id','imagen_principal_escritorio','imagen_principal_movil','titulo','entradilla')->orderBy('id','desc')->get();

    }

    public function getBlogId($id){
        return DB::table('blog')->select('blog.*','categorias.*','blog.entradilla as entrada')->leftJoin('categorias','blog.categoria_id','categorias.id')->where('blog.id','=',$id)->get();

    }
}
