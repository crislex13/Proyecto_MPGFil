<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasAuditoria;
use App\Models\LoteProducto;
use Illuminate\Database\Eloquent\Relations\HasOne;
class IngresoProducto extends Model
{
    use HasAuditoria;

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
        'metodo_pago',
        'fecha_vencimiento',
        'registrado_por',
        'modificado_por',
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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ingreso) {
            $producto = Productos::find($ingreso->producto_id);
            if (!$producto)
                return;

            // Validación de cantidades
            if (
                (empty($ingreso->cantidad_unidades) || $ingreso->cantidad_unidades == 0) &&
                (empty($ingreso->cantidad_paquetes) || $ingreso->cantidad_paquetes == 0)
            ) {
                throw new \Exception('Debe ingresar al menos unidades o paquetes.');
            }

            // Validación de precios
            if ($ingreso->cantidad_unidades && empty($ingreso->precio_unitario)) {
                throw new \Exception('Debe ingresar el precio unitario si se registran unidades.');
            }

            if ($ingreso->cantidad_paquetes && empty($ingreso->precio_paquete)) {
                throw new \Exception('Debe ingresar el precio por paquete si se registran paquetes.');
            }

            // Validación de vencimiento si es perecedero
            if ($producto->es_perecedero && !$ingreso->fecha_vencimiento) {
                throw new \Exception('Debe ingresar la fecha de vencimiento para productos perecederos.');
            }

            $ingreso->registrado_por = auth()->id();
        });

        static::created(function ($ingreso) {
            $producto = $ingreso->producto;

            LoteProducto::create([
                'producto_id' => $producto->id,
                'ingreso_producto_id' => $ingreso->id,
                'fecha_ingreso' => now(),
                'fecha_vencimiento' => $producto->es_perecedero ? $ingreso->fecha_vencimiento : null,
                'stock_unidades' => $ingreso->cantidad_unidades ?? 0,
                'stock_paquetes' => $ingreso->cantidad_paquetes ?? 0,
                'precio_unitario' => $ingreso->precio_unitario ?? 0,
                'precio_paquete' => $ingreso->precio_paquete ?? 0,
                'es_perecedero' => $producto->es_perecedero,
                'registrado_por' => $ingreso->registrado_por,
            ]);
        });

        static::updating(function ($ingreso) {
            $ingreso->modificado_por = auth()->id();
        });

        static::updated(function ($ingreso) {
            if (!$ingreso->lote)
                return;

            $ingreso->lote->update([
                'stock_unidades' => $ingreso->cantidad_unidades ?? 0,
                'stock_paquetes' => $ingreso->cantidad_paquetes ?? 0,
                'precio_unitario' => $ingreso->precio_unitario ?? 0,
                'precio_paquete' => $ingreso->precio_paquete ?? 0,
                'fecha_vencimiento' => $ingreso->fecha_vencimiento,
                'modificado_por' => auth()->id(),
            ]);
        });

        static::deleting(function ($ingreso) {
            $ingreso->lote?->delete();
        });
    }

    public function lote(): HasOne
    {
        return $this->hasOne(LoteProducto::class, 'ingreso_producto_id');
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    // (Opcional) si quieres también la de modificador:
    public function modificadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modificado_por');
    }

}