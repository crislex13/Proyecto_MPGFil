<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Traits\HasAuditoria;

class Turno extends Model
{
    use HasAuditoria;
    protected $fillable = [
        'nombre',
        'dia',
        'hora_inicio',
        'hora_fin',
        'duracion_minutos',
        'estado',
        'personal_id',
        'registrado_por',
        'modificado_por',
    ];

    protected $casts = [
        'dia' => 'integer',
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
        return "{$this->dia_nombre} - {$this->nombre} ({$this->hora_inicio} - {$this->hora_fin})";
    }

    public function personal()
    {
        return $this->belongsTo(Personal::class);
    }

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    public function modificadoPor()
    {
        return $this->belongsTo(User::class, 'modificado_por');
    }

    public function getDiaNombreAttribute(): string
    {
        return [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'][$this->dia] ?? (string) $this->dia;
    }

}
