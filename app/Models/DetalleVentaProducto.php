<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Productos;
use App\Models\VentaProducto;
use App\Models\LoteProducto;
use App\Traits\HasAuditoria;

class DetalleVentaProducto extends Model
{
    use HasAuditoria;

    protected $table = 'detalle_ventas_productos';

    protected $fillable = [
        'venta_producto_id',
        'producto_id',
        'lote_producto_id',
        'lote_origen_id',
        'cantidad_convertida_desde_paquete',
        'cantidad',
        'precio_unitario',
        'subtotal',
        'registrado_por',
        'modificado_por',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Productos::class, 'producto_id');
    }

    public function ventaProducto(): BelongsTo
    {
        return $this->belongsTo(VentaProducto::class, 'venta_producto_id');
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(LoteProducto::class, 'lote_producto_id');
    }

    protected static function booted()
    {
        static::created(function ($detalle) {
            $detalle->actualizarTotalVenta();
        });
    }

    public function actualizarTotalVenta()
    {
        $venta = $this->ventaProducto;
        if ($venta) {
            $venta->update([
                'total' => $venta->detalles()->sum('subtotal'),
            ]);
        }
    }
    public function loteOrigen(): BelongsTo
    {
        return $this->belongsTo(LoteProducto::class, 'lote_origen_id');
    }
}