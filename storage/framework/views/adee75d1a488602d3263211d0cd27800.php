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
            margin: 4px 0 8px
        }

        .periodo {
            text-align: center;
            font-size: 12px;
            margin-bottom: 8px
        }

        .meta {
            font-size: 11px;
            color: #555;
            text-align: center;
            margin-bottom: 10px
        }

        .seccion {
            border-top: 2px solid #FF6600;
            margin-top: 12px;
            padding-top: 6px
        }

        .titulo-seccion {
            font-size: 14px;
            font-weight: bold;
            color: #FF6600;
            margin-bottom: 6px
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px
        }

        .table th,
        .table td {
            border: 1px solid #777;
            padding: 5px;
            font-size: 11px;
            text-align: left
        }

        .muted {
            color: #666;
            font-size: 11px
        }

        .right {
            text-align: right
        }
    </style>
</head>

<body>
    <div class="logo">
        <img src="<?php echo e(public_path('images/LogosMPG/Recurso 3.png')); ?>" alt="MaxPowerGym">
    </div>
    <h2><?php echo e($titulo); ?></h2>
    <div class="periodo"><?php echo e($periodo); ?></div>
    <div class="meta">Generado el: <?php echo e($generado_el); ?></div>

    <div class="seccion">
        <div class="titulo-seccion">Resumen de Categorías</div>
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Creada</th>
                    <th class="right">Productos</th>
                    <th class="right">Lotes</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $categorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($i + 1); ?></td>
                        <td><?php echo e($c->nombre); ?></td>
                        <td><?php echo e($c->descripcion); ?></td>
                        <td><?php echo e($c->created_at ? \Carbon\Carbon::parse($c->created_at)->format('Y-m-d') : '—'); ?></td>
                        <td class="right"><?php echo e(number_format($c->productos_count)); ?></td>
                        <td class="right"><?php echo e(number_format($c->lotes_count)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="6" class="muted">Sin categorías.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html><?php /**PATH C:\xampp\htdocs\Laravel\Proyecto_MPGFil\resources\views/pdf/categorias_resumen.blade.php ENDPATH**/ ?>