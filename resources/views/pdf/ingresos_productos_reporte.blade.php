<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }}</title>
    <style>
        body { font-family: 'Poppins', sans-serif; font-size: 12px; color: #000; padding: 20px; max-width: 720px; margin: 0 auto; }
        .logo { text-align: center; margin-bottom: 8px; }
        .logo img { height: 60px; }
        h2 { text-align: center; color: #FF6600; margin: 4px 0 12px; }
        .periodo { text-align: center; font-size: 12px; margin-bottom: 8px; }

        .seccion { border-top: 2px solid #FF6600; margin-top: 16px; padding-top: 6px; }
        .titulo-seccion { font-size: 14px; font-weight: bold; color: #FF6600; margin-bottom: 6px; }

        .kpis { display: table; width: 100%; border-collapse: collapse; margin-top: 6px; }
        .kpis .kpi { display: table-cell; width: 25%; text-align: center; padding: 8px; border: 1px solid #777; }
        .kpi .label { font-size: 10px; color: #666; }
        .kpi .val { font-size: 16px; font-weight: 700; }

        .table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .table th, .table td { border: 1px solid #777; padding: 5px; font-size: 11px; text-align: left; }
        .right { text-align: right; }
        .center { text-align: center; }

        .bar-wrap { background: #eee; height: 10px; width: 100%; border-radius: 4px; overflow: hidden; }
        .bar     { background: #FF6600; height: 10px; }

        .footer { text-align: center; margin-top: 18px; font-size: 13px; font-weight: bold; color: #FF6600; }
        .muted { color: #666; font-size: 11px; }

        .meta { margin-top: 6px; font-size: 11px; color: #555; }
        .meta .row { display: table; width: 100%; }
        .meta .cell { display: table-cell; width: 50%; }
    </style>
</head>
<body>
    <div class="logo">
        <img src="{{ public_path('images/LogosMPG/Recurso 3.png') }}" alt="MaxPowerGym">
    </div>
    <h2>{{ $titulo }}</h2>
    <div class="periodo">{{ $periodo }}</div>

    <div class="meta">
        <div class="row">
            <div class="cell"><strong>Generado por:</strong> {{ $generado_por }}</div>
            <div class="cell right"><strong>Generado el:</strong> {{ $generado_el }}</div>
        </div>
    </div>

    {{-- RESUMEN EJECUTIVO --}}
    <div class="seccion">
        <div class="titulo-seccion">Resumen ejecutivo</div>
        <div class="kpis">
            <div class="kpi"><div class="label">Registros</div><div class="val">{{ number_format($registros) }}</div></div>
            <div class="kpi"><div class="label">Unidades</div><div class="val">{{ number_format($totalUnidades) }}</div></div>
            <div class="kpi"><div class="label">Paquetes</div><div class="val">{{ number_format($totalPaquetes) }}</div></div>
            <div class="kpi"><div class="label">Costo total</div><div class="val">Bs {{ number_format($totalCosto, 2) }}</div></div>
        </div>
        <p class="muted" style="margin-top:6px;">
            <strong>Costo promedio por registro:</strong> Bs {{ number_format($ticketPromedio, 2) }}
        </p>
    </div>

    {{-- TOP 5 PRODUCTOS POR COSTO --}}
    <div class="seccion">
        <div class="titulo-seccion">Top 5 productos por costo total</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th class="center">Unid.</th>
                    <th class="center">Paq.</th>
                    <th style="width:40%;">Comparativa</th>
                    <th class="right">Costo</th>
                </tr>
            </thead>
            <tbody>
                @php $top5 = $porProducto->take(5); @endphp
                @forelse ($top5 as $row)
                    @php
                        $pct = $maxCostoProd ? round(($row->costo_total / $maxCostoProd) * 100) : 0;
                    @endphp
                    <tr>
                        <td>{{ $row->producto ?? '—' }}</td>
                        <td class="center">{{ $row->unidades ?? 0 }}</td>
                        <td class="center">{{ $row->paquetes ?? 0 }}</td>
                        <td><div class="bar-wrap"><div class="bar" style="width: {{ $pct }}%;"></div></div></td>
                        <td class="right">Bs {{ number_format($row->costo_total, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="center">Sin datos en el período.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- POR USUARIO (QUIÉN REGISTRÓ) --}}
    <div class="seccion">
        <div class="titulo-seccion">Ingresos por usuario</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th class="center">Registros</th>
                    <th style="width:40%;">Comparativa</th>
                    <th class="right">Costo</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($porUsuario as $u)
                    @php
                        $pctU = $maxCostoUser ? round(($u->costo_total / $maxCostoUser) * 100) : 0;
                    @endphp
                    <tr>
                        <td>{{ $u->usuario ?? '—' }}</td>
                        <td class="center">{{ $u->registros }}</td>
                        <td><div class="bar-wrap"><div class="bar" style="width: {{ $pctU }}%;"></div></div></td>
                        <td class="right">Bs {{ number_format($u->costo_total, 2) }}</td>
                    </tr>
                @endforeach
                @if ($porUsuario->isEmpty())
                    <tr><td colspan="4" class="center">Sin datos.</td></tr>
                @endif
            </tbody>
        </table>
    </div>

    {{-- POR DÍA DE LA SEMANA --}}
    <div class="seccion">
        <div class="titulo-seccion">Por día de la semana</div>
        <table class="table">
            <thead><tr><th>Día</th><th class="center">Registros</th><th class="right">Costo</th></tr></thead>
            <tbody>
                @forelse ($porDiaSemana as $d)
                    <tr>
                        <td>{{ $d->dia_nombre }}</td>
                        <td class="center">{{ $d->registros }}</td>
                        <td class="right">Bs {{ number_format($d->costo_total, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="center">Sin datos.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- DETALLE --}}
    <div class="seccion">
        <div class="titulo-seccion">Detalle de ingresos del período</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Producto</th>
                    <th class="right">Unidades</th>
                    <th class="right">P. Unit</th>
                    <th class="right">Paquetes</th>
                    <th class="right">P. Pack</th>
                    <th class="center">Vencimiento</th>
                    <th>Registrado por</th>
                    <th class="right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($detalle as $r)
                    <tr>
                        <td>{{ optional($r->fecha)->format('d/m/Y H:i') }}</td>
                        <td>{{ $r->producto?->nombre ?? '—' }}</td>
                        <td class="right">{{ $r->cantidad_unidades ?? 0 }}</td>
                        <td class="right">Bs {{ number_format($r->precio_unitario ?? 0, 2) }}</td>
                        <td class="right">{{ $r->cantidad_paquetes ?? 0 }}</td>
                        <td class="right">Bs {{ number_format($r->precio_paquete ?? 0, 2) }}</td>
                        <td class="center">{{ optional($r->fecha_vencimiento)->format('d/m/Y') ?? '—' }}</td>
                        <td>{{ $r->registradoPor?->name ?? '—' }}</td>
                        <td class="right">Bs {{ number_format($r->subtotal, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="center">Sin registros.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer">¡ACEPTA EL DESAFÍO, ROMPE LOS LÍMITES!</div>
</body>
</html>
