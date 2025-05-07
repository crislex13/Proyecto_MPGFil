<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Ficha Mensual del Personal</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            color: #000000;
            font-size: 12px;
            max-width: 720px;
            margin: 0 auto;
            padding: 20px;
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
            margin-top: 15px;
            padding-top: 5px;
        }

        .titulo-seccion {
            font-size: 14px;
            font-weight: bold;
            color: #FF6600;
            margin-bottom: 5px;
        }

        .fila {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 4px;
        }

        .campo {
            width: 48%;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            table-layout: fixed;
            word-wrap: break-word;
        }

        .table th,
        .table td {
            border: 1px solid #777777;
            padding: 4px;
            font-size: 11px;
            text-align: left;
        }

        .datos {
            margin-bottom: 10px;
        }

        .foto-nombre {
            text-align: center;
            margin-bottom: 15px;
        }

        .foto-nombre img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 2px solid #FF6600;
            object-fit: cover;
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

    <h2>Ficha Mensual de Personal - {{ $mes }}</h2>

    <div class="seccion">
        <div class="titulo-seccion">Datos Personales</div>
        <div class="foto-nombre">
            <img src="{{ $personal->foto_path_for_pdf }}" alt="Foto del Personal">
            <p style="margin-top: 5px; font-weight: bold;">{{ $personal->nombre_completo }}</p>
        </div>
        <div class="fila">
            <div class="campo"><strong>C.I.:</strong> {{ $personal->ci }}</div>
            <div class="campo"><strong>Teléfono:</strong> {{ $personal->telefono }}</div>
        </div>
        <div class="fila">
            <div class="campo"><strong>Cargo:</strong> {{ ucfirst($personal->cargo) }}</div>
            <div class="campo"><strong>Fecha de Contratación:</strong> {{ $personal->fecha_contratacion }}</div>
        </div>
        <div class="fila">
            <div class="campo"><strong>Fecha de nacimiento:</strong> {{ $personal->fecha_de_nacimiento }}</div>
            <div class="campo"><strong>Correo:</strong> {{ $personal->correo }}</div>
        </div>
        <div class="fila">
            <div class="campo"><strong>Dirección:</strong> {{ $personal->direccion }}</div>
        </div>
    </div>

    <div class="seccion">
        <div class="titulo-seccion">Turnos Asignados</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Día</th>
                    <th>Hora Inicio</th>
                    <th>Hora Fin</th>
                </tr>
            </thead>
            <tbody>
                @foreach($personal->turnos as $turno)
                    <tr>
                        <td>{{ $turno->nombre }}</td>
                        <td>{{ $turno->dia }}</td>
                        <td>{{ $turno->hora_inicio }}</td>
                        <td>{{ $turno->hora_fin }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="seccion">
        <div class="titulo-seccion">Asistencias del Mes</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Entrada</th>
                    <th>Salida</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($personal->asistencias as $asistencia)
                    <tr>
                        <td>{{ $asistencia->fecha }}</td>
                        <td>{{ $asistencia->hora_entrada?->format('H:i') ?? '—' }}</td>
                        <td>{{ $asistencia->hora_salida?->format('H:i') ?? '—' }}</td>
                        <td>{{ ucfirst($asistencia->estado) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="seccion">
        <div class="titulo-seccion">Permisos del Mes</div>
        @if($personal->permisos->count())
            <table class="table">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Motivo</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($personal->permisos as $permiso)
                        <tr>
                            <td>{{ $permiso->tipo }}</td>
                            <td>{{ $permiso->motivo }}</td>
                            <td>{{ $permiso->fecha_inicio }}</td>
                            <td>{{ $permiso->fecha_fin }}</td>
                            <td>{{ ucfirst($permiso->estado) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="text-align: center;">No tiene permisos este mes.</p>
        @endif
    </div>

    <div class="seccion">
        <div class="titulo-seccion">Salas Asignadas</div>
        <ul>
            @forelse($salasUnicas as $sala)
                <li>{{ $sala }}</li>
            @empty
                <li>No tiene salas asignadas este mes.</li>
            @endforelse
        </ul>
    </div>

    <div class="seccion">
        <div class="titulo-seccion">Pagos del Mes</div>
        @if($pagos->count())
            <table class="table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Monto</th>
                        <th>Descripción</th>
                        <th>Sala</th>
                        <th>Turno</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pagos as $pago)
                        <tr>
                            <td>{{ $pago->fecha }}</td>
                            <td>Bs {{ number_format($pago->monto, 2) }}</td>
                            <td>{{ $pago->descripcion }}</td>
                            <td>{{ $pago->sala->nombre ?? '-' }}</td>
                            <td>{{ $pago->turno->display_horario ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="text-align: center;">No hay pagos registrados este mes.</p>
        @endif
    </div>

    <div class="footer">
        <p class="marca">¡ACEPTA EL DESAFÍO, ROMPE LOS LÍMITES!</p>
    </div>
</body>

</html>
