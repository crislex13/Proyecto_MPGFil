<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }}</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            color: #000;
            font-size: 12px;
            padding: 20px;
            max-width: 720px;
            margin: 0 auto
        }

        .logo {
            text-align: center;
            margin-bottom: 10px
        }

        .logo img {
            height: 60px
        }

        h2 {
            text-align: center;
            color: #FF6600;
            margin: 0 0 8px
        }

        .periodo {
            text-align: center;
            font-size: 12px;
            margin-bottom: 10px
        }

        .seccion {
            border-top: 2px solid #FF6600;
            margin-top: 15px;
            padding-top: 6px
        }

        .titulo-seccion {
            font-size: 14px;
            font-weight: 700;
            color: #FF6600;
            margin-bottom: 5px
        }

        .datos {
            margin-bottom: 6px
        }

        .fila {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px
        }

        .campo {
            width: 48%
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px
        }

        .table th,
        .table td {
            border: 1px solid #777;
            padding: 4px;
            font-size: 11px;
            text-align: left;
            vertical-align: top
        }

        .center {
            text-align: center
        }

        .right {
            text-align: right
        }

        .muted {
            color: #666
        }

        .kpis {
            display: table;
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px
        }

        .kpi {
            display: table-cell;
            text-align: center;
            padding: 8px;
            border: 1px solid #777
        }

        .kpi .label {
            font-size: 10px;
            color: #666
        }

        .kpi .val {
            font-size: 15px;
            font-weight: 700
        }

        .footer {
            text-align: center;
            margin-top: 16px;
            font-weight: bold;
            color: #4E5054;
            font-size: 14px
        }

        .marca {
            color: #FF6600
        }

        .avatar {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #FF6600;
            display: block;
            margin: 0 auto
        }
    </style>
</head>

