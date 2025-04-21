<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IngresoProducto extends Model
{
    protected $table = 'ingresos_productos';
    protected $fillable = [
        'producto_id',
        'usuario_id',
        'cantidad_unidades',
        'cantidad_paquetes',
        'precio_unitario',
        'precio_paquete',
        'observacion',
        'fecha',
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Productos::class, 'producto_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}