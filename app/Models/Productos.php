<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use App\Models\CategoriaProducto;
use Illuminate\Support\Facades\Auth;
use App\Traits\HasAuditoria;

class Productos extends Model
{
    use HasAuditoria;
    protected $table = 'productos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio_unitario',
        'precio_paquete',
        'unidades_por_paquete',
        'stock_unidades',
        'stock_paquetes',
        'categoria_id',
        'imagen',
        'es_perecedero',
        'registrado_por',
        'modificado_por',
        'activo',
    ];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaProducto::class, 'categoria_id');
    }
    public function getFotoUrlAttribute(): string
    {
        return $this->imagen ? Storage::url($this->imagen) : '/default-product.png';
    }

    public function ingresos()
    {
        return $this->hasMany(IngresoProducto::class, 'producto_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    public function modificadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modificado_por');
    }

    public function lotes()
    {
        return $this->hasMany(LoteProducto::class, 'producto_id');
    }

    public function detallesVenta()
    {
        return $this->hasMany(\App\Models\DetalleVentaProducto::class, 'producto_id');
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function getStockUnidadesAttribute(): int
    {
        return $this->lotes()->sum('stock_unidades');
    }

    public function getStockPaquetesAttribute(): int
    {
        return $this->lotes()->sum('stock_paquetes');
    }

}