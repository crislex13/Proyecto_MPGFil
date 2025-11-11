<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo e($titulo); ?></title>
    <style>
        body { font-family: 'Poppins', sans-serif; font-size: 12px; color: #000; padding: 20px; max-width: 720px; margin: 0 auto; }
        .logo { text-align: center; margin-bottom: 8px; }
        .logo img { height: 60px; }
        h2 { text-align: center; color: #FF6600; margin: 4px 0 12px; }
        .periodo { text-align: center; font-size: 12px; margin-bottom: 8px; }

        .seccion { border-top: 2px solid #FF6600; margin-top: 16px; padding-top: 6px; }
        .titulo-seccion { font-size: 14px; font-weight: bold; color: #FF6600; margin-bottom: 6px; }

        .kpis { display: table; width: 100%; border-collapse: collapse; margin-top: 6px; }
        .kpis .kpi { display: table-cell; width: 25%; text-align: center; padding: 8px; border: 1px solid #777; }
        .kpi .label { font-size: 10px; color: #666; }
        .kpi .val { font-size: 16px; font-weight: 700; }

        .table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .table th, .table td { border: 1px solid #777; padding: 5px; font-size: 11px; text-align: left; }
        .right { text-align: right; }
        .center { text-align: center; }

        .bar-wrap { background: #eee; height: 10px; width: 100%; border-radius: 4px; overflow: hidden; }
        .bar     { background: #FF6600; height: 10px; }

        .footer { text-align: center; margin-top: 18px; font-size: 13px; font-weight: bold; color: #FF6600; }
        .muted { color: #666; font-size: 11px; }

        .meta { margin-top: 6px; font-size: 11px; color: #555; }
        .meta .row { display: table; width: 100%; }
        .meta .cell { display: table-cell; width: 50%; }
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
            <div class="kpi"><div class="label">Registros</div><div class="val"><?php echo e(number_format($registros)); ?></div></div>
            <div class="kpi"><div class="label">Unidades</div><div class="val"><?php echo e(number_format($totalUnidades)); ?></div></div>
            <div class="kpi"><div class="label">Paquetes</div><div class="val"><?php echo e(number_format($totalPaquetes)); ?></div></div>
            <div class="kpi"><div class="label">Costo total</div><div class="val">Bs <?php echo e(number_format($totalCosto, 2)); ?></div></div>
        </div>
        <p class="muted" style="margin-top:6px;">
            <strong>Costo promedio por registro:</strong> Bs <?php echo e(number_format($ticketPromedio, 2)); ?>

        </p>
    </div>

    
    <div class="seccion">
        <div class="titulo-seccion">Top 5 productos por costo total</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th class="center">Unid.</th>
                    <th class="center">Paq.</th>
                    <th style="width:40%;">Comparativa</th>
                    <th class="right">Costo</th>
                </tr>
            </thead>
            <tbody>
                <?php $top5 = $porProducto->take(5); ?>
                <?php $__empty_1 = true; $__currentLoopData = $top5; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $pct = $maxCostoProd ? round(($row->costo_total / $maxCostoProd) * 100) : 0;
                    ?>
                    <tr>
                        <td><?php echo e($row->producto ?? '—'); ?></td>
                        <td class="center"><?php echo e($row->unidades ?? 0); ?></td>
                        <td class="center"><?php echo e($row->paquetes ?? 0); ?></td>
                        <td><div class="bar-wrap"><div class="bar" style="width: <?php echo e($pct); ?>%;"></div></div></td>
                        <td class="right">Bs <?php echo e(number_format($row->costo_total, 2)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="5" class="center">Sin datos en el período.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    
    <div class="seccion">
        <div class="titulo-seccion">Ingresos por usuario</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th class="center">Registros</th>
                    <th style="width:40%;">Comparativa</th>
                    <th class="right">Costo</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $porUsuario; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $pctU = $maxCostoUser ? round(($u->costo_total / $maxCostoUser) * 100) : 0;
                    ?>
                    <tr>
                        <td><?php echo e($u->usuario ?? '—'); ?></td>
                        <td class="center"><?php echo e($u->registros); ?></td>
                        <td><div class="bar-wrap"><div class="bar" style="width: <?php echo e($pctU); ?>%;"></div></div></td>
                        <td class="right">Bs <?php echo e(number_format($u->costo_total, 2)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php if($porUsuario->isEmpty()): ?>
                    <tr><td colspan="4" class="center">Sin datos.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    
    <div class="seccion">
        <div class="titulo-seccion">Por día de la semana</div>
        <table class="table">
            <thead><tr><th>Día</th><th class="center">Registros</th><th class="right">Costo</th></tr></thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $porDiaSemana; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($d->dia_nombre); ?></td>
                        <td class="center"><?php echo e($d->registros); ?></td>
                        <td class="right">Bs <?php echo e(number_format($d->costo_total, 2)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="3" class="center">Sin datos.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    
    <div class="seccion">
        <div class="titulo-seccion">Detalle de ingresos del período</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Producto</th>
                    <th class="right">Unidades</th>
                    <th class="right">P. Unit</th>
                    <th class="right">Paquetes</th>
                    <th class="right">P. Pack</th>
                    <th class="center">Vencimiento</th>
                    <th>Registrado por</th>
                    <th class="right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $detalle; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e(optional($r->fecha)->format('d/m/Y H:i')); ?></td>
                        <td><?php echo e($r->producto?->nombre ?? '—'); ?></td>
                        <td class="right"><?php echo e($r->cantidad_unidades ?? 0); ?></td>
                        <td class="right">Bs <?php echo e(number_format($r->precio_unitario ?? 0, 2)); ?></td>
                        <td class="right"><?php echo e($r->cantidad_paquetes ?? 0); ?></td>
                        <td class="right">Bs <?php echo e(number_format($r->precio_paquete ?? 0, 2)); ?></td>
                        <td class="center"><?php echo e(optional($r->fecha_vencimiento)->format('d/m/Y') ?? '—'); ?></td>
                        <td><?php echo e($r->registradoPor?->name ?? '—'); ?></td>
                        <td class="right">Bs <?php echo e(number_format($r->subtotal, 2)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="9" class="center">Sin registros.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="footer">¡ACEPTA EL DESAFÍO, ROMPE LOS LÍMITES!</div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\Laravel\Proyecto_MPGFil\resources\views/pdf/ingresos_productos_reporte.blade.php ENDPATH**/ ?>