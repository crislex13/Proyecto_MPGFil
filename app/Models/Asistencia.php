<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\SesionAdicional;
use App\Traits\HasAuditoria;
use App\Models\Clientes;
use App\Models\Personal;
use Illuminate\Support\Carbon;

class Asistencia extends Model
{
    use HasAuditoria;
    protected $table = 'asistencias';

    protected $fillable = [
        'asistible_id',
        'asistible_type',
        'fecha',
        'hora_entrada',
        'hora_salida',
        'estado',
        'observacion',
        'tipo_asistencia',
        'origen',
        'usuario_registro_id',
        'sesion_adicional_id',
        'registrado_por',
        'modificado_por',
    ];


    protected $casts = [
        'fecha' => 'date',
        'hora_entrada' => 'datetime',
        'hora_salida' => 'datetime',
    ];

    // 📌 Relación polimórfica con Cliente o Personal
    public function asistible()
    {
        return $this->morphTo();
    }

    // 📌 Relación con el usuario que registró la asistencia
    public function usuarioRegistro(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_registro_id');
    }

    // 📌 Accesor para obtener el nombre completo de quien asistió
    public function getNombreCompletoAttribute(): string
    {
        return $this->asistible->nombre_completo ?? 'Desconocido';
    }

    // 📌 Accesor para obtener la foto (cliente o personal)
    public function getFotoUrlAttribute(): string
    {
        return $this->asistible->foto_url ?? '/default-user.png';
    }

    // 📌 Accesor para distinguir el rol
    public function getRolAttribute(): string
    {
        return class_basename($this->asistible_type) === 'Personal' ? 'Personal' : 'Cliente';
    }

    // 📌 Evitar modificar una asistencia una vez creada
    protected static function booted(): void
    {
        static::updating(function ($asistencia) {
            if ($asistencia->isDirty(['hora_entrada', 'estado', 'usuario_registro_id', 'fecha'])) {
                throw new \Exception("No está permitido modificar registros de asistencia excepto hora_salida.");
            }
        });
    }

    public function sesionAdicional()
    {
        return $this->belongsTo(SesionAdicional::class);
    }

    public function cliente()
    {
        return $this->morphTo(__FUNCTION__, 'asistible_type', 'asistible_id');
    }

    public function personal()
    {
        return $this->morphTo(__FUNCTION__, 'asistible_type', 'asistible_id');
    }

    public function scopeAbiertaDeClienteEnFecha($query, Clientes $cliente, Carbon|string $fecha)
    {
        $fecha = $fecha instanceof Carbon ? $fecha->toDateString() : $fecha;

        return $query->whereDate('fecha', $fecha)
            ->where('asistible_id', $cliente->id)
            ->where('asistible_type', Clientes::class)
            ->whereNull('hora_salida');
    }

    /** 🔎 Scope: asistencia abierta de personal en una fecha (simetría) */
    public function scopeAbiertaDePersonalEnFecha($query, Personal $personal, Carbon|string $fecha)
    {
        $fecha = $fecha instanceof Carbon ? $fecha->toDateString() : $fecha;

        return $query->whereDate('fecha', $fecha)
            ->where('asistible_id', $personal->id)
            ->where('asistible_type', Personal::class)
            ->whereNull('hora_salida');
    }

    public function getMinRestantesAttribute(): ?int
    {
        if ($this->hora_salida || !$this->hora_entrada) {
            return null;
        }

        $fin = null;

        // 1) Sesión adicional: usa hora_fin si existe
        if ($this->tipo_asistencia === 'sesion' && $this->sesionAdicional && $this->sesionAdicional->hora_fin) {
            $fin = Carbon::parse($this->sesionAdicional->hora_fin);
        }

        // 2) Plan: usa turnoHoy()->hora_fin si está disponible
        if ($this->tipo_asistencia === 'plan') {
            $asistible = $this->asistible; // <- puede ser null
            if ($asistible && method_exists($asistible, 'turnoHoy')) {
                $t = $asistible->turnoHoy();
                if ($t && !empty($t->hora_fin)) {
                    $fin = Carbon::parse($t->hora_fin);
                }
            }

            // Si no hay hora_fin de turno, asumimos 90 min desde la entrada
            if (!$fin) {
                $fin = Carbon::parse($this->hora_entrada)->copy()->addMinutes(90);
            }
        }

        return $fin ? now()->diffInMinutes($fin, false) : null;
    }



}