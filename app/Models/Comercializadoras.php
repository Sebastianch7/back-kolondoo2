<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comercializadoras extends Model
{
    use HasFactory;
    protected $table = "comercializadoras";
    protected $fillable = [
        'id',
        'nombre',
        'logo',
        'logo_negativo',
        'isotipo',
        'alt_logo',
        'telefono',
        'politica_privacidad',
        'activo',
        'fecha_registro'
    ];
}
