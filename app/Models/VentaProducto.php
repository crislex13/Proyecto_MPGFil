<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasAuditoria;

class VentaProducto extends Model
{
    use HasAuditoria;
    protected $table = 'ventas_productos';
    protected $fillable = [
        'usuario_id',
        'metodo_pago',
        'total',
        'fecha',
        'registrado_por',
        'modificado_por',
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleVentaProducto::class, 'venta_producto_id');
    }

}