<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasAuditoria;

class Sala extends Model
{
    use HasAuditoria;
    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
        'registrado_por',
        'modificado_por',
    ];

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    public function modificadoPor()
    {
        return $this->belongsTo(User::class, 'modificado_por');
    }
}
