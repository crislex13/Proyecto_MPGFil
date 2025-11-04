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

        /* Barras simples compatibles con DomPDF */
        .bar-wrap { background: #eee; height: 10px; width: 100%; border-radius: 4px; overflow: hidden; }
        .bar     { background: #FF6600; height: 10px; }

        .footer { text-align: center; margin-top: 18px; font-size: 13px; font-weight: bold; color: #FF6600; }
        .muted { color: #666; font-size: 11px; }

        /* Metadatos */
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
            <div class="kpi"><div class="label">Contratos</div><div class="val"><?php echo e(number_format($contratos)); ?></div></div>
            <div class="kpi"><div class="label">Facturado (Total)</div><div class="val">Bs <?php echo e(number_format($facturado, 2)); ?></div></div>
            <div class="kpi"><div class="label">Cobrado (A cuenta)</div><div class="val">Bs <?php echo e(number_format($cobrado, 2)); ?></div></div>
            <div class="kpi"><div class="label">Saldo Pendiente</div><div class="val">Bs <?php echo e(number_format($saldo, 2)); ?></div></div>
        </div>
        <p class="muted" style="margin-top:6px;">
            <strong>Nuevos:</strong> <?php echo e($nuevos); ?> &nbsp;|&nbsp; <strong>Renovaciones:</strong> <?php echo e($renovaciones); ?>

        </p>
    </div>

    
    <div class="seccion">
        <div class="titulo-seccion">Top 5 planes por cantidad de contratos</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Plan</th>
                    <th class="center">Contratos</th>
                    <th style="width:40%;">Comparativa</th>
                    <th class="right">Cobrado</th>
                    <th class="right">Saldo</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $topPlanes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $planNombre = $row->plan_nombre ?? '—';
                        $pct = $maxContratosPlan ? round(($row->contratos / $maxContratosPlan) * 100) : 0;
                    ?>
                    <tr>
                        <td><?php echo e($planNombre); ?></td>
                        <td class="center"><?php echo e($row->contratos); ?></td>
                        <td><div class="bar-wrap"><div class="bar" style="width: <?php echo e($pct); ?>%;"></div></div></td>
                        <td class="right">Bs <?php echo e(number_format($row->cobrado, 2)); ?></td>
                        <td class="right">Bs <?php echo e(number_format($row->saldo, 2)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="5" class="center">Sin datos en el período.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    
    <div class="seccion">
        <div class="titulo-seccion">Distribución por estado</div>
        <table class="table">
            <thead><tr><th>Estado</th><th class="center">Cantidad</th></tr></thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $porEstado; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $estado => $cant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr><td><?php echo e(ucfirst($estado)); ?></td><td class="center"><?php echo e($cant); ?></td></tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="2" class="center">Sin datos.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    
    <div class="seccion">
        <div class="titulo-seccion">Métodos de pago</div>
        <table class="table">
            <thead><tr><th>Método</th><th class="center">Contratos</th><th class="right">Cobrado</th></tr></thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $metodosPago; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e(strtoupper($m->metodo_pago ?? '—')); ?></td>
                        <td class="center"><?php echo e($m->cantidad); ?></td>
                        <td class="right">Bs <?php echo e(number_format($m->cobrado, 2)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="3" class="center">Sin datos.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    
    <div class="seccion">
        <div class="titulo-seccion">Contratos por disciplina</div>
        <table class="table">
            <thead><tr><th>Disciplina</th><th class="center">Contratos</th><th class="right">Cobrado</th></tr></thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $porDisciplina; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($d->disciplina_nombre ?? '—'); ?></td>
                        <td class="center"><?php echo e($d->contratos); ?></td>
                        <td class="right">Bs <?php echo e(number_format($d->cobrado, 2)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="3" class="center">Sin datos.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    
    <div class="seccion">
        <div class="titulo-seccion">Detalle de contratos del período</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th><th>Cliente</th><th>Plan</th><th>Disciplina</th>
                    <th class="right">Total</th><th class="right">A cuenta</th><th class="right">Saldo</th><th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $detalle; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e(\Carbon\Carbon::parse($pc->fecha_inicio)->format('Y-m-d')); ?></td>
                        <td><?php echo e($pc->cliente?->nombre_completo ?? '—'); ?></td>
                        <td><?php echo e($pc->plan?->nombre ?? '—'); ?></td>
                        <td><?php echo e($pc->disciplina?->nombre ?? '—'); ?></td>
                        <td class="right">Bs <?php echo e(number_format($pc->total, 2)); ?></td>
                        <td class="right">Bs <?php echo e(number_format($pc->a_cuenta, 2)); ?></td>
                        <td class="right">Bs <?php echo e(number_format($pc->saldo, 2)); ?></td>
                        <td><?php echo e(ucfirst($pc->estado)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="8" class="center">Sin registros.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="footer">¡ACEPTA EL DESAFÍO, ROMPE LOS LÍMITES!</div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\Laravel\Proyecto_MPGFil\resources\views/pdf/planes_resumen.blade.php ENDPATH**/ ?>