<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use App\Models\PermisoCliente;
use App\Traits\HasAuditoria;


class PlanCliente extends Model
{
    use HasAuditoria;
    protected $table = 'planes_clientes';

    protected $fillable = [
        'cliente_id',
        'plan_id',
        'disciplina_id',
        'fecha_inicio',
        'fecha_final',
        'precio_plan',
        'a_cuenta',
        'saldo',
        'total',
        'metodo_pago',
        'comprobante',
        'estado',
        'dias_permitidos',
        'registrado_por',
        'modificado_por',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_final' => 'date',
        'dias_permitidos' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($plan) {
            $plan->estado = $plan->calcularEstado();
        });

        static::updating(function ($plan) {
            // Recalcular estado
            $plan->estado = $plan->calcularEstado();

            // Si cambia la fecha de inicio
            if ($plan->isDirty('fecha_inicio')) {
                $fechaInicio = Carbon::parse($plan->fecha_inicio);
                $duracion = $plan->plan?->duracion_dias ?? 0;

                // Buscar permisos aprobados dentro del nuevo rango
                $permisosExtra = PermisoCliente::where('cliente_id', $plan->cliente_id)
                    ->where('estado', 'aprobado')
                    ->whereBetween('fecha', [
                        $fechaInicio,
                        $fechaInicio->copy()->addDays($duracion - 1)
                    ])
                    ->count();

                // Nueva fecha final = duración del plan + días extra - 1
                $plan->fecha_final = $fechaInicio->copy()->addDays($duracion + $permisosExtra - 1);
            }
        });

        static::saving(function ($plan) {
            if (
                $plan->estado === 'bloqueado' &&
                $plan->saldo <= 0 &&
                now()->between($plan->fecha_inicio, $plan->fecha_final)
            ) {
                $plan->estado = 'vigente';
            }
        });
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Clientes::class, 'cliente_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function disciplina(): BelongsTo
    {
        return $this->belongsTo(Disciplina::class);
    }

    public function sesionesAdicionales()
    {
        return $this->hasMany(\App\Models\SesionAdicional::class);
    }

    public function getClienteDisplayNameAttribute()
    {
        return $this->cliente
            ? $this->cliente->nombre . ' ' . $this->cliente->apellido_paterno . ' ' . $this->cliente->apellido_materno
            : 'Sin nombre';
    }

    public function recalcularTotal()
    {
        $precioPlan = $this->precio_plan ?? 0;
        $casillero = $this->casillero_monto ?? 0;
        $sesiones = $this->sesionesAdicionales()->sum('precio') ?? 0;

        $total = $precioPlan + $casillero + $sesiones;

        $this->total = $total;
        $this->saldo = max($total - $this->a_cuenta, 0);
        $this->estado = $this->calcularEstado();
        $this->save();
    }

    public function calcularEstado(): string
    {
        if ($this->estado === 'bloqueado') {
            return 'bloqueado';
        }

        $fechaInicio = Carbon::parse($this->fecha_inicio);
        $duracion = $this->plan?->duracion_dias ?? 0;

        // Fecha base (sin permisos)
        $fechaFinalBase = $fechaInicio->copy()->addDays($duracion - 1);

        // Contar permisos aprobados dentro del rango original
        $permisosExtra = PermisoCliente::where('cliente_id', $this->cliente_id)
            ->where('estado', 'aprobado')
            ->whereBetween('fecha', [$fechaInicio, $fechaFinalBase])
            ->count();

        // Nueva fecha final considerando permisos
        $fechaFinalConPermisos = $fechaFinalBase->copy()->addDays($permisosExtra);

        // Comparar con fecha actual
        if (now()->greaterThan($fechaFinalConPermisos)) {
            return 'vencido';
        }

        if ($this->saldo > 0) {
            return 'deuda';
        }

        return 'vigente';
    }
}
