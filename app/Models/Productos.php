<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Productos extends Model
{
    use HasFactory;

    protected $table = 'productos'; // Asegúrate de que el nombre de la tabla sea correcto

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'stock',
        'categoria',
        'imagen',
        'fecha_vencimiento',
    ];

    protected $dates = [
        'fecha_vencimiento',
    ];

    public function setImagenAttribute($value)
    {
        if (is_object($value) && method_exists($value, 'store')) {
            // Guardar el archivo de imagen en el directorio 'productos_imagenes' dentro de 'storage/app/public'
            $this->attributes['imagen'] = $value->store('productos_imagenes', 'public');
        }
    }
}
