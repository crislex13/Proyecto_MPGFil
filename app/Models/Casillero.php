<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Casillero extends Model
{
    protected $fillable = [
        'numero',
        'ubicacion',
        'estado',
        'cliente_id',
        'fecha_entrega_llave',
        'fecha_final_llave',
        'reposicion_llave',
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