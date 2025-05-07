<?php

namespace App\Observers;

use App\Models\PermisoCliente;
use App\Models\PlanCliente;
use Carbon\Carbon;
use Filament\Notifications\Notification;

class PermisoClienteObserver
{
    public function saving(PermisoCliente $permiso)
    {
        // Validar solo si se está aprobando el permiso
        if ($permiso->isDirty('estado') && $permiso->estado === 'aprobado') {
            $mes = Carbon::parse($permiso->fecha)->month;
            $anio = Carbon::parse($permiso->fecha)->year;

            $cantidadAprobados = PermisoCliente::where('cliente_id', $permiso->cliente_id)
                ->where('estado', 'aprobado')
                ->whereMonth('fecha', $mes)
                ->whereYear('fecha', $anio)
                // Excluir el permiso actual si ya está guardado (por si es una edición)
                ->when($permiso->exists, fn($q) => $q->where('id', '!=', $permiso->id))
                ->count();

            if ($cantidadAprobados >= 3) {
                $permiso->estado = 'pendiente';

                Notification::make()
                    ->title('🚫 Límite de permisos alcanzado')
                    ->body('Este cliente ya tiene 3 permisos aprobados este mes.')
                    ->danger()
                    ->send();
            }
        }
    }

    public function updated(PermisoCliente $permiso)
    {
        if ($permiso->isDirty('estado')) {
            $original = $permiso->getOriginal('estado');
            $nuevo = $permiso->estado;

            $plan = PlanCliente::where('cliente_id', $permiso->cliente_id)
                ->where('estado', 'vigente')
                ->orderByDesc('fecha_inicio')
                ->first();

            if ($plan) {
                if ($original !== 'aprobado' && $nuevo === 'aprobado') {
                    $plan->fecha_final = Carbon::parse($plan->fecha_final)->addDay();
                    $plan->save();
                }

                if ($original === 'aprobado' && in_array($nuevo, ['pendiente', 'rechazado'])) {
                    $plan->fecha_final = Carbon::parse($plan->fecha_final)->subDay();
                    $plan->save();
                }
            }
        }
    }
}