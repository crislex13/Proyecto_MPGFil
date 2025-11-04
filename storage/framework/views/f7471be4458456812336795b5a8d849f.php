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
        <img src="<?php echo e(public_path('images/LogosMPG/Recurso 3.png')); ?>" alt="MaxPowerGym">
    </div>

    <h2>Ficha Mensual de Personal — <?php echo e($mes); ?></h2>

    <?php
        $dias = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];
    ?>

    
    <div class="seccion">
        <div class="titulo-seccion">Datos Personales</div>
        <div class="foto-nombre">
            <img src="<?php echo e($personal->foto_path_for_pdf); ?>" alt="Foto del Personal">
            <p style="margin-top:5px; font-weight:bold;"><?php echo e($personal->nombre_completo); ?></p>
        </div>
        <div class="fila">
            <div class="campo"><strong>C.I.:</strong> <?php echo e($personal->ci); ?></div>
            <div class="campo"><strong>Teléfono:</strong> <?php echo e($personal->telefono); ?></div>
        </div>
        <div class="fila">
            <div class="campo"><strong>Cargo:</strong> <?php echo e(ucfirst($personal->cargo)); ?></div>
            <div class="campo"><strong>Fecha de Contratación:</strong>
                <?php echo e(optional($personal->fecha_contratacion)->format('d/m/Y')); ?></div>
        </div>
        <div class="fila">
            <div class="campo"><strong>Fecha de nacimiento:</strong>
                <?php echo e(optional($personal->fecha_de_nacimiento)->format('d/m/Y')); ?></div>
            <div class="campo"><strong>Correo:</strong> <?php echo e($personal->correo); ?></div>
        </div>
        <div class="fila">
            <div class="campo"><strong>Dirección:</strong> <?php echo e($personal->direccion); ?></div>
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
                <?php $__currentLoopData = $personal->turnos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $turno): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($turno->nombre); ?></td>
                        <td><?php echo e($dias[$turno->dia] ?? $turno->dia); ?></td>
                        <td><?php echo e($turno->hora_inicio); ?></td>
                        <td><?php echo e($turno->hora_fin); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>

    
    <div class="seccion">
        <div class="titulo-seccion">Resumen de Asistencias del Mes</div>

        <div class="kpis">
            <div class="kpi">
                <div class="label">Puntuales</div>
                <div class="val"><?php echo e(number_format($puntuales)); ?></div>
            </div>
            <div class="kpi">
                <div class="label">Atrasos</div>
                <div class="val"><?php echo e(number_format($atrasos)); ?></div>
            </div>
            <div class="kpi">
                <div class="label">Faltas</div>
                <div class="val"><?php echo e(number_format($faltas)); ?></div>
            </div>
            <div class="kpi">
                <div class="label">Permisos</div>
                <div class="val"><?php echo e(number_format($conPermiso)); ?></div>
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
                            <div class="bar green" style="width: <?php echo e($porcPuntualidad); ?>%;"></div>
                        </div>
                    </td>
                    <td class="right"><?php echo e($porcPuntualidad); ?>%</td>
                </tr>
                <tr>
                    <td>Atrasos</td>
                    <td>
                        <div class="bar-wrap">
                            <div class="bar orange" style="width: <?php echo e($porcAtraso); ?>%;"></div>
                        </div>
                    </td>
                    <td class="right"><?php echo e($porcAtraso); ?>%</td>
                </tr>
                <tr>
                    <td>Faltas</td>
                    <td>
                        <div class="bar-wrap">
                            <div class="bar red" style="width: <?php echo e($porcFalta); ?>%;"></div>
                        </div>
                    </td>
                    <td class="right"><?php echo e($porcFalta); ?>%</td>
                </tr>
            </tbody>
        </table>
        <p class="muted" style="margin-top:6px;">Periodo: <?php echo e($inicioMes->format('d/m/Y')); ?> —
            <?php echo e($finMes->format('d/m/Y')); ?>

        </p>
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
                <?php $__currentLoopData = $personal->asistencias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $asistencia): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e(optional($asistencia->fecha)->format('d/m/Y')); ?></td>
                        <td><?php echo e(optional($asistencia->hora_entrada)->format('H:i') ?? '—'); ?></td>
                        <td><?php echo e(optional($asistencia->hora_salida)->format('H:i') ?? '—'); ?></td>
                        <td><?php echo e(ucfirst($asistencia->estado)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>

    
    <div class="seccion">
        <div class="titulo-seccion">Permisos del Mes</div>
        <?php if($personal->permisos->count()): ?>
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
                    <?php $__currentLoopData = $personal->permisos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permiso): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($permiso->tipo); ?></td>
                            <td><?php echo e($permiso->motivo); ?></td>
                            <td><?php echo e(optional($permiso->fecha_inicio)->format('d/m/Y')); ?></td>
                            <td><?php echo e(optional($permiso->fecha_fin)->format('d/m/Y')); ?></td>
                            <td><?php echo e(ucfirst($permiso->estado)); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="center">No tiene permisos este mes.</p>
        <?php endif; ?>
    </div>

    
    <div class="seccion">
        <div class="titulo-seccion">Salas Asignadas (según pagos del mes)</div>
        <ul>
            <?php $__empty_1 = true; $__currentLoopData = $salasUnicas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sala): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <li><?php echo e($sala); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <li>No tiene salas asignadas este mes.</li>
            <?php endif; ?>
        </ul>
    </div>

    
    <div class="seccion">
        <div class="titulo-seccion">Pagos del Mes</div>
        <p><strong>Total pagado:</strong> Bs <?php echo e(number_format($totalPagosMes, 2)); ?></p>

        <?php if($pagos->count()): ?>
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
                    <?php $__currentLoopData = $pagos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pago): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e(optional($pago->fecha)->format('d/m/Y')); ?></td>
                            <td class="right">Bs <?php echo e(number_format($pago->monto, 2)); ?></td>
                            <td><?php echo e($pago->descripcion); ?></td>
                            <td><?php echo e($pago->sala->nombre ?? '—'); ?></td>
                            <td><?php echo e($pago->turno->display_horario ?? '—'); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                        <?php $__currentLoopData = $pagosPorSala; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $pct = $maxMontoSala ? round(($row->monto / $maxMontoSala) * 100) : 0;
                            ?>
                            <tr>
                                <td><?php echo e($row->sala); ?></td>
                                <td>
                                    <div class="bar-wrap">
                                        <div class="bar brand" style="width: <?php echo e($pct); ?>%;"></div>
                                    </div>
                                </td>
                                <td class="right">Bs <?php echo e(number_format($row->monto, 2)); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

        <?php else: ?>
            <p class="center">No hay pagos registrados este mes.</p>
        <?php endif; ?>
    </div>

    <div class="footer">
        <p class="marca">¡ACEPTA EL DESAFÍO, ROMPE LOS LÍMITES!</p>
    </div>
</body>

</html><?php /**PATH C:\xampp\htdocs\Laravel\Proyecto_MPGFil\resources\views/pdf/ficha-personal-mensual.blade.php ENDPATH**/ ?>