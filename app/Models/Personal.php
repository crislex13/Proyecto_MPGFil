<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Personal extends Model
{
    protected $fillable = [
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'ci',
        'telefono',
        'direccion',
        'fecha_de_nacimiento',
        'correo',
        'cargo',
        'biometrico_id',
        'horario',
        'salario',
        'fecha_contratacion',
        'estado',
        'foto',
        'observaciones',
    ];

    protected $casts = [
        'fecha_de_nacimiento' => 'date',
        'fecha_contratacion' => 'date',
        'horario' => 'array',
    ];
}
