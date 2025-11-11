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
            margin-bottom: 10px;
        }

        .logo img {
            height: 60px;
        }

        h2 {
            text-align: center;
            color: #FF6600;
            margin-bottom: 12px;
        }

        .periodo {
            text-align: center;
            font-size: 12px;
            margin-bottom: 8px;
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

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
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

    <div class="muted" style="margin-top:6px;">
        <div><strong>Generado por:</strong> <?php echo e($generado_por); ?></div>
        <div><strong>Generado el:</strong> <?php echo e($generado_el); ?></div>
    </div>

    
    <div class="seccion">
        <div class="titulo-seccion">Resumen de Ingresos</div>
        <div class="kpis">
            <div class="kpi">
                <div class="label">Registros</div>
                <div class="val"><?php echo e(number_format($ing_registros)); ?></div>
            </div>
            <div class="kpi">
                <div class="label">Unidades</div>
                <div class="val"><?php echo e(number_format($ing_unidades)); ?></div>
            </div>
            <div class="kpi">
                <div class="label">Paquetes</div>
                <div class="val"><?php echo e(number_format($ing_paquetes)); ?></div>
            </div>
            <div class="kpi">
                <div class="label">Costo Total</div>
                <div class="val">Bs <?php echo e(number_format($ing_costo, 2)); ?></div>
            </div>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th class="center">Unid.</th>
                    <th class="center">Pack</th>
                    <th class="right">P. Unit</th>
                    <th class="right">P. Pack</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $ingresos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e(optional($r->producto)->nombre ?? '—'); ?></td>
                        <td class="center"><?php echo e((int) ($r->cantidad_unidades ?? 0)); ?></td>
                        <td class="center"><?php echo e((int) ($r->cantidad_paquetes ?? 0)); ?></td>
                        <td class="right">Bs <?php echo e(number_format($r->precio_unitario ?? 0, 2)); ?></td>
                        <td class="right">Bs <?php echo e(number_format($r->precio_paquete ?? 0, 2)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="5" class="center">Sin ingresos en el período.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    
    <div class="seccion">
        <div class="titulo-seccion">Resumen de Ventas</div>
        <div class="kpis">
            <div class="kpi">
                <div class="label">Ventas</div>
                <div class="val"><?php echo e(number_format($ven_registros)); ?></div>
            </div>
            <div class="kpi">
                <div class="label">Ítems vendidos</div>
                <div class="val"><?php echo e(number_format($ven_items)); ?></div>
            </div>
            <div class="kpi">
                <div class="label">Total QR</div>
                <div class="val">Bs <?php echo e(number_format($ven_qr, 2)); ?></div>
            </div>
            <div class="kpi">
                <div class="label">Total Efectivo</div>
                <div class="val">Bs <?php echo e(number_format($ven_efectivo, 2)); ?></div>
            </div>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th class="center">Cant.</th>
                    <th class="right">P. Unit</th>
                    <th class="right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $detalleVentas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e(optional($d->producto)->nombre ?? '—'); ?></td>
                        <td class="center"><?php echo e((int) ($d->cantidad ?? 0)); ?></td>
                        <td class="right">Bs <?php echo e(number_format($d->precio_unitario ?? 0, 2)); ?></td>
                        <td class="right">Bs <?php echo e(number_format($d->subtotal ?? 0, 2)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="4" class="center">Sin ventas en el período.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="muted" style="margin-top:6px;">
            <strong>Total vendido (todas las formas de pago):</strong> Bs <?php echo e(number_format($ven_total, 2)); ?>

        </div>
    </div>

    
    <div class="seccion">
        <div class="titulo-seccion">Top 5 productos por monto vendido</div>
        <?php $top = collect($topPorProducto ?? [])->take(5); ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th class="center">Cant.</th>
                    <th style="width:40%;">Comparativa</th>
                    <th class="right">Monto</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $top; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $monto = (float) ($row['monto'] ?? 0);
                        $cant = (int) ($row['cantidad'] ?? 0);
                        $name = $row['nombre'] ?? '—';
                        $pct = ($maxMontoTop ?? 1) ? round(($monto / max($maxMontoTop, 1)) * 100) : 0;
                    ?>
                    <tr>
                        <td><?php echo e($name); ?></td>
                        <td class="center"><?php echo e($cant); ?></td>
                        <td>
                            <div class="bar-wrap">
                                <div class="bar" style="width: <?php echo e($pct); ?>%;"></div>
                            </div>
                        </td>
                        <td class="right">Bs <?php echo e(number_format($monto, 2)); ?></td>
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
        <div class="titulo-seccion">Control de Stock</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th class="center">Stock Inicial</th>
                    <th class="center">Ingresos</th>
                    <th class="center">Ventas</th>
                    <th class="center">Stock Final</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $controlStock; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $prod => $datos): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($prod); ?></td>
                        <td class="center"><?php echo e((int) data_get($datos, 'inicial', 0)); ?></td>
                        <td class="center"><?php echo e((int) data_get($datos, 'ingresado', 0)); ?></td>
                        <td class="center"><?php echo e((int) data_get($datos, 'vendido', 0)); ?></td>
                        <td class="center"><?php echo e((int) data_get($datos, 'final', 0)); ?></td>
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
        <div class="titulo-seccion">Totales por producto (ventas)</div>
        <?php if(!empty($totalesPorProducto)): ?>
            <ul>
                <?php $__currentLoopData = $totalesPorProducto; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $producto => $total): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($producto); ?>: Bs <?php echo e(number_format($total, 2)); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        <?php else: ?>
            <div class="muted">Sin ventas en el período.</div>
        <?php endif; ?>
    </div>

    <div class="footer">¡ACEPTA EL DESAFÍO, ROMPE LOS LÍMITES!</div>
</body>

</html><?php /**PATH C:\xampp\htdocs\Laravel\Proyecto_MPGFil\resources\views/pdf/productos_resumen.blade.php ENDPATH**/ ?>