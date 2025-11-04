<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title><?php echo e($titulo); ?></title>
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

        .right {
            text-align: right;
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

        .center {
            text-align: center;
        }

        .footer {
            text-align: center;
            margin-top: 18px;
            font-size: 13px;
            font-weight: bold;
            color: #FF6600;
        }

        .muted {
            color: #666;
            font-size: 11px;
        }
    </style>
</head>

<body>
    <div class="logo">
        <img src="<?php echo e(public_path('images/LogosMPG/Recurso 3.png')); ?>" alt="MaxPowerGym">
    </div>
    <h2><?php echo e($titulo); ?></h2>
    <div class="periodo"><?php echo e($periodo); ?></div>
    <div class="meta">
        <div class="row">
            <div class="cell"><strong>Generado por:</strong> <?php echo e($generado_por); ?></div>
            <div class="cell right"><strong>Generado el:</strong> <?php echo e($generado_el); ?></div>
        </div>
    </div>

    
    <div class="seccion">
        <div class="titulo-seccion">Resumen ejecutivo</div>
        <div class="kpis">
            <div class="kpi">
                <div class="label">Sesiones</div>
                <div class="val"><?php echo e(number_format($sesiones)); ?></div>
            </div>
            <div class="kpi">
                <div class="label">Clientes únicos</div>
                <div class="val"><?php echo e(number_format($clientesUnicos)); ?></div>
            </div>
            <div class="kpi">
                <div class="label">Ingresos</div>
                <div class="val">Bs <?php echo e(number_format($ingresos, 2)); ?></div>
            </div>
            <div class="kpi">
                <div class="label">Ticket promedio</div>
                <div class="val">Bs <?php echo e(number_format($ticketPromedio, 2)); ?></div>
            </div>
        </div>
    </div>

    
    <div class="seccion">
        <div class="titulo-seccion">Top 5 disciplinas por sesiones</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Disciplina</th>
                    <th class="center">Sesiones</th>
                    <th style="width:40%;">Comparativa</th>
                    <th class="right">Ingresos</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $topDisciplinas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php $pct = $maxSesDisciplina ? round(($d->sesiones / $maxSesDisciplina) * 100) : 0; ?>
                    <tr>
                        <td><?php echo e($d->disciplina_nombre); ?></td>
                        <td class="center"><?php echo e($d->sesiones); ?></td>
                        <td>
                            <div class="bar-wrap">
                                <div class="bar" style="width: <?php echo e($pct); ?>%;"></div>
                            </div>
                        </td>
                        <td class="right">Bs <?php echo e(number_format($d->ingresos, 2)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="4" class="center">Sin datos.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    
    <div class="seccion">
        <div class="titulo-seccion">Top 5 instructores por sesiones</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Instructor</th>
                    <th class="center">Sesiones</th>
                    <th style="width:40%;">Comparativa</th>
                    <th class="right">Ingresos</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $topInstructores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php $pct = $maxSesInstructor ? round(($i->sesiones / $maxSesInstructor) * 100) : 0; ?>
                    <tr>
                        <td><?php echo e(trim($i->instructor_nombre) ?: '—'); ?></td>
                        <td class="center"><?php echo e($i->sesiones); ?></td>
                        <td>
                            <div class="bar-wrap">
                                <div class="bar" style="width: <?php echo e($pct); ?>%;"></div>
                            </div>
                        </td>
                        <td class="right">Bs <?php echo e(number_format($i->ingresos, 2)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="4" class="center">Sin datos.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    
    <div class="seccion">
        <div class="titulo-seccion">Top 5 clientes por gasto en sesiones</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th class="center">Sesiones</th>
                    <th style="width:40%;">Comparativa</th>
                    <th class="right">Gasto</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $topClientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php $pct = $maxGastoCliente ? round(($c->gasto / $maxGastoCliente) * 100) : 0; ?>
                    <tr>
                        <td><?php echo e(trim($c->cliente_nombre) ?: '—'); ?></td>
                        <td class="center"><?php echo e($c->sesiones); ?></td>
                        <td>
                            <div class="bar-wrap">
                                <div class="bar" style="width: <?php echo e($pct); ?>%;"></div>
                            </div>
                        </td>
                        <td class="right">Bs <?php echo e(number_format($c->gasto, 2)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="4" class="center">Sin datos.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    
    <div class="seccion">
        <div class="titulo-seccion">Distribución por día de semana</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Día</th>
                    <th class="center">Sesiones</th>
                    <th class="right">Ingresos</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $porDiaSemana; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($d->dia_nombre); ?></td>
                        <td class="center"><?php echo e($d->sesiones); ?></td>
                        <td class="right">Bs <?php echo e(number_format($d->ingresos, 2)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="3" class="center">Sin datos.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    
    <div class="seccion">
        <div class="titulo-seccion">Distribución por hora del día</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Hora</th>
                    <th class="center">Sesiones</th>
                    <th class="right">Ingresos</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $porHora; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e(str_pad($h->h, 2, '0', STR_PAD_LEFT)); ?>:00</td>
                        <td class="center"><?php echo e($h->sesiones); ?></td>
                        <td class="right">Bs <?php echo e(number_format($h->ingresos, 2)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="3" class="center">Sin datos.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    
    <div class="seccion">
        <div class="titulo-seccion">Detalle de sesiones del período</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Instructor</th>
                    <th>Tipo</th>
                    <th class="right">Precio</th>
                    <th>Hora</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $detalle; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e(\Carbon\Carbon::parse($s->fecha)->format('Y-m-d')); ?></td>
                        <td><?php echo e($s->cliente?->nombre); ?> <?php echo e($s->cliente?->apellido_paterno); ?>

                            <?php echo e($s->cliente?->apellido_materno); ?></td>
                        <td><?php echo e($s->instructor?->nombre); ?> <?php echo e($s->instructor?->apellido_paterno); ?>

                            <?php echo e($s->instructor?->apellido_materno); ?></td>
                        <td><?php echo e($s->disciplina?->nombre ?? '—'); ?></td>
                        <td class="right">Bs <?php echo e(number_format($s->precio, 2)); ?></td>
                        <td>
                            <?php
                                $hi = $s->hora_inicio ? \Carbon\Carbon::parse($s->hora_inicio)->format('H:i') : null;
                                $hf = $s->hora_fin ? \Carbon\Carbon::parse($s->hora_fin)->format('H:i') : null;
                            ?>
                            <?php echo e($hi && $hf ? "$hi - $hf" : ($hi ?? '—')); ?>

                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="6" class="center">Sin registros.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="footer">¡ACEPTA EL DESAFÍO, ROMPE LOS LÍMITES!</div>
</body>

</html><?php /**PATH C:\xampp\htdocs\Laravel\Proyecto_MPGFil\resources\views/pdf/sesiones_resumen.blade.php ENDPATH**/ ?>