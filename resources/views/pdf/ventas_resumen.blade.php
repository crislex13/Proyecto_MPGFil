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
            margin-bottom: 12px;
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

    <div class="muted" style="margin-top:6px;">
        <div><strong>Generado por:</strong> {{ $generado_por ?? '—' }}</div>
        <div><strong>Generado el:</strong> {{ $generado_el ?? now()->format('d/m/Y H:i') }}</div>
    </div>

    {{-- KPIs --}}
    <div class="seccion">
        <div class="titulo-seccion">Resumen</div>
        <div class="kpis">
            <div class="kpi">
                <div class="label">Ventas</div>
                <div class="val">{{ number_format($ven_registros) }}</div>
            </div>
            <div class="kpi">
                <div class="label">Ítems vendidos</div>
                <div class="val">{{ number_format($ven_items) }}</div>
            </div>
            <div class="kpi">
                <div class="label">Total QR</div>
                <div class="val">Bs {{ number_format($ven_qr, 2) }}</div>
            </div>
            <div class="kpi">
                <div class="label">Total Efectivo</div>
                <div class="val">Bs {{ number_format($ven_efectivo, 2) }}</div>
            </div>
        </div>
        <p class="muted" style="margin-top:6px;">
            <strong>Total vendido:</strong> Bs {{ number_format($ven_total, 2) }}
        </p>
    </div>

    {{-- TOP 5 PRODUCTOS --}}
    <div class="seccion">
        <div class="titulo-seccion">Top 5 productos por monto</div>
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
                @php $top5 = $porProducto->take(5); @endphp
                @forelse ($top5 as $row)
                    @php $pct = $maxMontoProd ? round(($row->monto / $maxMontoProd) * 100) : 0; @endphp
                    <tr>
                        <td>{{ $row->nombre }}</td>
                        <td class="center">{{ $row->qty }}</td>
                        <td>
                            <div class="bar-wrap">
                                <div class="bar" style="width: {{ $pct }}%;"></div>
                            </div>
                        </td>
                        <td class="right">Bs {{ number_format($row->monto, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="center">Sin datos.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- SOLO GLOBAL: RANKING POR USUARIO --}}
    @if (!empty($es_global) && $es_global && $porUsuario->count())
        <div class="seccion">
            <div class="titulo-seccion">Ventas por usuario</div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th class="center">Ventas</th>
                        <th style="width:40%;">Comparativa</th>
                        <th class="right">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($porUsuario as $u)
                        @php $pctU = $maxMontoUser ? round(($u->monto / $maxMontoUser) * 100) : 0; @endphp
                        <tr>
                            <td>{{ $u->usuario }}</td>
                            <td class="center">{{ $u->registros }}</td>
                            <td>
                                <div class="bar-wrap">
                                    <div class="bar" style="width: {{ $pctU }}%;"></div>
                                </div>
                            </td>
                            <td class="right">Bs {{ number_format($u->monto, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- DETALLE --}}
    <div class="seccion">
        <div class="titulo-seccion">Detalle de ventas</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Producto</th>
                    <th class="center">Cant.</th>
                    <th class="right">P. Unit</th>
                    <th class="right">Subtotal</th>
                    <th>Vendedor</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($detalleTabla as $r)
                    <tr>
                        <td>{{ $r->fecha }}</td>
                        <td>{{ $r->producto }}</td>
                        <td class="center">{{ $r->cantidad }}</td>
                        <td class="right">Bs {{ number_format($r->pu, 2) }}</td>
                        <td class="right">Bs {{ number_format($r->subtotal, 2) }}</td>
                        <td>{{ $r->vendedor }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="center">Sin registros.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer">¡ACEPTA EL DESAFÍO, ROMPE LOS LÍMITES!</div>
</body>

</html>