<?php

namespace App\Http\Controllers;

use App\Models\SesionAdicional;
use App\Models\Personal;
use App\Models\Clientes;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class SesionReportController extends Controller
{
    public function dia(Request $request)
    {
        $date = $request->input('date', now()->toDateString());
        $start = Carbon::parse($date)->startOfDay();
        $end = Carbon::parse($date)->endOfDay();

        $data = $this->buildData($start, $end, $request);
        $data['titulo'] = 'Reporte Diario de Sesiones';
        $data['periodo'] = Carbon::parse($date)->isoFormat('dddd D [de] MMMM YYYY');
        $data['generado_por'] = optional(auth()->user())->name ?? 'Sistema';
        $data['generado_el'] = now()->isoFormat('dddd D [de] MMMM YYYY, HH:mm');

        return $this->pdf($data, "reporte-sesiones-diario-{$start->toDateString()}.pdf");
    }

    public function mes(Request $request)
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = (clone $start)->endOfMonth();

        $data = $this->buildData($start, $end, $request);
        $data['titulo'] = 'Reporte Mensual de Sesiones';
        $data['periodo'] = $start->isoFormat('MMMM YYYY');
        $data['generado_por'] = optional(auth()->user())->name ?? 'Sistema';
        $data['generado_el'] = now()->isoFormat('dddd D [de] MMMM YYYY, HH:mm');

        return $this->pdf($data, "reporte-sesiones-{$start->format('Y-m')}.pdf");
    }

    public function anio(Request $request)
    {
        $year = (int) $request->input('year', now()->year);
        $start = Carbon::create($year, 1, 1)->startOfYear();
        $end = (clone $start)->endOfYear();

        $data = $this->buildData($start, $end, $request);
        $data['titulo'] = 'Reporte Anual de Sesiones';
        $data['periodo'] = (string) $year;
        $data['generado_por'] = optional(auth()->user())->name ?? 'Sistema';
        $data['generado_el'] = now()->isoFormat('dddd D [de] MMMM YYYY, HH:mm');

        return $this->pdf($data, "reporte-sesiones-{$year}.pdf");
    }

    /**
     * Núcleo de agregaciones por rango
     *
     * Extra: puedes pasar ?hora=real en la URL para usar la hora REAL de entrada
     *        (asistencias.hora_entrada) en lugar de la hora programada (turnos.hora_inicio).
     */
    protected function buildData(Carbon $start, Carbon $end, Request $request): array
    {
        Carbon::setLocale('es');

        $saTable = (new SesionAdicional)->getTable(); // sesiones_adicionales
        $personalTable = (new Personal)->getTable();
        $clientesTable = (new Clientes)->getTable();

        $base = SesionAdicional::query()
            ->with([
                'cliente:id,nombre,apellido_paterno,apellido_materno,foto',
                'instructor:id,nombre,apellido_paterno,apellido_materno,foto',
                'turno:id,hora_inicio,hora_fin',
                'disciplina:id,nombre', // 👈 importante
            ])
            ->whereBetween("{$saTable}.fecha", [$start->toDateString(), $end->toDateString()]);

        // KPIs
        $sesiones = (clone $base)->count();
        $clientesUnicos = (clone $base)->distinct("{$saTable}.cliente_id")->count("{$saTable}.cliente_id");
        $ingresos = (clone $base)->sum("{$saTable}.precio");
        $ticketPromedio = $sesiones > 0 ? round($ingresos / $sesiones, 2) : 0;

        // 🔁 TOP disciplinas (sustituye a "tipos de sesión")
        $porDisciplina = (clone $base)
            ->leftJoin('disciplinas as d', 'd.id', '=', "{$saTable}.disciplina_id")
            ->selectRaw("
            COALESCE(d.nombre, '—') as disciplina_nombre,
            COUNT(*) as sesiones,
            SUM({$saTable}.precio) as ingresos
        ")
            ->groupBy('disciplina_nombre')
            ->orderByDesc('sesiones')
            ->get();
        $topDisciplinas = $porDisciplina->take(5);

        // Instructores más cotizados
        $porInstructor = (clone $base)
            ->leftJoin("{$personalTable} as ins", "ins.id", "=", "{$saTable}.instructor_id")
            ->selectRaw("
            {$saTable}.instructor_id,
            CONCAT(
                COALESCE(ins.nombre, '—'), ' ',
                COALESCE(ins.apellido_paterno, ''), ' ',
                COALESCE(ins.apellido_materno, '')
            ) as instructor_nombre,
            COUNT(*) as sesiones,
            SUM({$saTable}.precio) as ingresos
        ")
            ->groupBy("{$saTable}.instructor_id", 'ins.nombre', 'ins.apellido_paterno', 'ins.apellido_materno')
            ->orderByDesc('sesiones')
            ->get();
        $topInstructores = $porInstructor->take(5);

        // Clientes que más compran sesiones
        $porCliente = (clone $base)
            ->leftJoin("{$clientesTable} as c", "c.id", "=", "{$saTable}.cliente_id")
            ->selectRaw("
            {$saTable}.cliente_id,
            CONCAT(
                COALESCE(c.nombre, '—'), ' ',
                COALESCE(c.apellido_paterno, ''), ' ',
                COALESCE(c.apellido_materno, '')
            ) as cliente_nombre,
            COUNT(*) as sesiones,
            SUM({$saTable}.precio) as gasto
        ")
            ->groupBy("{$saTable}.cliente_id", 'c.nombre', 'c.apellido_paterno', 'c.apellido_materno')
            ->orderByDesc('gasto')
            ->get();
        $topClientes = $porCliente->take(5);

        // Distribución por día de semana
        $porDiaSemana = (clone $base)
            ->selectRaw("DAYOFWEEK({$saTable}.fecha) as dow, COUNT(*) as sesiones, SUM({$saTable}.precio) as ingresos")
            ->groupBy('dow')
            ->orderBy('dow')
            ->get()
            ->map(function ($r) {
                $map = [1 => 'Domingo', 2 => 'Lunes', 3 => 'Martes', 4 => 'Miércoles', 5 => 'Jueves', 6 => 'Viernes', 7 => 'Sábado'];
                $r->dia_nombre = $map[(int) $r->dow] ?? $r->dow;
                return $r;
            });

        // Distribución por hora (igual que ya tenías)
        $usarHoraReal = strtolower($request->input('hora', 'programada')) === 'real';
        if (!$usarHoraReal) {
            $porHora = \DB::table("{$saTable} as sa")
                ->join('turnos as t', 't.id', '=', 'sa.turno_id')
                ->whereBetween('sa.fecha', [$start->toDateString(), $end->toDateString()])
                ->whereNotNull('sa.turno_id')
                ->selectRaw('HOUR(t.hora_inicio) as h, COUNT(*) as sesiones, SUM(sa.precio) as ingresos')
                ->groupBy('h')
                ->orderBy('h')
                ->get();
        } else {
            $porHora = \DB::table('asistencias as a')
                ->join("{$saTable} as sa", 'sa.id', '=', 'a.sesion_adicional_id')
                ->where('a.tipo_asistencia', 'sesion')
                ->whereNotNull('a.hora_entrada')
                ->whereBetween('a.fecha', [$start->toDateString(), $end->toDateString()])
                ->selectRaw('HOUR(a.hora_entrada) as h, COUNT(DISTINCT a.sesion_adicional_id) as sesiones, SUM(sa.precio) as ingresos')
                ->groupBy('h')
                ->orderBy('h')
                ->get();
        }

        // Detalle
        $detalle = (clone $base)->orderBy("{$saTable}.fecha", 'desc')->get();

        // Aux para barras relativas
        $maxSesDisciplina = max($porDisciplina->max('sesiones') ?? 1, 1);
        $maxSesInstructor = max($porInstructor->max('sesiones') ?? 1, 1);
        $maxGastoCliente = max($porCliente->max('gasto') ?? 1, 1);

        return compact(
            'start',
            'end',
            'sesiones',
            'clientesUnicos',
            'ingresos',
            'ticketPromedio',
            // 👇 nombres nuevos
            'porDisciplina',
            'topDisciplinas',
            'porInstructor',
            'topInstructores',
            'porCliente',
            'topClientes',
            'porDiaSemana',
            'porHora',
            'detalle',
            'maxSesDisciplina',
            'maxSesInstructor',
            'maxGastoCliente'
        );
    }

    protected function pdf(array $data, string $filename)
    {
        return Pdf::loadView('pdf.sesiones_resumen', $data)
            ->setPaper('a4', 'portrait')
            ->stream($filename);
    }
}
