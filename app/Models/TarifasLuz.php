<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TarifasLuz extends Model
{
    use HasFactory;
    protected $table = "tarifasLuz";
    protected $fillable = [
        '`id',
        'id_producto',
        'comercializadora',
        'detalles_tarifa',
        'nombre_tarifa',
        'parrilla_bloque_1',
        'parrilla_bloque_2',
        'parrilla_bloque_3',
        'parrilla_bloque_4',
        'landing_dato_adicional',
        'line_icon_1',
        'line_icon_2',
        'line_icon_3',
        'line_icon_4',
        'meses_permanencia',
        'luz_nombre_tarifa',
        'luz_tarifa_indexada',
        'luz_discriminacion_horaria',
        'pastilla',
        'precio',
        'precio_final',
        'luz_precio_potencia_punta',
        'luz_precio_potencia_valle',
        'luz_precio_energia_punta',
        'luz_precio_energia_llano',
        'luz_precio_energia_valle',
        'luz_precio_energia_24h',
        'energia_verde',
        'imagen_promo',
        'promocion',
        'num_meses_promo',
        'texto_alternativo_promo',
        'antenimiento',
        'coste_de_gestion',
        'orden_parrilla_general',
        'orden_parrilla_comercializadora',
        'tarifa_activa',
        'ferta',
        'fecha_publicacion',
        'do_not_list_in_dates',
        'fecha_expiracion',
        'fecha_registro',
        'moneda',
        'landingLead'
    ];
}
