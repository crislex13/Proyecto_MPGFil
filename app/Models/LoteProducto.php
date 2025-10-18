<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasAuditoria;

class LoteProducto extends Model
{
    use HasAuditoria;
    protected $table = 'lote_productos';

    protected $fillable = [
        'producto_id',
        'ingreso_producto_id', 
        'fecha_ingreso',
        'fecha_vencimiento',
        'stock_unidades',
        'stock_paquetes',
        'precio_unitario',
        'precio_paquete',
        'es_perecedero',
        'registrado_por',
        'modificado_por',

    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Productos::class, 'producto_id');
    }

    public function ingreso(): BelongsTo
    {
        return $this->belongsTo(IngresoProducto::class, 'ingreso_producto_id');
    }
}