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

        /* Barras simples compatibles con DomPDF */
        .bar-wrap { background: #eee; height: 10px; width: 100%; border-radius: 4px; overflow: hidden; }
        .bar     { background: #FF6600; height: 10px; }

        .footer { text-align: center; margin-top: 18px; font-size: 13px; font-weight: bold; color: #FF6600; }
        .muted { color: #666; font-size: 11px; }

        /* Metadatos */
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
            <div class="kpi"><div class="label">Contratos</div><div class="val">{{ number_format($contratos) }}</div></div>
            <div class="kpi"><div class="label">Facturado (Total)</div><div class="val">Bs {{ number_format($facturado, 2) }}</div></div>
            <div class="kpi"><div class="label">Cobrado (A cuenta)</div><div class="val">Bs {{ number_format($cobrado, 2) }}</div></div>
            <div class="kpi"><div class="label">Saldo Pendiente</div><div class="val">Bs {{ number_format($saldo, 2) }}</div></div>
        </div>
        <p class="muted" style="margin-top:6px;">
            <strong>Nuevos:</strong> {{ $nuevos }} &nbsp;|&nbsp; <strong>Renovaciones:</strong> {{ $renovaciones }}
        </p>
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
                    @php
                        $planNombre = $row->plan_nombre ?? '—';
                        $pct = $maxContratosPlan ? round(($row->contratos / $maxContratosPlan) * 100) : 0;
                    @endphp
                    <tr>
                        <td>{{ $planNombre }}</td>
                        <td class="center">{{ $row->contratos }}</td>
                        <td><div class="bar-wrap"><div class="bar" style="width: {{ $pct }}%;"></div></div></td>
                        <td class="right">Bs {{ number_format($row->cobrado, 2) }}</td>
                        <td class="right">Bs {{ number_format($row->saldo, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="center">Sin datos en el período.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- DISTRIBUCIÓN POR ESTADO --}}
    <div class="seccion">
        <div class="titulo-seccion">Distribución por estado</div>
        <table class="table">
            <thead><tr><th>Estado</th><th class="center">Cantidad</th></tr></thead>
            <tbody>
                @forelse ($porEstado as $estado => $cant)
                    <tr><td>{{ ucfirst($estado) }}</td><td class="center">{{ $cant }}</td></tr>
                @empty
                    <tr><td colspan="2" class="center">Sin datos.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- MÉTODOS DE PAGO --}}
    <div class="seccion">
        <div class="titulo-seccion">Métodos de pago</div>
        <table class="table">
            <thead><tr><th>Método</th><th class="center">Contratos</th><th class="right">Cobrado</th></tr></thead>
            <tbody>
                @forelse ($metodosPago as $m)
                    <tr>
                        <td>{{ strtoupper($m->metodo_pago ?? '—') }}</td>
                        <td class="center">{{ $m->cantidad }}</td>
                        <td class="right">Bs {{ number_format($m->cobrado, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="center">Sin datos.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- POR DISCIPLINA --}}
    <div class="seccion">
        <div class="titulo-seccion">Contratos por disciplina</div>
        <table class="table">
            <thead><tr><th>Disciplina</th><th class="center">Contratos</th><th class="right">Cobrado</th></tr></thead>
            <tbody>
                @forelse ($porDisciplina as $d)
                    <tr>
                        <td>{{ $d->disciplina_nombre ?? '—' }}</td>
                        <td class="center">{{ $d->contratos }}</td>
                        <td class="right">Bs {{ number_format($d->cobrado, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="center">Sin datos.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- DETALLE --}}
    <div class="seccion">
        <div class="titulo-seccion">Detalle de contratos del período</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th><th>Cliente</th><th>Plan</th><th>Disciplina</th>
                    <th class="right">Total</th><th class="right">A cuenta</th><th class="right">Saldo</th><th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($detalle as $pc)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($pc->fecha_inicio)->format('Y-m-d') }}</td>
                        <td>{{ $pc->cliente?->nombre_completo ?? '—' }}</td>
                        <td>{{ $pc->plan?->nombre ?? '—' }}</td>
                        <td>{{ $pc->disciplina?->nombre ?? '—' }}</td>
                        <td class="right">Bs {{ number_format($pc->total, 2) }}</td>
                        <td class="right">Bs {{ number_format($pc->a_cuenta, 2) }}</td>
                        <td class="right">Bs {{ number_format($pc->saldo, 2) }}</td>
                        <td>{{ ucfirst($pc->estado) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="center">Sin registros.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer">¡ACEPTA EL DESAFÍO, ROMPE LOS LÍMITES!</div>
</body>
</html>
