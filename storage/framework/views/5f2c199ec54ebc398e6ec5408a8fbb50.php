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
            margin: 6px 0 10px;
        }

        .muted {
            color: #666;
            font-size: 11px;
        }

        .card {
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 8px;
        }

        .grid-2 {
            display: table;
            width: 100%;
        }

        .grid-2 .col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 4px 6px;
        }

        .kpis {
            display: table;
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        .kpis .kpi {
            display: table-cell;
            width: 20%;
            text-align: center;
            padding: 8px;
            border: 1px solid #777;
        }

        .kpi .label {
            font-size: 10px;
            color: #666;
        }

        .kpi .val {
            font-size: 15px;
            font-weight: 700;
        }

        .seccion {
            border-top: 2px solid #FF6600;
            margin-top: 14px;
            padding-top: 6px;
        }

        .titulo-seccion {
            font-size: 14px;
            font-weight: bold;
            color: #FF6600;
            margin-bottom: 6px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
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

        /* Barras comparativas */
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

        .foto {
            width: 100%;
            text-align: center;
        }

        .foto img {
            max-height: 120px;
            max-width: 100%;
            object-fit: contain;
            border: 1px solid #ddd;
            border-radius: 6px;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
        }

        .badge-ok {
            background: #e6f7e6;
            color: #237804;
            border: 1px solid #b7eb8f;
        }

        .badge-warn {
            background: #fff7e6;
            color: #ad6800;
            border: 1px solid #ffd591;
        }

        .badge-danger {
            background: #fff1f0;
            color: #a8071a;
            border: 1px solid #ffa39e;
        }

        .footer {
            text-align: center;
            margin-top: 16px;
            font-size: 13px;
            font-weight: bold;
            color: #FF6600;
        }
    </style>
</head>

<body>
    <div class="logo">
        <img src="<?php echo e(public_path('images/LogosMPG/Recurso 3.png')); ?>" alt="MaxPowerGym">
    </div>
    <h2>Ficha de Producto</h2>

    <div class="muted">
        <div><strong>Generado por:</strong> <?php echo e($generado_por); ?></div>
        <div><strong>Generado el:</strong> <?php echo e($generado_el); ?></div>
    </div>

    
    <div class="seccion">
        <div class="grid-2">
            <div class="col">
                <div class="card">
                    <div class="foto">
                        <?php if($imgPath): ?>
                            <img src="<?php echo e($imgPath); ?>" alt="Producto">
                        <?php else: ?>
                            <div class="muted">Sin imagen</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card">
                    <strong>Nombre:</strong> <?php echo e($producto->nombre); ?><br>
                    <strong>Categoría:</strong> <?php echo e(optional($producto->categoria)->nombre ?? '—'); ?><br>
                    <strong>Perecedero:</strong>
                    <?php if($producto->es_perecedero): ?>
                        <span class="badge badge-warn">Sí</span>
                    <?php else: ?>
                        <span class="badge badge-ok">No</span>
                    <?php endif; ?>
                    <br>
                    <strong>Unid./Paquete:</strong> <?php echo e((int) ($producto->unidades_por_paquete ?? 0)); ?><br>
                    <strong>P. Venta Unitario:</strong> Bs <?php echo e(number_format($producto->precio_unitario ?? 0, 2)); ?><br>
                    <strong>P. Venta Paquete:</strong> Bs <?php echo e(number_format($producto->precio_paquete ?? 0, 2)); ?><br>
                    <strong>Registrado por:</strong> <?php echo e(optional($producto->registradoPor)->name ?? '—'); ?><br>
                    <strong>Modificado por:</strong> <?php echo e(optional($producto->modificadoPor)->name ?? '—'); ?>

                </div>
            </div>
        </div>
    </div>

    
    <div class="seccion">
        <div class="titulo-seccion">Resumen de Inventario</div>
        <div class="kpis">
            <div class="kpi">
                <div class="label">Lotes totales</div>
                <div class="val"><?php echo e($totalLotes); ?></div>
            </div>
            <div class="kpi">
                <div class="label">Stock (unid.)</div>
                <div class="val"><?php echo e(number_format($stockUnidades)); ?></div>
            </div>
            <div class="kpi">
                <div class="label">Stock (paq.)</div>
                <div class="val"><?php echo e(number_format($stockPaquetes)); ?></div>
            </div>
            <div class="kpi">
                <div class="label">Lotes vigentes</div>
                <div class="val"><?php echo e($lotesVigentes); ?></div>
            </div>
            <div class="kpi">
                <div class="label">Lotes vencidos</div>
                <div class="val"><?php echo e($lotesVencidos); ?></div>
            </div>
        </div>
        <p class="muted" style="margin-top:6px;">
            <strong>Próximo vencimiento:</strong>
            <?php if($proxVencimiento): ?>
                <?php echo e(\Carbon\Carbon::parse($proxVencimiento)->isoFormat('D [de] MMM YYYY')); ?>

                <?php if(!is_null($diasProxVenc)): ?>
                    <?php
                        $badgeClass = $diasProxVenc <= 7 ? 'badge-warn' : 'badge-ok';
                        if ($diasProxVenc < 0)
                            $badgeClass = 'badge-danger';
                    ?>
                    <span class="badge <?php echo e($badgeClass); ?>">
                        <?php if($diasProxVenc >= 0): ?>
                            en <?php echo e($diasProxVenc); ?> día(s)
                        <?php else: ?>
                            vencido hace <?php echo e(abs($diasProxVenc)); ?> día(s)
                        <?php endif; ?>
                    </span>
                <?php endif; ?>
            <?php else: ?>
                —
            <?php endif; ?>
        </p>
    </div>

    
    <div class="seccion">
        <div class="titulo-seccion">Lotes del producto</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Ingreso</th>
                    <th>Vence</th>
                    <th class="center">Stock (u)</th>
                    <th class="center">Stock (paq)</th>
                    <th class="right">P. Unit</th>
                    <th class="right">P. Paq</th>
                    <th class="center">Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $lotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $vence = $l->fecha_vencimiento ? \Carbon\Carbon::parse($l->fecha_vencimiento) : null;
                        $estado = 'Vigente';
                        $badge = 'badge-ok';
                        if ($vence && $vence->lt(\Carbon\Carbon::today())) {
                            $estado = 'Vencido';
                            $badge = 'badge-danger';
                        } elseif ($vence && $vence->diffInDays(\Carbon\Carbon::today(), false) * -1 <= 7) {
                            $estado = 'Por vencer';
                            $badge = 'badge-warn';
                        }
                    ?>
                    <tr>
                        <td><?php echo e(optional($l->fecha_ingreso)->format('d/m/Y') ?? '—'); ?></td>
                        <td><?php echo e($vence ? $vence->format('d/m/Y') : ($producto->es_perecedero ? '—' : 'No aplica')); ?></td>
                        <td class="center"><?php echo e((int) ($l->stock_unidades ?? 0)); ?></td>
                        <td class="center"><?php echo e((int) ($l->stock_paquetes ?? 0)); ?></td>
                        <td class="right">Bs <?php echo e(number_format($l->precio_unitario ?? 0, 2)); ?></td>
                        <td class="right">Bs <?php echo e(number_format($l->precio_paquete ?? 0, 2)); ?></td>
                        <td class="center"><span class="badge <?php echo e($badge); ?>"><?php echo e($estado); ?></span></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="7" class="center">Sin lotes registrados.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    
    <div class="seccion">
        <div class="titulo-seccion">Últimos ingresos</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th class="center">Unid.</th>
                    <th class="right">P. Unit</th>
                    <th class="center">Paq.</th>
                    <th class="right">P. Paq</th>
                    <th class="center">Vence</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $ingresosTabla; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e(optional($i->fecha)->format('d/m/Y H:i') ?? '—'); ?></td>
                        <td class="center"><?php echo e((int) ($i->cantidad_unidades ?? 0)); ?></td>
                        <td class="right">Bs <?php echo e(number_format($i->precio_unitario ?? 0, 2)); ?></td>
                        <td class="center"><?php echo e((int) ($i->cantidad_paquetes ?? 0)); ?></td>
                        <td class="right">Bs <?php echo e(number_format($i->precio_paquete ?? 0, 2)); ?></td>
                        <td class="center"><?php echo e(optional($i->fecha_vencimiento)->format('d/m/Y') ?? '—'); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="6" class="center">Sin ingresos recientes.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <p class="muted" style="margin-top:6px;">
            <strong>Total ingresos (recientes):</strong> <?php echo e(number_format($totalUnidIngresadas)); ?> unid. /
            <?php echo e(number_format($totalPaqIngresados)); ?> paq.
            <?php if($ultimoIngreso): ?> — <strong>Último:</strong>
            <?php echo e(\Carbon\Carbon::parse($ultimoIngreso)->isoFormat('D [de] MMM YYYY, HH:mm')); ?> <?php endif; ?>
        </p>
    </div>

    
    <div class="seccion">
        <div class="titulo-seccion">Últimas ventas</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th class="center">Cant.</th>
                    <th class="right">P. Unit</th>
                    <th class="right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $ventasTabla; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e(optional($v->created_at)->format('d/m/Y H:i') ?? '—'); ?></td>
                        <td class="center"><?php echo e((int) ($v->cantidad ?? 0)); ?></td>
                        <td class="right">Bs <?php echo e(number_format($v->precio_unitario ?? 0, 2)); ?></td>
                        <td class="right">Bs <?php echo e(number_format($v->subtotal ?? 0, 2)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="4" class="center">Sin ventas recientes.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <p class="muted" style="margin-top:6px;">
            <strong>Total vendido (reciente):</strong> <?php echo e(number_format($totalUnidVendidas)); ?> unid. —
            <strong>Monto:</strong> Bs <?php echo e(number_format($montoVendido, 2)); ?>

            <?php if($ultimaVenta): ?> — <strong>Última:</strong>
            <?php echo e(\Carbon\Carbon::parse($ultimaVenta)->isoFormat('D [de] MMM YYYY, HH:mm')); ?> <?php endif; ?>
        </p>
    </div>

    
    <div class="seccion">
        <div class="titulo-seccion">Ventas por mes (últimos 12)</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Mes</th>
                    <th class="center">Cant.</th>
                    <th style="width:40%;">Comparativa</th>
                    <th class="right">Monto</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $ventasPorMes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $pct = $maxMontoMes ? round((($row['monto'] ?? 0) / $maxMontoMes) * 100) : 0;
                    ?>
                    <tr>
                        <td><?php echo e($row['etiqueta']); ?></td>
                        <td class="center"><?php echo e((int) ($row['cantidad'] ?? 0)); ?></td>
                        <td>
                            <div class="bar-wrap">
                                <div class="bar" style="width: <?php echo e($pct); ?>%;"></div>
                            </div>
                        </td>
                        <td class="right">Bs <?php echo e(number_format($row['monto'] ?? 0, 2)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>

    <div class="footer">¡ACEPTA EL DESAFÍO, ROMPE LOS LÍMITES!</div>
</body>

</html><?php /**PATH C:\xampp\htdocs\Laravel\Proyecto_MPGFil\resources\views/pdf/ficha_producto.blade.php ENDPATH**/ ?>