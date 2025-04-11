<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;


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

    public function getNombreCompletoAttribute(): string
    {
        return "{$this->nombre} {$this->apellido_paterno} {$this->apellido_materno}";
    }

    public function getFotoUrlAttribute(): string
    {
        return $this->foto ? Storage::url($this->foto) : '/default-user.png';
    }

    public function turnos()
    {
        return $this->hasMany(Turno::class);
    }

}
