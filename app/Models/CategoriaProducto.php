<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasAuditoria;

class CategoriaProducto extends Model
{
    use HasAuditoria;
    protected $table = 'categorias';

    protected $fillable = [
        'nombre',
        'descripcion',
        'registrado_por',
        'modificado_por',
    ];

    public function productos(): HasMany
    {
        return $this->hasMany(Productos::class, 'categoria_producto_id');
    }
}