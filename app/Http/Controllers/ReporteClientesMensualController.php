<?php

namespace App\Http\Controllers;

use App\Models\Clientes;
use App\Models\PlanCliente;
use App\Models\SesionAdicional;
use App\Models\Asistencia;
use App\Models\PermisoCliente;
use App\Models\Casillero;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteClientesMensualController extends Controller
{
    public function reporteMensual(Request $request, int $id)
    {
        // Permite seleccionar mes con ?fecha=YYYY-MM-DD (opcional)
        $fecha = Carbon::parse($request->input('fecha', now()->toDateString()));
        $inicioMes = $fecha->copy()->startOfMonth();
        $finMes = $fecha->copy()->endOfMonth();

        $cliente = Clientes::with(['usuario', 'modificadoPor'])->findOrFail($id);

        // ---------- PLANES DEL MES ----------
        $planesDelMes = PlanCliente::with(['plan:id,nombre', 'disciplina:id,nombre'])
            ->where('cliente_id', $id)
            ->whereBetween('fecha_inicio', [$inicioMes->toDateString(), $finMes->toDateString()])
            ->orderBy('fecha_inicio', 'asc')
            ->get();

        // KPIs Planes
        $kpiPlanes = [
            'contratos' => $planesDelMes->count(),
            'facturado' => round((float) $planesDelMes->sum('total'), 2),
            'cobrado' => round((float) $planesDelMes->sum('a_cuenta'), 2),
            'saldo' => round((float) $planesDelMes->sum('saldo'), 2),
        ];

        // ---------- SESIONES ADICIONALES DEL MES ----------
        $sesionesDelMes = SesionAdicional::with(['instructor', 'turno'])
            ->where('cliente_id', $id)
            ->whereBetween('fecha', [$inicioMes->toDateString(), $finMes->toDateString()])
            ->orderBy('fecha', 'asc')
            ->get();

        $kpiSesiones = [
            'cantidad' => $sesionesDelMes->count(),
            'monto' => round((float) $sesionesDelMes->sum('precio'), 2),
        ];

        // ---------- ASISTENCIAS DEL MES ----------
        // “Solo las permitidas”: asumimos asistencias del cliente registradas por el sistema (tipo plan o sesión).
        // Si tu tabla tiene otra columna (p.ej. valida/permitida), cambia el where para reflejarla.
        $asistenciasDelMes = Asistencia::query()
            ->where('asistible_type', Clientes::class)
            ->where('asistible_id', $id)
            ->whereBetween('fecha', [$inicioMes->toDateString(), $finMes->toDateString()])
            ->orderBy('fecha', 'asc')
            ->get();

        $kpiAsistencias = [
            'total' => $asistenciasDelMes->count(),
            'por_tipo' => $asistenciasDelMes->groupBy('tipo_asistencia')->map->count(),
        ];

        // ---------- PERMISOS DEL CLIENTE EN EL MES ----------
        $permisosDelMes = PermisoCliente::query()
            ->where('cliente_id', $id)
            ->whereBetween('fecha', [$inicioMes->toDateString(), $finMes->toDateString()])
            ->orderBy('fecha', 'asc')
            ->get();

        $kpiPermisos = [
            'solicitados' => $permisosDelMes->count(),
            'pendientes' => $permisosDelMes->where('estado', 'pendiente')->count(),
            'aprobados' => $permisosDelMes->where('estado', 'aprobado')->count(),
            'rechazados' => $permisosDelMes->where('estado', 'rechazado')->count(),
        ];

        // ---------- CASILLEROS (si tuvo alguno activo o entregado en el mes) ----------
        $casillerosMes = Casillero::with('cliente')
            ->where('cliente_id', $id)
            ->where(function ($q) use ($inicioMes, $finMes) {
                $q->whereBetween(DB::raw('DATE(fecha_entrega_llave)'), [$inicioMes->toDateString(), $finMes->toDateString()])
                    ->orWhereBetween(DB::raw('DATE(fecha_final_llave)'), [$inicioMes->toDateString(), $finMes->toDateString()]);
            })
            ->orderBy('fecha_entrega_llave', 'asc')
            ->get();

        $kpiCasillero = [
            'activos' => $casillerosMes->where('estado', 'ocupado')->count(),
            'mensual' => round((float) $casillerosMes->sum('costo_mensual'), 2),
            'repos' => round((float) $casillerosMes->sum('monto_reposiciones'), 2),
            'metodos' => $casillerosMes->groupBy('metodo_pago')->map->count(),
        ];

        // ---------- METADATOS + LOGO ----------
        $titulo = 'Ficha Mensual del Cliente';
        $periodo = $inicioMes->isoFormat('MMMM [de] YYYY');
        $generado_por = optional(auth()->user())->name ?? 'Sistema';
        $generado_el = now()->isoFormat('dddd D [de] MMMM YYYY, HH:mm');
        $logo = public_path('images/LogosMPG/Recurso 3.png');

        // Foto del cliente para PDF – si tienes accessor, úsalo; si no, resolvemos aquí:
        $fotoPath = method_exists($cliente, 'getFotoPathForPdfAttribute')
            ? $cliente->foto_path_for_pdf
            : ($cliente->foto
                ? (public_path('storage/' . $cliente->foto))
                : public_path('images/default-user.png')
            );

        return Pdf::loadView('pdf.ficha-cliente-mensual', compact(
            'cliente',
            'titulo',
            'periodo',
            'generado_por',
            'generado_el',
            'logo',
            'fotoPath',
            'inicioMes',
            'finMes',
            'planesDelMes',
            'kpiPlanes',
            'sesionesDelMes',
            'kpiSesiones',
            'asistenciasDelMes',
            'kpiAsistencias',
            'permisosDelMes',
            'kpiPermisos',
            'casillerosMes',
            'kpiCasillero'
        ))
            ->setPaper('a4', 'portrait')
            ->stream('Ficha_Cliente_Mensual_' . $cliente->ci . '_' . $inicioMes->format('Ym') . '.pdf');
    }
}
