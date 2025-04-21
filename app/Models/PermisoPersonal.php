<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Personal;
use Illuminate\Support\Facades\Auth;

class PermisoPersonal extends Model
{
    protected $table = 'permisos_personal';

    protected $fillable = [
        'personal_id',
        'fecha_inicio',
        'fecha_fin',
        'tipo',
        'motivo',
        'estado',
        'autorizado_por',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];
    public function personal(): BelongsTo
    {
        return $this->belongsTo(Personal::class);
    }

    public function autorizadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autorizado_por');
    }

    protected static function booted()
    {
        static::creating(function ($permiso) {
            if (!$permiso->autorizado_por) {
                $permiso->autorizado_por = Auth::id();
            }
        });
    }
}