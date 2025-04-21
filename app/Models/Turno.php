<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Turno extends Model
{
    protected $fillable = [
        'nombre',
        'dia',
        'hora_inicio',
        'hora_fin',
        'duracion_minutos',
        'estado',
        'personal_id',
    ];

    protected static function booted(): void
    {
        static::saving(function ($turno) {
            if ($turno->hora_inicio && $turno->hora_fin) {
                $inicio = Carbon::parse($turno->hora_inicio);
                $fin = Carbon::parse($turno->hora_fin);

                // Aseguramos que la hora de fin sea mayor a la de inicio
                if ($fin->greaterThan($inicio)) {
                    $turno->duracion_minutos = $inicio->diffInMinutes($fin);
                } else {
                    $turno->duracion_minutos = null;
                }
            }
        });
    }

    public function getDisplayHorarioAttribute(): string
    {
        return "{$this->dia} - {$this->nombre} ({$this->hora_inicio} - {$this->hora_fin})";
    }

    public function personal()
    {
        return $this->belongsTo(Personal::class);
    }

}
