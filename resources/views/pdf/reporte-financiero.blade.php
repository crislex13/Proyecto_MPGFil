<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte Financiero Diario</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            font-size: 12px;
            color: #000000;
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

        .seccion {
            border-top: 2px solid #FF6600;
            margin-top: 10px;
            padding-top: 5px;
        }

        .titulo-seccion {
            font-size: 13px;
            font-weight: bold;
            color: #FF6600;
            margin-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th,
        td {
            border: 1px solid #777777;
            padding: 4px;
            text-align: center;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            font-weight: bold;
            font-size: 14px;
            color: #4E5054;
        }

        .marca {
            color: #FF6600;
        }
    </style>
</head>

<body>
    <div class="logo">
        <img src="{{ public_path('images/LogosMPG/Recurso 3.png') }}" alt="MaxPowerGym">
    </div>

    <h2>
        REPORTE FINANCIERO {{ strtoupper($tipo) }}
        -
        @if($tipo === 'diario')
            {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}
        @elseif($tipo === 'mensual')
            {{ \Carbon\Carbon::parse($fecha)->formatLocalized('%B de %Y') }}
        @elseif($tipo === 'anual')
            {{ \Carbon\Carbon::parse($fecha)->format('Y') }}
        @endif
    </h2>

    <p style="text-align: center; font-size: 12px; margin-top: -10px; margin-bottom: 15px;">
        @if($tipo === 'mensual' || $tipo === 'anual')
            Del {{ \Carbon\Carbon::parse($desde)->format('d/m/Y') }}
            al {{ \Carbon\Carbon::parse($hasta)->format('d/m/Y') }}
        @elseif($tipo === 'diario')
            Día: {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}
        @endif
    </p>

    {{-- SECCIÓN 1: INSCRIPCIONES --}}
    <div class="seccion">
        <div class="titulo-seccion">Control de Inscripciones</div>
        <table>
            <tr>
                <th>Planes</th>
                <th>Sesiones</th>
                <th>Reposiciones Llave</th>
                <th>Casilleros</th>
                <th>QR</th>
                <th>Total</th>
            </tr>
            <tr>
                <td>{{ $inscripciones['planes'] }}</td>
                <td>{{ $inscripciones['sesiones'] }}</td>
                <td>{{ $inscripciones['rep_llave'] }}</td>
                <td>{{ $inscripciones['casilleros'] }}</td>
                <td>{{ $inscripciones['qr'] }}</td>
                <td>{{ $inscripciones['total'] }}</td>
            </tr>
        </table>
    </div>

    {{-- SECCIÓN 2: PAGOS A INSTRUCTORES --}}
    <div class="seccion">
        <div class="titulo-seccion">Pagos a Instructores</div>
        <table>
            <tr>
                <th>Hora</th>
                <th>Instructor</th>
                <th>Sala A</th>
                <th>Sala B</th>
                <th>Pago (Bs)</th>
            </tr>
            @foreach($instructores as $i)
                <tr>
                    <td>{{ $i['hora'] }}</td>
                    <td>{{ $i['nombre'] }}</td>
                    <td>{{ $i['sala_a'] }}</td>
                    <td>{{ $i['sala_b'] }}</td>
                    <td>{{ $i['pago'] }}</td>
                </tr>
            @endforeach
        </table>
    </div>

    {{-- SECCIÓN 3: INGRESOS --}}
    <div class="seccion">
        <div class="titulo-seccion">Ingresos</div>
        <table>
            <tr>
                <th>Categoría</th>
                <th>Total (Bs)</th>
            </tr>
            @foreach($ingresos as $ing)
                <tr>
                    <td>{{ $ing['categoria'] }}</td>
                    <td>{{ $ing['total'] }}</td>
                </tr>
            @endforeach
        </table>
    </div>

    {{-- SECCIÓN 4: EGRESOS --}}
    <div class="seccion">
        <div class="titulo-seccion">Egresos</div>
        <table>
            <tr>
                <th>Categoría</th>
                <th>Total (Bs)</th>
            </tr>
            @foreach($egresos as $egr)
                <tr>
                    <td>{{ $egr['categoria'] }}</td>
                    <td>{{ $egr['total'] }}</td>
                </tr>
            @endforeach
        </table>
    </div>

    {{-- SECCIÓN 5: TOTALES --}}
    <div class="seccion">
        <div class="titulo-seccion">Totales</div>
        <table>
            <tr>
                <th>Ingresos</th>
                <th>Egresos</th>
                <th>Utilidad</th>
                <th>QR</th>
                <th>Efectivo</th>
            </tr>
            <tr>
                <td>{{ $totales['ingresos'] }}</td>
                <td>{{ $totales['egresos'] }}</td>
                <td>{{ $totales['utilidad'] }}</td>
                <td>{{ $totales['qr'] }}</td>
                <td>{{ $totales['efectivo'] }}</td>
            </tr>
        </table>
    </div>

    {{-- SECCIÓN 6: OBSERVACIONES --}}
    <div class="seccion">
        <div class="titulo-seccion">Observaciones</div>
        <p>{{ $observaciones ?: 'Ninguna' }}</p>
    </div>

    <div class="footer">
        <p class="marca">¡ACEPTA EL DESAFÍO, ROMPE LOS LÍMITES!</p>
    </div>
</body>

</html>