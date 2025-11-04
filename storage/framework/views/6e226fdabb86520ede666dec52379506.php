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

        .seccion {
            border-top: 2px solid #FF6600;
            margin-top: 16px;
            padding-top: 6px;
        }

        .titulo-seccion {
            font-size: 14px;
            font-weight: 700;
            color: #FF6600;
            margin-bottom: 6px;
        }

        table.table {
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
            vertical-align: top;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
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

        .footer {
            text-align: center;
            margin-top: 18px;
            font-size: 13px;
            font-weight: bold;
            color: #FF6600;
        }
    </style>
</head>

<body>
    <div class="logo">
        <img src="<?php echo e($logo); ?>" alt="MaxPowerGym">
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
        <div class="titulo-seccion">Resumen mensual</div>
        <table class="table">
            <thead>
                <tr>
                    <th class="center">Pendientes</th>
                    <th class="center">Aprobados</th>
                    <th class="center">Rechazados</th>
                    <th class="center">Solicitados</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="center"><?php echo e((int) ($totales['pendientes'] ?? 0)); ?></td>
                    <td class="center"><?php echo e((int) ($totales['aprobados'] ?? 0)); ?></td>
                    <td class="center"><?php echo e((int) ($totales['rechazados'] ?? 0)); ?></td>
                    <td class="center"><?php echo e((int) ($totales['solicitados'] ?? 0)); ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    
    <div class="seccion">
        <div class="titulo-seccion">Top motivos</div>
        <?php
            $arr = collect($topMotivos ?? []);
            $maxMot = max($arr->pluck('c')->toArray() ?: [0]);
        ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Motivo</th>
                    <th class="center">Cantidad</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $arr; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php $pct = $maxMot ? round(($m->c / $maxMot) * 100) : 0; ?>
                    <tr>
                        <td>
                            <?php echo e($m->motivo); ?>

                            <div class="bar-wrap">
                                <div class="bar" style="width: <?php echo e($pct); ?>%;"></div>
                            </div>
                        </td>
                        <td class="center"><?php echo e((int) $m->c); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="2" class="center">Sin datos</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    
    <div class="seccion">
        <div class="titulo-seccion">Clientes con ≥ 3 permisos en el mes</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th class="center">Permisos</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = ($limiteClientes ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e(data_get($lc, 'cliente_nombre', '—')); ?></td>
                        <td class="center"><?php echo e((int) data_get($lc, 'c', 0)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="2" class="center">Sin casos</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    
    <div class="seccion">
        <div class="titulo-seccion">Detalle de permisos</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Estado</th>
                    <th>Motivo</th>
                    <th>Autorizado por</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = ($detalle ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td>
                            <?php echo e(!empty($p->fecha) ? \Carbon\Carbon::parse($p->fecha)->format('d/m/Y') : '—'); ?>

                        </td>
                        <td><?php echo e(optional($p->cliente)->nombre_completo ?? '—'); ?></td>
                        <td><?php echo e(isset($p->estado) ? ucfirst($p->estado) : '—'); ?></td>
                        <td><?php echo e($p->motivo ?? '—'); ?></td>
                        <td><?php echo e(optional($p->autorizadoPor)->name ?? '—'); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="5" class="center">Sin registros</td>
                    </tr>
                <?php endif; ?>
            </tbody>

        </table>
    </div>

    <div class="footer">¡ACEPTA EL DESAFÍO, ROMPE LOS LÍMITES!</div>
</body>

</html><?php /**PATH C:\xampp\htdocs\Laravel\Proyecto_MPGFil\resources\views/pdf/permisos-clientes-mensual.blade.php ENDPATH**/ ?>