<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasAuditoria;

class ConversionPaquete extends Model
{
    use HasAuditoria;
    protected $table = 'conversiones_paquetes';

    protected $fillable = [
        'producto_id',
        'cantidad_convertida',
        'fecha_conversion',
        'registrado_por',
        'modificado_por',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Productos::class, 'producto_id');
    }
}