<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

// Models (ajusta los namespaces si difieren)
use App\Models\PlanCliente;
use App\Models\SesionAdicional;
use App\Models\VentaProducto;
use App\Models\PagoPersonal;
use App\Models\Casillero;
use Illuminate\Support\Facades\Log;

class PdfExportController extends Controller
{
    public function finanzasGeneral(Request $request)
    {
        abort_unless(Auth::user()?->hasRole('admin'), 403);

        $tipo = $request->input('tipo', 'diario');  // diario | mensual | anual
        $fecha = Carbon::parse($request->input('fecha', now()->toDateString()));

        [$ini, $fin, $titulo] = match ($tipo) {
            'mensual' => [$fecha->copy()->startOfMonth(), $fecha->copy()->endOfMonth(), 'Finanzas Generales — Mensual'],
            'anual' => [$fecha->copy()->startOfYear(), $fecha->copy()->endOfYear(), 'Finanzas Generales — Anual'],
            default => [$fecha->copy()->startOfDay(), $fecha->copy()->endOfDay(), 'Finanzas Generales — Diario'],
        };

        // =========================
        // RESUMEN DE PLANES (KPI)
        // =========================
        $planesAgg = PlanCliente::whereBetween('planes_clientes.created_at', [$ini, $fin])
            ->selectRaw('COUNT(*) as contratos, COALESCE(SUM(total),0) as facturado, COALESCE(SUM(a_cuenta),0) as cobrado, COALESCE(SUM(saldo),0) as saldo')
            ->first();

        $contratos = (int) ($planesAgg->contratos ?? 0);
        $facturado = (float) ($planesAgg->facturado ?? 0);
        $cobrado = (float) ($planesAgg->cobrado ?? 0);
        $saldo = (float) ($planesAgg->saldo ?? 0);

        // Si NO tienes tipo_contrato, deja en 0.
        $nuevos = 0;
        $renovaciones = 0;

        // =========================
        // TOP 5 PLANES, ESTADO, DISCIPLINA, DETALLE
        // =========================
        $topPlanes = PlanCliente::whereBetween('planes_clientes.created_at', [$ini, $fin])
            ->join('planes', 'planes.id', '=', 'planes_clientes.plan_id')
            ->selectRaw('planes.nombre as plan_nombre, COUNT(*) as contratos, COALESCE(SUM(planes_clientes.a_cuenta),0) as cobrado, COALESCE(SUM(planes_clientes.saldo),0) as saldo')
            ->groupBy('planes.nombre')
            ->orderByDesc('contratos')
            ->limit(5)
            ->get();

        $maxContratosPlan = (int) ($topPlanes->max('contratos') ?? 0);

        $porEstado = PlanCliente::whereBetween('planes_clientes.created_at', [$ini, $fin])
            ->selectRaw('estado, COUNT(*) as c')
            ->groupBy('estado')
            ->pluck('c', 'estado')
            ->toArray();

        $porDisciplina = PlanCliente::whereBetween('planes_clientes.created_at', [$ini, $fin])
            ->leftJoin('disciplinas', 'disciplinas.id', '=', 'planes_clientes.disciplina_id')
            ->selectRaw('disciplinas.nombre as disciplina_nombre, COUNT(*) as contratos, COALESCE(SUM(planes_clientes.a_cuenta),0) as cobrado')
            ->groupBy('disciplinas.nombre')
            ->orderByDesc('contratos')
            ->get();

        $detalle = PlanCliente::with(['cliente', 'plan', 'disciplina'])
            ->whereBetween('planes_clientes.created_at', [$ini, $fin])
            ->orderBy('planes_clientes.created_at', 'asc')
            ->get();

        // =========================
        // INGRESOS / EGRESOS (método de pago)
        // =========================
        $ingresosMetodoDet = collect(); // filas: origen, metodo_pago, cantidad, cobrado
        $egresosMetodoDet = collect(); // filas: origen, metodo_pago, cantidad, monto

        // PLANES (cobrado: a_cuenta)
        if (Schema::hasColumn('planes_clientes', 'metodo_pago')) {
            $rows = PlanCliente::whereBetween('planes_clientes.created_at', [$ini, $fin])
                ->selectRaw("LOWER(TRIM(COALESCE(metodo_pago,'efectivo'))) as metodo_pago")
                ->selectRaw('COUNT(*) as cantidad')
                ->selectRaw('COALESCE(SUM(a_cuenta),0) as cobrado')
                ->groupBy(DB::raw("LOWER(TRIM(COALESCE(metodo_pago,'efectivo')))"))
                ->get();

            $ingresosMetodoDet = $ingresosMetodoDet->concat($rows->map(fn($r) => [
                'origen' => 'Planes',
                'metodo_pago' => $r->metodo_pago ?: '—',
                'cantidad' => (int) $r->cantidad,
                'cobrado' => (float) $r->cobrado,
            ]));
        }

        // VENTAS DE PRODUCTOS (total)
        if (Schema::hasTable('ventas_productos') && Schema::hasColumn('ventas_productos', 'metodo_pago')) {
            $campoFechaVentas = Schema::hasColumn('ventas_productos', 'fecha') ? 'fecha' : 'created_at';

            $rows = VentaProducto::whereBetween($campoFechaVentas, [$ini, $fin])
                ->selectRaw("LOWER(TRIM(COALESCE(metodo_pago,'efectivo'))) as metodo_pago")
                ->selectRaw('COUNT(*) as cantidad')
                ->selectRaw('COALESCE(SUM(total),0) as cobrado')
                ->groupBy(DB::raw("LOWER(TRIM(COALESCE(metodo_pago,'efectivo')))"))
                ->get();

            $ingresosMetodoDet = $ingresosMetodoDet->concat($rows->map(fn($r) => [
                'origen' => 'Ventas',
                'metodo_pago' => $r->metodo_pago ?: '—',
                'cantidad' => (int) $r->cantidad,
                'cobrado' => (float) $r->cobrado,
            ]));
        }

        // SESIONES ADICIONALES (precio)
        if (Schema::hasTable('sesiones_adicionales') && Schema::hasColumn('sesiones_adicionales', 'metodo_pago')) {
            $campoFechaSes = Schema::hasColumn('sesiones_adicionales', 'fecha') ? 'fecha' : 'created_at';

            $rows = SesionAdicional::whereBetween($campoFechaSes, [$ini->toDateString(), $fin->toDateString()])
                ->selectRaw("LOWER(TRIM(COALESCE(metodo_pago,'efectivo'))) as metodo_pago")
                ->selectRaw('COUNT(*) as cantidad')
                ->selectRaw('COALESCE(SUM(precio),0) as cobrado')
                ->groupBy(DB::raw("LOWER(TRIM(COALESCE(metodo_pago,'efectivo')))"))
                ->get();

            $ingresosMetodoDet = $ingresosMetodoDet->concat($rows->map(fn($r) => [
                'origen' => 'Sesiones',
                'metodo_pago' => $r->metodo_pago ?: '—',
                'cantidad' => (int) $r->cantidad,
                'cobrado' => (float) $r->cobrado,
            ]));
        }

        // CASILLEROS (mensualidad: costo_mensual, fecha_entrega_llave o created_at)
        if (Schema::hasTable('casilleros') && Schema::hasColumn('casilleros', 'metodo_pago')) {
            $campoFechaCas = Schema::hasColumn('casilleros', 'fecha_entrega_llave') ? 'fecha_entrega_llave' : 'created_at';

            $rows = Casillero::where('estado', 'ocupado')
                ->whereBetween($campoFechaCas, [$ini->toDateString(), $fin->toDateString()])
                ->selectRaw("LOWER(TRIM(COALESCE(metodo_pago,'efectivo'))) as metodo_pago")
                ->selectRaw('COUNT(*) as cantidad')
                ->selectRaw('COALESCE(SUM(costo_mensual),0) as cobrado')
                ->groupBy(DB::raw("LOWER(TRIM(COALESCE(metodo_pago,'efectivo')))"))
                ->get();

            $ingresosMetodoDet = $ingresosMetodoDet->concat($rows->map(fn($r) => [
                'origen' => 'Casilleros',
                'metodo_pago' => $r->metodo_pago ?: '—',
                'cantidad' => (int) $r->cantidad,
                'cobrado' => (float) $r->cobrado,
            ]));
        }

        // CASILLEROS (reposiciones: monto_reposiciones, updated_at)
        if (Schema::hasTable('casilleros') && Schema::hasColumn('casilleros', 'metodo_pago_reposicion')) {
            $rows = Casillero::where('monto_reposiciones', '>', 0)
                ->whereBetween('updated_at', [$ini, $fin])
                ->selectRaw("LOWER(TRIM(COALESCE(metodo_pago_reposicion,'efectivo'))) as metodo_pago")
                ->selectRaw('COUNT(*) as cantidad')
                ->selectRaw('COALESCE(SUM(monto_reposiciones),0) as cobrado')
                ->groupBy(DB::raw("LOWER(TRIM(COALESCE(metodo_pago_reposicion,'efectivo')))"))
                ->get();

            $ingresosMetodoDet = $ingresosMetodoDet->concat($rows->map(fn($r) => [
                'origen' => 'Reposiciones',
                'metodo_pago' => $r->metodo_pago ?: '—',
                'cantidad' => (int) $r->cantidad,
                'cobrado' => (float) $r->cobrado,
            ]));
        }

        // =========================
        // EGRESOS POR MÉTODO
        // =========================

        // Pagos a personal (monto)
        if (Schema::hasTable('pagos_personal') && Schema::hasColumn('pagos_personal', 'metodo_pago')) {
            $campoFechaPP = Schema::hasColumn('pagos_personal', 'fecha') ? 'fecha' : 'created_at';

            $rows = PagoPersonal::whereBetween($campoFechaPP, [$ini->toDateString(), $fin->toDateString()])
                ->selectRaw("LOWER(TRIM(COALESCE(metodo_pago,'efectivo'))) as metodo_pago")
                ->selectRaw('COUNT(*) as cantidad')
                ->selectRaw('COALESCE(SUM(monto),0) as monto')
                ->groupBy(DB::raw("LOWER(TRIM(COALESCE(metodo_pago,'efectivo')))"))
                ->get();

            $egresosMetodoDet = $egresosMetodoDet->concat($rows->map(fn($r) => [
                'origen' => 'Pagos Personal',
                'metodo_pago' => $r->metodo_pago ?: '—',
                'cantidad' => (int) $r->cantidad,
                'monto' => (float) $r->monto,
            ]));
        }

        // Compras de inventario (ingresos_productos) como egreso
        $comprasInventario = 0;
        if (Schema::hasTable('ingresos_productos')) {
            $campoFechaIP = Schema::hasColumn('ingresos_productos', 'fecha') ? 'fecha' : 'created_at';

            $rowTot = DB::table('ingresos_productos')
                ->whereBetween($campoFechaIP, [$ini, $fin])
                ->selectRaw('COALESCE(SUM(COALESCE(cantidad_unidades,0)*COALESCE(precio_unitario,0) + COALESCE(cantidad_paquetes,0)*COALESCE(precio_paquete,0)),0) as total')
                ->first();
            $comprasInventario = (float) ($rowTot?->total ?? 0);

            if (Schema::hasColumn('ingresos_productos', 'metodo_pago')) {
                $rows = DB::table('ingresos_productos')
                    ->whereBetween($campoFechaIP, [$ini, $fin])
                    ->selectRaw("LOWER(TRIM(COALESCE(metodo_pago,'efectivo'))) as metodo_pago")
                    ->selectRaw('COUNT(*) as cantidad')
                    ->selectRaw('COALESCE(SUM(COALESCE(cantidad_unidades,0)*COALESCE(precio_unitario,0) + COALESCE(cantidad_paquetes,0)*COALESCE(precio_paquete,0)),0) as monto')
                    ->groupBy(DB::raw("LOWER(TRIM(COALESCE(metodo_pago,'efectivo')))"))
                    ->get();

                $egresosMetodoDet = $egresosMetodoDet->concat(collect($rows)->map(fn($r) => [
                    'origen' => 'Compras Inventario',
                    'metodo_pago' => $r->metodo_pago ?: '—',
                    'cantidad' => (int) $r->cantidad,
                    'monto' => (float) $r->monto,
                ]));
            }
        }


        // =========================
        // TOTALES POR MÉTODO (INGRESOS)
        // =========================
        $ingresosMetodoTot = $ingresosMetodoDet
            ->groupBy('metodo_pago')
            ->map(fn($grp) => [
                'metodo_pago' => strtoupper($grp->first()['metodo_pago'] ?? '—'),
                'cantidad' => $grp->sum('cantidad'),
                'cobrado' => round($grp->sum('cobrado'), 2),
            ])->values();


        // =========================
        // TOTALES GLOBALES INGRESOS/EGRESOS (para KPIs)
        // =========================
        $totalIngresos = round($ingresosMetodoDet->sum('cobrado'), 2);
        $totalEgresos = round($egresosMetodoDet->sum('monto'), 2);
        $resultado = $totalIngresos - $totalEgresos;

        // =========================
        // CABECERA PDF
        // =========================
        $periodo = $ini->format('d/m/Y H:i') . ' — ' . $fin->format('d/m/Y H:i');
        $generado_por = Auth::user()?->name ?? 'Sistema';
        $generado_el = now()->format('d/m/Y H:i');

        $logo = str_replace('\\', '/', public_path('images/LogosMPG/Recurso 3.png'));

        $pdf = Pdf::loadView('pdf.finanzas-general', [
            // Cabecera
            'titulo' => $titulo,
            'logo' => $logo,
            'periodo' => $periodo,
            'generado_por' => $generado_por,
            'generado_el' => $generado_el,

            // KPIs Planes + Globales
            'contratos' => $contratos,
            'facturado' => $facturado,
            'cobrado' => $cobrado,
            'saldo' => $saldo,
            'nuevos' => $nuevos,
            'renovaciones' => $renovaciones,

            // Tablas Planes
            'topPlanes' => $topPlanes,
            'maxContratosPlan' => $maxContratosPlan,
            'porEstado' => $porEstado,
            'porDisciplina' => $porDisciplina,
            'detalle' => $detalle,

            // Finanzas consolidadas
            'totalIngresos' => $totalIngresos,
            'totalEgresos' => $totalEgresos,
            'resultado' => $resultado,

            // MÉTODOS
            'ingresosMetodoDet' => $ingresosMetodoDet, // detalle por origen
            'ingresosMetodoTot' => $ingresosMetodoTot, // totales por método
            'egresosMetodoDet' => $egresosMetodoDet,  // egresos por método
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("finanzas-{$tipo}-{$fecha->format('Ymd')}.pdf");
    }
}
