
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Mensual - Ingresos, Egresos y Stock</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            font-size: 12px;
            color: #000000;
            padding: 20px;
            max-width: 720px;
            margin: 0 auto;
        }
        .logo { text-align: center; margin-bottom: 10px; }
        .logo img { height: 60px; }
        h2 { text-align: center; color: #FF6600; margin-bottom: 15px; }
        .seccion { border-top: 2px solid #FF6600; margin-top: 20px; padding-top: 5px; }
        .titulo-seccion {
            font-size: 14px;
            font-weight: bold;
            color: #FF6600;
            margin-bottom: 5px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .table th, .table td {
            border: 1px solid #777777;
            padding: 5px;
            font-size: 11px;
            text-align: left;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 14px;
            font-weight: bold;
            color: #FF6600;
        }
        .resumen { margin-top: 10px; font-size: 12px; }
    </style>
</head>
<body>
    <div class="logo">
        <img src="{{ public_path('images/LogosMPG/Recurso 3.png') }}" alt="MaxPowerGym">
    </div>
    <h2>Reporte Mensual - Ingresos, Egresos y Stock</h2>

    <div class="seccion">
        <div class="titulo-seccion">Ingresos del Mes</div>
        <table class="table">
            <thead>
                <tr><th>Producto</th><th>Unidades</th><th>Paquetes</th><th>Precio U.</th><th>Precio Pqte.</th></tr>
            </thead>
            <tbody>
                @foreach ($ingresos as $ingreso)
                <tr>
                    <td>{{ $ingreso->producto->nombre }}</td>
                    <td>{{ $ingreso->cantidad_unidades }}</td>
                    <td>{{ $ingreso->cantidad_paquetes }}</td>
                    <td>Bs {{ number_format($ingreso->precio_unitario, 2) }}</td>
                    <td>Bs {{ number_format($ingreso->precio_paquete, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="seccion">
        <div class="titulo-seccion">Ventas del Mes</div>
        <table class="table">
            <thead>
                <tr><th>Producto</th><th>Cantidad</th><th>Precio Unitario</th><th>Subtotal</th></tr>
            </thead>
            <tbody>
                @foreach ($detalleVentas as $detalle)
                <tr>
                    <td>{{ $detalle->producto->nombre }}</td>
                    <td>{{ $detalle->cantidad }}</td>
                    <td>Bs {{ number_format($detalle->precio_unitario, 2) }}</td>
                    <td>Bs {{ number_format($detalle->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="seccion">
        <div class="titulo-seccion">Control de Stock del Mes</div>
        <table class="table">
            <thead>
                <tr><th>Producto</th><th>Stock Inicial</th><th>Ingresos</th><th>Ventas</th><th>Stock Final</th></tr>
            </thead>
            <tbody>
                @foreach ($stockResumen as $producto => $resumen)
                <tr>
                    <td>{{ $producto }}</td>
                    <td>{{ $resumen['stock_inicial'] }}</td>
                    <td>{{ $resumen['ingresos'] }}</td>
                    <td>{{ $resumen['ventas'] }}</td>
                    <td>{{ $resumen['stock_final'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="seccion resumen">
        <div class="titulo-seccion">Resumen del Mes</div>
        <p><strong>Total por Producto Vendido:</strong></p>
        <ul>
            @foreach ($totalesPorProducto as $producto => $total)
                <li>{{ $producto }}: Bs {{ number_format($total, 2) }}</li>
            @endforeach
        </ul>
        <p><strong>Total General:</strong> Bs {{ number_format($totalGeneral, 2) }}</p>
        <p><strong>Total QR:</strong> Bs {{ number_format($totalQR, 2) }}</p>
        <p><strong>Total Efectivo:</strong> Bs {{ number_format($totalEfectivo, 2) }}</p>
    </div>

    <div class="footer">
        ¡ACEPTA EL DESAFÍO, ROMPE LOS LÍMITES!
    </div>
</body>
</html>
