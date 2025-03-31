<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Personal extends Model
{
    use HasFactory;

    protected $table = 'personals'; // Asegúrate de que el nombre de la tabla sea correcto

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
        'horario',
        'salario',
        'fecha_contratacion',
    ];

    protected $dates = [
        'fecha_de_nacimiento',
        'fecha_contratacion',
    ];
}
