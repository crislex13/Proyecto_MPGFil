<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #000;
            padding: 20px;
            max-width: 720px;
            margin: 0 auto
        }

        .logo {
            text-align: center;
            margin-bottom: 8px
        }

        .logo img {
            height: 60px
        }

        h2 {
            text-align: center;
            color: #FF6600;
            margin: 4px 0 12px
        }

        .periodo {
            text-align: center;
            font-size: 12px;
            margin-bottom: 8px
        }

        .seccion {
            border-top: 2px solid #FF6600;
            margin-top: 16px;
            padding-top: 6px
        }

        .titulo-seccion {
            font: 700 14px/1 sans-serif;
            color: #FF6600;
            margin-bottom: 6px
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px
        }

        .table th,
        .table td {
            border: 1px solid #777;
            padding: 5px;
            font-size: 11px;
            text-align: left;
            vertical-align: top
        }

        .kpis {
            display: table;
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px
        }

        .kpis .kpi {
            display: table-cell;
            width: 20%;
            text-align: center;
            padding: 8px;
            border: 1px solid #777
        }

        .kpi .label {
            font-size: 10px;
            color: #666
        }

        .kpi .val {
            font-size: 16px;
            font-weight: 700
        }

        .center {
            text-align: center
        }

        .right {
            text-align: right
        }

        .meta {
            margin-top: 6px;
            font-size: 11px;
            color: #555
        }

        .meta .row {
            display: table;
            width: 100%
        }

        .meta .cell {
            display: table-cell;
            width: 50%
        }

        .footer {
            text-align: center;
            margin-top: 18px;
            font: 700 13px/1 sans-serif;
            color: #FF6600
        }
    </style>
</head>

<body>
    <div class="logo"><img src="{{ public_path('images/LogosMPG/Recurso 3.png') }}" alt="MaxPowerGym"></div>
    <h2>{{ $titulo }}</h2>
    <div class="periodo">{{ $periodo }}</div>

    <div class="meta">
        <div class="row">
            <div class="cell"><strong>Generado por:</strong> {{ $generado_por }}</div>
            <div class="cell right"><strong>Generado el:</strong> {{ $generado_el }}</div>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="seccion">
        <div class="titulo-seccion">Resumen ejecutivo</div>
        <div class="kpis">
            <div class="kpi">
                <div class="label">Total</div>
                <div class="val">{{ $kpis['total'] }}</div>
            </div>
            <div class="kpi">
                <div class="label">Disponibles</div>
                <div class="val">{{ $kpis['disponibles'] }}</div>
            </div>
            <div class="kpi">
                <div class="label">Ocupados</div>
                <div class="val">{{ $kpis['ocupados'] }}</div>
            </div>
            <div class="kpi">
                <div class="label">Mantenimiento</div>
                <div class="val">{{ $kpis['mantenimiento'] }}</div>
            </div>
            <div class="kpi">
                <div class="label">Vencidos</div>
                <div class="val">{{ $kpis['vencidos'] }}</div>
            </div>
        </div>
        <div class="kpis" style="margin-top:6px">
            <div class="kpi">
                <div class="label">Ingresos Mensualidad</div>
                <div class="val">Bs {{ number_format($kpis['ingresos_mensual'], 2) }}</div>
            </div>
            <div class="kpi">
                <div class="label">Ingresos Reposiciones</div>
                <div class="val">Bs {{ number_format($kpis['ingresos_repos'], 2) }}</div>
            </div>
            <div class="kpi">
                <div class="label">Ingresos Totales</div>
                <div class="val">Bs {{ number_format($kpis['ingresos_totales'], 2) }}</div>
            </div>
            <div class="kpi">
                <div class="label">Próx. a vencer (7d)</div>
                <div class="val">{{ $kpis['proximos_vencer_7d'] }}</div>
            </div>
            <div class="kpi">
                <div class="label">&nbsp;</div>
                <div class="val">&nbsp;</div>
            </div>
        </div>
    </div>

    {{-- Estado (snapshot) --}}
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
                @php $map = ['disponible' => 'Disponible', 'ocupado' => 'Ocupado', 'mantenimiento' => 'Mantenimiento']; @endphp
                @forelse ($porEstado as $estado => $cant)
                    <tr>
                        <td>{{ $map[$estado] ?? ucfirst($estado) }}</td>
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

    {{-- Métodos de pago (mensualidad) --}}
    <div class="seccion">
        <div class="titulo-seccion">Métodos de pago — mensualidad</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Método</th>
                    <th class="center">Contratos</th>
                    <th class="right">Cobrado</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($metodosMensual as $m)
                    <tr>
                        <td>{{ strtoupper($m->metodo_pago ?? '—') }}</td>
                        <td class="center">{{ (int) $m->cantidad }}</td>
                        <td class="right">Bs {{ number_format($m->cobrado, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="center">Sin datos.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Métodos de pago (reposiciones) --}}
    <div class="seccion">
        <div class="titulo-seccion">Métodos de pago — reposiciones</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Método</th>
                    <th class="center">Operaciones</th>
                    <th class="right">Importe</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($metodosRepos as $m)
                    <tr>
                        <td>{{ strtoupper($m->metodo_pago ?? '—') }}</td>
                        <td class="center">{{ (int) $m->cantidad }}</td>
                        <td class="right">Bs {{ number_format($m->cobrado, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="center">Sin datos.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Detalle de casilleros del período --}}
    <div class="seccion">
        <div class="titulo-seccion">Detalle de casilleros entregados en el período</div>
        <table class="table">
            <thead>
                <tr>
                    <th>N°</th>
                    <th>Cliente</th>
                    <th>Estado</th>
                    <th>Entrega</th>
                    <th>Vence</th>
                    <th class="right">Mensualidad</th>
                    <th class="right">Reposiciones</th>
                    <th>Mét. pago</th>
                    <th>Mét. rep.</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($detalle as $c)
                    <tr>
                        <td>{{ $c->numero }}</td>
                        <td>{{ $c->cliente ? ($c->cliente->nombre . ' ' . $c->cliente->apellido_paterno . ' ' . $c->cliente->apellido_materno) : '—' }}
                        </td>
                        <td>{{ ucfirst($c->estado) }}</td>
                        <td>{{ $c->fecha_entrega_llave ? \Carbon\Carbon::parse($c->fecha_entrega_llave)->format('d/m/Y') : '—' }}
                        </td>
                        <td>{{ $c->fecha_final_llave ? \Carbon\Carbon::parse($c->fecha_final_llave)->format('d/m/Y') : '—' }}
                        </td>
                        <td class="right">Bs {{ number_format((float) $c->costo_mensual, 2) }}</td>
                        <td class="right">Bs {{ number_format((float) $c->monto_reposiciones, 2) }}</td>
                        <td>{{ strtoupper($c->metodo_pago ?? '—') }}</td>
                        <td>{{ strtoupper($c->metodo_pago_reposicion ?? '—') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="center">Sin registros.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer">¡ACEPTA EL DESAFÍO, ROMPE LOS LÍMITES!</div>
</body>

</html>