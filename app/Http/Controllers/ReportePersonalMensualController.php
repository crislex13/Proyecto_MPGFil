<?php

namespace App\Http\Controllers;

use App\Models\PagoPersonal;
use App\Models\Personal;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;

class ReportePersonalMensualController extends Controller
{
    public function fichaMensual($id, Request $request)
    {
        // Permite año/mes por query; por defecto, mes actual
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $inicioMes = Carbon::create($year, $month, 1)->startOfMonth();
        $finMes = $inicioMes->clone()->endOfMonth();

        $personal = Personal::with([
            'turnos',
            'asistencias' => function ($q) use ($inicioMes, $finMes) {
                $q->whereBetween('fecha', [$inicioMes, $finMes]);
            },
            'permisos' => function ($q) use ($inicioMes, $finMes) {
                $q->where(function ($qq) use ($inicioMes, $finMes) {
                    $qq->whereBetween('fecha_inicio', [$inicioMes, $finMes])
                        ->orWhereBetween('fecha_fin', [$inicioMes, $finMes])
                        ->orWhere(function ($q3) use ($inicioMes, $finMes) {
                            // Permiso que cubre todo el mes
                            $q3->where('fecha_inicio', '<=', $inicioMes)
                                ->where('fecha_fin', '>=', $finMes);
                        });
                });
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

        // ===== KPIs de asistencias =====
        $asistencias = $personal->asistencias;
        $puntuales = (int) $asistencias->where('estado', 'puntual')->count();
        $atrasos = (int) $asistencias->where('estado', 'atrasado')->count();
        $faltas = (int) $asistencias->where('estado', 'falta')->count();   // si aún no registras faltas, quedará en 0
        $conPermiso = (int) $personal->permisos->count();

        $totalEventosMes = max($puntuales + $atrasos + $faltas, 1);
        $porcPuntualidad = round(($puntuales / $totalEventosMes) * 100);
        $porcAtraso = round(($atrasos / $totalEventosMes) * 100);
        $porcFalta = round(($faltas / $totalEventosMes) * 100);

        // ===== Pagos: totales y ranking por sala =====
        $totalPagosMes = (float) $pagos->sum('monto');

        $pagosPorSala = $pagos->groupBy(fn($p) => $p->sala->nombre ?? '—')
            ->map(function ($rows, $sala) {
                return (object) [
                    'sala' => $sala,
                    'monto' => (float) $rows->sum('monto'),
                    'items' => $rows->count(),
                ];
            })
            ->values()
            ->sortByDesc('monto')
            ->values();

        $maxMontoSala = max(($pagosPorSala->max(fn($o) => $o->monto) ?? 1), 1);

        $mesTexto = $inicioMes->translatedFormat('F Y');

        $data = [
            'personal' => $personal,
            'pagos' => $pagos,
            'salasUnicas' => $salasUnicas,
            'mes' => $mesTexto,

            // KPIs asistencias
            'puntuales' => $puntuales,
            'atrasos' => $atrasos,
            'faltas' => $faltas,
            'conPermiso' => $conPermiso,
            'porcPuntualidad' => $porcPuntualidad,
            'porcAtraso' => $porcAtraso,
            'porcFalta' => $porcFalta,

            // Pagos y ranking por sala
            'totalPagosMes' => $totalPagosMes,
            'pagosPorSala' => $pagosPorSala,
            'maxMontoSala' => $maxMontoSala,

            // Periodo
            'inicioMes' => $inicioMes,
            'finMes' => $finMes,
        ];

        return Pdf::loadView('pdf.ficha-personal-mensual', $data)
            ->setPaper('A4')
            ->stream('Ficha_Personal_Mensual_' . $personal->ci . '_' . $inicioMes->format('Y_m') . '.pdf');
    }
}
