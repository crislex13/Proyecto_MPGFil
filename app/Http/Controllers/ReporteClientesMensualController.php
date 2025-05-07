<?php

namespace App\Http\Controllers;

use App\Models\Clientes;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReporteClientesMensualController extends Controller
{
    public function reporteMensual($id)
    {
        $cliente = Clientes::with([
            'planesCliente.plan',
            'casillero',
            'sesionesAdicionales.turno',
            'sesionesAdicionales.instructor',
            'asistencias',
            'usuario',
            'modificadoPor',
        ])->findOrFail($id);

        $inicioMes = Carbon::now()->startOfMonth();
        $finMes = Carbon::now()->endOfMonth();

        $planesDelMes = $cliente->planesCliente->filter(function ($plan) use ($inicioMes, $finMes) {
            return $plan->fecha_inicio >= $inicioMes && $plan->fecha_inicio <= $finMes;
        });

        $sesionesDelMes = $cliente->sesionesAdicionales->filter(function ($sesion) use ($inicioMes, $finMes) {
            return $sesion->fecha >= $inicioMes && $sesion->fecha <= $finMes;
        });

        $asistenciasDelMes = $cliente->asistencias->filter(function ($a) use ($inicioMes, $finMes) {
            return $a->fecha >= $inicioMes && $a->fecha <= $finMes;
        });

        return Pdf::loadView('pdf.ficha-cliente-mensual', [
            'cliente' => $cliente,
            'planesDelMes' => $planesDelMes,
            'sesionesDelMes' => $sesionesDelMes,
            'asistenciasDelMes' => $asistenciasDelMes,
        ])->setPaper('A4')->stream('Ficha_Cliente_Mensual_' . $cliente->ci . '.pdf');
    }
}