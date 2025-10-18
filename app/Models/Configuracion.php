<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasAuditoria;

class Configuracion extends Model
{
    use HasAuditoria;
    protected $table = 'configuraciones';

    protected $fillable = [
        'clientes_pueden_acceder',
        'instructores_pueden_acceder',
        'registrado_por',
        'modificado_por',
    ];

    public $timestamps = true;
}