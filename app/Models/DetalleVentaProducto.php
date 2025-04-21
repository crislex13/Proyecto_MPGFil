<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Productos;
use App\Models\VentaProducto;

class DetalleVentaProducto extends Model
{
    protected $table = 'detalle_ventas_productos';

    protected $fillable = [
        'venta_producto_id',
        'producto_id',
        'cantidad',
        'precio_unitario',
        'subtotal',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Productos::class, 'producto_id');
    }

    public function ventaProducto(): BelongsTo
    {
        return $this->belongsTo(VentaProducto::class, 'venta_producto_id');
    }

    protected static function booted()
    {
        
        static::created(function ($detalle) {
            $venta = VentaProducto::find($detalle->venta_producto_id);
            if ($venta) {
                $venta->update([
                    'total' => $venta->detalles()->sum('subtotal'),
                ]);
            }
        });

        static::deleted(function ($detalle) {
            $producto = $detalle->producto;
            if ($producto) {
                $producto->increment('stock_unidades', $detalle->cantidad);
            }

            $venta = VentaProducto::find($detalle->venta_producto_id);
            if ($venta) {
                $venta->update([
                    'total' => $venta->detalles()->sum('subtotal'),
                ]);
            }
        });
    }

    public function actualizarTotalVenta()
    {
        $venta = $this->ventaProducto;
        if ($venta) {
            $venta->total = $venta->detalles()->sum('subtotal');
            $venta->save();
        }
    }
}