<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Casillero extends Model
{
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