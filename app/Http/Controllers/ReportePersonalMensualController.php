<?php

namespace App\Http\Controllers;

use App\Models\PagoPersonal;
use App\Models\Personal;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;

class ReportePersonalMensualController extends Controller
{
    public function fichaMensual($id)
    {
        $inicioMes = now()->startOfMonth();
        $finMes = now()->endOfMonth();

        $personal = Personal::with([
            'turnos',
            'asistencias' => function ($q) use ($inicioMes, $finMes) {
                $q->whereBetween('fecha', [$inicioMes, $finMes]);
            },
            'permisos' => function ($q) use ($inicioMes, $finMes) {
                $q->whereBetween('fecha_inicio', [$inicioMes, $finMes])
                    ->orWhereBetween('fecha_fin', [$inicioMes, $finMes]);
            }
        ])->findOrFail($id);

        $pagos = PagoPersonal::with(['turno', 'sala'])
            ->where('personal_id', $personal->id)
            ->where('pagado', true)
            ->whereBetween('fecha', [$inicioMes, $finMes])
            ->get();

        $salasUnicas = $pagos
            ->filter(fn($p) => $p->sala)
            ->pluck('sala.nombre')
            ->unique();

        return Pdf::loadView('pdf.ficha-personal-mensual', [
            'personal' => $personal,
            'pagos' => $pagos,
            'salasUnicas' => $salasUnicas,
            'mes' => Carbon::now()->translatedFormat('F Y')
        ])->setPaper('A4')->stream('Ficha_Personal_Mensual_' . $personal->ci . '.pdf');
    }
}
