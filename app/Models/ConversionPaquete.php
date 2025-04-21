<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversionPaquete extends Model
{
    protected $table = 'conversiones_paquetes';

    protected $fillable = [
        'producto_id',
        'cantidad_convertida',
        'fecha_conversion',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Productos::class, 'producto_id');
    }
}