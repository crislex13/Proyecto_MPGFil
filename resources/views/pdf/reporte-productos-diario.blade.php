<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }}</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
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
            width: 20%;
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

        .muted {
            color: #666;
            font-size: 11px;
        }

        .footer {
            text-align: center;
            margin-top: 18px;
            font-size: 13px;
            font-weight: bold;
            color: #FF6600;
        }
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

    {{-- RESUMEN INGRESOS (KPI) --}}
    <div class="seccion">
        <div class="titulo-seccion">Resumen — Ingresos del día</div>
        <div class="kpis">
            <div class="kpi">
                <div class="label">Registros</div>
                <div class="val">{{ number_format($registrosIngreso) }}</div>
            </div>
            <div class="kpi">
                <div class="label">Unidades</div>
                <div class="val">{{ number_format($unidadesIngreso) }}</div>
            </div>
            <div class="kpi">
                <div class="label">Paquetes</div>
                <div class="val">{{ number_format($paquetesIngreso) }}</div>
            </div>
            <div class="kpi">
                <div class="label">Costo total</div>
                <div class="val">Bs {{ number_format($totalCostoIngresos, 2) }}</div>
            </div>
            <div class="kpi">
                <div class="label">Ticket prom.</div>
                <div class="val">Bs {{ number_format($ticketPromIngreso, 2) }}</div>
            </div>
        </div>
    </div>

    {{-- TOP 5 INGRESOS --}}
    <div class="seccion">
        <div class="titulo-seccion">Top 5 productos por costo de ingreso</div>
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
                @forelse ($top5Ingreso as $r)
                    @php $pct = $maxCostoIngreso ? round(($r->costo_total / $maxCostoIngreso) * 100) : 0; @endphp
                    <tr>
                        <td>{{ $r->producto }}</td>
                        <td class="center">{{ (int) $r->unidades }}</td>
                        <td class="center">{{ (int) $r->paquetes }}</td>
                        <td>
                            <div class="bar-wrap">
                                <div class="bar" style="width: {{ $pct }}%;"></div>
                            </div>
                        </td>
                        <td class="right">Bs {{ number_format($r->costo_total, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="center">Sin datos.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- RESUMEN VENTAS (KPI) --}}
    <div class="seccion">
        <div class="titulo-seccion">Resumen — Ventas del día</div>
        <div class="kpis">
            <div class="kpi">
                <div class="label">Detalles vendidos</div>
                <div class="val">{{ number_format($registrosVentas) }}</div>
            </div>
            <div class="kpi">
                <div class="label">Total ventas</div>
                <div class="val">Bs {{ number_format($totalVentas, 2) }}</div>
            </div>
            <div class="kpi">
                <div class="label">Ticket prom. detalle</div>
                <div class="val">Bs {{ number_format($ticketPromVenta, 2) }}</div>
            </div>
            <div class="kpi">
                <div class="label">QR</div>
                <div class="val">Bs {{ number_format($totalQR, 2) }}</div>
            </div>
            <div class="kpi">
                <div class="label">Efectivo</div>
                <div class="val">Bs {{ number_format($totalEfectivo, 2) }}</div>
            </div>
        </div>
    </div>

    {{-- TOP 5 VENTAS --}}
    <div class="seccion">
        <div class="titulo-seccion">Top 5 productos por monto vendido</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th class="center">Cant.</th>
                    <th style="width:40%;">Comparativa</th>
                    <th class="right">Monto</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($top5Venta as $r)
                    @php $pct = $maxMontoVenta ? round(($r->monto / $maxMontoVenta) * 100) : 0; @endphp
                    <tr>
                        <td>{{ $r->producto }}</td>
                        <td class="center">{{ (int) $r->cantidad }}</td>
                        <td>
                            <div class="bar-wrap">
                                <div class="bar" style="width: {{ $pct }}%;"></div>
                            </div>
                        </td>
                        <td class="right">Bs {{ number_format($r->monto, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="center">Sin datos.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- DETALLE INGRESOS --}}
    <div class="seccion">
        <div class="titulo-seccion">Detalle de ingresos</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Producto</th>
                    <th class="right">Unid.</th>
                    <th class="right">P.Unit</th>
                    <th class="right">Paq.</th>
                    <th class="right">P.Pack</th>
                    <th class="center">Vence</th>
                    <th>Usuario</th>
                    <th class="right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($ingresos as $i)
                    @php
                        $sub = ($i->cantidad_unidades ?? 0) * ($i->precio_unitario ?? 0)
                            + ($i->cantidad_paquetes ?? 0) * ($i->precio_paquete ?? 0);
                    @endphp
                    <tr>
                        <td>{{ optional($i->fecha)->format('d/m/Y H:i') }}</td>
                        <td>{{ $i->producto?->nombre ?? '—' }}</td>
                        <td class="right">{{ (int) ($i->cantidad_unidades ?? 0) }}</td>
                        <td class="right">Bs {{ number_format($i->precio_unitario ?? 0, 2) }}</td>
                        <td class="right">{{ (int) ($i->cantidad_paquetes ?? 0) }}</td>
                        <td class="right">Bs {{ number_format($i->precio_paquete ?? 0, 2) }}</td>
                        <td class="center">{{ optional($i->fecha_vencimiento)->format('d/m/Y') ?? '—' }}</td>
                        <td>{{ $i->registradoPor?->name ?? '—' }}</td>
                        <td class="right">Bs {{ number_format($sub, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="center">Sin registros.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- CONTROL DE STOCK --}}
    <div class="seccion">
        <div class="titulo-seccion">Control de stock (hoy)</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th class="right">Inicial</th>
                    <th class="right">Ingresos</th>
                    <th class="right">Ventas</th>
                    <th class="right">Final</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($controlStock as $producto => $d)
                    <tr>
                        <td>{{ $producto }}</td>
                        <td class="right">{{ (int) $d['inicial'] }}</td>
                        <td class="right">{{ (int) $d['ingresado'] }}</td>
                        <td class="right">{{ (int) $d['vendido'] }}</td>
                        <td class="right">{{ (int) $d['final'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="center">Sin datos.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- RESUMEN DEL DÍA --}}
    <div class="seccion">
        <div class="titulo-seccion">Resumen del día</div>
        @if (!empty($totalesPorProducto))
            <table class="table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th class="right">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($totalesPorProducto as $producto => $monto)
                        <tr>
                            <td>{{ $producto }}</td>
                            <td class="right">Bs {{ number_format($monto, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="muted">Sin ventas hoy.</p>
        @endif
        <p class="muted"><strong>Total ventas:</strong> Bs {{ number_format($totalVentas, 2) }} — <strong>QR:</strong>
            Bs {{ number_format($totalQR, 2) }} — <strong>Efectivo:</strong> Bs {{ number_format($totalEfectivo, 2) }}
        </p>
    </div>

    <div class="footer">¡ACEPTA EL DESAFÍO, ROMPE LOS LÍMITES!</div>
</body>

</html>