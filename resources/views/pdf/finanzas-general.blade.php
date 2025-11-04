<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }}</title>
    <style>
        /* Fuente segura para DomPDF */
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #000;
            padding: 20px;
            max-width: 720px;
            margin: 0 auto;
        }

        .logo {
            text-align: center;
            margin-bottom: 8px;
        }

        .logo img {
            height: 60px;
        }

        h2 {
            text-align: center;
            color: #FF6600;
            margin: 4px 0 12px;
        }

        .periodo {
            text-align: center;
            font-size: 12px;
            margin-bottom: 8px;
        }

        .seccion {
            border-top: 2px solid #FF6600;
            margin-top: 16px;
            padding-top: 6px;
        }

        .titulo-seccion {
            font-size: 14px;
            font-weight: bold;
            color: #FF6600;
            margin-bottom: 6px;
        }

        .kpis {
            display: table;
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        .kpis .kpi {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 8px;
            border: 1px solid #777;
        }

        .kpi .label {
            font-size: 10px;
            color: #666;
        }

        .kpi .val {
            font-size: 16px;
            font-weight: 700;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .table th,
        .table td {
            border: 1px solid #777;
            padding: 5px;
            font-size: 11px;
            text-align: left;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .bar-wrap {
            background: #eee;
            height: 10px;
            width: 100%;
            border-radius: 4px;
            overflow: hidden;
        }

        .bar {
            background: #FF6600;
            height: 10px;
        }

        .footer {
            text-align: center;
            margin-top: 18px;
            font-size: 13px;
            font-weight: bold;
            color: #FF6600;
        }

        .muted {
            color: #666;
            font-size: 11px;
        }

        .meta {
            margin-top: 6px;
            font-size: 11px;
            color: #555;
        }

        .meta .row {
            display: table;
            width: 100%;
        }

        .meta .cell {
            display: table-cell;
            width: 50%;
        }
    </style>
</head>

<body>
    <div class="logo">
        <img src="{{ $logo }}" alt="MaxPowerGym">
    </div>

    <h2>{{ $titulo }}</h2>
    <div class="periodo">{{ $periodo }}</div>

    <div class="meta">
        <div class="row">
            <div class="cell"><strong>Generado por:</strong> {{ $generado_por }}</div>
            <div class="cell right"><strong>Generado el:</strong> {{ $generado_el }}</div>
        </div>
    </div>

    {{-- RESUMEN EJECUTIVO (finanzas) --}}
    <div class="seccion">
        <div class="titulo-seccion">Resumen ejecutivo</div>
        <div class="kpis">
            <div class="kpi">
                <div class="label">Total Ingresos</div>
                <div class="val">Bs {{ number_format($totalIngresos, 2) }}</div>
            </div>
            <div class="kpi">
                <div class="label">Total Egresos</div>
                <div class="val">Bs {{ number_format($totalEgresos, 2) }}</div>
            </div>
            <div class="kpi">
                <div class="label">Utilidad / Pérdida</div>
                <div class="val">Bs {{ number_format($resultado, 2) }}</div>
            </div>
            <div class="kpi">
                <div class="label">Contratos (período)</div>
                <div class="val">{{ number_format($contratos) }}</div>
            </div>
        </div>
        <p class="muted" style="margin-top:6px;">
            <strong>Facturado:</strong> Bs {{ number_format($facturado, 2) }} &nbsp;|&nbsp;
            <strong>Cobrado:</strong> Bs {{ number_format($cobrado, 2) }} &nbsp;|&nbsp;
            <strong>Saldo:</strong> Bs {{ number_format($saldo, 2) }} &nbsp;|&nbsp;
            <strong>Nuevos:</strong> {{ $nuevos }} &nbsp;|&nbsp; <strong>Renovaciones:</strong> {{ $renovaciones }}
        </p>
    </div>

    {{-- INGRESOS POR MÉTODO (DETALLE POR ORIGEN) --}}
    <div class="seccion">
        <div class="titulo-seccion">Ingresos por método (detalle)</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Origen</th>
                    <th>Método</th>
                    <th class="center">Transacciones</th>
                    <th class="right">Cobrado</th>
                </tr>
            </thead>
            <tbody>
                @php $maxIngDet = max(($ingresosMetodoDet?->pluck('cobrado')->toArray() ?? [0])); @endphp
                @forelse ($ingresosMetodoDet as $r)
                    @php $pct = $maxIngDet ? round(($r['cobrado'] / $maxIngDet) * 100) : 0; @endphp
                    <tr>
                        <td>
                            {{ $r['origen'] }}
                            <div class="bar-wrap">
                                <div class="bar" style="width: {{ $pct }}%;"></div>
                            </div>
                        </td>
                        <td>{{ strtoupper($r['metodo_pago'] ?? '—') }}</td>
                        <td class="center">{{ $r['cantidad'] }}</td>
                        <td class="right">Bs {{ number_format($r['cobrado'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="center">Sin datos.</td>
                    </tr>
                @endforelse
                <tr>
                    <th colspan="3">Total Ingresos</th>
                    <th class="right">Bs {{ number_format($totalIngresos, 2) }}</th>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- INGRESOS POR MÉTODO (TOTALES) --}}
    <div class="seccion">
        <div class="titulo-seccion">Ingresos por método (totales)</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Método</th>
                    <th class="center">Transacciones</th>
                    <th class="right">Cobrado</th>
                </tr>
            </thead>
            <tbody>
                @php $maxIngTot = max(($ingresosMetodoTot?->pluck('cobrado')->toArray() ?? [0])); @endphp
                @forelse ($ingresosMetodoTot as $t)
                    @php $pct = $maxIngTot ? round(($t['cobrado'] / $maxIngTot) * 100) : 0; @endphp
                    <tr>
                        <td>
                            {{ $t['metodo_pago'] }}
                            <div class="bar-wrap">
                                <div class="bar" style="width: {{ $pct }}%;"></div>
                            </div>
                        </td>
                        <td class="center">{{ $t['cantidad'] }}</td>
                        <td class="right">Bs {{ number_format($t['cobrado'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="center">Sin datos.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- EGRESOS POR MÉTODO --}}
    <div class="seccion">
        <div class="titulo-seccion">Egresos por método</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Origen</th>
                    <th>Método</th>
                    <th class="center">Transacciones</th>
                    <th class="right">Monto</th>
                </tr>
            </thead>
            <tbody>
                @php $maxEgr = max(($egresosMetodoDet?->pluck('monto')->toArray() ?? [0])); @endphp
                @forelse ($egresosMetodoDet as $e)
                    @php $pct = $maxEgr ? round(($e['monto'] / $maxEgr) * 100) : 0; @endphp
                    <tr>
                        <td>
                            {{ $e['origen'] }}
                            <div class="bar-wrap">
                                <div class="bar" style="width: {{ $pct }}%;"></div>
                            </div>
                        </td>
                        <td>{{ strtoupper($e['metodo_pago'] ?? '—') }}</td>
                        <td class="center">{{ $e['cantidad'] }}</td>
                        <td class="right">Bs {{ number_format($e['monto'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="center">Sin datos.</td>
                    </tr>
                @endforelse
                <tr>
                    <th colspan="3">Total Egresos</th>
                    <th class="right">Bs {{ number_format($totalEgresos, 2) }}</th>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- TOP 5 PLANES --}}
    <div class="seccion">
        <div class="titulo-seccion">Top 5 planes por cantidad de contratos</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Plan</th>
                    <th class="center">Contratos</th>
                    <th style="width:40%;">Comparativa</th>
                    <th class="right">Cobrado</th>
                    <th class="right">Saldo</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($topPlanes as $row)
                    @php $pct = $maxContratosPlan ? round(($row->contratos / $maxContratosPlan) * 100) : 0; @endphp
                    <tr>
                        <td>{{ $row->plan_nombre ?? '—' }}</td>
                        <td class="center">{{ $row->contratos }}</td>
                        <td>
                            <div class="bar-wrap">
                                <div class="bar" style="width: {{ $pct }}%;"></div>
                            </div>
                        </td>
                        <td class="right">Bs {{ number_format($row->cobrado, 2) }}</td>
                        <td class="right">Bs {{ number_format($row->saldo, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="center">Sin datos en el período.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- DISTRIBUCIÓN POR ESTADO --}}
    <div class="seccion">
        <div class="titulo-seccion">Distribución por estado</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Estado</th>
                    <th class="center">Cantidad</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($porEstado as $estado => $cant)
                    <tr>
                        <td>{{ ucfirst($estado) }}</td>
                        <td class="center">{{ $cant }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="center">Sin datos.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- POR DISCIPLINA --}}
    <div class="seccion">
        <div class="titulo-seccion">Contratos por disciplina</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Disciplina</th>
                    <th class="center">Contratos</th>
                    <th class="right">Cobrado</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($porDisciplina as $d)
                    <tr>
                        <td>{{ $d->disciplina_nombre ?? '—' }}</td>
                        <td class="center">{{ $d->contratos }}</td>
                        <td class="right">Bs {{ number_format($d->cobrado, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="center">Sin datos.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- DETALLE DE CONTRATOS --}}
    <div class="seccion">
        <div class="titulo-seccion">Detalle de contratos del período</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Plan</th>
                    <th>Disciplina</th>
                    <th class="right">Total</th>
                    <th class="right">A cuenta</th>
                    <th class="right">Saldo</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($detalle as $pc)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($pc->created_at)->format('Y-m-d') }}</td>
                        <td>{{ $pc->cliente?->nombre_completo ?? '—' }}</td>
                        <td>{{ $pc->plan?->nombre ?? '—' }}</td>
                        <td>{{ $pc->disciplina?->nombre ?? '—' }}</td>
                        <td class="right">Bs {{ number_format($pc->total, 2) }}</td>
                        <td class="right">Bs {{ number_format($pc->a_cuenta, 2) }}</td>
                        <td class="right">Bs {{ number_format($pc->saldo, 2) }}</td>
                        <td>{{ ucfirst($pc->estado) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="center">Sin registros.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer">¡ACEPTA EL DESAFÍO, ROMPE LOS LÍMITES!</div>
</body>

</html>