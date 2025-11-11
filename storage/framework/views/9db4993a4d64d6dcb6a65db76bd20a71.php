<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title><?php echo e($titulo); ?></title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #000;
            max-width: 720px;
            margin: 0 auto;
            padding: 20px
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
                    <th class="center">Registros</th>
                    <th class="center">Pagados</th>
                    <th class="center">Pendientes</th>
                    <th class="center">Monto total (Bs)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="center"><?php echo e((int) $registros); ?></td>
                    <td class="center"><?php echo e((int) $pagados); ?></td>
                    <td class="center"><?php echo e((int) $pendientes); ?></td>
                    <td class="center"><?php echo e(number_format($montoTotal, 2)); ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    
    <div class="seccion">
        <div class="titulo-seccion">Por método de pago</div>
        <?php $mm = (int) ($maxMetodoMonto ?? 0); ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Método</th>
                    <th class="center" style="width:18%">Monto (Bs)</th>
                    <th class="center" style="width:10%">Reg.</th>
                    <th style="width:45%">Comparativa</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = ($porMetodo ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php $pct = $mm ? round(($row->monto / $mm) * 100) : 0; ?>
                    <tr>
                        <td><?php echo e(ucfirst($row->metodo ?? '—')); ?></td>
                        <td class="center"><?php echo e(number_format($row->monto, 2)); ?></td>
                        <td class="center"><?php echo e((int) $row->count); ?></td>
                        <td>
                            <div class="bar-wrap">
                                <div class="bar" style="width: <?php echo e($pct); ?>%"></div>
                            </div>
                        </td>
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
        <div class="titulo-seccion">Top salas (por monto)</div>
        <?php $ms = (int) ($maxSalaMonto ?? 0); ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Sala</th>
                    <th class="center" style="width:18%">Monto (Bs)</th>
                    <th class="center" style="width:10%">Reg.</th>
                    <th style="width:45%">Comparativa</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = ($porSala ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php $pct = $ms ? round(($row->monto / $ms) * 100) : 0; ?>
                    <tr>
                        <td><?php echo e($row->sala); ?></td>
                        <td class="center"><?php echo e(number_format($row->monto, 2)); ?></td>
                        <td class="center"><?php echo e((int) $row->count); ?></td>
                        <td>
                            <div class="bar-wrap">
                                <div class="bar" style="width: <?php echo e($pct); ?>%"></div>
                            </div>
                        </td>
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
        <div class="titulo-seccion">Top turnos (por monto)</div>
        <?php $mt = (int) ($maxTurnoMonto ?? 0); ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Turno</th>
                    <th>Día</th>
                    <th class="center" style="width:18%">Monto (Bs)</th>
                    <th class="center" style="width:10%">Reg.</th>
                    <th style="width:45%">Comparativa</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = ($porTurno ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php $pct = $mt ? round(($row->monto / $mt) * 100) : 0; ?>
                    <tr>
                        <td><?php echo e($row->turno); ?></td>
                        <td><?php echo e($row->dia); ?></td>
                        <td class="center"><?php echo e(number_format($row->monto, 2)); ?></td>
                        <td class="center"><?php echo e((int) $row->count); ?></td>
                        <td>
                            <div class="bar-wrap">
                                <div class="bar" style="width: <?php echo e($pct); ?>%"></div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="5" class="center">Sin datos.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    
    <div class="seccion">
        <div class="titulo-seccion">Ranking por personal (monto)</div>
        <?php $mp = (int) ($maxPersonalMonto ?? 0); ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Personal</th>
                    <th class="center" style="width:18%">Monto (Bs)</th>
                    <th class="center" style="width:10%">Reg.</th>
                    <th style="width:45%">Comparativa</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = ($porPersonal ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php $pct = $mp ? round(($row->monto / $mp) * 100) : 0; ?>
                    <tr>
                        <td><?php echo e($row->personal); ?></td>
                        <td class="center"><?php echo e(number_format($row->monto, 2)); ?></td>
                        <td class="center"><?php echo e((int) $row->count); ?></td>
                        <td>
                            <div class="bar-wrap">
                                <div class="bar" style="width: <?php echo e($pct); ?>%"></div>
                            </div>
                        </td>
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
        <div class="titulo-seccion">Detalle</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Personal</th>
                    <th>Turno</th>
                    <th>Día</th>
                    <th>Sala</th>
                    <th>Método</th>
                    <th class="right">Monto (Bs)</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = ($detalle ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($r->fecha); ?></td>
                        <td><?php echo e($r->personal); ?></td>
                        <td><?php echo e($r->turno); ?></td>
                        <td><?php echo e($r->dia); ?></td>
                        <td><?php echo e($r->sala); ?></td>
                        <td><?php echo e(ucfirst($r->metodo)); ?></td>
                        <td class="right"><?php echo e(number_format($r->monto, 2)); ?></td>
                        <td><?php echo e($r->estado); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="8" class="center">Sin registros.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html><?php /**PATH C:\xampp\htdocs\Laravel\Proyecto_MPGFil\resources\views/pdf/pagos_resumen.blade.php ENDPATH**/ ?>