<body>

    <div class="logo"><img src="{{ $logo }}" alt="MaxPowerGym"></div>
    <h2>{{ $titulo }}</h2>
    <div class="periodo">{{ $periodo }}</div>

    {{-- Datos personales --}}
    <div class="seccion">
        <div class="titulo-seccion">Datos personales</div>
        <div class="datos">
            <div style="text-align:center;margin-bottom:12px;">
                <img class="avatar" src="{{ $fotoPath }}" alt="Foto del Cliente">
                <p style="margin-top:5px;font-weight:bold;">{{ $cliente->nombre_completo }}</p>
            </div>
            <div class="fila">
                <div class="campo"><strong>Nombre completo:</strong> {{ $cliente->nombre_completo }}</div>
                <div class="campo"><strong>C.I.:</strong> {{ $cliente->ci }}</div>
            </div>
            <div class="fila">
                <div class="campo"><strong>Fecha de nacimiento:</strong> {{ $cliente->fecha_de_nacimiento }}</div>
                <div class="campo"><strong>Sexo:</strong> {{ $cliente->sexo ?? '—' }}</div>
            </div>
            <div class="fila">
                <div class="campo"><strong>Teléfono:</strong> {{ $cliente->telefono }}</div>
                <div class="campo"><strong>Correo:</strong> {{ $cliente->correo ?? '—' }}</div>
            </div>
            <p class="muted" style="margin-top:4px">
                <strong>Generado por:</strong> {{ $generado_por }} &nbsp;|&nbsp;
                <strong>Generado el:</strong> {{ $generado_el }}
            </p>
        </div>
    </div>

    {{-- KPIs del mes --}}
    <div class="seccion">
        <div class="titulo-seccion">Resumen mensual</div>
        <div class="kpis">
            <div class="kpi">
                <div class="label">Contratos de plan</div>
                <div class="val">{{ $kpiPlanes['contratos'] }}</div>
            </div>
            <div class="kpi">
                <div class="label">Cobrado en planes</div>
                <div class="val">Bs {{ number_format($kpiPlanes['cobrado'], 2) }}</div>
            </div>
            <div class="kpi">
                <div class="label">Saldo de planes</div>
                <div class="val">Bs {{ number_format($kpiPlanes['saldo'], 2) }}</div>
            </div>
            <div class="kpi">
                <div class="label">Sesiones adicionales</div>
                <div class="val">{{ $kpiSesiones['cantidad'] }}</div>
            </div>
            <div class="kpi">
                <div class="label">Ingreso por sesiones</div>
                <div class="val">Bs {{ number_format($kpiSesiones['monto'], 2) }}</div>
            </div>
        </div>
        <div class="kpis" style="margin-top:6px">
            <div class="kpi">
                <div class="label">Asistencias</div>
                <div class="val">{{ $kpiAsistencias['total'] }}</div>
            </div>
            <div class="kpi">
                <div class="label">Permisos solicitados</div>
                <div class="val">{{ $kpiPermisos['solicitados'] }}</div>
            </div>
            <div class="kpi">
                <div class="label">Aprobados / Rechazados</div>
                <div class="val">{{ $kpiPermisos['aprobados'] }} / {{ $kpiPermisos['rechazados'] }}</div>
            </div>
            <div class="kpi">
                <div class="label">Casilleros activos</div>
                <div class="val">{{ $kpiCasillero['activos'] }}</div>
            </div>
            <div class="kpi">
                <div class="label">Casillero (mensual/rep.)</div>
                <div class="val">Bs {{ number_format($kpiCasillero['mensual'], 2) }} /
                    {{ number_format($kpiCasillero['repos'], 2) }}</div>
            </div>
        </div>
    </div>

    {{-- Planes del mes --}}
    <div class="seccion">
        <div class="titulo-seccion">Planes contratados en el mes</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Plan</th>
                    <th>Disciplina</th>
                    <th>Inicio</th>
                    <th>Fin</th>
                    <th class="right">Total</th>
                    <th class="right">A cuenta</th>
                    <th class="right">Saldo</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($planesDelMes as $plan)
                    <tr>
                        <td>{{ $plan->plan->nombre ?? '—' }}</td>
                        <td>{{ $plan->disciplina->nombre ?? '—' }}</td>
                        <td>{{ $plan->fecha_inicio }}</td>
                        <td>{{ $plan->fecha_final }}</td>
                        <td class="right">Bs {{ number_format($plan->total, 2) }}</td>
                        <td class="right">Bs {{ number_format($plan->a_cuenta, 2) }}</td>
                        <td class="right">Bs {{ number_format($plan->saldo, 2) }}</td>
                        <td>{{ ucfirst($plan->estado) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="center">Sin contratos este mes.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Asistencias del mes --}}
    <div class="seccion">
        <div class="titulo-seccion">Asistencias del mes</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Tipo</th>
                </tr>
            </thead>
            <tbody>
                @forelse($asistenciasDelMes as $a)
                    <tr>
                        <td>{{ $a->fecha }}</td>
                        <td>{{ $a->hora_entrada ? \Carbon\Carbon::parse($a->hora_entrada)->format('H:i') : '—' }}</td>
                        <td>{{ ucfirst($a->tipo_asistencia ?? '—') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="center">Sin asistencias registradas este mes.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($kpiAsistencias['por_tipo']?->count())
            <p class="muted">Distribución por tipo:
                @foreach($kpiAsistencias['por_tipo'] as $tipo => $cant)
                    <strong>{{ ucfirst($tipo) }}</strong>: {{ $cant }}@if(!$loop->last), @endif
                @endforeach
            </p>
        @endif
    </div>

    {{-- Permisos del mes --}}
    <div class="seccion">
        <div class="titulo-seccion">Permisos del mes</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Motivo</th>
                    <th>Autorizado por</th>
                </tr>
            </thead>
            <tbody>
                @forelse($permisosDelMes as $p)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($p->fecha)->format('d/m/Y') }}</td>
                        <td>{{ ucfirst($p->estado ?? '—') }}</td>
                        <td>{{ $p->motivo ?? '—' }}</td>
                        <td>{{ optional($p->autorizadoPor)->name ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="center">Sin permisos solicitados este mes.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <p class="muted">Pendientes: {{ $kpiPermisos['pendientes'] }} &nbsp;|&nbsp; Aprobados:
            {{ $kpiPermisos['aprobados'] }} &nbsp;|&nbsp; Rechazados: {{ $kpiPermisos['rechazados'] }}</p>
    </div>

    {{-- Sesiones adicionales --}}
    <div class="seccion">
        <div class="titulo-seccion">Sesiones adicionales</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Instructor</th>
                    <th>Turno</th>
                    <th>Tipo</th>
                    <th class="right">Precio</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sesionesDelMes as $s)
                    <tr>
                        <td>{{ $s->fecha }}</td>
                        <td>{{ $s->instructor->nombre_completo ?? '—' }}</td>
                        <td>{{ $s->turno->nombre ?? '—' }}</td>
                        <td>{{ ucfirst($s->tipo_sesion ?? '—') }}</td>
                        <td class="right">Bs {{ number_format((float) $s->precio, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="center">Sin sesiones adicionales este mes.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Casilleros del mes --}}
    <div class="seccion">
        <div class="titulo-seccion">Casilleros</div>
        <table class="table">
            <thead>
                <tr>
                    <th>N°</th>
                    <th>Estado</th>
                    <th>Entrega</th>
                    <th>Vence</th>
                    <th>Mét. pago</th>
                    <th class="right">Mensualidad</th>
                    <th class="right">Reposiciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($casillerosMes as $c)
                    <tr>
                        <td>{{ $c->numero }}</td>
                        <td>{{ ucfirst($c->estado) }}</td>
                        <td>{{ $c->fecha_entrega_llave ? \Carbon\Carbon::parse($c->fecha_entrega_llave)->format('d/m/Y') : '—' }}
                        </td>
                        <td>{{ $c->fecha_final_llave ? \Carbon\Carbon::parse($c->fecha_final_llave)->format('d/m/Y') : '—' }}
                        </td>
                        <td>{{ strtoupper($c->metodo_pago ?? '—') }}</td>
                        <td class="right">Bs {{ number_format((float) $c->costo_mensual, 2) }}</td>
                        <td class="right">Bs {{ number_format((float) $c->monto_reposiciones, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="center">Sin casilleros para este mes.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer"><span class="marca">¡ACEPTA EL DESAFÍO, ROMPE LOS LÍMITES!</span></div>
</body>

</html>