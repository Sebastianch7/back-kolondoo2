<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Operadoras extends Model
{
    use HasFactory;
    protected $table = "operadoras";
    protected $fillable = [
        'id',
        'nombre',
        'logo',
        'logo_negativo',
        'isotipo',
        'alt_logo',
        'telefono',
        'activo',
        'fecha_registro',
    ];
}
