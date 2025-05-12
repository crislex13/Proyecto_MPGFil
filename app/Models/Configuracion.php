<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    protected $table = 'configuraciones';

    protected $fillable = [
        'clientes_pueden_acceder',
        'instructores_pueden_acceder',
    ];

    public $timestamps = true;
}