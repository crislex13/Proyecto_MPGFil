<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Ficha del Cliente</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            color: #000000;
            font-size: 12px;
        }

        .logo {
            text-align: center;
            margin-bottom: 10px;
        }

        .logo img {
            height: 60px;
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

        .datos {
            margin-bottom: 10px;
        }

        .fila {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }

        .campo {
            width: 48%;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .table th,
        .table td {
            border: 1px solid #777777;
            padding: 4px;
            font-size: 11px;
            text-align: left;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            font-weight: bold;
            color: #4E5054;
            font-size: 14px;
        }

        .marca {
            color: #FF6600;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <div class="logo">
        <img src="<?php echo e(public_path('images/LogosMPG/Recurso 3.png')); ?>" alt="MaxPowerGym">
    </div>

    <h2 style="text-align: center; color: #FF6600;">Ficha de Cliente</h2>

    <div class="seccion">
        <div class="titulo-seccion">Datos Personales</div>
        <div class="datos">
            <div style="text-align: center; margin-bottom: 20px;">
                <img src="<?php echo e($cliente->foto_path_for_pdf); ?>" alt="Foto del Cliente"
                    style="width: 100px; height: 100px; object-fit: cover; border-radius: 50%; border: 2px solid #FF6600; display: block; margin: 0 auto;">
                <p style="margin-top: 5px; font-weight: bold;"><?php echo e($cliente->nombre_completo); ?></p>
            </div>
            <div class="fila">
                <div class="campo"><strong>Nombre Completo:</strong> <?php echo e($cliente->nombre_completo); ?></div>
                <div class="campo"><strong>C.I.:</strong> <?php echo e($cliente->ci); ?></div>
            </div>
            <div class="fila">
                <div class="campo"><strong>Fecha de nacimiento:</strong> <?php echo e($cliente->fecha_de_nacimiento); ?></div>
                <div class="campo"><strong>Sexo:</strong> <?php echo e($cliente->sexo); ?></div>
            </div>
            <div class="fila">
                <div class="campo"><strong>Teléfono:</strong> <?php echo e($cliente->telefono); ?></div>
                <div class="campo"><strong>Correo:</strong> <?php echo e($cliente->correo); ?></div>
            </div>
        </div>
    </div>

    <div class="seccion">
        <div class="titulo-seccion">Emergencias y Salud</div>
        <div class="datos">
            <div class="fila">
                <div class="campo"><strong>Antecedentes Médicos:</strong>
                    <?php echo e($cliente->antecedentes_medicos ?? 'Ninguno'); ?></div>
            </div>
            <div class="fila">
                <div class="campo"><strong>Nombre de contacto:</strong> <?php echo e($cliente->contacto_emergencia_nombre); ?></div>
                <div class="campo"><strong>Parentesco:</strong> <?php echo e($cliente->contacto_emergencia_parentesco); ?></div>
            </div>
            <div class="fila">
                <div class="campo"><strong>Celular:</strong> <?php echo e($cliente->contacto_emergencia_celular); ?></div>
            </div>
        </div>
    </div>

    <div class="seccion">
        <div class="titulo-seccion">Historial de Planes</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Plan</th>
                    <th>Inicio</th>
                    <th>Fin</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $cliente->planesCliente; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($plan->plan->nombre); ?></td>
                        <td><?php echo e($plan->fecha_inicio); ?></td>
                        <td><?php echo e($plan->fecha_final); ?></td>
                        <td><?php echo e($plan->estado); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>

    <div class="seccion">
        <div class="titulo-seccion">Sesiones Adicionales</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Instructor</th>
                    <th>Disciplina</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $cliente->sesionesAdicionales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sesion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($sesion->fecha); ?></td>
                        <td><?php echo e($sesion->instructor->nombre_completo ?? '-'); ?></td>
                        <td><?php echo e($sesion->tipo_sesion); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>

    <div class="seccion">
        <div class="titulo-seccion">Casillero</div>
        <?php if($cliente->casillero): ?>
            <div class="fila">
                <div class="campo"><strong>Nº Casillero:</strong> <?php echo e($cliente->casillero->numero); ?></div>
                <div class="campo"><strong>Estado:</strong> <?php echo e($cliente->casillero->estado); ?></div>
            </div>
            <div class="fila">
                <div class="campo"><strong>Fecha de Entrega:</strong> <?php echo e($cliente->casillero->fecha_entrega_llave); ?></div>
                <div class="campo"><strong>Fecha de Vencimiento:</strong> <?php echo e($cliente->casillero->fecha_final_llave); ?></div>
            </div>
        <?php else: ?>
            <p>🔓 No tiene casillero asignado.</p>
        <?php endif; ?>
    </div>

    <div class="seccion">
        <div class="titulo-seccion">Historial de Asistencias</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Tipo</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $cliente->asistencias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $asistencia): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($asistencia->fecha); ?></td>
                        <td>
                            <?php echo e($asistencia->hora_entrada ? \Carbon\Carbon::parse($asistencia->hora_entrada)->format('H:i') : '—'); ?>

                        </td>
                        <td><?php echo e(ucfirst($asistencia->tipo_asistencia)); ?></td>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p class="marca">¡ACEPTA EL DESAFÍO, ROMPE LOS LÍMITES!</p>
    </div>

</body>

</html><?php /**PATH C:\xampp\htdocs\Laravel\Proyecto_MPGFil\resources\views/pdf/ficha-cliente.blade.php ENDPATH**/ ?>