<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanCliente extends Model
{
    protected $table = 'planes_clientes';

    protected $fillable = [
        'cliente_id',
        'plan_id',
        'disciplina_id',
        'fecha_inicio',
        'fecha_final',
        'precio_plan',
        'a_cuenta',
        'saldo',
        'total',
        'casillero_monto',
        'metodo_pago',
        'comprobante',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Clientes::class, 'cliente_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function disciplina(): BelongsTo
    {
        return $this->belongsTo(Disciplina::class);
    }



}