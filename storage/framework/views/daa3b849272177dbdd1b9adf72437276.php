<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Ficha del Personal</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            color: #000000;
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
            border: 1px solid #777777;
            padding: 4px;
            font-size: 11px;
            text-align: left;
        }

        .datos {
            margin-bottom: 10px;
        }

        .foto-nombre {
            display: flex;
            align-items: center;
            gap: 20px;
            justify-content: center;
            margin-bottom: 10px;
        }

        .foto-nombre img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 2px solid #FF6600;
            object-fit: cover;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
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

    <h2>Ficha de Personal</h2>

    <?php
        $dias = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];
    ?>

    <div class="seccion">
        <div class="titulo-seccion">Datos Personales</div>

        <div style="text-align: center; margin-bottom: 15px;">
            <img src="<?php echo e($personal->foto_path_for_pdf); ?>" alt="Foto del Personal"
                style="width: 100px; height: 100px; border-radius: 50%; border: 2px solid #FF6600; object-fit: cover;">
            <p style="margin-top: 5px; font-weight: bold;"><?php echo e($personal->nombre_completo); ?></p>
        </div>

        <div class="fila">
            <div class="campo"><strong>C.I.:</strong> <?php echo e($personal->ci); ?></div>
            <div class="campo"><strong>Teléfono:</strong> <?php echo e($personal->telefono); ?></div>
        </div>

        <div class="fila">
            <div class="campo"><strong>Cargo:</strong> <?php echo e(ucfirst($personal->cargo)); ?></div>
            <div class="campo"><strong>Fecha de Contratación:</strong> <?php echo e($personal->fecha_contratacion); ?></div>
        </div>

        <div class="fila">
            <div class="campo"><strong>Fecha de nacimiento:</strong> <?php echo e($personal->fecha_de_nacimiento); ?></div>
            <div class="campo"><strong>Correo:</strong> <?php echo e($personal->correo); ?></div>
        </div>

        <div class="fila">
            <div class="campo"><strong>Dirección:</strong> <?php echo e($personal->direccion); ?></div>
        </div>
    </div>

    <div class="fila">
        <div class="campo"><strong>Fecha de nacimiento:</strong> <?php echo e($personal->fecha_de_nacimiento); ?></div>
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
        <div class="titulo-seccion">Historial de Asistencias</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Hora Entrada</th>
                    <th>Hora Salida</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $personal->asistencias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $asistencia): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($asistencia->fecha); ?></td>
                        <td><?php echo e($asistencia->hora_entrada ? $asistencia->hora_entrada->format('H:i') : '—'); ?></td>
                        <td><?php echo e($asistencia->hora_salida ? $asistencia->hora_salida->format('H:i') : '—'); ?></td>
                        <td><?php echo e(ucfirst($asistencia->estado)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>

    <div class="seccion">
        <div class="titulo-seccion">Historial de Permisos</div>
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
                            <td><?php echo e($permiso->fecha_inicio); ?></td>
                            <td><?php echo e($permiso->fecha_fin); ?></td>
                            <td><?php echo e(ucfirst($permiso->estado)); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align: center;">No tiene permisos registrados.</p>
        <?php endif; ?>
    </div>

    <div class="seccion">
        <div class="titulo-seccion">Salas Asignadas</div>
        <ul>
            <?php $__empty_1 = true; $__currentLoopData = $salasUnicas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sala): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <li><?php echo e($sala); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <li>No tiene salas asignadas.</li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="seccion">
        <div class="titulo-seccion">Pagos Realizados</div>
        <?php if($pagos->count()): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Monto</th>
                        <th>Descripción</th>
                        <th>Sala</th>
                        <th>Turno</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $pagos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pago): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($pago->fecha); ?></td>
                            <td>Bs <?php echo e(number_format($pago->monto, 2, '.', ',')); ?></td>
                            <td><?php echo e($pago->descripcion); ?></td>
                            <td><?php echo e($pago->sala->nombre ?? '-'); ?></td>
                            <td><?php echo e($pago->turno->display_horario ?? '-'); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align: center;">No hay pagos registrados.</p>
        <?php endif; ?>
    </div>

    <div class="footer">
        <p class="marca">¡ACEPTA EL DESAFÍO, ROMPE LOS LÍMITES!</p>
    </div>

</body>

</html><?php /**PATH C:\xampp\htdocs\Laravel\Proyecto_MPGFil\resources\views/pdf/ficha-personal.blade.php ENDPATH**/ ?>