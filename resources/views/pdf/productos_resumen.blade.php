<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }}</title>
    <style>
        body { font-family: 'Poppins', sans-serif; font-size: 12px; color: #000; padding: 20px; max-width: 720px; margin: 0 auto; }
        .logo { text-align: center; margin-bottom: 10px; }
        .logo img { height: 60px; }
        h2 { text-align: center; color: #FF6600; margin-bottom: 12px; }
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

        .footer { text-align: center; margin-top: 18px; font-size: 13px; font-weight: bold; color: #FF6600; }
        .muted { color: #666; font-size: 11px; }
    </style>
</head>
<body>
    <div class="logo">
        <img src="{{ public_path('images/LogosMPG/Recurso 3.png') }}" alt="MaxPowerGym">
    </div>
    <h2>{{ $titulo }}</h2>
    <div class="periodo">{{ $periodo }}</div>

    <div class="muted" style="margin-top:6px;">
        <div><strong>Generado por:</strong> {{ $generado_por }}</div>
        <div><strong>Generado el:</strong> {{ $generado_el }}</div>
    </div>

    {{-- KPIs INGRESOS --}}
    <div class="seccion">
        <div class="titulo-seccion">Resumen de Ingresos</div>
        <div class="kpis">
            <div class="kpi"><div class="label">Registros</div><div class="val">{{ number_format($ing_registros) }}</div></div>
            <div class="kpi"><div class="label">Unidades</div><div class="val">{{ number_format($ing_unidades) }}</div></div>
            <div class="kpi"><div class="label">Paquetes</div><div class="val">{{ number_format($ing_paquetes) }}</div></div>
            <div class="kpi"><div class="label">Costo Total</div><div class="val">Bs {{ number_format($ing_costo, 2) }}</div></div>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Producto</th><th class="center">Unid.</th><th class="center">Pack</th>
                    <th class="right">P. Unit</th><th class="right">P. Pack</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($ingresos as $r)
                    <tr>
                        <td>{{ $r->producto->nombre ?? '—' }}</td>
                        <td class="center">{{ (int)($r->cantidad_unidades ?? 0) }}</td>
                        <td class="center">{{ (int)($r->cantidad_paquetes ?? 0) }}</td>
                        <td class="right">Bs {{ number_format($r->precio_unitario ?? 0, 2) }}</td>
                        <td class="right">Bs {{ number_format($r->precio_paquete ?? 0, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="center">Sin ingresos en el período.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- KPIs VENTAS --}}
    <div class="seccion">
        <div class="titulo-seccion">Resumen de Ventas</div>
        <div class="kpis">
            <div class="kpi"><div class="label">Ventas</div><div class="val">{{ number_format($ven_registros) }}</div></div>
            <div class="kpi"><div class="label">Ítems vendidos</div><div class="val">{{ number_format($ven_items) }}</div></div>
            <div class="kpi"><div class="label">Total QR</div><div class="val">Bs {{ number_format($ven_qr, 2) }}</div></div>
            <div class="kpi"><div class="label">Total Efectivo</div><div class="val">Bs {{ number_format($ven_efectivo, 2) }}</div></div>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Producto</th><th class="center">Cant.</th><th class="right">P. Unit</th><th class="right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($detalleVentas as $d)
                    <tr>
                        <td>{{ $d->producto->nombre ?? '—' }}</td>
                        <td class="center">{{ (int)($d->cantidad ?? 0) }}</td>
                        <td class="right">Bs {{ number_format($d->precio_unitario ?? 0, 2) }}</td>
                        <td class="right">Bs {{ number_format($d->subtotal ?? 0, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="center">Sin ventas en el período.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="muted" style="margin-top:6px;">
            <strong>Total vendido (todas las formas de pago):</strong> Bs {{ number_format($ven_total, 2) }}
        </div>
    </div>

    {{-- CONTROL DE STOCK --}}
    <div class="seccion">
        <div class="titulo-seccion">Control de Stock</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Producto</th><th class="center">Stock Inicial</th><th class="center">Ingresos</th><th class="center">Ventas</th><th class="center">Stock Final</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($controlStock as $prod => $datos)
                    <tr>
                        <td>{{ $prod }}</td>
                        <td class="center">{{ data_get($datos, 'inicial', 0) }}</td>
                        <td class="center">{{ data_get($datos, 'ingresado', 0) }}</td>
                        <td class="center">{{ data_get($datos, 'vendido', 0) }}</td>
                        <td class="center">{{ data_get($datos, 'final', 0) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- RESUMEN RÁPIDO POR PRODUCTO --}}
    <div class="seccion">
        <div class="titulo-seccion">Totales por producto (ventas)</div>
        @if (count($totalesPorProducto))
            <ul>
                @foreach ($totalesPorProducto as $producto => $total)
                    <li>{{ $producto }}: Bs {{ number_format($total, 2) }}</li>
                @endforeach
            </ul>
        @else
            <div class="muted">Sin ventas en el período.</div>
        @endif
    </div>

    <div class="footer">¡ACEPTA EL DESAFÍO, ROMPE LOS LÍMITES!</div>
</body>
</html>
