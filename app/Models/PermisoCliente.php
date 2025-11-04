<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasAuditoria;

class PermisoCliente extends Model
{
    use HasAuditoria;
    protected $table = 'permisos_clientes';

    protected $fillable = [
        'cliente_id',
        'fecha',
        'motivo',
        'estado',
        'autorizado_por',
        'registrado_por',
        'modificado_por',
    ];

    protected static function booted()
    {
        static::saved(function (PermisoCliente $perm) {
            // Solo cuando esté aprobado
            if ($perm->estado !== 'aprobado') {
                return;
            }

            // Toma el plan vigente del cliente (el más reciente)
            $pc = \App\Models\PlanCliente::where('cliente_id', $perm->cliente_id)
                ->where('estado', 'vigente')
                ->latest('fecha_inicio')
                ->first();

            if (!$pc)
                return;

            // Recalcula considerando días permitidos + permisos dentro del rango base
            $pc->fecha_final = $pc->calcularFechaFinalConDiasYPermisos();

            // Guarda SIN disparar notificaciones/loops (y sin tocar estado si no quieres)
            $pc->saveQuietly();
        });
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Clientes::class);
    }

    public function autorizadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autorizado_por');
    }

    public function planActivo()
    {
        return $this->belongsTo(PlanCliente::class, 'cliente_id', 'cliente_id')
            ->where('estado', 'vigente');
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'registrado_por');
    }

    public function modificadoPor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'modificado_por');
    }
}