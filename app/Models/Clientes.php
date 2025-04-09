<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

class Clientes extends Model
{
    use Notifiable;

    protected $fillable = [
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'fecha_de_nacimiento',
        'ci',
        'telefono',
        'correo',
        'sexo',
        'foto',
        'biometrico_id',
        'registrado_por',
        'antecedentes_medicos',
        'contacto_emergencia_nombre',
        'contacto_emergencia_parentesco',
        'contacto_emergencia_celular',
        'estado',
        'bloqueado_por_deuda',
    ];

    // Relaciones
    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    // Imagen por defecto
    public function getFotoUrlAttribute(): string
    {
        return $this->foto
            ? asset('storage/' . $this->foto)
            : asset('images/default-user.png');
    }

    public function inscripciones()
    {
        return $this->hasMany(PlanCliente::class);
    }

    public function casillero()
    {
        return $this->hasOne(Casillero::class);
    }
}