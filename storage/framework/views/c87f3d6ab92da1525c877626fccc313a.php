<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Ficha del Cliente</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            color: #000;
            font-size: 12px;
            padding: 20px;
            max-width: 720px;
            margin: 0 auto
        }

        .logo {
            text-align: center;
            margin-bottom: 10px
        }

        .logo img {
            height: 60px
        }

        h2 {
            text-align: center;
            color: #FF6600;
            margin: 0 0 8px
        }

        .meta {
            margin-top: 4px;
            font-size: 11px;
            color: #555;
            text-align: center
        }

        .seccion {
            border-top: 2px solid #FF6600;
            margin-top: 15px;
            padding-top: 6px
        }

        .titulo-seccion {
            font-size: 14px;
            font-weight: bold;
            color: #FF6600;
            margin-bottom: 5px
        }

        .fila {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px
        }

        .campo {
            width: 48%
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px
        }

        .table th,
        .table td {
            border: 1px solid #777;
            padding: 4px;
            font-size: 11px;
            text-align: left;
            vertical-align: top
        }

        .footer {
            text-align: center;
            margin-top: 18px;
            font-weight: bold;
            color: #4E5054;
            font-size: 14px
        }

        .marca {
            color: #FF6600
        }

        .avatar {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #FF6600;
            display: block;
            margin: 0 auto
        }

        .muted {
            color: #666
        }
    </style>
</head>

<body>

    <div class="logo">
        <img src="<?php echo e($logo); ?>" alt="MaxPowerGym">
    </div>
    <h2>Ficha de Cliente</h2>
    <div class="meta">
        <strong>Generado por:</strong> <?php echo e($generadoPor); ?> &nbsp;|&nbsp;
        <strong>Generado el:</strong> <?php echo e($generadoEl); ?>

    </div>

    
    <div class="seccion">
        <div class="titulo-seccion">Datos personales</div>
        <div style="text-align:center;margin-bottom:12px;">
            <img src="<?php echo e($fotoPath); ?>" alt="Foto del Cliente" class="avatar">
            <p style="margin-top:6px;font-weight:bold;"><?php echo e($cliente->nombre_completo); ?></p>
        </div>
        <div class="fila">
            <div class="campo"><strong>Nombre completo:</strong> <?php echo e($cliente->nombre_completo); ?></div>
            <div class="campo"><strong>C.I.:</strong> <?php echo e($cliente->ci); ?></div>
        </div>
        <div class="fila">
            <div class="campo"><strong>Fecha de nacimiento:</strong> <?php echo e($cliente->fecha_de_nacimiento ?? '—'); ?></div>
            <div class="campo"><strong>Sexo:</strong> <?php echo e($cliente->sexo ?? '—'); ?></div>
        </div>
        <div class="fila">
            <div class="campo"><strong>Teléfono:</strong> <?php echo e($cliente->telefono ?? '—'); ?></div>
            <div class="campo"><strong>Correo:</strong> <?php echo e($cliente->correo ?? '—'); ?></div>
        </div>
        <?php if(!empty($cliente->direccion)): ?>
            <div class="fila">
                <div class="campo"><strong>Dirección:</strong> <?php echo e($cliente->direccion); ?></div>
                <div class="campo"></div>
            </div>
        <?php endif; ?>
    </div>

    
    <div class="seccion">
        <div class="titulo-seccion">Emergencias y salud</div>
        <div class="fila">
            <div class="campo"><strong>Antecedentes médicos:</strong> <?php echo e($cliente->antecedentes_medicos ?? 'Ninguno'); ?>

            </div>
            <div class="campo"></div>
        </div>
        <div class="fila">
            <div class="campo"><strong>Contacto de emergencia:</strong>
                <?php echo e($cliente->contacto_emergencia_nombre ?? '—'); ?></div>
            <div class="campo"><strong>Parentesco:</strong> <?php echo e($cliente->contacto_emergencia_parentesco ?? '—'); ?></div>
        </div>
        <div class="fila">
            <div class="campo"><strong>Celular de emergencia:</strong>
                <?php echo e($cliente->contacto_emergencia_celular ?? '—'); ?></div>
            <div class="campo"></div>
        </div>
    </div>

    
    <div class="seccion">
        <div class="titulo-seccion">Plan (vigente o más reciente)</div>
        <?php if($planVigente): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Plan</th>
                        <th>Disciplina</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                        <th>Estado</th>
                        <th class="right">Total</th>
                        <th class="right">A cuenta</th>
                        <th class="right">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo e($planVigente->plan->nombre ?? '—'); ?></td>
                        <td><?php echo e($planVigente->disciplina->nombre ?? '—'); ?></td>
                        <td><?php echo e($planVigente->fecha_inicio ?? '—'); ?></td>
                        <td><?php echo e($planVigente->fecha_final ?? '—'); ?></td>
                        <td><?php echo e(ucfirst($planVigente->estado ?? '—')); ?></td>
                        <td class="right">Bs <?php echo e(number_format((float) $planVigente->total, 2)); ?></td>
                        <td class="right">Bs <?php echo e(number_format((float) $planVigente->a_cuenta, 2)); ?></td>
                        <td class="right">Bs <?php echo e(number_format((float) $planVigente->saldo, 2)); ?></td>
                    </tr>
                </tbody>
            </table>
            <?php if($planVigente->observaciones ?? false): ?>
                <p class="muted" style="margin-top:4px;"><strong>Obs.:</strong> <?php echo e($planVigente->observaciones); ?></p>
            <?php endif; ?>
        <?php else: ?>
            <p class="muted">No registra plan vigente ni reciente.</p>
        <?php endif; ?>
    </div>

    
    <div class="seccion">
        <div class="titulo-seccion">Casillero</div>
        <?php if($cliente->casillero): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Estado</th>
                        <th>Entrega</th>
                        <th>Vence</th>
                        <th>Mét. pago</th>
                        <th class="right">Mensualidad</th>
                        <th class="right">Reposiciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo e($cliente->casillero->numero); ?></td>
                        <td><?php echo e(ucfirst($cliente->casillero->estado)); ?></td>
                        <td><?php echo e($cliente->casillero->fecha_entrega_llave ?? '—'); ?></td>
                        <td><?php echo e($cliente->casillero->fecha_final_llave ?? '—'); ?></td>
                        <td><?php echo e(strtoupper($cliente->casillero->metodo_pago ?? '—')); ?></td>
                        <td class="right">Bs <?php echo e(number_format((float) $cliente->casillero->costo_mensual, 2)); ?></td>
                        <td class="right">Bs <?php echo e(number_format((float) $cliente->casillero->monto_reposiciones, 2)); ?></td>
                    </tr>
                </tbody>
            </table>
        <?php else: ?>
            <p class="muted">🔓 No tiene casillero asignado.</p>
        <?php endif; ?>
    </div>

    <div class="footer">
        <span class="marca">¡ACEPTA EL DESAFÍO, ROMPE LOS LÍMITES!</span>
    </div>

</body>

</html><?php /**PATH C:\xampp\htdocs\Laravel\Proyecto_MPGFil\resources\views/pdf/ficha-cliente.blade.php ENDPATH**/ ?>