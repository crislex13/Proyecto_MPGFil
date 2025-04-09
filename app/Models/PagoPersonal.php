<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagoPersonal extends Model
{
    protected $table = 'pagos_personal';
    protected $fillable = [
        'personal_id',
        'fecha',
        'monto',
        'descripcion',
        'turno_id',
        'sala_id',
        'pagado',
    ];

    public function personal()
    {
        return $this->belongsTo(Personal::class);
    }

    public function turno()
    {
        return $this->belongsTo(Turno::class);
    }

    public function sala()
    {
        return $this->belongsTo(Sala::class);
    }

}
