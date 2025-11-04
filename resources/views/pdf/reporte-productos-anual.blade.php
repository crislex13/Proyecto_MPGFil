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
            margin-bottom: 10px;
        }

        .logo img {
            height: 60px;
        }

        h2 {
            text-align: center;
            color: #FF6600;
            margin-bottom: 15px;
        }

        .periodo {
            text-align: center;
            font-size: 12px;
            margin-top: -6px;
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

        .card {
            border: 1px solid #777;
            padding: 10px;
            margin-top: 6px;
        }

        .card .title {
            font-weight: 700;
            margin-bottom: 4px;
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

    {{-- RESUMEN — INGRESOS ANUALES --}}
    <div class="seccion">
        <div class="titulo-seccion">Resumen — Ingresos del año</div>
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

    {{-- TOP 5 — COSTO DE INGRESO (CON BARRAS) --}}
    <div class="seccion">
        <div class="titulo-seccion">Top 5 productos por costo de ingreso (anual)</div>
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
                    <tr>
                        <td>{{ $r->producto }}</td>
                        <td class="center">{{ (int) $r->unidades }}</td>
                        <td class="center">{{ (int) $r->paquetes }}</td>
                        <td>
                            <div class="bar-wrap">
                                <div class="bar" style="width: {{ $r->pct_bar }}%;"></div>
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

    {{-- RESUMEN — VENTAS ANUALES --}}
    <div class="seccion">
        <div class="titulo-seccion">Resumen — Ventas del año</div>
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
                <div class="label">Ticket prom.</div>
                <div class="val">Bs {{ number_format($ticketPromVenta, 2) }}</div>
            </div>
            <div class="kpi">
                <div class="label">QR</div>
                <div class="val">Bs {{ number_format($totalQR_kpi, 2) }}</div>
            </div>
            <div class="kpi">
                <div class="label">Efectivo</div>
                <div class="val">Bs {{ number_format($totalEfectivo_kpi, 2) }}</div>
            </div>
        </div>

        {{-- CARD: Producto más vendido (monto) --}}
        <div class="card">
            <div class="title">Producto más vendido (monto)</div>
            @if ($productoMasVendido)
                <div style="margin-bottom:4px;"><strong>{{ $productoMasVendido->producto }}</strong></div>
                <div class="bar-wrap" style="margin-bottom:6px;">
                    <div class="bar" style="width: 100%;"></div>
                </div>
                <div class="muted">
                    Cant.: {{ number_format($productoMasVendido->cantidad) }} —
                    Monto: <strong>Bs {{ number_format($productoMasVendido->monto, 2) }}</strong>
                </div>
            @else
                <div class="muted">Sin ventas en el año.</div>
            @endif
        </div>
    </div>

    {{-- TOP 5 — MONTO VENDIDO (CON BARRAS) --}}
    <div class="seccion">
        <div class="titulo-seccion">Top 5 productos por monto vendido (anual)</div>
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
                    <tr>
                        <td>{{ $r->producto }}</td>
                        <td class="center">{{ (int) $r->cantidad }}</td>
                        <td>
                            <div class="bar-wrap">
                                <div class="bar" style="width: {{ $r->pct_bar }}%;"></div>
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

    {{-- CONTROL DE STOCK ANUAL (de tu método) --}}
    <div class="seccion">
        <div class="titulo-seccion">Control de stock (año)</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th class="right">Stock inicial</th>
                    <th class="right">Ingresos</th>
                    <th class="right">Ventas</th>
                    <th class="right">Stock final</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($stockResumen as $producto => $r)
                    <tr>
                        <td>{{ $producto }}</td>
                        <td class="right">{{ (int) $r['stock_inicial'] }}</td>
                        <td class="right">{{ (int) $r['ingresos'] }}</td>
                        <td class="right">{{ (int) $r['ventas'] }}</td>
                        <td class="right">{{ (int) $r['stock_final'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="center">Sin datos.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- RESUMEN DE MONTOS (reutilizas los del método calcularTotales) --}}
    <div class="seccion">
        <div class="titulo-seccion">Resumen del año</div>
        <p><strong>Total por producto vendido (conteo x 1):</strong></p>
        <ul>
            @foreach ($totalesPorProducto as $producto => $total)
                <li>{{ $producto }}: Bs {{ number_format($total, 2) }}</li>
            @endforeach
        </ul>
        <p><strong>Total General:</strong> Bs {{ number_format($totalGeneral, 2) }}</p>
        <p><strong>Total QR:</strong> Bs {{ number_format($totalQR, 2) }}</p>
        <p><strong>Total Efectivo:</strong> Bs {{ number_format($totalEfectivo, 2) }}</p>
    </div>

    <div class="footer">¡ACEPTA EL DESAFÍO, ROMPE LOS LÍMITES!</div>
</body>

</html>