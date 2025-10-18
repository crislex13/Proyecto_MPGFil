<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasAuditoria;

class SesionAdicional extends Model
{
    use HasAuditoria;
    protected $table = 'sesiones_adicionales';
    protected $fillable = [
        'cliente_id',
        'plan_cliente_id',
        'instructor_id',
        'turno_id',
        'hora_inicio',
        'hora_fin',
        'tipo_sesion',
        'precio',
        'fecha',
        'registrado_por',
        'modificado_por',
    ];

    protected static function booted()
    {
        static::created(function ($sesion) {
            $sesion->planCliente?->recalcularTotal();
        });

        static::updated(function ($sesion) {
            $sesion->planCliente?->recalcularTotal();
        });

        static::deleted(function ($sesion) {
            $sesion->planCliente?->recalcularTotal();
        });
    }

    public function cliente()
    {
        return $this->belongsTo(Clientes::class);
    }

    public function planCliente()
    {
        return $this->belongsTo(PlanCliente::class, 'plan_cliente_id');
    }

    public function instructor()
    {
        return $this->belongsTo(Personal::class, 'instructor_id');
    }

    public function turno()
    {
        return $this->belongsTo(Turno::class, 'turno_id');
    }

    public function disciplina()
    {
        return $this->belongsTo(\App\Models\Disciplina::class);
    }

}