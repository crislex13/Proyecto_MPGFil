<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use App\Models\CategoriaProducto;
use Illuminate\Support\Facades\Auth;

class Productos extends Model
{
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
        'registrado_por',
        'modificado_por',
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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($producto) {
            $producto->registrado_por = Auth::id();
        });

        static::updating(function ($producto) {
            $producto->modificado_por = Auth::id();
        });
    }

}