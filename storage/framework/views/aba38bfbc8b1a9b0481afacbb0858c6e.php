<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo e($titulo); ?></title>
    <style>
        :root{
            --brand:#FF6600;
            --ink:#000000;
            --muted:#4E5054;
            --line:#C7C7C7;
            --bg:#FFFFFF;
            --soft:#F6F6F6;
            --ok:#19a34a;
            --warn:#d9480f;
        }
        *{box-sizing:border-box}
        body{
            font-family: DejaVu Sans, sans-serif;
            font-size:12px; color:var(--ink);
            background:var(--bg);
            max-width:720px; margin:0 auto; padding:20px;
        }
        /* Header con banda de marca */
        .brand-band{
            border:1px solid var(--line);
            border-radius:10px; overflow:hidden;
            margin-bottom:14px;
        }
        .brand-top{
            background:var(--brand);
            color:#fff; text-align:center; padding:10px 12px;
        }
        .brand-top h2{margin:0; font-size:18px; letter-spacing:.3px}
        .brand-body{padding:10px 14px; background:#fff}
        .logo{ text-align:center; margin:4px 0 8px }
        .logo img{ height:60px }

        /* Meta */
        .meta{
            display:flex; justify-content:space-between; gap:12px;
            color:var(--muted); font-size:11px; margin-top:6px;
            border-top:1px dashed var(--line); padding-top:6px;
        }

        /* Tabla “card” */
        .card{
            border:1px solid var(--line); border-radius:10px;
            margin-top:14px; overflow:hidden;
        }
        .card-head{
            background:var(--soft);
            color:var(--brand); font-weight:700;
            padding:8px 12px; border-bottom:1px solid var(--line);
        }
        table{
            width:100%; border-collapse:collapse;
        }
        th,td{
            border-bottom:1px solid var(--line);
            padding:7px 8px; vertical-align:top;
        }
        th{ width:34%; color:var(--muted); font-weight:700; }
        td.right{text-align:right}
        .row:last-child th, .row:last-child td { border-bottom:none; }

        /* Badges */
        .badge{
            display:inline-block; line-height:1;
            padding:5px 8px; border-radius:999px; font-size:11px; font-weight:700;
        }
        .badge-ok{ background:#e9f7ee; color:var(--ok); border:1px solid #bfe5ca; }
        .badge-warn{ background:#fff0e6; color:var(--warn); border:1px solid #ffd8bf; }
        .badge-pay{ background:#eef2ff; color:#1e40af; border:1px solid #c7d2fe; }     /* método: QR */
        .badge-cash{ background:#fff9db; color:#8a5d00; border:1px solid #ffe58f; }   /* método: Efectivo */

        /* Firma */
        .sign{
            margin-top:28px; text-align:center; color:var(--muted);
        }
        .sign .line{
            margin:34px auto 6px; width:60%; border-top:1px solid var(--ink);
            height:0;
        }

        /* Footer lema */
        .footer{
            margin-top:16px; text-align:center; font-weight:700;
            color:var(--brand); letter-spacing:.4px;
        }

        /* Watermark leve (opcional) */
        .wm{
            position:fixed; inset:0; pointer-events:none; opacity:.04;
            font-size:90px; font-weight:900; color:var(--brand);
            display:flex; align-items:center; justify-content:center;
            transform:rotate(-18deg);
        }
    </style>
</head>
<body>
    
    <div class="brand-band">
        <div class="brand-top">
            <h2><?php echo e($titulo); ?></h2>
        </div>
        <div class="brand-body">
            <div class="logo"><img src="<?php echo e($logo); ?>" alt="MaxPowerGym"></div>
            <div class="meta">
                <div><strong>Generado por:</strong> <?php echo e($generado_por); ?></div>
                <div><strong>Fecha:</strong> <?php echo e($generado_el); ?></div>
            </div>
        </div>
    </div>

    
    <div class="card">
        <div class="card-head">Detalle del pago</div>
        <table>
            <tbody>
                <tr class="row">
                    <th>Personal</th>
                    <td><?php echo e($pago->personal->nombre_completo ?? '—'); ?></td>
                </tr>
                <tr class="row">
                    <th>Fecha del pago</th>
                    <td><?php echo e(\Carbon\Carbon::parse($pago->fecha)->format('d/m/Y')); ?></td>
                </tr>
                <tr class="row">
                    <th>Método</th>
                    <td>
                        <?php $m = strtolower($pago->metodo_pago ?? ''); ?>
                        <?php if($m === 'qr'): ?>
                            <span class="badge badge-pay">QR</span>
                        <?php elseif($m === 'efectivo'): ?>
                            <span class="badge badge-cash">Efectivo</span>
                        <?php else: ?>
                            <span class="badge"><?php echo e(strtoupper($pago->metodo_pago ?? '—')); ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr class="row">
                    <th>Monto (Bs)</th>
                    <td class="right"><strong><?php echo e(number_format($pago->monto, 2)); ?></strong></td>
                </tr>
                <tr class="row">
                    <th>Estado</th>
                    <td>
                        <?php if($pago->pagado): ?>
                            <span class="badge badge-ok">Pagado</span>
                        <?php else: ?>
                            <span class="badge badge-warn">Pendiente</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr class="row">
                    <th>Turno</th>
                    <td><?php echo e($pago->turno->nombre ?? '—'); ?><?php echo e($pago->turno?->dia_nombre ? ' ('.$pago->turno->dia_nombre.')' : ''); ?></td>
                </tr>
                <tr class="row">
                    <th>Sala</th>
                    <td><?php echo e($pago->sala->nombre ?? '—'); ?></td>
                </tr>
                <tr class="row">
                    <th>Observaciones</th>
                    <td><?php echo e($pago->descripcion ?: '—'); ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    
    <div class="sign">
        <div class="line"></div>
        <div>Firma de conformidad</div>
    </div>

    
    <div class="footer">¡ACEPTA EL DESAFÍO, ROMPE LOS LÍMITES!</div>

    
    
</body>
</html>
<?php /**PATH C:\xampp\htdocs\Laravel\Proyecto_MPGFil\resources\views/pdf/pago_comprobante.blade.php ENDPATH**/ ?>