<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Ficha Mensual del Personal</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            color: #000;
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
            border: 1px solid #777;
            padding: 4px;
            font-size: 11px;
            text-align: left;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
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

        /* KPIs mini */
        .kpis {
            display: table;
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        .kpi {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 6px;
            border: 1px solid #777;
        }

        .kpi .label {
            font-size: 10px;
            color: #666;
        }

        .kpi .val {
            font-size: 14px;
            font-weight: 700;
        }

        /* Barras */
        .bar-wrap {
            background: #eee;
            height: 10px;
            width: 100%;
            border-radius: 4px;
            overflow: hidden;
        }

        .bar {
            height: 10px;
        }

        .bar.green {
            background: #16a34a;
        }

        .bar.orange {
            background: #f59e0b;
        }

        .bar.red {
            background: #ef4444;
        }

        .bar.brand {
            background: #FF6600;
        }

        .muted {
            color: #666;
            font-size: 11px;
        }

        .footer {
            text-align: center;
            margin-top: 18px;
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

    <h2>Ficha Mensual de Personal — {{ $mes }}</h2>

    @php
        $dias = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];
    @endphp

    {{-- DATOS PERSONALES --}}
    <div class="seccion">
        <div class="titulo-seccion">Datos Personales</div>
        <div class="foto-nombre">
            <img src="{{ $personal->foto_path_for_pdf }}" alt="Foto del Personal">
            <p style="margin-top:5px; font-weight:bold;">{{ $personal->nombre_completo }}</p>
        </div>
        <div class="fila">
            <div class="campo"><strong>C.I.:</strong> {{ $personal->ci }}</div>
            <div class="campo"><strong>Teléfono:</strong> {{ $personal->telefono }}</div>
        </div>
        <div class="fila">
            <div class="campo"><strong>Cargo:</strong> {{ ucfirst($personal->cargo) }}</div>
            <div class="campo"><strong>Fecha de Contratación:</strong>
                {{ optional($personal->fecha_contratacion)->format('d/m/Y') }}</div>
        </div>
        <div class="fila">
            <div class="campo"><strong>Fecha de nacimiento:</strong>
                {{ optional($personal->fecha_de_nacimiento)->format('d/m/Y') }}</div>
            <div class="campo"><strong>Correo:</strong> {{ $personal->correo }}</div>
        </div>
        <div class="fila">
            <div class="campo"><strong>Dirección:</strong> {{ $personal->direccion }}</div>
        </div>
    </div>

    {{-- TURNOS --}}
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
                        <td>{{ $dias[$turno->dia] ?? $turno->dia }}</td>
                        <td>{{ $turno->hora_inicio }}</td>
                        <td>{{ $turno->hora_fin }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- RESUMEN ASISTENCIAS (KPIs + BARRAS) --}}
    <div class="seccion">
        <div class="titulo-seccion">Resumen de Asistencias del Mes</div>

        <div class="kpis">
            <div class="kpi">
                <div class="label">Puntuales</div>
                <div class="val">{{ number_format($puntuales) }}</div>
            </div>
            <div class="kpi">
                <div class="label">Atrasos</div>
                <div class="val">{{ number_format($atrasos) }}</div>
            </div>
            <div class="kpi">
                <div class="label">Faltas</div>
                <div class="val">{{ number_format($faltas) }}</div>
            </div>
            <div class="kpi">
                <div class="label">Permisos</div>
                <div class="val">{{ number_format($conPermiso) }}</div>
            </div>
        </div>

        <table class="table" style="margin-top:8px;">
            <thead>
                <tr>
                    <th style="width:25%;">Métrica</th>
                    <th>Comparativa</th>
                    <th class="right" style="width:18%;">%</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Puntualidad</td>
                    <td>
                        <div class="bar-wrap">
                            <div class="bar green" style="width: {{ $porcPuntualidad }}%;"></div>
                        </div>
                    </td>
                    <td class="right">{{ $porcPuntualidad }}%</td>
                </tr>
                <tr>
                    <td>Atrasos</td>
                    <td>
                        <div class="bar-wrap">
                            <div class="bar orange" style="width: {{ $porcAtraso }}%;"></div>
                        </div>
                    </td>
                    <td class="right">{{ $porcAtraso }}%</td>
                </tr>
                <tr>
                    <td>Faltas</td>
                    <td>
                        <div class="bar-wrap">
                            <div class="bar red" style="width: {{ $porcFalta }}%;"></div>
                        </div>
                    </td>
                    <td class="right">{{ $porcFalta }}%</td>
                </tr>
            </tbody>
        </table>
        <p class="muted" style="margin-top:6px;">Periodo: {{ $inicioMes->format('d/m/Y') }} —
            {{ $finMes->format('d/m/Y') }}
        </p>
    </div>

    {{-- ASISTENCIAS DETALLADAS --}}
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
                        <td>{{ optional($asistencia->fecha)->format('d/m/Y') }}</td>
                        <td>{{ optional($asistencia->hora_entrada)->format('H:i') ?? '—' }}</td>
                        <td>{{ optional($asistencia->hora_salida)->format('H:i') ?? '—' }}</td>
                        <td>{{ ucfirst($asistencia->estado) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- PERMISOS --}}
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
                            <td>{{ optional($permiso->fecha_inicio)->format('d/m/Y') }}</td>
                            <td>{{ optional($permiso->fecha_fin)->format('d/m/Y') }}</td>
                            <td>{{ ucfirst($permiso->estado) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="center">No tiene permisos este mes.</p>
        @endif
    </div>

    {{-- SALAS (lista simple) --}}
    <div class="seccion">
        <div class="titulo-seccion">Salas Asignadas (según pagos del mes)</div>
        <ul>
            @forelse($salasUnicas as $sala)
                <li>{{ $sala }}</li>
            @empty
                <li>No tiene salas asignadas este mes.</li>
            @endforelse
        </ul>
    </div>

    {{-- PAGOS DEL MES + BARRAS POR SALA --}}
    <div class="seccion">
        <div class="titulo-seccion">Pagos del Mes</div>
        <p><strong>Total pagado:</strong> Bs {{ number_format($totalPagosMes, 2) }}</p>

        @if($pagos->count())
            <table class="table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th class="right">Monto</th>
                        <th>Descripción</th>
                        <th>Sala</th>
                        <th>Turno</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pagos as $pago)
                        <tr>
                            <td>{{ optional($pago->fecha)->format('d/m/Y') }}</td>
                            <td class="right">Bs {{ number_format($pago->monto, 2) }}</td>
                            <td>{{ $pago->descripcion }}</td>
                            <td>{{ $pago->sala->nombre ?? '—' }}</td>
                            <td>{{ $pago->turno->display_horario ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="seccion" style="margin-top:10px;">
                <div class="titulo-seccion">Pagos por Sala (comparativa)</div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Sala</th>
                            <th>Comparativa</th>
                            <th class="right">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pagosPorSala as $row)
                            @php
                                $pct = $maxMontoSala ? round(($row->monto / $maxMontoSala) * 100) : 0;
                            @endphp
                            <tr>
                                <td>{{ $row->sala }}</td>
                                <td>
                                    <div class="bar-wrap">
                                        <div class="bar brand" style="width: {{ $pct }}%;"></div>
                                    </div>
                                </td>
                                <td class="right">Bs {{ number_format($row->monto, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        @else
            <p class="center">No hay pagos registrados este mes.</p>
        @endif
    </div>

    <div class="footer">
        <p class="marca">¡ACEPTA EL DESAFÍO, ROMPE LOS LÍMITES!</p>
    </div>
</body>

</html>