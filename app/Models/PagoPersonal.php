<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasAuditoria;

class PagoPersonal extends Model
{
    use HasAuditoria;
    protected $table = 'pagos_personal';
    protected $fillable = [
        'personal_id',
        'fecha',
        'monto',
        'descripcion',
        'turno_id',
        'sala_id',
        'pagado',
        'registrado_por',
        'modificado_por',
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

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    public function modificadoPor()
    {
        return $this->belongsTo(User::class, 'modificado_por');
    }


}
