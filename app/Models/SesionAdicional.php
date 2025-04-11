<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SesionAdicional extends Model
{
    protected $table = 'sesiones_adicionales';
    protected $fillable = [
        'plan_cliente_id',
        'instructor_id',
        'tipo_sesion',
        'precio',
        'fecha',
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

    public function planCliente(): BelongsTo
    {
        return $this->belongsTo(PlanCliente::class);
    }

    public function instructor()
    {
        return $this->belongsTo(Personal::class, 'personal_id');
    }

    public function turno()
    {
        return $this->belongsTo(Turno::class);
    }

}