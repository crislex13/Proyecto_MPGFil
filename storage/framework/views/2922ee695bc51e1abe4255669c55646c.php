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
            max-width: 720px;
            margin: 0 auto;
            padding: 20px;
        }

        .logo {
            text-align: center;
            margin-bottom: 8px
        }

        .logo img {
            height: 60px
        }

        h2 {
            text-align: center;
            color: #FF6600;
            margin: 4px 0 12px
        }

        .periodo {
            text-align: center;
            font-size: 12px;
            margin-bottom: 8px
        }

        .meta {
            margin-top: 6px;
            font-size: 11px;
            color: #555
        }

        .row {
            display: table;
            width: 100%
        }

        .cell {
            display: table-cell;
            width: 50%
        }

        .seccion {
            border-top: 2px solid #FF6600;
            margin-top: 16px;
            padding-top: 6px
        }

        .titulo-seccion {
            font-size: 14px;
            font-weight: 700;
            color: #FF6600;
            margin-bottom: 6px
        }

        table.table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px
        }

        .table th,
        .table td {
            border: 1px solid #777;
            padding: 5px;
            font-size: 11px;
            text-align: left;
            vertical-align: top
        }

        .right {
            text-align: right
        }

        .center {
            text-align: center
        }

        .bar-wrap {
            background: #eee;
            height: 10px;
            width: 100%;
            border-radius: 4px;
            overflow: hidden
        }

        .bar {
            background: #FF6600;
            height: 10px
        }
    </style>
</head>

<body>
    <div class="logo"><img src="<?php echo e($logo); ?>" alt="MaxPowerGym"></div>
    <h2><?php echo e($titulo); ?></h2>
    <div class="periodo"><?php echo e($periodo); ?></div>
    <div class="meta">
        <div class="row">
            <div class="cell"><strong>Generado por:</strong> <?php echo e($generadoPor); ?></div>
            <div class="cell right"><strong>Generado el:</strong> <?php echo e($generadoEl); ?></div>
        </div>
    </div>

    <div class="seccion">
        <div class="titulo-seccion">Resumen</div>
        <table class="table">
            <thead>
                <tr>
                    <th class="center">Turnos</th>
                    <th class="center">Activos</th>
                    <th class="center">Inactivos</th>
                    <th class="center">Horas programadas (mes)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="center"><?php echo e((int) $totTurnos); ?></td>
                    <td class="center"><?php echo e((int) $activos); ?></td>
                    <td class="center"><?php echo e((int) $inactivos); ?></td>
                    <td class="center"><?php echo e(number_format($horasProgramadasTotales, 2)); ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="seccion">
        <div class="titulo-seccion">Distribución por día</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Día</th>
                    <th class="center" style="width:18%">Turnos</th>
                    <th style="width:45%">Comparativa</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $porDia; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $pct = $maxDia ? round(($d->c / $maxDia) * 100) : 0; ?>
                    <tr>
                        <td><?php echo e($d->dia); ?></td>
                        <td class="center"><?php echo e((int) $d->c); ?></td>
                        <td>
                            <div class="bar-wrap">
                                <div class="bar" style="width: <?php echo e($pct); ?>%"></div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>

    <div class="seccion">
        <div class="titulo-seccion">Horas programadas por personal (ranking)</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Personal</th>
                    <th class="center" style="width:18%">Horas</th>
                    <th style="width:45%">Comparativa</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $porPersonalHoras; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php $pct = $maxHorasPersonal ? round(($row->horas / $maxHorasPersonal) * 100) : 0; ?>
                    <tr>
                        <td><?php echo e($row->personal); ?></td>
                        <td class="center"><?php echo e(number_format($row->horas, 2)); ?></td>
                        <td>
                            <div class="bar-wrap">
                                <div class="bar" style="width: <?php echo e($pct); ?>%"></div>
                            </div>
                        </td>
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
        <div class="titulo-seccion">Cobertura (programados vs asistidos)</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Personal</th>
                    <th>Turno</th>
                    <th>Día</th>
                    <th>Horario</th>
                    <th class="center">Programados</th>
                    <th class="center">Asistidos</th>
                    <th class="center" style="width:12%">% Cumpl.</th>
                    <th style="width:30%">Barra</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $detalleCobertura; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($r->personal); ?></td>
                        <td><?php echo e($r->turno); ?></td>
                        <td><?php echo e($r->dia); ?></td>
                        <td><?php echo e($r->hora); ?></td>
                        <td class="center"><?php echo e((int) $r->programados); ?></td>
                        <td class="center"><?php echo e((int) $r->asistidos); ?></td>
                        <td class="center"><?php echo e((int) $r->cumplimiento); ?>%</td>
                        <td>
                            <div class="bar-wrap">
                                <div class="bar" style="width: <?php echo e($r->cumplimiento); ?>%"></div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="8" class="center">Sin registros.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="footer">¡ACEPTA EL DESAFÍO, ROMPE LOS LÍMITES!</div>
</body>

</html><?php /**PATH C:\xampp\htdocs\Laravel\Proyecto_MPGFil\resources\views/pdf/turnos_cobertura_mensual.blade.php ENDPATH**/ ?>