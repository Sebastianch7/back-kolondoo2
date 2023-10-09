<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TarifasMovil extends Model
{
    use HasFactory;
    protected $table = "tarifasMovil";
    protected $fillable = [
        'id',
        'id_producto',
        'operadora',
        'permanencia',
        'detalles_tarifa',
        'nombre_tarifa',
        'parrilla_bloque_1',
        'parrilla_bloque_2',
        'parrilla_bloque_3',
        'parrilla_bloque_4',
        'meses_permanencia',
        'precio',
        'precio_final',
        'num_meses_promo',
        'porcentaje_descuento',
        'imagen_promo',
        'promocion',
        'texto_alternativo_promo',
        'GB',
        'llamadas_ilimitadas',
        'coste_llamadas_minuto',
        'coste_establecimiento_llamada',
        'num_minutos_gratis',
        'tarifa_activa',
        'fecha_publicacion',
        'fecha_expiracion',
        'fecha_registro',
        'moneda',
        'landingLead'
    ];
}
