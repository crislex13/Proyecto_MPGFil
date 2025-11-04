<?php

namespace App\Http\Controllers;

use App\Models\PagoPersonal;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PagoPersonalReportController extends Controller
{
    public function comprobante(PagoPersonal $pago)
    {
        $pago->load(['personal', 'turno', 'sala']);

        $data = [
            'titulo' => 'Comprobante de Pago a Personal',
            'pago' => $pago,
            'generado_el' => now()->isoFormat('dddd D [de] MMMM YYYY, HH:mm'),
            'generado_por' => optional(auth()->user())->name ?? 'Sistema',
            'logo' => public_path('images/LogosMPG/Recurso 3.png'),
        ];

        return Pdf::loadView('pdf.pago_comprobante', $data)
            ->setPaper('A4', 'portrait')
            ->stream('comprobante-pago-' . $pago->id . '.pdf');
    }

    public function dia(Request $req)
    {
        $date = $req->date ? Carbon::parse($req->date) : today();
        $start = $date->clone()->startOfDay();
        $end = $date->clone()->endOfDay();

        return $this->resumenCore($start, $end, "Resumen Diario de Pagos ({$date->isoFormat('D [de] MMMM YYYY')})");
    }

    public function mes(Request $req)
    {
        $y = (int) ($req->year ?? now()->year);
        $m = (int) ($req->month ?? now()->month);
        $start = Carbon::create($y, $m, 1)->startOfMonth();
        $end = $start->clone()->endOfMonth();

        return $this->resumenCore($start, $end, "Resumen Mensual de Pagos ({$start->isoFormat('MMMM [de] YYYY')})");
    }

    public function anio(Request $req)
    {
        $y = (int) ($req->year ?? now()->year);
        $start = Carbon::create($y, 1, 1)->startOfYear();
        $end = $start->clone()->endOfYear();

        return $this->resumenCore($start, $end, "Resumen Anual de Pagos ({$y})");
    }

    public function rango(Request $req)
    {
        $start = Carbon::parse($req->start)->startOfDay();
        $end = Carbon::parse($req->end)->endOfDay();

        return $this->resumenCore($start, $end, "Resumen de Pagos ({$start->isoFormat('D MMM YYYY')} – {$end->isoFormat('D MMM YYYY')})");
    }

    // ---------------- CORE ----------------
    protected function resumenCore(Carbon $start, Carbon $end, string $titulo)
    {
        $pagos = PagoPersonal::with(['personal', 'turno', 'sala'])
            ->whereBetween('fecha', [$start->toDateString(), $end->toDateString()])
            ->get();

        // Resumen
        $registros = $pagos->count();
        $pagados = $pagos->where('pagado', true)->count();
        $pendientes = $pagos->where('pagado', false)->count();
        $montoTotal = (float) $pagos->sum('monto');

        // Por método
        $porMetodo = $pagos->groupBy('metodo_pago')->map(function ($rows, $metodo) {
            return (object) [
                'metodo' => $metodo ?? '—',
                'monto' => (float) $rows->sum('monto'),
                'count' => $rows->count(),
            ];
        })->values()->sortByDesc('monto')->values();
        $maxMetodoMonto = max($porMetodo->pluck('monto')->toArray() ?: [0]);

        // Por sala
        $porSala = $pagos->groupBy('sala_id')->map(function ($rows) {
            $sala = optional($rows->first()->sala)->nombre ?? '—';
            return (object) [
                'sala' => $sala,
                'monto' => (float) $rows->sum('monto'),
                'count' => $rows->count(),
            ];
        })->values()->sortByDesc('monto')->values();
        $maxSalaMonto = max($porSala->pluck('monto')->toArray() ?: [0]);

        // Por turno (muestra nombre y día literal)
        $porTurno = $pagos->groupBy('turno_id')->map(function ($rows) {
            $t = optional($rows->first()->turno);
            return (object) [
                'turno' => $t->nombre ?? '—',
                'dia' => $t?->dia_nombre ?? '—',
                'monto' => (float) $rows->sum('monto'),
                'count' => $rows->count(),
            ];
        })->values()->sortByDesc('monto')->values();
        $maxTurnoMonto = max($porTurno->pluck('monto')->toArray() ?: [0]);

        // Ranking por personal
        $porPersonal = $pagos->groupBy('personal_id')->map(function ($rows) {
            return (object) [
                'personal' => optional($rows->first()->personal)->nombre_completo ?? '—',
                'monto' => (float) $rows->sum('monto'),
                'count' => $rows->count(),
            ];
        })->values()->sortByDesc('monto')->values();
        $maxPersonalMonto = max($porPersonal->pluck('monto')->toArray() ?: [0]);

        // Detalle
        $detalle = $pagos->map(function ($p) {
            return (object) [
                'fecha' => \Carbon\Carbon::parse($p->fecha)->format('d/m/Y'),
                'personal' => optional($p->personal)->nombre_completo ?? '—',
                'turno' => optional($p->turno)->nombre ?? '—',
                'dia' => optional($p->turno)->dia_nombre ?? '—',
                'sala' => optional($p->sala)->nombre ?? '—',
                'metodo' => $p->metodo_pago ?? '—',
                'monto' => (float) $p->monto,
                'estado' => $p->pagado ? 'Pagado' : 'Pendiente',
            ];
        });

        // Header en camelCase para tu Blade
        $data = [
            'titulo' => $titulo,
            'periodo' => "{$start->isoFormat('D MMM YYYY')} – {$end->isoFormat('D MMM YYYY')}",
            'logo' => public_path('images/LogosMPG/Recurso 3.png'),
            'generadoPor' => auth()->user()->name ?? 'Sistema',
            'generadoEl' => now()->isoFormat('dddd D [de] MMMM YYYY, HH:mm'),

            'registros' => $registros,
            'pagados' => $pagados,
            'pendientes' => $pendientes,
            'montoTotal' => $montoTotal,

            'porMetodo' => $porMetodo,
            'maxMetodoMonto' => $maxMetodoMonto,

            'porSala' => $porSala,
            'maxSalaMonto' => $maxSalaMonto,

            'porTurno' => $porTurno,
            'maxTurnoMonto' => $maxTurnoMonto,

            'porPersonal' => $porPersonal,
            'maxPersonalMonto' => $maxPersonalMonto,

            'detalle' => $detalle,
        ];

        return Pdf::loadView('pdf.pagos_resumen', $data)
            ->setPaper('A4', 'portrait')
            ->stream('pagos-resumen.pdf');
    }
}
