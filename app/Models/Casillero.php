<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasAuditoria;

class Casillero extends Model
{
    use HasAuditoria;
    protected $fillable = [
        'numero',
        'estado',
        'cliente_id',
        'fecha_entrega_llave',
        'fecha_final_llave',
        'reposicion_llave',
        'costo_mensual',
        'total_reposiciones',
        'monto_reposiciones',
        'metodo_pago',
        'metodo_pago_reposicion',
        'registrado_por',
        'modificado_por',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Clientes::class, 'cliente_id');
    }

    public function getNombreClienteAttribute(): string
    {
        if (!$this->cliente) {
            return '— Disponible —';
        }

        return "{$this->cliente->nombre} {$this->cliente->apellido_paterno} {$this->cliente->apellido_materno}";
    }


}