<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color:#000; padding:20px; max-width:720px; margin:0 auto; }
        .logo { text-align:center; margin-bottom:8px; }
        .logo img { height:60px; }
        h2 { text-align:center; color:#FF6600; margin:4px 0 12px; }
        .periodo { text-align:center; font-size:12px; margin-bottom:8px; }
        .meta { margin-top:6px; font-size:11px; color:#555; }
        .meta .row { display:table; width:100%; }
        .meta .cell { display:table-cell; width:50%; }
        .seccion { border-top:2px solid #FF6600; margin-top:16px; padding-top:6px; }
        .titulo-seccion { font-size:14px; font-weight:700; color:#FF6600; margin-bottom:6px; }
        table.table { width:100%; border-collapse:collapse; margin-top:8px; }
        .table th,.table td { border:1px solid #777; padding:5px; font-size:11px; text-align:left; vertical-align:top; }
        .right { text-align:right; }
        .center { text-align:center; }
        .bar-wrap { background:#eee; height:10px; width:100%; border-radius:4px; overflow:hidden; }
        .bar { background:#FF6600; height:10px; }
        .footer { text-align:center; margin-top:18px; font-size:13px; font-weight:bold; color:#FF6600; }
    </style>
</head>
<body>
    <div class="logo">
        @if(!empty($logo))
            <img src="{{ $logo }}" alt="MaxPowerGym">
        @else
            <div style="height:60px"></div>
        @endif
    </div>

    <h2>{{ $titulo }}</h2>
    <div class="periodo">{{ $periodo }}</div>

    <div class="meta">
        <div class="row">
            <div class="cell"><strong>Generado por:</strong> {{ $generado_por }}</div>
            <div class="cell right"><strong>Generado el:</strong> {{ $generado_el }}</div>
        </div>
    </div>

    {{-- RESUMEN --}}
    <div class="seccion">
        <div class="titulo-seccion">Resumen mensual</div>
        <table class="table">
            <thead>
                <tr>
                    <th class="center">Pendientes</th>
                    <th class="center">Aprobados</th>
                    <th class="center">Rechazados</th>
                    <th class="center">Solicitados</th>
                    <th class="center">Días aprobados</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="center">{{ (int) data_get($totales,'pendientes',0) }}</td>
                    <td class="center">{{ (int) data_get($totales,'aprobados',0) }}</td>
                    <td class="center">{{ (int) data_get($totales,'rechazados',0) }}</td>
                    <td class="center">{{ (int) data_get($totales,'solicitados',0) }}</td>
                    <td class="center">{{ (int) ($diasAprobados ?? 0) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- TOP MOTIVOS --}}
    <div class="seccion">
        <div class="titulo-seccion">Top motivos</div>
        @php $arr = collect($topMotivos ?? []); $maxMot = max($arr->pluck('c')->toArray() ?: [0]); @endphp
        <table class="table">
            <thead>
                <tr>
                    <th>Motivo</th>
                    <th class="center">Cantidad</th>
                </tr>
            </thead>
            <tbody>
                @forelse($arr as $m)
                    @php $pct = $maxMot ? round(($m->c / $maxMot) * 100) : 0; @endphp
                    <tr>
                        <td>
                            {{ $m->motivo }}
                            <div class="bar-wrap"><div class="bar" style="width: {{ $pct }}%;"></div></div>
                        </td>
                        <td class="center">{{ (int) $m->c }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="center">Sin datos</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- DETALLE --}}
    <div class="seccion">
        <div class="titulo-seccion">Detalle de permisos</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Inicio</th>
                    <th>Fin</th>
                    <th>Personal</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Motivo</th>
                    <th>Autorizado por</th>
                </tr>
            </thead>
            <tbody>
                @forelse(($detalle ?? collect()) as $p)
                    <tr>
                        <td>{{ !empty($p->fecha_inicio) ? \Carbon\Carbon::parse($p->fecha_inicio)->format('d/m/Y') : '—' }}</td>
                        <td>{{ !empty($p->fecha_fin) ? \Carbon\Carbon::parse($p->fecha_fin)->format('d/m/Y') : '—' }}</td>
                        <td>{{ optional($p->personal)->nombre_completo ?? '—' }}</td>
                        <td>{{ !empty($p->tipo) ? ucfirst($p->tipo) : '—' }}</td>
                        <td>{{ !empty($p->estado) ? ucfirst($p->estado) : '—' }}</td>
                        <td>{{ $p->motivo ?? '—' }}</td>
                        <td>{{ optional($p->autorizadoPor)->name ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="center">Sin registros</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer">¡ACEPTA EL DESAFÍO, ROMPE LOS LÍMITES!</div>
</body>
</html>
