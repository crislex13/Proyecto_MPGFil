<?php

namespace App\Http\Controllers;

use App\Models\IngresoProducto;
use App\Models\Productos;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IngresoProductoReportController extends Controller
{
    public function dia(Request $request)
    {
        $date = $request->input('date', now()->toDateString());
        $start = Carbon::parse($date)->startOfDay();
        $end = Carbon::parse($date)->endOfDay();

        $data = $this->buildData($start, $end);
        $data['titulo'] = 'Reporte Diario de Ingresos de Productos';
        $data['periodo'] = Carbon::parse($date)->isoFormat('dddd D [de] MMMM YYYY');
        $data['generado_por'] = optional(auth()->user())->name ?? 'Sistema';
        $data['generado_el'] = now()->isoFormat('dddd D [de] MMMM YYYY, HH:mm');

        return $this->pdf($data, "reporte-ingresos-diario-{$start->toDateString()}.pdf");
    }

    public function mes(Request $request)
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = (clone $start)->endOfMonth();

        $data = $this->buildData($start, $end);
        $data['titulo'] = 'Reporte Mensual de Ingresos de Productos';
        $data['periodo'] = $start->isoFormat('MMMM [de] YYYY');
        $data['generado_por'] = optional(auth()->user())->name ?? 'Sistema';
        $data['generado_el'] = now()->isoFormat('dddd D [de] MMMM YYYY, HH:mm');

        return $this->pdf($data, "reporte-ingresos-{$start->format('Y-m')}.pdf");
    }

    public function anio(Request $request)
    {
        $year = (int) $request->input('year', now()->year);
        $start = Carbon::create($year, 1, 1)->startOfYear();
        $end = (clone $start)->endOfYear();

        $data = $this->buildData($start, $end);
        $data['titulo'] = 'Reporte Anual de Ingresos de Productos';
        $data['periodo'] = (string) $year;
        $data['generado_por'] = optional(auth()->user())->name ?? 'Sistema';
        $data['generado_el'] = now()->isoFormat('dddd D [de] MMMM YYYY, HH:mm');

        return $this->pdf($data, "reporte-ingresos-{$year}.pdf");
    }

    public function rango(Request $request)
    {
        $start = Carbon::parse($request->input('start'))->startOfDay();
        $end = Carbon::parse($request->input('end'))->endOfDay();

        $data = $this->buildData($start, $end);
        $data['titulo'] = 'Reporte de Ingresos de Productos (Rango)';
        $data['periodo'] = $start->isoFormat('D [de] MMM YYYY') . ' — ' . $end->isoFormat('D [de] MMM YYYY');
        $data['generado_por'] = optional(auth()->user())->name ?? 'Sistema';
        $data['generado_el'] = now()->isoFormat('dddd D [de] MMMM YYYY, HH:mm');

        return $this->pdf($data, "reporte-ingresos-{$start->toDateString()}_{$end->toDateString()}.pdf");
    }

    /** ---------------- Core ---------------- */
    protected function buildData(Carbon $start, Carbon $end): array
    {
        Carbon::setLocale('es');

        $ingTable = (new IngresoProducto)->getTable(); // ingresos_productos
        $prodTable = (new Productos)->getTable();       // productos
        $userTable = (new User)->getTable();            // users

        // Subtotal por registro: (unidades*precio_unitario) + (paquetes*precio_paquete)
        $subtotalExpr = "COALESCE({$ingTable}.cantidad_unidades,0)*COALESCE({$ingTable}.precio_unitario,0)"
            . " + COALESCE({$ingTable}.cantidad_paquetes,0)*COALESCE({$ingTable}.precio_paquete,0)";

        // Base
        $base = IngresoProducto::query()
            ->with(['producto', 'registradoPor']) // <- en vez de 'usuario'
            ->whereBetween("{$ingTable}.fecha", [$start->toDateTimeString(), $end->toDateTimeString()]);

        // KPIs
        $registros = (clone $base)->count();
        $totalUnidades = (clone $base)->sum("{$ingTable}.cantidad_unidades");
        $totalPaquetes = (clone $base)->sum("{$ingTable}.cantidad_paquetes");
        $totalCosto = (clone $base)->selectRaw("SUM({$subtotalExpr}) as t")->value('t') ?? 0;
        $ticketPromedio = $registros > 0 ? round($totalCosto / $registros, 2) : 0;

        // Agrupaciones
        $porProducto = (clone $base)
            ->leftJoin("{$prodTable} as p", "p.id", "=", "{$ingTable}.producto_id")
            ->selectRaw("
                {$ingTable}.producto_id,
                COALESCE(p.nombre,'—') as producto,
                SUM({$ingTable}.cantidad_unidades) as unidades,
                SUM({$ingTable}.cantidad_paquetes) as paquetes,
                SUM({$subtotalExpr}) as costo_total
            ")
            ->groupBy("{$ingTable}.producto_id", "p.nombre")
            ->orderByDesc('costo_total')
            ->get();

        $porUsuario = (clone $base)
            ->leftJoin("{$userTable} as u", "u.id", "=", "{$ingTable}.registrado_por")
            ->selectRaw("
        {$ingTable}.registrado_por as usuario_id,
        COALESCE(u.name,'—') as usuario,
        COUNT(*) as registros,
        SUM({$subtotalExpr}) as costo_total
    ")
            ->groupBy("{$ingTable}.registrado_por", "u.name")
            ->orderByDesc('costo_total')
            ->get();

        $porDiaSemana = (clone $base)
            ->selectRaw("
        DAYOFWEEK({$ingTable}.fecha) AS dow,
        COUNT(*) AS registros,
        SUM({$subtotalExpr}) AS costo_total
    ")
            ->groupBy('dow')
            ->orderBy('dow')
            ->get()
            ->map(function ($r) {
                $map = [1 => 'Domingo', 2 => 'Lunes', 3 => 'Martes', 4 => 'Miércoles', 5 => 'Jueves', 6 => 'Viernes', 7 => 'Sábado'];
                $r->dia_nombre = $map[(int) $r->dow] ?? $r->dow;
                return $r;
            });

        // Detalle (para tabla principal del PDF)
        $detalle = (clone $base)
            ->select("{$ingTable}.*")
            ->orderBy("{$ingTable}.fecha", 'desc')
            ->get()
            ->map(function ($i) {
                $i->subtotal = ($i->cantidad_unidades ?? 0) * ($i->precio_unitario ?? 0)
                    + ($i->cantidad_paquetes ?? 0) * ($i->precio_paquete ?? 0);
                return $i;
            });

        // Aux para barras relativas si las usas
        $maxCostoProd = max($porProducto->max('costo_total') ?? 1, 1);
        $maxCostoUser = max($porUsuario->max('costo_total') ?? 1, 1);

        return compact(
            'start',
            'end',
            'registros',
            'totalUnidades',
            'totalPaquetes',
            'totalCosto',
            'ticketPromedio',
            'porProducto',
            'porUsuario',
            'porDiaSemana',
            'detalle',
            'maxCostoProd',
            'maxCostoUser'
        );
    }

    protected function pdf(array $data, string $filename)
    {
        return Pdf::loadView('pdf.ingresos_productos_reporte', $data)
            ->setPaper('a4', 'portrait')
            ->stream($filename);
    }
}
