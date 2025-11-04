<?php

namespace App\Http\Controllers;

use App\Models\Turno;
use App\Models\Asistencia;
use App\Models\Personal;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TurnoReportController extends Controller
{
    public function coberturaMensual(Request $request)
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $inicio = Carbon::create($year, $month, 1)->startOfMonth();
        $fin = $inicio->copy()->endOfMonth();

        $turnos = Turno::with('personal')
            ->whereIn('estado', ['activo', 'inactivo'])
            ->orderBy('personal_id')
            ->orderBy('dia')
            ->orderBy('hora_inicio')
            ->get();

        $countWeekdayInMonth = function (int $weekday, Carbon $start, Carbon $end): int {
            $cursor = $start->copy();
            $count = 0;
            while ($cursor->lte($end)) {
                if ($cursor->isoWeekday() === $weekday)
                    $count++;
                $cursor->addDay();
            }
            return $count;
        };

        $totTurnos = $turnos->count();
        $activos = $turnos->where('estado', 'activo')->count();
        $inactivos = $turnos->where('estado', 'inactivo')->count();

        $porDia = collect([1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'])
            ->map(fn($nombre, $n) => (object) ['dia' => $nombre, 'c' => $turnos->where('dia', $n)->count()])
            ->values();
        $maxDia = max($porDia->pluck('c')->toArray() ?: [0]);

        $horasProgramadasTotales = 0;
        foreach ($turnos as $t) {
            if (!$t->duracion_minutos)
                continue;
            $vecesEsteMes = $countWeekdayInMonth((int) $t->dia, $inicio, $fin);
            $horasProgramadasTotales += ($t->duracion_minutos / 60) * $vecesEsteMes;
        }

        $porPersonalHoras = $turnos->groupBy('personal_id')->map(function ($rows) use ($countWeekdayInMonth, $inicio, $fin) {
            $p = optional($rows->first()->personal);
            $horas = 0;
            foreach ($rows as $t) {
                if (!$t->duracion_minutos)
                    continue;
                $veces = $countWeekdayInMonth((int) $t->dia, $inicio, $fin);
                $horas += ($t->duracion_minutos / 60) * $veces;
            }
            return (object) ['personal' => $p?->nombre_completo ?? '—', 'horas' => round($horas, 2)];
        })->values()->sortByDesc('horas')->values();
        $maxHorasPersonal = max($porPersonalHoras->pluck('horas')->toArray() ?: [0]);

        $detalleCobertura = $turnos->map(function ($t) use ($inicio, $fin, $countWeekdayInMonth) {
            $programados = $countWeekdayInMonth((int) $t->dia, $inicio, $fin);

            // MySQL WEEKDAY(): 0=lunes ... 6=domingo
            $weekdayMysql = $t->dia % 7; // 1..7 -> 1..6,0
            $asis = Asistencia::query()
                ->whereBetween('fecha', [$inicio->toDateString(), $fin->toDateString()])
                ->where('asistible_type', Personal::class)
                ->where('asistible_id', $t->personal_id)
                ->whereRaw('WEEKDAY(fecha) = ?', [$weekdayMysql])
                ->count();

            $cumpl = $programados > 0 ? round(($asis / $programados) * 100) : 0;

            $diaNombre = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'][$t->dia] ?? (string) $t->dia;

            return (object) [
                'personal' => optional($t->personal)->nombre_completo ?? '—',
                'turno' => $t->nombre,
                'dia' => $diaNombre,
                'hora' => "{$t->hora_inicio} - {$t->hora_fin}",
                'programados' => (int) $programados,
                'asistidos' => (int) $asis,
                'cumplimiento' => (int) $cumpl,
            ];
        })->sortBy(['personal', 'dia', 'turno'])->values();

        $titulo = 'Cobertura de Turnos (Mensual)';
        $periodo = Str::ucfirst($inicio->locale('es')->isoFormat('MMMM [de] YYYY'));
        $generadoPor = auth()->user()->name ?? 'Sistema';
        $generadoEl = now()->locale('es')->isoFormat('dddd D [de] MMMM YYYY, HH:mm');
        $logo = public_path('images/LogosMPG/Recurso 3.png');

        $data = compact(
            'titulo',
            'periodo',
            'generadoPor',
            'generadoEl',
            'logo',
            'totTurnos',
            'activos',
            'inactivos',
            'horasProgramadasTotales',
            'porDia',
            'maxDia',
            'porPersonalHoras',
            'maxHorasPersonal',
            'detalleCobertura'
        );

        return Pdf::loadView('pdf.turnos_cobertura_mensual', $data)
            ->setPaper('A4', 'portrait')
            ->stream('turnos_cobertura_' . $inicio->format('Y-m') . '.pdf');
    }
}
