<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait HasAuditoria
{
    public static function bootHasAuditoria(): void
    {
        static::creating(function ($model) {
            $model->registrado_por = Auth::id();
        });

        static::updating(function ($model) {
            $model->modificado_por = Auth::id();
        });
    }

    public function registradoPor()
    {
        return $this->belongsTo(\App\Models\User::class, 'registrado_por');
    }

    public function modificadoPor()
    {
        return $this->belongsTo(\App\Models\User::class, 'modificado_por');
    }
}