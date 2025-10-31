<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PlanCliente;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PlanReportController extends Controller
{
    public function dia(Request $request)
    {
        $date = $request->input('date', now()->toDateString());
        $start = Carbon::parse($date)->startOfDay();
        $end = Carbon::parse($date)->endOfDay();

        $data = $this->buildData($start, $end);
        $data['titulo'] = 'Reporte Diario de Planes';
        $data['periodo'] = Carbon::parse($date)->isoFormat('dddd D [de] MMMM YYYY');

        $data['generado_por'] = optional(auth()->user())->name ?? 'Sistema';
        $data['generado_el'] = now()->isoFormat('dddd D [de] MMMM YYYY, HH:mm');

        return $this->pdf($data, "reporte-planes-diario-{$start->toDateString()}.pdf");
    }

    public function mes(Request $request)
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = Carbon::create($year, $month, 1)->endOfMonth();

        $data = $this->buildData($start, $end);
        $data['titulo'] = 'Reporte Mensual de Planes';
        $data['periodo'] = $start->isoFormat('MMMM YYYY');

        $data['generado_por'] = optional(auth()->user())->name ?? 'Sistema';
        $data['generado_el'] = now()->isoFormat('dddd D [de] MMMM YYYY, HH:mm');

        return $this->pdf($data, "reporte-planes-{$start->format('Y-m')}.pdf");
    }

    public function anio(Request $request)
    {
        $year = (int) $request->input('year', now()->year);
        $start = Carbon::create($year, 1, 1)->startOfYear();
        $end = Carbon::create($year, 12, 31)->endOfYear();

        $data = $this->buildData($start, $end);
        $data['titulo'] = 'Reporte Anual de Planes';
        $data['periodo'] = (string) $year;

        $data['generado_por'] = optional(auth()->user())->name ?? 'Sistema';
        $data['generado_el'] = now()->isoFormat('dddd D [de] MMMM YYYY, HH:mm');

        return $this->pdf($data, "reporte-planes-{$year}.pdf");
    }

    /** ---------- Núcleo: agregaciones por rango ---------- */
    protected function buildData(Carbon $start, Carbon $end): array
    {
        $base = PlanCliente::query()
            ->with(['plan:id,nombre', 'disciplina:id,nombre', 'cliente:id,nombre,apellido_paterno,apellido_materno,foto'])
            ->whereBetween('fecha_inicio', [$start->toDateString(), $end->toDateString()]);

        // Resumen ejecutivo
        $contratos = (clone $base)->count();
        $facturado = (clone $base)->sum('total');      // Total del plan vendido
        $cobrado = (clone $base)->sum('a_cuenta');   // Cobrado (ingreso)
        $saldo = (clone $base)->sum('saldo');      // Pendiente

        // TOP planes por cantidad (JOIN y GROUP BY estables)
        $porPlan = (clone $base)
            ->leftJoin('planes', 'planes.id', '=', 'planes_clientes.plan_id')
            ->selectRaw('planes_clientes.plan_id, COALESCE(planes.nombre, "—") as plan_nombre, COUNT(*) as contratos, SUM(planes_clientes.total) as total, SUM(planes_clientes.a_cuenta) as cobrado, SUM(planes_clientes.saldo) as saldo')
            ->groupBy('planes_clientes.plan_id', 'planes.nombre')
            ->orderByDesc('contratos')
            ->get();

        $topPlanes = $porPlan->take(5);

        // Distribución por estado
        $porEstado = (clone $base)
            ->selectRaw('estado, COUNT(*) as cantidad')
            ->groupBy('estado')
            ->pluck('cantidad', 'estado')
            ->toArray();

        // Por disciplina (JOIN y GROUP BY)
        $porDisciplina = (clone $base)
            ->leftJoin('disciplinas', 'disciplinas.id', '=', 'planes_clientes.disciplina_id')
            ->selectRaw('planes_clientes.disciplina_id, COALESCE(disciplinas.nombre, "—") as disciplina_nombre, COUNT(*) as contratos, SUM(planes_clientes.a_cuenta) as cobrado')
            ->groupBy('planes_clientes.disciplina_id', 'disciplinas.nombre')
            ->orderByDesc('contratos')
            ->get();

        // Métodos de pago
        $metodosPago = (clone $base)
            ->selectRaw('metodo_pago, COUNT(*) as cantidad, SUM(a_cuenta) as cobrado')
            ->groupBy('metodo_pago')
            ->get();

        // Nuevos vs Renovaciones (nuevo = primer plan del cliente)
        $nuevos = PlanCliente::whereBetween('fecha_inicio', [$start->toDateString(), $end->toDateString()])
            ->whereRaw('fecha_inicio = (SELECT MIN(pc2.fecha_inicio) FROM planes_clientes pc2 WHERE pc2.cliente_id = planes_clientes.cliente_id)')
            ->count();
        $renovaciones = max($contratos - $nuevos, 0);

        // Detalle de contratos en el rango
        $detalle = (clone $base)->orderBy('fecha_inicio', 'desc')->get();

        // Para “barras” CSS (porcentaje seguro)
        $maxContratosPlan = max($porPlan->max('contratos') ?? 1, 1);

        return compact(
            'start',
            'end',
            'contratos',
            'facturado',
            'cobrado',
            'saldo',
            'porPlan',
            'topPlanes',
            'porEstado',
            'porDisciplina',
            'metodosPago',
            'nuevos',
            'renovaciones',
            'detalle',
            'maxContratosPlan'
        );
    }

    protected function pdf(array $data, string $filename)
    {
        return Pdf::loadView('pdf.planes_resumen', $data)
            ->setPaper('a4', 'portrait')
            ->stream($filename); // o ->download($filename)
    }
}
