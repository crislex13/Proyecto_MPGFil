<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class PlanCliente extends Model
{
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
    ];

    protected static function booted()
    {
        static::creating(function ($plan) {
            $plan->estado = $plan->calcularEstado();
        });

        static::updating(function ($plan) {
            $plan->estado = $plan->calcularEstado();
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

        if (Carbon::parse($this->fecha_final)->isPast()) {
            return 'vencido';
        }

        if ($this->saldo > 0) {
            return 'con deuda';
        }

        return 'vigente';
    }
}