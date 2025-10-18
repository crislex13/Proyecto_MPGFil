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
}