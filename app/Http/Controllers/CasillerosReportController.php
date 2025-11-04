<?php

namespace App\Http\Controllers;

use App\Models\Casillero;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CasillerosReportController extends Controller
{
    public function dia(Request $r)
    {
        $date = Carbon::parse($r->input('date', now()->toDateString()));
        $start = $date->copy()->startOfDay();
        $end = $date->copy()->endOfDay();

        $data = $this->buildData($start, $end);
        $data['titulo'] = 'Casilleros — Diario';
        $data['periodo'] = $date->isoFormat('dddd D [de] MMMM YYYY');
        $data['generado_por'] = optional(auth()->user())->name ?? 'Sistema';
        $data['generado_el'] = now()->isoFormat('dddd D [de] MMMM YYYY, HH:mm');

        return $this->pdf($data, "casilleros-{$date->format('Ymd')}.pdf");
    }

    public function mes(Request $r)
    {
        $year = (int) $r->input('year', now()->year);
        $month = (int) $r->input('month', now()->month);
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $data = $this->buildData($start, $end);
        $data['titulo'] = 'Casilleros — Mensual';
        $data['periodo'] = $start->isoFormat('MMMM YYYY');
        $data['generado_por'] = optional(auth()->user())->name ?? 'Sistema';
        $data['generado_el'] = now()->isoFormat('dddd D [de] MMMM YYYY, HH:mm');

        return $this->pdf($data, "casilleros-{$start->format('Y-m')}.pdf");
    }

    public function anio(Request $r)
    {
        $year = (int) $r->input('year', now()->year);
        $start = Carbon::create($year, 1, 1)->startOfYear();
        $end = Carbon::create($year, 12, 31)->endOfYear();

        $data = $this->buildData($start, $end);
        $data['titulo'] = 'Casilleros — Anual';
        $data['periodo'] = (string) $year;
        $data['generado_por'] = optional(auth()->user())->name ?? 'Sistema';
        $data['generado_el'] = now()->isoFormat('dddd D [de] MMMM YYYY, HH:mm');

        return $this->pdf($data, "casilleros-{$year}.pdf");
    }

    /** ---------- Núcleo: agregaciones por rango ---------- */
    protected function buildData(Carbon $start, Carbon $end): array
    {
        // Base con posibles filtros de rango (entregas dentro del rango)
        $base = Casillero::query()
            ->with(['cliente:id,nombre,apellido_paterno,apellido_materno,foto'])
            ->when(true, function ($q) use ($start, $end) {
                // Consideramos casilleros entregados en el rango
                $q->whereBetween(DB::raw('DATE(fecha_entrega_llave)'), [$start->toDateString(), $end->toDateString()]);
            });

        // Totales por estado
        $porEstado = Casillero::select('estado', DB::raw('COUNT(*) as c'))
            ->groupBy('estado')
            ->pluck('c', 'estado')
            ->toArray();

        $totalCasilleros = array_sum($porEstado ?: []);

        // Ingresos por mensualidad (solo los que se entregaron en el rango y están/estaban ocupados)
        $ingresosMensualidad = (clone $base)
            ->where('estado', 'ocupado')
            ->sum('costo_mensual');

        // Reposiciones en el rango (tomamos updated_at en rango si hay cambios o simplemente sumamos monto_reposiciones de los que variaron en el período; si no, usamos los del rango por fecha_entrega)
        $reposiciones = Casillero::query()
            ->whereBetween('updated_at', [$start, $end])
            ->sum('monto_reposiciones');

        // Distribución por método de pago (mensualidad)
        $metodosMensual = (clone $base)
            ->whereNotNull('metodo_pago')
            ->selectRaw("LOWER(TRIM(COALESCE(metodo_pago,'efectivo'))) as metodo_pago, COUNT(*) as cantidad, COALESCE(SUM(costo_mensual),0) as cobrado")
            ->groupBy(DB::raw("LOWER(TRIM(COALESCE(metodo_pago,'efectivo')))"))
            ->get();

        // Distribución por método de pago (reposiciones en el rango por updated_at)
        $metodosRepos = Casillero::query()
            ->whereBetween('updated_at', [$start, $end])
            ->whereNotNull('metodo_pago_reposicion')
            ->selectRaw("LOWER(TRIM(COALESCE(metodo_pago_reposicion,'efectivo'))) as metodo_pago, COUNT(*) as cantidad, COALESCE(SUM(monto_reposiciones),0) as cobrado")
            ->groupBy(DB::raw("LOWER(TRIM(COALESCE(metodo_pago_reposicion,'efectivo')))"))
            ->get();

        // Próximos a vencer (dentro de 7 días desde $end)
        $proximosAVencer = Casillero::query()
            ->whereNotNull('fecha_final_llave')
            ->whereBetween(DB::raw('DATE(fecha_final_llave)'), [$end->copy()->addDay()->toDateString(), $end->copy()->addDays(7)->toDateString()])
            ->count();

        // Vencidos hasta el fin del rango
        $vencidos = Casillero::query()
            ->whereNotNull('fecha_final_llave')
            ->whereDate('fecha_final_llave', '<', $end->toDateString())
            ->count();

        // Detalle de casilleros entregados en el rango
        $detalle = (clone $base)
            ->orderBy('fecha_entrega_llave', 'asc')
            ->get();

        // KPIs
        $kpis = [
            'total' => $totalCasilleros,
            'disponibles' => (int) ($porEstado['disponible'] ?? 0),
            'ocupados' => (int) ($porEstado['ocupado'] ?? 0),
            'mantenimiento' => (int) ($porEstado['mantenimiento'] ?? 0),
            'ingresos_mensual' => (float) $ingresosMensualidad,
            'ingresos_repos' => (float) $reposiciones,
            'ingresos_totales' => (float) ($ingresosMensualidad + $reposiciones),
            'proximos_vencer_7d' => (int) $proximosAVencer,
            'vencidos' => (int) $vencidos,
        ];

        return compact('start', 'end', 'kpis', 'porEstado', 'metodosMensual', 'metodosRepos', 'detalle');
    }

    protected function pdf(array $data, string $filename)
    {
        return Pdf::loadView('pdf.casilleros_resumen', $data)
            ->setPaper('a4', 'portrait')
            ->stream($filename);
    }
}
