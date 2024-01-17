<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contactenos extends Model
{
    use HasFactory;
    protected $table = "formContactanos";
    protected $fillable = [
        'name',
        'message',
        'email',
        'politica',
        'urlOffer',
        'created_at',
        'updated_at'
    ];
}
